<?php

/*
 * This file is part of the doctrine-oci8-extended package.
 *
 * (c) Jason Hofer <jason.hofer@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Doctrine\DBAL\Test;

use Doctrine\DBAL;
use PHPUnit_Framework_TestCase;
use Doctrine\DBAL\Driver\OCI8Ext\Driver;
use ReflectionObject;
use RuntimeException;

use function getenv;
use function implode;
use function oci_close;
use function oci_connect;
use function oci_error;
use function oci_execute;
use function oci_parse;
use function sprintf;

use const OCI_DEFAULT;

/**
 * Class AbstractTestCase
 *
 * @package Doctrine\DBAL\Test
 * @author  Jason Hofer <jason.hofer@gmail.com>
 * 2018-02-23 4:26 PM
 */
abstract class AbstractTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var DBAL\Connection
     */
    private $connection;

    /**
     * @var OciWrapper
     */
    private $oci;

    /**
     * @return DBAL\Connection
     *
     * @throws DBAL\DBALException
     */
    protected function getConnection() : DBAL\Connection
    {
        if ($this->connection) {
            return $this->connection;
        }

        $params = [
            'user'        => getenv('DB_USER'),
            'password'    => getenv('DB_PASSWORD'),
            'host'        => getenv('DB_HOST'),
            'port'        => getenv('DB_PORT'),
            'dbname'      => getenv('DB_SCHEMA'),
            'driverClass' => Driver::class,
        ];

        $config = new DBAL\Configuration();

        return $this->connection = DBAL\DriverManager::getConnection($params, $config);
    }

    protected function getPropertyValue($obj, $prop)
    {
        $rObj  = new ReflectionObject($obj);
        $rProp = $rObj->getProperty($prop);
        $rProp->setAccessible(true);

        return $rProp->getValue($obj);
    }

    protected function invokeMethod($obj, $method, array $args = [])
    {
        $rObj    = new ReflectionObject($obj);
        $rMethod = $rObj->getMethod($method);
        $rMethod->setAccessible(true);

        return $rMethod->invokeArgs($obj, $args);
    }

    /**
     * @return OciWrapper
     */
    protected function oci() : OciWrapper
    {
        return $this->oci ?: ($this->oci = new OciWrapper());
    }
}
