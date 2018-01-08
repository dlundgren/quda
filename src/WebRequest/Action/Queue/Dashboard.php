<?php

namespace Quda\WebRequest\Action\Queue;

use Quda\Queue\Job\Repository;
use Quda\WebRequest\Action\AbstractAction;
use Quda\WebRequest\Responder\Html;
use Slim\Http\Request;
use Slim\Http\Response;

class Dashboard
	extends AbstractAction
{
	/**
	 * @var Repository
	 */
	private $repository;

	/**
	 * @var string
	 */
	private $pidFile;

	public function __construct(Html $responder, Repository $repository, $pidFile)
	{
		$this->pidFile    = $pidFile;
		$this->repository = $repository;
		parent::__construct($responder);
	}

	protected function data(Request $request, Response $response): array
	{
		$pid     = 0;
		$running = false;
		if (file_exists($this->pidFile)) {
			$pid = file_get_contents($this->pidFile);
			// @TODO: Make this work on windows - dlundgren
			$check = trim(`ps -p {$pid} -o command= | grep -c queue:run`);
			if ($check > 0) {
				$running = true;
			}
		}

		return [
			'jobs'   => $this->repository->findAll(),
			'worker' => [
				'running' => $running,
				'pid'     => $pid
			]
		];
	}
}