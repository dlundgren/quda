<?php

namespace Quda\Job;

use Quda\Queue\Job\Job;
use Quda\Queue\Job\JobItem;
use Quda\Queue\Mixin\IsJob;

class ProcessImage
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
		for ($t=1; $t< 60; $t+=10) {
			sleep(5);
		}

		return self::STATUS_OK;
	}
}