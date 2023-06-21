<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\DoctrineOci8\Doctrine\DBAL\Driver\OCI8;

use Doctrine\DBAL\Driver\AbstractOracleDriver;
use Doctrine\DBAL\Driver\OCI8\Driver as BaseDriver;
use Doctrine\DBAL\Driver\OCI8\Exception\ConnectionFailed;
use Throwable;

final class Driver extends AbstractOracleDriver
{
    private BaseDriver $driver;

    public function __construct(
    ) {
        $this->driver = new BaseDriver();
    }

    public function connect(
        array $params,
    ): Connection {
        try {
            $connection = new Connection($this->driver->connect($params));
        } catch (Throwable) {
            throw ConnectionFailed::new();
        }

        return $connection;
    }
}
