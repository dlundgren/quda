<?php

namespace Quda\_Config\Provider;

use FastFrame\Kernel\Provider;
use Interop\Container\ContainerInterface;
use Quda\Vendor\FastFrame\Kernel\IsProvider;
use Slim\App;
use Quda\WebRequest\Action;

class Routing
	implements Provider
{
	use IsProvider;

	public function modify(ContainerInterface $container)
	{
		/** @var App $router */
		$router = $container->get('app/router');

		// front-end no login
		$router->get('/', Action\Site::class)->setName('site');

		$router->get('/queue', Action\Queue\Dashboard::class)->setName('dashboard');
		$router->group('/queue/job/{id}', function() {
			$this->get('/',Action\Queue\Job\Display::class)->setName('job-detail');
			$this->put('/', Action\Queue\Job\Retry::class)->setName('job-retry');
			$this->delete('/', Action\Queue\Job\Delete::class)->setName('job-delete');
		});
		$router->delete('/queue/worker', Action\Queue\WorkerRestart::class)->setName('worker-restart');

	}

}