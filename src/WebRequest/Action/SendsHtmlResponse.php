<?php

namespace Quda\WebRequest\Action;

use Quda\WebRequest\Responder\Html;
use Slim\Http\Request;
use Slim\Http\Response;

trait SendsHtmlResponse
{
	/**
	 * @var Responder
	 */
	protected $responder;

	public function __construct(Html $responder)
	{
		$this->responder = $responder;
	}

	public function template()
	{
		$class = str_replace('\\', '/', strtolower(get_class($this)));
		return $this->template ?? array_pop(explode("/action/", $class, 2));
	}

	public function handle(Request $request, Response $response): Response
	{
		return $this->responder->respond($response, [], $this);
	}
}