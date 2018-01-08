<?php

/**
 * @file
 * Contains Quda\Queue\Worker\ContainerForkingWorker
 */

namespace Quda\Queue\Worker;

use Quda\Queue\Job\Factory as JobFactory;
use Quda\Queue\Job\Job;
use Quda\Queue\Job\JobItem;
use Quda\Utility\Conversion;
use josegonzalez\Queuesadilla\Engine\EngineInterface;
use Psr\Log\LoggerInterface;

/**
 * ContainerForkingWorker
 *
 * Uses a container and looks for "queue/jobs/<class>"
 *
 * @package Quda\Queue\Worker
 */
class ContainerForkingWorker
	extends ForkingWorker
{
	/**
	 * @var JobFactory
	 */
	private $jobFactory;

	public function __construct(JobFactory $jobFactory, EngineInterface $engine, LoggerInterface $logger,
								array $options)
	{
		parent::__construct($engine, $logger, $options);
		$this->jobFactory = $jobFactory;
	}

	/**
	 * Overrides the parent to use a container
	 *
	 * This is effectively the same as running {Resque_Job::perform}
	 *
	 * @param $item
	 * @return bool
	 */
	protected function processJob($item)
	{
		$jobItem = $this->createJobItem($item);

		$jobId = $jobItem->id();

		/** @var Job $job */
		try {
			$job = $this->jobFactory->newInstanceFromItem($jobItem);

			$this->logger->debug("Processing job: {$jobId}");

			$status  = $job->perform();
			$jobItem = $job->jobItem();
		}
		catch (\Exception $e) {
			$status  = Job::STATUS_FAIL;
			$jobItem = $jobItem->withException($e);
		}

		$code = 0;
		switch ($status) {
			case Job::STATUS_OK:
				$msg = "Job done {$jobId}";
				$this->engine->acknowledge($item);
				break;
			case Job::STATUS_DELAY:
				$msg = "Delaying job {$jobId}";
				$this->engine->release($this->convertToArray($jobItem));
				break;
			case Job::STATUS_RETRY:
				$msg = "Retrying job {$jobId}";
				$this->engine->release($this->convertToArray($jobItem));
				break;
			case Job::STATUS_FAIL:
			default:
				$this->engine->reject($this->convertToArray($jobItem));
				$code = -1;
				$msg  = "job failed {$jobId}";
				break;
		}

		$this->logger->debug($msg);

		return $code;
	}

	/**
	 * Whether or not the item should be rejected
	 *
	 * @param $item
	 * @return bool
	 */
	protected function shouldReject($item)
	{
		if (isset($item['attempts']) && ($item['attempts'] - 1) == 0) {
			return true;
		}

		return false;
	}

	/**
	 * Updates the item for failed attempts and delay
	 *
	 * @param $item
	 * @return mixed
	 */
	protected function updateItemForFailedAttempt($item)
	{
		if (isset($item['attempts']) && $item['attempts'] > 0) {
			$item['attempts'] -= 1;
		}

		if (isset($item['options']['attempts_delay'])) {
			$item['delay'] = $item['options']['attempts_delay'];
		}

		return $item;
	}

	/**
	 * Creates the JobItem object
	 *
	 * @param $item
	 * @return JobItem
	 */
	private function createJobItem($item)
	{
		$meta = $item['@meta'];
		unset($item['@meta']);

		return new JobItem(
			$meta['id'],
			$meta['queue'],
			$item,
			Conversion::convertToDateTimeOrNull($meta['started_at']),
			Conversion::convertToDateTimeOrNull($meta['expires_at']),
			Conversion::convertToDateTimeOrNull($meta['delay_until']),
			$meta['lock_id'],
			(int)$meta['attempts']
		);
	}

	/**
	 * Converts the JobItem into an Array
	 *
	 * @param JobItem $jobItem
	 * @return mixed
	 */
	private function convertToArray(JobItem $jobItem)
	{
		$ary          = $jobItem->data();
		$ary['@meta'] = [
			'id' => $jobItem->id(),
		];
		foreach (['expiresAt', 'delayUntil', 'attempts'] as $k) {
			$d = $jobItem->$k();
			if (isset($d)) {
				$ary['@meta'][$k] = $d;
			}
		}

		return $ary;
	}
}