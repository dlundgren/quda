<?php

/**
 * @file
 * Contains Quda\Vendor\Pagerfanta\Paginator
 */

namespace Quda\Vendor\Pagerfanta;

use Pagerfanta\Pagerfanta;

/**
 * Paginator for Pagerfanta
 *
 * This wraps Pagerfanta into something a little more usable
 *
 * @see https://github.com/adrienbrault/pagerfanta-iterator
 * @package Quda\Paginator
 */
class Paginator
	implements \Countable, \Iterator
{
	/**
	 * @var Pagerfanta
	 */
	private $pager;

	/**
	 * @var int
	 */
	private $pageCount;

	/**
	 * @var int
	 */
	private $currentPage = 1;

	public function __construct(Pagerfanta $pager)
	{
		$this->pager       = $pager;
		$this->rewind();
	}

	/**
	 * Sets the max items per page.
	 *
	 * Proxies to the pagerfanta object
	 *
	 * @param $maxPerPage
	 */
	public function setMaxPerPage($maxPerPage)
	{
		$this->pager->setMaxPerPage($maxPerPage);
		$this->rewind();
	}

	public function itemCount()
	{

	}

	/**
	 * Returns the count of pages
	 *
	 * @return mixed
	 */
	public function count()
	{
		return $this->pageCount;
	}

	/**
	 * Return the current element
	 *
	 * @link  http://php.net/manual/en/iterator.current.php
	 * @return mixed Can return any type.
	 * @since 5.0.0
	 */
	public function current()
	{
		$this->pager->setCurrentPage($this->pager->getCurrentPage());
		return $this->pager->getIterator();
	}

	/**
	 * Move forward to next element
	 *
	 * @link  http://php.net/manual/en/iterator.next.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function next()
	{
		$this->currentPage++;

		if ($this->valid()) {
			$this->pager->setCurrentPage($this->pager->getNextPage());
		}
	}

	/**
	 * Return the key of the current element
	 *
	 * @link  http://php.net/manual/en/iterator.key.php
	 * @return mixed scalar on success, or null on failure.
	 * @since 5.0.0
	 */
	public function key()
	{
		return $this->pager->getCurrentPageResults();
	}

	/**
	 * Checks if current position is valid
	 *
	 * @link  http://php.net/manual/en/iterator.valid.php
	 * @return boolean The return value will be casted to boolean and then evaluated.
	 *        Returns true on success or false on failure.
	 * @since 5.0.0
	 */
	public function valid()
	{
		return $this->currentPage <= $this->pageCount;
	}

	/**
	 * Rewind the Iterator to the first element
	 *
	 * @link  http://php.net/manual/en/iterator.rewind.php
	 * @return void Any returned value is ignored.
	 * @since 5.0.0
	 */
	public function rewind()
	{
		$this->currentPage = 1;
		$this->pager->setCurrentPage((int)$this->currentPage);
		$this->pageCount   = $this->pager->getNbPages();
	}
}