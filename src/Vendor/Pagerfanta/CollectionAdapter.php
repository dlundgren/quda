<?php

/**
 * @file
 * Contains Quda\Vendor\Pagerfanta\CollectionAdapter
 */

namespace Quda\Vendor\Pagerfanta;

use Pagerfanta\Adapter\AdapterInterface;
use Quda\Queue\Job\Collection;

/**
 * Domain Collection adapter for Pagerfanta to ease pagination of results
 *
 * @package Brooks\Infrastructure\Paginator\Adapter
 */
class CollectionAdapter
	implements AdapterInterface
{
	/**
	 * @var Collection
	 */
	private $collection;

	/**
	 * @var int The total number of items in the collection
	 */
	private $itemCount;

	public function __construct($collection)
	{
		$this->collection = $collection;
	}

	/**
	 * Returns the number of results.
	 *
	 * @return integer The number of results.
	 */
	public function getNbResults()
	{
		if (!$this->itemCount) {
			$this->itemCount = count($this->collection);
		}

		return $this->itemCount;
	}

	/**
	 * Returns an slice of the results.
	 *
	 * @param integer $offset The offset.
	 * @param integer $length The length.
	 *
	 * @return array|\Traversable The slice.
	 */
	public function getSlice($offset, $length)
	{
		return $this->collection->slice($offset, $length);
	}

}