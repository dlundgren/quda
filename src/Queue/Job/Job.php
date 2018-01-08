<?php

/**
 * @file
 * Contains Quda\Queue\Job\Job
 */

namespace Quda\Queue\Job;

use DateTimeImmutable;

/**
 * Job
 *
 * @package Quda\Queue\Job
 */
interface Job
{
	const STATUS_OK = 1;
	const STATUS_FAIL = 2;
	const STATUS_DELAY = 3;
	const STATUS_RETRY = 4;

	/**
	 * Returns the Job Item
	 *
	 * @return JobItem
	 */
	public function jobItem();

	/**
	 * Perform the job
	 *
	 * @return int One of the STATUS_* constants
	 */
	public function perform();
}