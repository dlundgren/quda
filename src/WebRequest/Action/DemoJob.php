<?php

/**
 * @file
 * Contains \Quda\WebRequest\Action\Queue\DemoJob
 */

namespace Quda\WebRequest\Action;

use Quda\Job\EmailDelayed;
use Quda\Job\EmailFailure;
use Quda\Job\EmailSuccess;
use Quda\Job\ProcessImage;
use Quda\WebRequest\Responder\Html;
use Slim\Http\Request;
use Slim\Http\Response;

use Quda\Proxy\Queue as Queue;

/**
 * Admin action to handle OpCache Dashboard
 *
 * @package Application\Web\Action\Admin\Application
 */
class DemoJob
	extends AbstractAction
{

	public function handle(Request $request, Response $response): Response
	{
		$path = str_replace('/demo/', '', $request->getUri()->getPath());
		switch ($path) {
			case 'email-failure':
				Queue::create('email', EmailFailure::class, 'someone@example.com');
				break;
			case 'email-success':
				Queue::create('email', EmailSuccess::class, 'someone@example.com');
				break;
			case 'email-delay':
				Queue::create('email', EmailDelayed::class, 'someone@example.com');
				break;
			case 'process-image':
				Queue::create('misc', ProcessImage::class, 'uploads/some-random-image.jpg');
				break;
			case 'pop-a-top':
				break;
			case 'in-the-future':
				break;
		}

		//

		return $response->withHeader('Location', '/queue');
	}
}