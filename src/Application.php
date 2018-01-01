<?php

namespace Quda;

use Ackee\AuraDiSlimContainer\ContainerBootstrap;
use Aura\Di\ContainerBuilder;
use Aura\Di\Injection\InjectionFactory;
use Aura\Di\Resolver\Reflector;
use Aura\Di\Resolver\Resolver;
use FastFrame\Kernel\Environment;
use Quda\_Config\System;
use Slim\App;
use Slim\Interfaces\RouteInterface;

class Application
	extends App
{
	/**
	 * @var System
	 */
	protected $containerConfig;

	public function __construct($rootPath, $autoloader)
	{
		$container = new Container(new InjectionFactory(new Resolver(new Reflector())));
		parent::__construct([], $container);

		$env                   = new Environment($rootPath, $autoloader);
		$this->containerConfig = new System($env);

		$container->set('app/router', $this);
		$container->set('env', $env);

		$this->containerConfig->define($container);
	}

	public function run()
	{
		$this->containerConfig->modify($this->getContainer());

		return parent::run();
	}

	/**
	 * Add route with multiple methods
	 *
	 * @param  string[]        $methods  Numeric array of HTTP method names
	 * @param  string          $pattern  The route URI pattern
	 * @param  callable|string $callable The route callback routine
	 *
	 * @return RouteInterface
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	public function map(array $methods, $pattern, $callable)
	{
		$di = $this->getContainer();
		if (is_string($callable)) {
			if ($di->has($callable)) {
				$callable = array($di->get($callable), 'handle');
			}
		}

		return parent::map($methods, $pattern, $callable);
	}

}