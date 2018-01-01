<?php
namespace Quda\Proxy;

use XStatic\StaticProxy;

/**
 * Class Log
 *
 * @method static debug(string $message, array $context = [])
 * @method static info(string $message, array $context = [])
 * @method static notice(string $message, array $context = [])
 * @method static warning(string $message, array $context = [])
 * @method static error(string $message, array $context = [])
 * @method static critical(string $message, array $context = [])
 * @method static alert(string $message, array $context = [])
 * @method static emergency(string $message, array $context = [])
 *
 */
class Log
	extends StaticProxy
{
	public static function getInstanceIdentifier()
	{
		return 'log';
	}

}