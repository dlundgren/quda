<?php

/**
 * @file
 * Contains Quda\Vendor\Doctrine\Type\Uuid
 */

namespace Quda\Vendor\Doctrine\Type;

use Doctrine\DBAL\Types\BinaryType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Type that maps a PHP array to a clob SQL type.
 *
 * @since 2.0
 * @link https://gist.github.com/Sitebase/5013494
 * @link https://blog.vandenbrand.org/2015/06/25/creating-a-custom-doctrine-dbal-type-the-right-way/
 */
class Uuid
	extends BinaryType
{
	const NAME = 'uuid';
	/**
	 * @param array            $fieldDeclaration
	 * @param AbstractPlatform $platform
	 * @return string
	 */
	public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		return sprintf('BINARY(%d) COMMENT \'(DC2Type:uuid)\'', $fieldDeclaration['length']);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'uuid';
	}

	/**
	 * @param mixed            $value
	 * @param AbstractPlatform $platform
	 * @return string
	 */
	public function convertToPhpValue($value, AbstractPlatform $platform)
	{
		if ($value !== null) {
			$value= unpack('H*', $value);
			$hash = array_shift($value);

			$uuid = substr($hash, 0, 8) . '-' . substr($hash, 8, 4) . '-' . substr($hash, 12, 4) . '-' . substr($hash, 16, 4) . '-' . substr($hash, 20, 12);

			return $uuid;
		}
	}

	/**
	 * @param mixed            $value
	 * @param AbstractPlatform $platform
	 * @return string
	 */
	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		if ($value !== null) {
			return hex2bin(substr($value, 14, 4) . substr($value, 9, 4) . substr($value, 0, 8) . substr($value, 19, 4) . substr($value, 24));
		}
	}
}