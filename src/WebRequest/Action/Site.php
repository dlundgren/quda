<?php

namespace Quda\WebRequest\Action;

use Quda\Queue\Job\Repository;
use Quda\Utility\Utility;
use Quda\WebRequest\Responder\Html;
use Slim\Http\Request;
use Slim\Http\Response;

class Site
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
		parent::__construct($responder);
		$this->repository = $repository;
		$this->pidFile = $pidFile;
	}

	protected function data(Request $request, Response $response): array
	{
		return [
			'worker' => Utility::workerStatus($this->pidFile),
			'jobs'   => $this->repository->findAll(),
			'emails' => $this->repository->findAllByQueue('email'),
			'others' => $this->repository->findAllByQueue('misc')
		];
	}

}