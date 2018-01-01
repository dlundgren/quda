<?php

namespace Quda\WebRequest\Responder;

use Phrender\Engine;
use Phrender\Exception\TemplateNotFound;
use Quda\Presenter;
use Quda\Proxy\Log;
use Slim\Http\Response;

class Html
{
	/**
	 * @var Engine
	 */
	protected $presenter;

	public function __construct(Presenter $presenter)
	{
		$this->presenter = $presenter;
	}

	public function respond(Response $response, $data): Response
	{
		$action = func_get_arg(2);

		$response->getBody()->write($this->render(is_object($action) ? $action->template() : $action, $data));

		return $response;
	}

	/**
	 * @param $tpl
	 * @param $data
	 * @return string
	 */
	private function render($tpl, $data)
	{
		try {
			$output = $this->presenter->render($tpl, $data);
		}
		catch (TemplateNotFound $e) {
			$this->presenter->withLayout('error');
			$output = $this->presenter->render('errors/404', $data);
		}

		if (isset($e)) {
			Log::critical($e->getMessage());
		}

		return $output ?? 'Internal Server Error';
	}
}