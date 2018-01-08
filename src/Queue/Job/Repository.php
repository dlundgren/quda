<?php

/**
 * @file
 * Contains Quda\Queue\Job\Repository
 */

namespace Quda\Queue\Job;

use Quda\Exception\EntityNotFound;
use Quda\Utility\ObjectCache;
use Quda\Utility\Conversion;
use Doctrine\DBAL\Connection;

/**
 * Repository class for viewing the jobs
 *
 * This is required as the queue engine only knows how to perform as a queue and not peek inside.
 *
 * @package Quda\Queue
 */
class Repository
{
	/**
	 * @var Connection
	 */
	private $db;

	/**
	 * @var string
	 */
	private $jobTable;

	public function __construct(Connection $db, $jobTable)
	{
		$this->db       = $db;
		$this->jobTable = $jobTable;
	}

	/**
	 * Updates the job with the exception
	 *
	 * @param JobItem    $job
	 * @param \Exception $exception
	 * @throws \Doctrine\DBAL\DBALException
	 */
	public function updateJobWithException(JobItem $job, \Exception $exception)
	{
		$data              = $job->data();
		$data['exception'] = [
			'class'   => get_class($exception),
			'message' => $exception->getMessage(),
			'code'    => $exception->getCode(),
			'line'    => $exception->getLine(),
			'file'    => $exception->getFile(),
			'trace'   => $exception->getTrace(),
		];

		$this->db->update($this->jobTable, ['lock_id' => '-1', 'data' => json_encode($data)], ['id' => $job->id()]);
	}

	/**
	 * Create the Job from the Database data
	 *
	 * @param $row
	 * @return JobItem
	 */
	public static function createFromDatabase($row)
	{
		$row  = array_map('trim', $row);
		$data = json_decode($row['data'], true);

		return new JobItem(
			$row['id'],
			$row['queue'],
			$data,
			Conversion::convertToDateTimeOrNull($row['started_at']),
			Conversion::convertToDateTimeOrNull($row['expires_at']),
			Conversion::convertToDateTimeOrNull($row['delay_until']),
			$row['lock_id'],
			(int)$row['attempts']
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($id)
	{
		$key = self::generateCacheKey($id);

		if (!ObjectCache::has($key)) {
			$row = $this->fetchById($id);
			if (empty($row)) {
				throw new EntityNotFound("Job id: $key");
			}

			ObjectCache::set($key, self::createFromDatabase($row));
		}

		return ObjectCache::get($key);
	}

	/**
	 * Resets the job so it will be visible again
	 *
	 * @param JobItem $jobItem
	 * @throws \Doctrine\DBAL\DBALException
	 */
	public function reset(JobItem $jobItem)
	{
		$data = [
			'started_at' => null,
			'lock_id'    => null,
			'expires_at' => null,
			'delay_until' => null,
		];

		$item = $jobItem->data();
		if (isset($item['exception'])) {
			unset($item['exception']);
		}

		$data['data'] = json_encode($item);

		$this->db->update($this->jobTable, $data, ['id' => $jobItem->id()]);
	}

	/**
	 * Deletes the job from the queue
	 *
	 * @param JobItem $job
	 * @throws \Doctrine\DBAL\DBALException
	 * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
	 */
	public function remove(JobItem $job)
	{
		$id  = $job->id();
		$key = self::generateCacheKey($id);

		ObjectCache::has($key) && ObjectCache::remove($key);
		$this->db->delete($this->jobTable, ['id' => $id]);
	}

	/**
	 * Returns a list of all jobs
	 *
	 * @return Collection
	 */
	public function findAll()
	{
		return new Collection($this->db);
	}

	/**
	 * Returns a list of all running jobs
	 *
	 * @param $queue
	 * @return Collection
	 */
	public function findAllRunningJobs($queue)
	{
		return new Collection($this->db, ['locked' => 1, 'queue' => $queue]);
	}

	/**
	 * Returns a list of all pending jobs
	 *
	 * @param $queue
	 * @return Collection
	 */
	public function findAllPendingJobs($queue)
	{
		return new Collection($this->db, ['locked' => 0, 'queue' => $queue, 'delay_until' => 'NULL']);
	}

	/**
	 * Returns a list of all delayed jobs
	 *
	 * @param $queue
	 * @return Collection
	 */
	public function findAllDelayedJobs($queue)
	{
		return new Collection($this->db, ['locked' => 0, 'queue' => $queue, 'delay_until' => 'NOT NULL']);
	}

	/**
	 * Returns a list of all failed jobs
	 *
	 * @param $queue
	 * @return Collection
	 */
	public function findAllFailedJobs($queue)
	{
		return new Collection($this->db, ['queue' => $queue, 'failed' => 1]);
	}

	/**
	 * Generates a cache key
	 *
	 * @param $id
	 * @return string
	 */
	private static function generateCacheKey($id)
	{
		return "queue-job.{$id}";
	}

	/**
	 * Internal function to grab the raw job data by id
	 *
	 * @param $id
	 * @return array
	 * @throws \Doctrine\DBAL\DBALException
	 */
	private function fetchById($id)
	{
		$row = $this->db->fetchAssoc("SELECT * FROM {$this->jobTable} WHERE id=:id", ['id' => $id]);

		return $row;
	}
}