<?php

/**
 * @file
 * Contains \Quda\WebRequest\Action\Queue\Restart
 */

namespace Quda\WebRequest\Action\Queue;

use Psr\Log\LoggerInterface;
use Quda\WebRequest\Action\AbstractAction;
use Quda\WebRequest\Responder\Html;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Admin action to handle OpCache Dashboard
 *
 * @package Application\Web\Action\Admin\Application
 */
class Restart
	extends AbstractAction
{
	/**
	 * @var string
	 */
	private $path;

	public function __construct(Html $responder, $path)
	{
		parent::__construct($responder);
		$this->path = $path;
	}

	public function handle(Request $request, Response $response): Response
	{
		// @TODO there has to be a better way of doing this? but for some reason it's missing in the server env
		putenv('PATH=/sbin:/bin:/usr/sbin:/usr/bin:/usr/local/bin:/usr/local/sbin');

		// This bug prevents execution so `at` is required as of now - bad php (worked in 5.5 or 5.6)
		// https://bugs.php.net/bug.php?id=70932
		proc_close(proc_open("echo \"{$this->path}/bin/queue-runner.sh restart >>/tmp/runner.log 2>&1\" | /usr/bin/at now", [], $foo));

		sleep(5);

		return $response->withHeader('Location', '/');
	}
}