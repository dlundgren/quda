<?php

namespace Quda\_Config\Provider;

use FastFrame\Kernel\Provider;
use Interop\Container\ContainerInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Quda\Proxy;
use Quda\Queue\Job\Repository;
use Quda\Vendor\FastFrame\Kernel\IsProvider;

class Services
	implements Provider
{
	use IsProvider;

	public function define(ContainerInterface $container)
	{
		$container->set(
			'log', function () {
			$handler = new RotatingFileHandler($this->env->rootPath() . '/data/logs/run.log', 0, $this->env->get('log_level', Logger::WARNING));
			$handler->setFormatter(new LineFormatter("[%datetime%] %message% %extra% %context%\n"));

			$logger = new Logger('services');
			$logger->pushHandler($handler);

			return $logger;
		});
		$this->env['proxyManager']->addProxy('Log', Proxy\Log::class);

		$db = $container->lazyGet('database:queue');

		$container->params[Repository::class] = [
			'db'       => $db,
			'jobTable' => 'queue_jobs'
		];
	}
}