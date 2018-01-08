<?php

/**
 * @file
 * Contains Quda\Vendor\Doctrine\Collection\AbstractCollection
 */

namespace Quda\Vendor\Doctrine\Collection;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

abstract class AbstractCollection
{
	/**
	 * @var \DateTimeZone
	 */
	protected static $utc;

	/**
	 * The Doctrine Connection
	 *
	 * @var Connection
	 */
	protected $db;

	/**
	 * List of criteria to filter by
	 *
	 * @var array
	 */
	protected $criteria;

	/**
	 * Total objects
	 *
	 * @var int
	 */
	protected $totalCount;

	/**
	 * @var QueryBuilder
	 */
	protected $query;

	/**
	 * The field used for COUNT()
	 *
	 * @var string
	 */
	protected $countField;

	/**
	 * The items in the collection's current count
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * The position in the collection
	 *
	 * @var int
	 */
	protected $cursor = 0;

	/**
	 * The table to select data from
	 *
	 * @var string
	 */
	protected $table;

	public function __construct(Connection $db, $criteria = [])
	{
		$this->db       = $db;
		$this->criteria = $criteria;
	}

	/**
	 * Returns a list of all items
	 *
	 * @return array|\Traversable
	 */
	public function items()
	{
		return $this->slice(0);
	}

	/**
	 * Returns an slice of the results.
	 *
	 * @param integer $offset The offset.
	 * @param integer $length The length.
	 *
	 * @return array|\Traversable The slice.
	 */
	public function slice($offset, $length = null)
	{
		if (!isset($this->query)) {
			$this->query = $this->createQuery($this->fields);
		}

		if ($length) {
			$this->query->setFirstResult($offset);
			$this->query->setMaxResults($length);
		}

		$results = $this->query->execute()->fetchAll();
		if (empty($results)) {
			return [];
		}

		return $this->filterResults($results);
	}

	/**
	 * Count elements of an object
	 *
	 * {@inheritdoc}
	 */
	public function count()
	{
		if (!$this->totalCount) {
			$this->totalCount = $this->createQuery(["COUNT({$this->countField}) AS total"])
									 ->execute()
									 ->fetchColumn(0);
		}

		return $this->totalCount;
	}

	/**
	 * Return the current element
	 *
	 * {@inheritdoc}
	 */
	public function current()
	{
		return $this->items[$this->cursor];
	}

	/**
	 * Move forward to next element
	 *
	 * {@inheritdoc}
	 */
	public function next()
	{
		$this->cursor++;

		if (!isset($this->items)) {
			$this->items = $this->items();
		}
	}

	/**
	 * Return the key of the current element
	 *
	 * {@inheritdoc}
	 */
	public function key()
	{
		return $this->cursor;
	}

	/**
	 * Checks if current position is valid
	 *
	 * {@inheritdoc}
	 */
	public function valid()
	{
		return $this->cursor < $this->totalCount;
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * {@inheritdoc}
	 */
	public function rewind()
	{
		$this->cursor = 0;
		if (!isset($this->items)) {
			$this->items = $this->items();
		}
	}

	/**
	 * Creates the query based
	 *
	 * @param array $fields
	 * @return QueryBuilder
	 */
	protected function createQuery($fields)
	{
		$qb = $this->db->createQueryBuilder();
		$this->applyCriteria($qb->select($fields)->from($this->table), $this->criteria);

		return $qb;
	}

	/**
	 * Returns a DateTimeInterface object
	 * @param      $date
	 * @param bool $immutable
	 * @return \DateTimeInterface|null
	 */
	protected function convertToDateTime($date, $immutable = true)
	{
		if (!isset($date) || empty($date)) {
			return null;
		}

		if (!isset(self::$utc)) {
			self::$utc = new \DateTimeZone('UTC');
		}

		return $immutable
			? new \DateTimeImmutable($date, self::$utc)
			: new \DateTime($date, self::$utc);

	}

	/**
	 * Applies the given criteria to the QueryBuilder
	 *
	 * @param QueryBuilder $queryBuilder
	 * @param mixed $criteria
	 * @return QueryBuilder
	 */
	abstract protected function applyCriteria(QueryBuilder $queryBuilder, $criteria);

	/**
	 * @param array $results
	 * @return array
	 */
	abstract protected function filterResults($results);
}