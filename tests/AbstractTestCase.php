<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace tests\EcPhp\DoctrineOci8;

use Doctrine\DBAL;
use Doctrine\DBAL\Types\Type;
use EcPhp\DoctrineOci8\Doctrine\DBAL\Driver\OCI8\Driver;
use EcPhp\DoctrineOci8\Doctrine\DBAL\Types\CursorType;
use PHPUnit\Framework\TestCase;
use ReflectionObject;

/**
 * @internal
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * @var DBAL\Connection
     */
    private $connection;

    private OciWrapper $oci;

    /**
     * @throws DBAL\DBALException
     */
    protected function getConnection(): DBAL\Connection
    {
        if ($this->connection) {
            return $this->connection;
        }

        $params = [
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWORD'],
            'host' => $_ENV['DB_HOST'],
            'port' => $_ENV['DB_PORT'],
            'dbname' => $_ENV['DB_SCHEMA'],
            'driverClass' => Driver::class,
        ];

        if (false === Type::hasType('cursor')) {
            Type::addType('cursor', CursorType::class);
        }

        $config = new DBAL\Configuration();

        return $this->connection = DBAL\DriverManager::getConnection($params, $config);
    }

    protected function getPropertyValue($obj, $prop)
    {
        $rObj = new ReflectionObject($obj);
        $rProp = $rObj->getProperty($prop);
        $rProp->setAccessible(true);

        return $rProp->getValue($obj);
    }

    protected function invokeMethod($obj, $method, array $args = [])
    {
        $rObj = new ReflectionObject($obj);
        $rMethod = $rObj->getMethod($method);
        $rMethod->setAccessible(true);

        return $rMethod->invokeArgs($obj, $args);
    }

    protected function oci(): OciWrapper
    {
        return $this->oci ?: ($this->oci = new OciWrapper());
    }
}
