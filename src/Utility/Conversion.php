<?php

/**
 * @file
 * Contains Quda\Utility\Conversion
 */

namespace Quda\Utility;

class Conversion
{
	const DEFAULT_TIMEZONE = 'UTC';

	protected static $tzs = [];

	public static function timezone($timezone)
	{
		if (!isset(self::$tzs[$timezone])) {
			self::$tzs[$timezone] = new \DateTimeZone($timezone);
		}

		return self::$tzs[$timezone];
	}

	/**
	 * Converts the value to a DateTimeInterface object or returns null
	 *
	 * @param        $datetime
	 * @param string $timezone
	 * @param bool   $immutable
	 * @return \DateTimeInterface|null
	 */
	public static function convertToDateTimeOrNull($value, $timezone = self::DEFAULT_TIMEZONE, $immutable = true)
	{
		if (!isset($value) || empty($value)) {
			return null;
		}

		return self::convertToDateTime($value, $timezone, $immutable);
	}

	/**
	 * Converts the value to a DateTimeInterface object
	 *
	 * @param        $datetime
	 * @param string $timezone
	 * @param bool   $immutable
	 * @return \DateTimeInterface
	 */
	public static function convertToDateTime($value, $timezone = self::DEFAULT_TIMEZONE, $immutable = true)
	{
		$timezone = self::timezone($timezone);
		if ($value instanceof \DateTimeInterface) {
			return $value->setTimezone($timezone);
		}

		$class = $immutable ? "DateTimeImmutable" : "DateTime";

		return new $class($value, $timezone);
	}

	/**
	 * Stack trace for Json
	 *
	 * @link  http://stackoverflow.com/a/8397118/1281788
	 * @param $branch
	 * @return array|string
	 */
	public static function stackTraceForJson($branch)
	{
		if (is_object($branch)) {
			// ideally we could list properties?
			$branch = get_class($branch);
		}
		elseif (is_array($branch)) {
			// array
			foreach ($branch as $k => $v) {
				$branch[$k] = self::stackTraceForJson($v);
			}
		}
		elseif (is_resource($branch)) {
			// resource
			$branch = (string)$branch . ' (' . get_resource_type($branch) . ')';
		}
		elseif (is_string($branch)) {
			// string (ensure it is UTF-8, see: https://bugs.php.net/bug.php?id=47130)
			$branch = utf8_encode($branch);
		}

		// other (hopefully serializable) stuff
		return $branch;
	}
}