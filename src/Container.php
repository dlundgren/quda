<?php

namespace Quda;

use Aura\Di\Exception\ServiceNotFound;
use Quda\WebRequest\Action\Site;

class Container
	extends \Aura\Di\Container
{
	private $internal = false;

	public function has($service)
	{
		$parentHas = parent::has($service);
		if ($this->internal) {
			return $parentHas;
		}

		return $parentHas || isset($this->resolver->params[$service]) || class_exists($service);
	}

	public function get($service)
	{
		if ($service instanceof \Closure) {
			return $service;
		}

		try {
			$this->internal = true;
			$instance = parent::get($service);
		}
		catch (ServiceNotFound $e) {
			$instance = self::newInstance($service);
		}

		$this->internal = false;

		return $instance;
	}

}