<?php
namespace Quda\Vendor\FastFrame\Kernel;

use FastFrame\Kernel\HasSubProviders;
use FastFrame\Kernel\Environment;
use Interop\Container\ContainerInterface;

/**
 * Basic trait for Providers
 *
 * @package Strux\Kernel\Provider
 */
trait IsProvider
{
	use HasSubProviders;

	/**
	 * @var Environment
	 */
	protected $env;

	public function __construct(Environment $env)
	{
		$this->providerConstructor($env);
	}

	/**
	 * IsProvider constructor.
	 *
	 * @param Environment $env
	 */
	public function providerConstructor(Environment $env)
	{
		$this->env       = $env;
		$this->providers = $this->initProviderList($env, $env->get(Environment::KEY_CONFIG_NAMESPACE, Environment::CONFIG_NAMESPACE));
	}

	public function define(ContainerInterface $container)
	{
		$this->runProviderDefine($container);
	}

	public function modify(ContainerInterface $container)
	{
		$this->runProviderModify($container);
	}
}