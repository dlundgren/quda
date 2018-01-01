<?php

namespace Quda\_Config;

use FastFrame\Kernel\Provider;
use Interop\Container\ContainerInterface;
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
	}

}