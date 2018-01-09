<?php

/**
 * @file
 * Contains Application\Module\Queue\Action\Job\Delete
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
 * Class Delete
 *
 * @package Application\Module\Queue\Action\Testing
 */
class Delete
	extends AbstractAction
{
	/**
	 * @var JobRepository
	 */
	private $jobRepository;

	public function __construct(Html $responder, Repository $repository)
	{
		$this->jobRepository = $repository;
		parent::__construct($responder);
	}

	public function handle(Request $request, Response $response): Response
	{
		try {
			$job = $this->jobRepository->get($request->getAttribute('id'));
			$this->jobRepository->remove($job);
		}
		catch (\Exception $e) {
		}

		return $response->withHeader('Location', '/queue');
	}

}