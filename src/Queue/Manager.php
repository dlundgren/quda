<?php

/**
 * @file
 * Contains Quda\Queue\Manager
 */

namespace Quda\Queue;

use Quda\Queue\Job\Job;
use Quda\Queue\Job\JobItem;
use Quda\Queue\Job\Repository as JobRepository;
use Quda\Queue\Job\Repository;
use Quda\Utility\Conversion;
use josegonzalez\Queuesadilla\Engine\EngineInterface;

/**
 * Queue manager
 *
 * Resque-like queue manager that uses the Queusadilla engine
 *
 * @package Quda\Queue
 */
class Manager
{
	/**
	 * @var EngineInterface
	 */
	private $engine;

	/**
	 * @var JobRepository
	 */
	private $jobRepository;

	public function __construct(EngineInterface $engine, JobRepository $jobRepository)
	{
		$this->engine           = $engine;
		$this->jobRepository    = $jobRepository;
	}

	/**
	 * Creates the job in the given queue
	 *
	 * @param       $queue
	 * @param       $class
	 * @param array $args
	 * @param bool  $monitor
	 * @param null  $id
	 * @return null|string
	 */
	public function create($queue, $class, $args = [], $monitor = false, $id = null)
	{
		$id = $this->generateId($id);

		$this->engine->push(
			[
				'class'      => $class,
				'args'       => (array)$args,
				'id'         => $id,
				'queue_time' => microtime(true)
			],
			[
				'queue' => $queue,
			]
		);

		return $id;
	}

	/**
	 * Reserves a job from the queue
	 *
	 * @param $queue
	 * @return Job|null
	 */
	public function reserve($queue)
	{
		$item = $this->engine->pop(['queue' => $queue]);
		if (empty($item)) {
			return null;
		}

		$meta = $item['@meta'];
		unset($item['@meta']);
		
		$jobItem = new JobItem(
			$meta['id'],
			$item,
			$item['queue'],
			Conversion::convertToDateTimeOrNull($meta['started_at']),
			Conversion::convertToDateTimeOrNull($meta['expires_at']),
			Conversion::convertToDateTimeOrNull($meta['delay_until']),
			$meta['lock_id'],
			$meta['attempts']
		);

		return $jobItem;
	}

	/**
	 * This marks the job as failed lock_id = -1 and attaches the exception to the payload
	 *
	 * @param Job $job
	 * @param     $exception
	 */
	public function fail(Job $job, $exception)
	{
		$this->jobRepository->updateJobWithException($job, $exception);

		return true;
	}

	/**
	 * Generates and ID for the job
	 *
	 * @param mixed $id
	 * @return string
	 */
	private function generateId($id)
	{
		if ($id) {
			return $id;
		}

		return md5(uniqid('', true));
	}
}