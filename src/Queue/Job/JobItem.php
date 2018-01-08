<?php

/**
 * @file
 * Contains Quda\Queue\Job\JobItem
 */

namespace Quda\Queue\Job;

use Quda\Utility\Conversion;
use DateTimeImmutable;

/**
 * Job Item
 *
 * @package Quda\Queue\Job
 */
class JobItem
{

	private $id;
	private $queue;
	private $data;
	private $startedAt;
	private $expiresAt;
	private $delayUntil;
	private $lockId;
	private $attempts;

	public function __construct($id,
								$queue,
								$data,
								DateTimeImmutable $startedAt = null,
								DateTimeImmutable $expiresAt = null,
								DateTimeImmutable $delayUntil = null,
								$lock_id,
								$attempts)
	{
		$this->id         = $id;
		$this->queue      = $queue;
		$this->data       = $data;
		$this->startedAt  = $startedAt;
		$this->expiresAt  = $expiresAt;
		$this->delayUntil = $delayUntil;
		$this->lockId     = $lock_id;
		$this->attempts   = $attempts;
	}

	/**
	 * @return string
	 */
	public function id()
	{
		return $this->id;
	}

	/**
	 * @return string
	 */
	public function queue()
	{
		return $this->queue;
	}

	/**
	 * @return mixed
	 */
	public function data()
	{
		return $this->data;
	}

	/**
	 * @return mixed
	 */
	public function startedAt()
	{
		return $this->startedAt;
	}

	/**
	 * @return mixed
	 */
	public function expiresAt()
	{
		return $this->expiresAt;
	}

	/**
	 * @return mixed
	 */
	public function delayUntil()
	{
		return $this->delayUntil;
	}

	/**
	 * @return mixed
	 */
	public function lockId()
	{
		return $this->lockId;
	}

	/**
	 * @return mixed
	 */
	public function attempts()
	{
		return $this->attempts;
	}

	/**
	 * Returns the class
	 *
	 * @return string
	 */
	public function name()
	{
		return $this->data['class'];
	}

	/**
	 * Returns the payload
	 *
	 * @return mixed
	 */
	public function payload()
	{
		return $this->data['args'];
	}

	/**
	 * Inserts the exception into the data
	 *
	 * @param \Exception $exception
	 * @return JobItem
	 */
	public function withException(\Exception $exception)
	{
		$item = clone $this;

		$item->data['exception'] = [
			'class'   => get_class($exception),
			'message' => $exception->getMessage(),
			'code'    => $exception->getCode(),
			'line'    => $exception->getLine(),
			'file'    => $exception->getFile(),
			'trace'   => Conversion::stackTraceForJson($exception->getTrace()),
		];

		return $item;
	}
}