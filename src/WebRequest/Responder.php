<?php

namespace Quda\WebRequest;

use Slim\Http\Response;

interface Responder
{
	public function respond(Response $response, $data): Response;
}