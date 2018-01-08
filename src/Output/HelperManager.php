<?php

namespace Quda\Output;

use Interop\Container\ContainerInterface;

class HelperManager
{
	const KEY_PREFIX = 'output/helper';

	/**
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * @var array
	 */
	protected $namedInstances = [];

	/**
	 * @var array<ContainerInterface>
	 */
	protected $delegates = [];

	public function __construct($container, $namespaces = [])
	{
		$this->container = $container;
		$this->namespaces  = empty($namespaces)
			? [__NAMESPACE__]
			: $namespaces;
	}

	public function addDelegate($delegate)
	{
		$this->delegates[] = $delegate;
	}

	public function __call($name, $arguments)
	{
		return $this->resolve($name);
	}

	public function resolve($name)
	{
		if (isset($this->namedInstances[$name])) {
			return $this->namedInstances[$name];
		}

		if ($this->container->has($key = self::KEY_PREFIX . ":{$name}")) {
			return $this->namedInstances[$name] = $this->container->get($key);
		}

		$class = ucfirst($name);
		foreach ($this->namespaces as $ns) {
			$obj = $this->resolveWithClass($name, "{$ns}\\{$class}");
			if ($obj) {
				return $obj;
			}
		}

		throw new \InvalidArgumentException("Helper not found: $name");
	}

	public function appendNamespace($ns)
	{
		$this->namespaces[] = $ns;
	}

	public function prependNamespace($ns)
	{
		array_unshift($this->namespaces, $ns);
	}

	protected function resolveWithClass($name, $class)
	{
		if ($this->container->has($class)) {
			return $this->namedInstances[$name] = $this->container->get($class);
		}

		foreach ($this->delegates as $delegate) {
			foreach ([$class, $name] as $t) {
				if ($delegate->has($t)) {
					return $this->namedInstances[$name] = $delegate->get($t);
				}
			}
		}

		if (class_exists($class)) {
			return $this->namedInstances[$name] = new $class;
		}

		return null;
	}

}