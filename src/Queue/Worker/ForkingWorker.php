<?php

/**
 * @file
 * Contains Quda\Queue\Worker\ForkingWorker
 */

namespace Quda\Queue\Worker;

use josegonzalez\Queuesadilla\Engine\EngineInterface;
use josegonzalez\Queuesadilla\Worker\Base;
use Psr\Log\LoggerInterface;

/**
 * Worker that forks children
 *
 * @package Quda\Queue\Worker
 */
class ForkingWorker
	extends Base
{
	/**
	 * @var int
	 */
	private $maxJobs = INF;

	/**
	 * The master PID
	 *
	 * @var int
	 */
	protected $masterPid;

	/**
	 * List of queues that this worker will be looking for
	 *
	 * @var array
	 */
	protected $queues = [];

	/**
	 * File to store the PID in
	 *
	 * @var string
	 */
	private $pidFile;

	public function __construct(EngineInterface $engine, LoggerInterface $logger, array $options)
	{
		parent::__construct($engine, $logger, $options);

		if (isset($options['pidFile'])) {
			$this->setPidFile($options['pidFile']);
		}
		else {
			throw new \InvalidArgumentException("pidFile must be supplied");
		}
	}

	/**
	 * Sets the maximum number of jobs the worker will run
	 *
	 * @param $maxJobs
	 */
	public function setMaxJobs($maxJobs)
	{
		$this->maxJobs = $maxJobs;
	}

	/**
	 * Allows to change the pidFile location
	 *
	 * @param $pidFile
	 */
	public function setPidFile($pidFile)
	{
		if (empty($pidFile)) {
			throw new \InvalidArgumentException("PID file cannot be empty");
		}
		$dir = dirname($pidFile);
		if (!file_exists($dir)) {
			throw new \RuntimeException("pidFile directory does not exist: {$dir}");
		}
		elseif (file_exists($pidFile) && !is_writable($pidFile)) {
			die("Cannot write to pidFile: {$pidFile}");
		}
		$this->pidFile = $pidFile;
	}

	/**
	 * Daemonizes the worker
	 */
	protected function daemonize()
	{
		$pid = pcntl_fork();
		if ($pid === -1) {
			throw new \RuntimeException("Master worker could not fork");
		}
		elseif ($pid > 0) {
			exit(0); // parent
		}

		posix_setsid();
		usleep(100000);

		$this->masterPid = posix_getpid();
		file_put_contents($this->pidFile, $this->masterPid);
		$this->logger->debug("Master started with pid {$this->masterPid}");
	}

	/**
	 * {@inheritdoc}
	 */
	public function work()
	{
		if (func_num_args() === 1) {
			$this->queues = (array)func_get_arg(1);
		}
		elseif (func_num_args() > 1) {
			$this->queues = func_get_args();
		}

		$this->daemonize();

		for ($i = 0; $i < $this->maxJobs;) {
			$this->engine->reconnect();

			$item = $this->engine->pop(['queue' => $this->queues]);
			if (empty($item)) {
				sleep(1);
				continue;
			}
			$i++;

			$pid = pcntl_fork();
			if ($pid === -1) {
				$this->logger->error('Unable to fork child');
				throw new \RuntimeException("Unable to fork child");
			}
			elseif ($pid === 0) {
				// child process
				exit($this->processJob($item) ? 0 : -1);
			}
			elseif ($pid > 0) {
				pcntl_wait($status);
				$exitStatus = pcntl_wexitstatus($status);
				if ($exitStatus !== 0) {
					$this->logger->error("Job {$item['id']}: failed with status " . $exitStatus);
				}
			}
		}

		$this->logger->debug("Master shutting down with pid {$this->masterPid}");

		return true;
	}

	/**
	 * Logs and updates the job
	 *
	 * @param $type
	 * @param $status
	 * @param $item
	 * @param $msg
	 */
	protected function logAndUpdate($type, $status, $item, $msg)
	{
		if ($status === 'reject') {
			$msg .= '. Rejecting from queue';
		}
		$this->logger->{$type}("Job {$item['id']}: $msg");
		$this->engine->{$status}($item);
	}

	/**
	 * Processes the job
	 *
	 * @param $item
	 * @return bool
	 */
	protected function processJob($item)
	{
		$this->logger->debug("Processing job: {$item['id']}");

		if (!is_callable($item['class'])) {
			$this->logAndUpdate('alert', 'reject', $item, "Invalid callable");

			return false;
		}

		$success = false;
		try {
			$success = call_user_func_array($item['class'], $item['data']);
		}
		catch (\Exception $e) {
			$this->logAndUpdate('error', 'release', $item, $e->getMessage());
		}

		if ($success) {
			$this->logAndUpdate('debug', 'acknowledge', $item, 'success');
		}
		else {
			$this->logAndUpdate('info', 'release', $item, 'failed');
		}

		return $success;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function disconnect()
	{
		if ($this->masterPid === getmypid()) {
			unlink($this->pidFile);
		}
	}

	/**
	 * Overrides the parent to not log since we don't count iterations
	 *
	 * @param null $signo
	 * @return bool
	 */
	public function shutdownHandler($signo = null)
	{
		$signals = array(
			SIGQUIT => "SIGQUIT",
			SIGTERM => "SIGTERM",
			SIGINT  => "SIGINT",
			SIGUSR1 => "SIGUSR1",
		);

		if ($signo !== null) {
			$signal = $signals[$signo];
			$this->logger->info(sprintf("Received received %s... Shutting down", $signal));
		}

		$this->disconnect();

		return true;
	}
}