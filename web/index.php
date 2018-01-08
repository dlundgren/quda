<?php
/**
 * Bootstrap for Queue Demo application
 *
 * @author  David Lundgren <dlundgren@syberisle.net>
 * @package quda
 */

$rootPath   = dirname(__DIR__);
$autoloader = require "{$rootPath}/vendor/autoload.php";

(new \Quda\Application($rootPath, $autoloader))->run();