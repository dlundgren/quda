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
		$router = $container->get('app');

		// front-end no login
		$router->get('/', Action\Site::class)->setName('site');

		$router->group('/demo', function() {
			$this->get('/email-failure', Action\DemoJob::class)->setName('email-failure');
			$this->get('/email-success', Action\DemoJob::class)->setName('email-success');
			$this->get('/process-image', Action\DemoJob::class)->setName('process-image');
			$this->get('/pop-a-top', Action\DemoJob::class)->setName('pop-a-top');
			$this->get('/in-the-future', Action\DemoJob::class)->setName('in-the-future');
		});
		$router->get('/queue', Action\Queue\Dashboard::class)->setName('dashboard');
		$router->group('/queue/job/{id}', function() {
			$this->get('', Action\Queue\Job\Display::class)->setName('job-view');
			$this->get('/retry', Action\Queue\Job\Retry::class)->setName('job-retry');
			$this->get('/delete', Action\Queue\Job\Delete::class)->setName('job-delete');
		});
		$router->get('/queue/worker', Action\Queue\Restart::class)->setName('restart');

	}

}