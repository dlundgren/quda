<?php

namespace Quda\_Config;

use Ackee\AuraDiSlimContainer\SlimConfig;
use FastFrame\Kernel\Provider as FastFrameKernelProvider;
use Interop\Container\ContainerInterface;
use Quda\Vendor\FastFrame\Kernel\IsProvider;
use XStatic\ProxyManager;

class System
	implements FastFrameKernelProvider
{
	use IsProvider;

	const UUID_FACTORY_KEY = 'uuid-factory';

	public function define(ContainerInterface $container)
	{
		$this->env['app.version'] = '0.1';
		$this->env['app.name'] = 'quda';

		// setup the static proxy
		$this->env['proxyManager'] = new ProxyManager($container);
		$this->env['proxyManager']->enable(ProxyManager::ROOT_NAMESPACE_ANY);

		$this->providers->append(Provider\SlimConfig::class);
		$this->providers->append(Provider\Services::class);
		$this->providers->append(Provider\Routing::class);
		$this->providers->append(Provider\Database::class);
//		$this->providers->append(Provider\Queues::class);
		$this->providers->append(Provider\Output::class);

		$this->providers->append(PHP_SAPI === 'cli' ? Console::class : WebRequest::class);
		$this->providers->define($container);

	}
}