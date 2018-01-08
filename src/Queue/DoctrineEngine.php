<?php

/**
 * @file
 * Contains Quda\Queue\DoctrineEngine
 */

namespace Quda\Queue;

use Quda\Vendor\Doctrine\Type\UuidBinary;
use Quda\Utility\Utility;
use Doctrine\DBAL\Connection;
use josegonzalez\Queuesadilla\Engine\Base;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidFactory;

/**
 * Doctrine engine for Queuesadilla
 *
 * @package Application\Vendor\Queuesadilla
 */
class DoctrineEngine
	extends Base
{
	const JOB_NAME_FIELD = 'class';
	const FIELD_HOSTNAME = 'hostname';
	const FIELD_JOB_DATA = 'data';

	/**
	 * @var Connection
	 */
	private $db;

	/**
	 * @var UuidFactory
	 */
	private $uuidFactory;

	/**
	 * @var string
	 */
	private $hostname;

	/**
	 * @var string
	 */
	private $table;

	public function __construct(Connection $conn,
								LoggerInterface $logger = null,
								UuidFactory $uuidFactory,
								array $config)
	{
		parent::__construct($logger, $config);

		$this->db          = $conn;
		$this->uuidFactory = $uuidFactory;
		$this->hostname    = Utility::hostname();

		$this->table = $this->config('table');
		if (empty($this->table)) {
			die('table not defined');
		}
	}

	/**
	 * Reconnects to the server
	 */
	public function reconnect()
	{
		$this->db->isConnected() && $this->db->close();
		$this->db->connect();
	}

	/**
	 * {@inheritdoc}
	 */
	public function connect()
	{
		$this->db->connect();

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function acknowledge($item)
	{
		if (!parent::acknowledge($item)) {
			return false;
		}

		return $this->db->delete($this->table, ['id' => (int)$item['id']]) === 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function reject($item)
	{
		if (!parent::acknowledge($item)) {
			return false;
		}

		return $this->release($item, ['mark_failed' => true]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function pop($options = [])
	{
		$queue    = $this->setting($options, 'queue', '*');
		$nextDate = (new \DateTime)->format('Y-m-d H:i:s');
		$platform = $this->db->getDatabasePlatform();
		$lockId   = $this->uuidFactory->uuid1();
		$lockSql  = $this->db->createQueryBuilder();
		$lockSql->update($this->table)
				->set("started_at", $platform->quoteStringLiteral($nextDate))
				->where('lock_id IS NULL')
				->andWhere('expires_at IS NULL OR expires_at > :next_date')
				->andWhere('delay_until IS NULL OR delay_until > :next_date');

		UuidBinary::addSetToQuery($lockSql, 'lock_id', $lockId, $platform);

		if ($queue === '*' || (is_array($queue) && in_array('*', $queue))) {
			$queue = [];
		}
		elseif (!is_array($queue)) {
			$queue = [$queue];
		}

		if (!empty($queue)) {
			$queueWhere = sprintf('queue IN (%s)', join(',', array_map([$platform, 'quoteStringLiteral'], $queue)));
			$lockSql->andWhere($queueWhere);
		}

		$lockSql->setParameter('next_date', $nextDate);

		// @TODO figure out a db agnostic way of handling this
		$updated = $this->db->executeUpdate($lockSql->getSQL() . ' LIMIT 1', $lockSql->getParameters(), $lockSql->getParameterTypes());
		if ($updated == 1) {
			$where = UuidBinary::buildWhereClause('lock_id', $lockId, $platform);
			$row   = $this->db->fetchAssoc("SELECT * FROM {$this->table} WHERE {$where}");
			$data  = json_decode($row['data'], true);

			return [
				'@meta'    => $row,
				'id'       => $row['id'],
				'class'    => $data['class'],
				'args'     => $data['args'],
				'queue'    => $row['queue'],
				'options'  => $data['options'],
				'attempts' => (int)$row['attempts']
			];
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function push($item, $options = [])
	{
		if (!is_array($options)) {
			$options = ['queue' => $options];
		}

		$queue     = $this->setting($options, 'queue');
		$delay     = $this->setting($options, 'delay');
		$expiresIn = $this->setting($options, 'expires_in');

		unset($options['queue']);
		unset($options['attempts']);
		$item['options']                   = $options;
		$item['options']['attempts_delay'] = $this->setting($options, 'attempts_delay');

		$count = $this->db->insert(
			$this->table,
			[
				'data'        => json_encode($item),
				'queue'       => $queue,
				'expires_at'  => $expiresIn === null ? null : $this->generateFutureDate($expiresIn),
				'delay_until' => $delay === null ? null : $this->generateFutureDate($delay),
				'attempts'    => isset($item['attempts']) ? $item['attempts'] : null,
			]);

		if ($count == 1) {
			$this->lastJobId = $this->db->lastInsertId();
		}

		return $count == 1;
	}

	/**
	 * {@inheritDoc}
	 */
	public function queues()
	{
		$sth     = $this->db->createQueryBuilder()
							->select('queue')
							->from($this->table)
							->groupBy('queue')
							->execute();
		$results = $sth->fetchAll(\PDO::FETCH_ASSOC);
		if (empty($results)) {
			return [];
		}

		return array_map(
			function ($result) {
				return trim($result['queue']);
			}, $results);
	}

	/**
	 * {@inheritdoc}
	 */
	public function release($item, $options = [])
	{
		$doReject = true;
		$data     = ['lock_id' => null];
		$meta     = [];

		if (isset($item['@meta'])) {
			$meta = $item['@meta'];
			unset($item['@meta']);
		}

		if (!isset($meta['id'])) {
			throw new \Exception("Missing id from :" . json_encode($meta));
		}

		$encoded = json_encode($item, JSON_UNESCAPED_UNICODE);
		if (!$encoded) {
			throw new \Exception(json_last_error_msg());
		}
		$data['data'] = $encoded;

		if (isset($options['mark_failed'])) {
			$data['lock_id'] = '-1';
		}
		else {
			if (isset($meta['delayUntil'])) {
				$data['delay_until'] = $this->generateFutureDate($meta['delayUntil']);
			}

			if (isset($meta['attempts']) && $meta['attempts'] > 0) {
				$data['attempts'] = $meta['attempts'];
				$doReject         = false;
			}
		}

		$count = $this->db->update($this->table, $data, ['id' => (int)$meta['id']]);

//		$doReject && $this->reject($item);

		return $count == 1;
	}

	/**
	 * Generates a date in the future
	 *
	 * @param int $seconds
	 * @return string
	 */
	private function generateFutureDate($seconds)
	{
		return (new \DateTime())->add(new \DateInterval(sprintf('PT % sS', $seconds)))->format('Y - m - d H:i:s');
	}
}