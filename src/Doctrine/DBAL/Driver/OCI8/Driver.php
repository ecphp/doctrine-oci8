<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\DoctrineOci8\Doctrine\DBAL\Driver\OCI8;

use Doctrine\DBAL\Driver\OCI8\Driver as BaseDriver;
use Doctrine\DBAL\Driver\OCI8\OCI8Exception;
use Doctrine\DBAL\Exception as DBALException;
use Throwable;

use const OCI_DEFAULT;

final class Driver extends BaseDriver
{
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
        } catch (OCI8Exception $e) {
            throw DBALException::driverException($this, $e);
        } catch (Throwable $e) {
            throw $e;
        }

        return $connection;
    }
}
