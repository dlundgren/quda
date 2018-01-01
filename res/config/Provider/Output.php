<?php

namespace Quda\_Config\Provider;

use FastFrame\Kernel\Provider;
use Interop\Container\ContainerInterface;
use Phrender\Context\Collection;
use Phrender\Engine;
use Phrender\Template\Factory;
use Quda\Presenter;
use Quda\Vendor\FastFrame\Kernel\IsProvider;
use Quda\WebRequest\Responder\Html;

class Output
	implements Provider
{
	use IsProvider;

	public function define(ContainerInterface $container)
	{
		$container->params[Html::class] = [
			'presenter' => $container->lazyGet(Presenter::class)
		];

		$container->params[Presenter::class] = [
			'factory' => $container->lazyNew(Factory::class),
			'context' => $container->lazyGet('app/context')
		];
		$container->params[Factory::class] = [
			'paths' => [
				$this->env->rootPath() . '/res/themes/default/templates/',
			]
		];

		$container->set('app/context', new Collection());
	}

	public function modify(ContainerInterface $container)
	{

	}

}