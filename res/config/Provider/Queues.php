<?php

/**
 * @file
 * Contains Quda\_Config\Providers\Queues
 */

namespace Quda\_Config\Provider;


use FastFrame\Kernel\Provider;
use Interop\Container\ContainerInterface;
use Quda\_Config\System;
use Quda\Vendor\FastFrame\Kernel\IsProvider;
use Quda\Job\SendConfirmationEmail;
use Quda\Job\SendResetEmail;
use Quda\Queue\Worker\ContainerForkingWorker;
use Quda\Queue\DoctrineEngine;
use Quda\Queue\Manager;
use Quda\Queue\Job\Factory as JobFactory;
use Quda\Queue\Job\Repository as JobRepository;
use josegonzalez\Queuesadilla\Queue;

/**
 * Queue configuration and setup
 *
 * Add jobs in {Console::registerQueueJobs}
 *
 * @package Quda\_Config
 */
class Queues
	implements Provider
{
	use IsProvider;

	public function define(ContainerInterface $di)
	{
		$jobsTable = 'queue_jobs';
		$db        = $di->lazyGet('db/apply');
		$engine    = [
			'uuidFactory' => $di->lazyGet(System::UUID_FACTORY_KEY),
			'conn'        => $db,
			'config'      => [
				'table' => $jobsTable,
			]
		];

		$di->params[JobFactory::class] = [
			'container' => $di
		];

		$di->params[JobRepository::class]          = [
			'db'       => $db,
			'jobTable' => $jobsTable
		];
		$di->params[ContainerForkingWorker::class] = [
			'jobFactory' => $di->lazyNew(JobFactory::class),
			'engine'     => $di->lazyGet('queue/engine'),
			'logger'     => $di->lazyGet('queue/logger'),
			'options'    => [
				'pidFile' => "{$this->path}/data/queue.pid"
			]
		];
		$di->params[Manager::class]                = [
			'engine'        => $di->lazyGet('queue/engine'),
			'jobRepository' => $di->lazyGet('queue/repository:job'),
		];

		$di->set(
			'queue/logger', $di->lazy(
			function () use ($di) {
				// We need to configure the queue to log to a new location
				AnalogFile::setFile($this->path . '/data/logs/queue.log');

				return $di->get('logger');
			}));
		$di->set('queue/repository:job', $di->lazyNew(JobRepository::class));
		$di->set('queue/engine', $di->lazyNew(DoctrineEngine::class, $engine));
		$di->set('queue/manager', $di->lazyNew(Manager::class));
		$di->set('app/queue', $di->lazyNew(Queue::class, ['engine' => $di->lazyGet('queue/engine')]));

		$di->set('queue/worker', $di->lazyNew(ContainerForkingWorker::class));
	}
}