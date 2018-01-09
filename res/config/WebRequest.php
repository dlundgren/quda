<?php

namespace Quda\_Config;

use FastFrame\Kernel\Provider;
use Interop\Container\ContainerInterface;
use Quda\Output\Helper\Route;
use Quda\Output\Helper\UrlFor;
use Quda\Queue\Job\Repository;
use Quda\Vendor\FastFrame\Kernel\IsProvider;
use Quda\WebRequest\Action;
use Quda\WebRequest\Responder\Html;

class WebRequest
	implements Provider
{
	use IsProvider;

	public function define(ContainerInterface $container)
	{
		$container->params[Action\AbstractAction::class] = [
			'responder' => $container->lazyGet(Html::class)
		];

		$container->params[Action\Queue\Job\Delete::class] =
			$container->params[Action\Queue\Job\Retry::class] =
		$container->params[Action\Queue\Job\Display::class] = [
			'repository' => $container->lazyGet(Repository::class),
	];
		$container->params[Action\Queue\Dashboard::class] = [
			'repository' => $container->lazyGet(Repository::class),
			'pidFile'    => $this->env->rootPath() . '/data/queue.pid'
		];

		$container->params[Action\Site::class] = [
			'repository' => $container->lazyGet(Repository::class),
			'pidFile'    => $this->env->rootPath() . '/data/queue.pid'
		];
		$container->params[Action\Queue\Restart::class] = [
			'path' => $this->env->rootPath()
		];

		$container->params[Route::class] = [
			'router'  => $container->lazyGet('router'),
			'request' => $container->lazyGet('request')
		];
		$container->params[UrlFor::class] = [
			'router'  => $container->lazyGet('router'),
		];
	}

}