<?php

/**
 * @file
 * Contains Quda\_Config\Providers\Queues
 */

namespace Quda\_Config\Provider;


use FastFrame\Kernel\Provider;
use Interop\Container\ContainerInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Quda\_Config\System;
use Quda\Vendor\FastFrame\Kernel\IsProvider;
use Quda\Job\SendConfirmationEmail;
use Quda\Job\SendResetEmail;
use Quda\Proxy;
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
		$db        = $di->lazyGet('database:queue');
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
				'pidFile' => "{$this->env->rootPath()}/data/queue.pid"
			]
		];
		$di->params[Manager::class]                = [
			'engine'        => $di->lazyGet('queue/engine'),
			'jobRepository' => $di->lazyGet('queue/repository:job'),
		];

		$di->set(
			'queue/logger', function () {
			$handler = new RotatingFileHandler('/home/vagrant/logs/queue.log', 0, $this->env->get('log_level', Logger::DEBUG));
			$handler->setFormatter(new LineFormatter("[%datetime%] %message% %extra% %context%\n"));

			$logger = new Logger('queue');
			$logger->pushHandler($handler);

			return $logger;
		});

		$di->set('queue/repository:job', $di->lazyNew(JobRepository::class));
		$di->set('queue/engine', $di->lazyNew(DoctrineEngine::class, $engine));
		$di->set('queue/manager', $di->lazyNew(Manager::class));
		$di->set('app/queue', $di->lazyNew(Queue::class, ['engine' => $di->lazyGet('queue/engine')]));

		$di->set('queue/worker', $di->lazyNew(ContainerForkingWorker::class));

		$this->env['proxyManager']->addProxy('Queue', Proxy\Queue::class);
	}
}