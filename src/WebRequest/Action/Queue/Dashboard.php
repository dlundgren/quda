<?php

namespace Quda\WebRequest\Action\Queue;

use Quda\Queue\Job\Repository;
use Quda\Utility\Utility;
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
		return [
			'jobs'   => $this->repository->findAll(),
			'worker' => Utility::workerStatus($this->pidFile)
		];
	}
}