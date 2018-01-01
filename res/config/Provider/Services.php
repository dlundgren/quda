<?php

namespace Quda\_Config\Provider;

use FastFrame\Kernel\Provider;
use Interop\Container\ContainerInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Quda\Proxy;
use Quda\Vendor\FastFrame\Kernel\IsProvider;

class Services
	implements Provider
{
	use IsProvider;

	public function define(ContainerInterface $container)
	{
		$container->set('log', function () {
			$handler = new RotatingFileHandler($this->env->rootPath() . '/data/logs/run.log', 0, $this->env->get('log_level', Logger::WARNING));
			$handler->setFormatter(new LineFormatter("[%datetime%] %message% %extra% %context%\n"));

			$logger = new Logger('services');
			$logger->pushHandler($handler);

			return $logger;
		});
		$this->env['proxyManager']->addProxy('Log', Proxy\Log::class);
	}

	public function modify(ContainerInterface $container)
	{
		// TODO: Implement modify() method.
	}

}