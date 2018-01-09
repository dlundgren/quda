<?php

namespace Quda\Http;

class RedirectResponse
{
	private $url;
	public function __construct($url)
	{
		$this->url = $url;

	}

}