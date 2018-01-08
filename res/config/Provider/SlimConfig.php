<?php

namespace Quda\_Config\Provider;

use FastFrame\Kernel\Provider;
use Interop\Container\ContainerInterface;
use Aura\Di\Container;
use Aura\Di\ContainerConfig;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Handlers\Error;
use Slim\Handlers\NotAllowed;
use Slim\Handlers\NotFound;
use Slim\Handlers\Strategies\RequestResponse;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Interfaces\CallableResolverInterface;
use Slim\Interfaces\Http\EnvironmentInterface;
use Slim\Interfaces\InvocationStrategyInterface;
use Slim\Interfaces\RouterInterface;
use Quda\Vendor\FastFrame\Kernel\IsProvider;
use Slim\Router;

class SlimConfig
	implements Provider
{
	use IsProvider;

	private $defaultSettings = [
		'cookieLifetime'                    => '20 minutes',
		'cookiePath'                        => '/',
		'cookieDomain'                      => null,
		'cookieSecure'                      => false,
		'cookieHttpOnly'                    => false,
		'httpVersion'                       => '1.1',
		'responseChunkSize'                 => 4096,
		'outputBuffering'                   => 'append',
		'determineRouteBeforeAppMiddleware' => false,
	];

	public function define(ContainerInterface $di)
	{
		$defaultSettings = $this->defaultSettings;

		$di->set(
			'slim/settings',
			function () use ($di, $defaultSettings) {
				$userSettings = $di->has('userSettings') ? $di->get('userSettings') : [];

				return array_merge($defaultSettings, $userSettings);
			});

		$di->set(
			'request',
			$di->lazy(
				function () use ($di) {
					return Request::createFromGlobals($_SERVER);
				}
			)
		);

		$di->set(
			'response',
			$di->lazy(
				function () use ($di) {
					$settings = $di->get('slim/settings');
					$headers  = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
					$response = new Response(200, $headers);

					return $response->withProtocolVersion($settings['httpVersion']);
				}
			)
		);

		$di->set('router', $di->lazyNew(Router::class));
		$di->set('errorHandler', $di->lazyNew(Error::class));
		$di->set('notFoundHandler', $di->lazyNew(NotFound::class));
		$di->set('notAllowedHandler', $di->lazyNew(NotAllowed::class));
		$di->set(
			'callableResolver',
			$di->lazyNew(
				'Slim\CallableResolver',
				[
					'container' => $di
				]));
	}
}