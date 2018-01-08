<?php

namespace Quda\_Config;

use FastFrame\Kernel\Provider;
use Interop\Container\ContainerInterface;
use Quda\Utility\Utility;
use Quda\Vendor\FastFrame\Kernel\IsProvider;
use Symfony\Component\Console\Application;

class Console
	implements Provider
{
	use IsProvider;

	public function define(ContainerInterface $container)
	{
		$container->set(
			'console', $container->lazy(
			function () use (&$container) {
				$console = $container->newInstance(
					Application::class,
					[
						'name'    => $this->env['app.name'],
						'version' => $this->env['app.version']
					]);

				$this->loadConsoleCommand($console, $container);

				return $console;
			}));
	}

	protected function loadConsoleCommand($console, $container)
	{
		Utility::loadConsoleCommands(
			$console,
			$container,
			$this->env->rootPath() . '/src/Console/Command',
			"Quda\\Console\\Command"
		);
	}
}