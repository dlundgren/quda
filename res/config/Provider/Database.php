<?php

/**
 * @file
 * Contains Quda\_Config\Provider\Database
 */

namespace Quda\_Config\Provider;

use Doctrine\DBAL\Configuration as DoctrineConfiguration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use FastFrame\Kernel\Provider;
use Interop\Container\ContainerInterface;
use Quda\Vendor\Doctrine\Type\UuidBinary;
use Quda\Vendor\FastFrame\Kernel\IsProvider;

/**
 * Database configuration and setup
 *
 * @package Quda\_Config
 */
class Database
	implements Provider
{
	use IsProvider;

	public function define(ContainerInterface $di)
	{
		$options = [
			\PDO::ATTR_CASE => \PDO::CASE_LOWER,
		];
		if (strpos($this->env['db_uri_local'], 'mysql:') !== false) {
			$options[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'utf8'";
		}
		$di->set(
			'database:queue',
			$di->lazy(
				function () use (&$options) {
					return DriverManager::getConnection(
						[
							'url'           => $this->env['db_uri_local'],
							'driverOptions' => $options
						], new DoctrineConfiguration());
				}));
	}

	public function modify(ContainerInterface $di)
	{
		$db = $di->get('database:queue');

		Type::addType(UuidBinary::NAME, UuidBinary::class);
		$db->getDatabasePlatform()->registerDoctrineTypeMapping(UuidBinary::NAME, UuidBinary::NAME);
	}
}