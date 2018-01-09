<?php
namespace Quda\Proxy;

use XStatic\StaticProxy;

class Queue
	extends StaticProxy
{
	public static function getInstanceIdentifier()
	{
		return 'queue/manager';
	}

}