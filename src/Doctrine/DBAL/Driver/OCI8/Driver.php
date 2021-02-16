<?php

declare(strict_types=1);

namespace EcPhp\DoctrineOci8\Doctrine\DBAL\Driver\OCI8;

use Doctrine\DBAL\Driver\OCI8\Driver as BaseDriver;
use Doctrine\DBAL\Driver\OCI8\OCI8Exception;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Types\CursorType;
use Doctrine\DBAL\Types\Type;
use Exception;

use const OCI_DEFAULT;

final class Driver extends BaseDriver
{
    public function __construct()
    {
        if (false === Type::hasType('cursor')) {
            Type::addType('cursor', CursorType::class);
        }
    }

    /**
     * @param mixed|null $username
     * @param mixed|null $password
     *
     * @throws Exception
     */
    public function connect(
        array $params,
        $username = null,
        $password = null,
        array $driverOptions = []
    ): OCI8Connection {
        try {
            $connection = new OCI8Connection(
                $username,
                $password,
                $this->_constructDsn($params),
                $params['charset'] ?? null,
                $params['sessionMode'] ?? OCI_DEFAULT,
                $params['persistent'] ?? false
            );
        } catch (Exception $e) {
            if ($e instanceof OCI8Exception) {
                throw DBALException::driverException($this, $e);
            }

            throw $e;
        }

        return $connection;
    }
}
