<?php

/**
 * @file
 * Contains Quda\Queue\Job\Factory
 */

namespace Quda\Queue\Job;

use Aura\Di\Container;

class Factory
{
	/**
	 * @var Container
	 */
	private $container;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * Creates a job from a Queuesadilla item
	 *
	 * @param JobItem $jobItem
	 * @return Job
	 * @throws \Aura\Di\Exception\SetterMethodNotFound
	 */
	public function newInstanceFromItem(JobItem $jobItem)
	{
		$class = $jobItem->name();
		if (empty($class) || !class_exists($class)) {
			throw new \RuntimeException("Class not defined or not found: $class");
		}

		return $this->container->newInstance($class, ['jobItem' => $jobItem]);
	}

}