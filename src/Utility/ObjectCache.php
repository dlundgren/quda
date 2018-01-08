<?php

/**
 * @file
 * Contains Quda\Utility\ObjectCache
 */

namespace Quda\Utility;

/**
 * Cache for PHP objects that come out of the database
 *
 * @package Quda\Utility
 */
class ObjectCache
{
	private static $items = [];

	public static function clear()
	{
		self::$items = [];
	}

	public static function has($key)
	{
		return isset(self::$items[$key]);
	}

	public static function set($key, $value)
	{
		self::$items[$key] = $value;
	}

	public static function remove($key)
	{
		unset(self::$items[$key]);
	}

	public static function get($key)
	{
		return self::$items[$key];
	}
}