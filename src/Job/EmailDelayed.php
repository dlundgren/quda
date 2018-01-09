<?php

namespace Quda\Job;

use Quda\Queue\Job\Job;
use Quda\Queue\Job\JobItem;
use Quda\Queue\Mixin\IsJob;

class EmailDelayed
	implements Job
{
	use IsJob;

	protected $jobItem;

	public function __construct(JobItem $jobItem)
	{
		$this->jobItem = $jobItem;
	}

	public function perform()
	{
		return self::STATUS_OK;
	}
}