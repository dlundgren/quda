<?php

/**
 * @file
 * Contains Application\Shared\Output\Foil\Extension\UrlFor
 */

namespace Quda\Output\Helper;

use Slim\App;
use Slim\Http\Request;
use Slim\Router;

class UrlFor
{
	/**
	 * @var Router
	 */
	private $router;

	/**
	 * @var Request
	 */
	private $request;


	public function __construct(Router $router)
	{
		$this->router = $router;
	}

	public function __invoke($name, $data = [], $query = [])
	{
		return $this->router->pathFor($name, $data, $query);
	}
}