<?php

namespace Quda\Job;

use Quda\Queue\Job\Job;
use Quda\Queue\Job\JobItem;
use Quda\Queue\Mixin\IsJob;

class EmailFailure
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
		$payload = $this->jobItem->payload();
		$email   = array_pop($payload);

		$e = new \Exception("SMTP server is down: unable to send to {$email}");
		$this->jobItem = $this->jobItem->withException($e);

		return self::STATUS_FAIL;
	}
}