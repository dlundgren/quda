<?php

namespace Quda\_Config\Provider;

use FastFrame\Kernel\Provider;
use Interop\Container\ContainerInterface;
use Quda\WebRequest\Action;

class SlimConfig
	implements Provider
{
	/**
	 * @var \Ackee\AuraDiSlimContainer\SlimConfig
	 */
	private $slimConfig;

	public function __construct()
	{
		$this->slimConfig = new \Ackee\AuraDiSlimContainer\SlimConfig();

	}

	public function define(ContainerInterface $container)
	{
		$this->slimConfig->define($container);
	}

	public function modify(ContainerInterface $container)
	{
		$this->slimConfig->modify($container);
	}

}