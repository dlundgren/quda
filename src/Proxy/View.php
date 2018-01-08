<?php
namespace Quda\Proxy;

use XStatic\StaticProxy;

class View
	extends StaticProxy
{
	public static function getInstanceIdentifier()
	{
		return 'view';
	}

	public static function __callStatic($method, $args)
	{
		$p = parent::__callStatic($method, $args);

		if (is_callable($p)) {
			return call_user_func_array($p, $args);
		}

		return $p;
	}

}