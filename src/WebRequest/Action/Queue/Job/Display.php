<?php

/**
 * @file
 * Contains \Quda\WebRequest\Action\Queue\Job\Display
 */

namespace Quda\WebRequest\Action\Queue\Job;

use josegonzalez\Queuesadilla\Engine\EngineInterface;
use Psr\Http\Message\ServerRequestInterface;
use Quda\Queue\Job\Repository;
use Quda\WebRequest\Action\AbstractAction;
use Quda\WebRequest\Responder\Html;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Display the job
 *
 * @package Quda\WebRequest\Action\Queue\Job
 */
class Display
	extends AbstractAction
{
	/**
	 * @var Repository
	 */
	private $jobRepository;

	public function __construct(Html $responder, Repository $repository)
	{
		$this->jobRepository = $repository;
		parent::__construct($responder);
	}

	protected function data(Request $request, Response $response): array
	{
		return [
			'job' => $this->jobRepository->get($request->getAttribute('id'))
		];
	}

}