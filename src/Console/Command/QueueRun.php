<?php

/**
 * @file
 * Contains \Quda\Console\Command\QueueRun
 */

namespace Quda\Console\Command;

use Quda\Queue\Worker\ContainerForkingWorker;
use Aura\Di\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console command for setting a configuration value
 *
 * @package Application\Console\Command
 */
class QueueRun
	extends Command
{
	/**
	 * @var Container
	 */
	protected $container;

	public function __construct(Container $container)
	{
		parent::__construct();
		$this->container = $container;
	}

	public function configure()
	{
		$this->setName('queue:run')
			 ->setDescription("Runs the Queue Worker")
			 ->addArgument(
				 'queues',
				 InputArgument::OPTIONAL,
				 "List of queues to do work for"
			 )
			 ->addArgument(
				 'pid-file',
				 InputArgument::OPTIONAL,
				 "Location to store the PID file"
			 );
	}

	public function execute(InputInterface $input, OutputInterface $output)
	{
		$this->container->get('queue/worker')->work();
	}

}