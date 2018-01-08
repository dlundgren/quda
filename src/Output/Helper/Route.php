<?php

/**
 * @file
 * Contains Application\Shared\Output\Foil\Extension\UrlFor
 */

namespace Quda\Output\Helper;

use Slim\App;
use Slim\Http\Request;
use Slim\Router;

class Route
{
	/**
	 * @var Router
	 */
	private $router;

	/**
	 * @var Request
	 */
	private $request;


	public function __construct(Router $router, Request $request)
	{
		$this->router = $router;
		$this->request = $request;
	}

	public function urlFor($name, $data = [], $query = [])
	{
		return $this->router->pathFor($name, $data, $query);
	}

	public function fullUrlFor($name, $data = [], $query = [])
	{
		return $this->request->getUri()->getBaseUrl() . $this->router->pathFor($name, $data, $query);
	}

}