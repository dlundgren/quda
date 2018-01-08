<?php

/**
 * @file
 * Contains Quda\Utility\Utility
 */

namespace Quda\Utility;

class Utility
{
	/**
	 * The hostname
	 *
	 * @var string
	 */
	private static $hostname;

	/**
	 * Returns the hostname
	 *
	 * @return string
	 */
	public static function hostname()
	{
		if (!self::$hostname) {
			self::$hostname = php_uname('n');
		}

		return self::$hostname;
	}

	/**
	 * Loads the commands in the directory in to Symfony's Console
	 *
	 * @param $console
	 * @param $container
	 * @param $path
	 * @param $namespace
	 */
	public static function loadConsoleCommands($console, $container, $path, $namespace)
	{
		$fs = new \FilesystemIterator($path);
		foreach ($fs as $file) {
			if ($file->isDir()) {
				continue;
			}
			$name = basename($file, '.php');
			if (strpos($name, '.') === 0 || $name === 'AbstractCommand') {
				continue;
			}

			$console->add($container->newInstance("$namespace\\${name}"));
		}
	}
}