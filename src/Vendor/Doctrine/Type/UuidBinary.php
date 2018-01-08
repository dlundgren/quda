<?php

/**
 * @file
 * Contains Quda\Vendor\Doctrine\Type\UuidBinary
 */

namespace Quda\Vendor\Doctrine\Type;

use Quda\Vendor\Doctrine\Collection\AbstractCollection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\BinaryType;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use Ramsey\Uuid\Uuid;

/**
 * Type that maps a PHP array to a clob SQL type.
 *
 * @since 2.0
 * @link https://gist.github.com/Sitebase/5013494
 * @link https://blog.vandenbrand.org/2015/06/25/creating-a-custom-doctrine-dbal-type-the-right-way/
 */
class UuidBinary
	extends Type
{
	const NAME = 'uuid_binary';

	public function getBindingType()
	{
		return \PDO::PARAM_LOB;
	}

	/**
	 * @param array            $fieldDeclaration
	 * @param AbstractPlatform $platform
	 * @return string
	 */
	public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		return sprintf('BINARY(%d) COMMENT \'(DC2Type:uuid_binary)\'', $fieldDeclaration['length']);
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return self::NAME;
	}

	/**
	 * Returns the WHERE clause for usage
	 *
	 * @param                  $key
	 * @param Uuid             $uuid
	 * @param AbstractPlatform $platform
	 * @return string
	 */
	public static function buildWhereClause($key, Uuid $uuid, AbstractPlatform $platform)
	{
		return sprintf("%s = X'%s'", $platform->quoteIdentifier($key), self::optimize($uuid));
	}

	/**
	 * Specialized function to add the hex conversion for the libraries
	 *
	 * @param                  $qb
	 * @param                  $key
	 * @param                  $uuid
	 * @param bool             $useConjunction
	 * @param AbstractPlatform $platform
	 */
	public static function addWhereClauseToQuery(QueryBuilder $qb, $key, $uuid, $useConjunction = true, AbstractPlatform $platform)
	{
		$key = $platform->quoteIdentifier($key);
		switch(get_class($platform)) {
			case stripos($platform, 'mysql'):
			case stripos($platform, 'mariadb'):
			case stripos($platform, 'drizzle'):
			case stripos($platform, 'sqlite'):
				$where = "HEX($key)";
				break;
			case stripos($platform, 'postgresql'):
				$where = "decode($key, 'hex')";
				break;
			case stripos($platform, 'sqlanywhere'):
				$where = "bintohex($key)";
				break;
			case stripos($platform, 'oracle'):
				$where = "RAWTOHEX($key)";
				break;
			case stripos($platform, 'sqlserver'):
			case stripos($platform, 'dblib'):
				$where = "CONVERT(CHAR(34), $key, 1)";
				break;
		}

		$where = sprintf("{$key} = X'%s'", self::optimize($uuid));
		if ($useConjunction) {
			$qb->andWhere($where);
		}
		else {
			$qb->orWhere($where);
		}

//		$qb->setParameter('ub_where_uuid', );
	}

	/**
	 * Adds the appropriate set parameter to the function
	 *
	 * @param QueryBuilder     $qb
	 * @param                  $key
	 * @param                  $uuid
	 * @param AbstractPlatform $platform
	 */
	public static function addSetToQuery(QueryBuilder $qb, $key, $uuid, AbstractPlatform $platform)
	{
		$qb->set($platform->quoteIdentifier($key), sprintf("X'%s'", self::optimize($uuid)));
	}

	/**
	 * @param mixed            $value
	 * @param AbstractPlatform $platform
	 * @return string
	 */
	public function convertToPhpValue($value, AbstractPlatform $platform)
	{
		if (empty($value)) {
			return null;
		}

		if ($value instanceof Uuid) {
			return $value;
		}

		$value= unpack('H*', $value);
		$hash = array_shift($value);

		$uuid = substr($hash, 0, 8) . '-' . substr($hash, 8, 4) . '-' . substr($hash, 12, 4) . '-' . substr($hash, 16, 4) . '-' . substr($hash, 20, 12);

		return Uuid::fromString($uuid);
	}

	/**
	 * @param mixed            $value
	 * @param AbstractPlatform $platform
	 * @return string
	 */
	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		if (empty($value)) {
			return null;
		}
		if ($value instanceof Uuid || Uuid::isValid($value)) {
			return self::optimize($value);
//			return substr($value, 14, 4) . substr($value, 9, 4) . substr($value, 0, 8) . substr($value, 19, 4) . substr($value, 24);
			// the hex should be converted to binary so we can safe on space
		}

		throw ConversionException::conversionFailed($value, self::NAME);
	}

	/**
	 * {@inheritdoc}
	 *
	 * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
	 * @return boolean
	 */
	public function requiresSQLCommentHint(AbstractPlatform $platform)
	{
		return true;
	}

	/**
	 * Optimizes the storage of the UUID
	 *
	 * @link https://www.percona.com/blog/2014/12/19/store-uuid-optimized-way/
	 * @param $value
	 * @return string
	 */
	private static function optimize($value)
	{
		return substr($value, 14, 4) . substr($value, 9, 4) . substr($value, 0, 8) . substr($value, 19, 4) . substr($value, 24);
	}

	public static function convertForSet($value)
	{
		return sprintf("X'%s'", self::optimize($value));
	}
}