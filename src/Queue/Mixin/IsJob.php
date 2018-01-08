<?php

/**
 * @file
 * Contains Quda\Queue\Mixin\IsJob
 */

namespace Quda\Queue\Mixin;

use Quda\Queue\Job\JobItem;

trait IsJob
{
	/**
	 * @var JobItem
	 */
	protected $jobItem;

	/**
	 * Returns the job item
	 *
	 * @return JobItem
	 */
	public function jobItem()
	{
		return $this->jobItem;
	}

}