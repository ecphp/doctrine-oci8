<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\DoctrineOci8\Doctrine\DBAL\Driver\OCI8;

use Doctrine\DBAL\Driver\OCI8\ExecutionMode;
use Doctrine\DBAL\Driver\OCI8\Statement as DriverOCI8Statement;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\ParameterType;

use function assert;
use function is_resource;
use function oci_new_cursor;

final class Cursor implements Statement
{
    private Statement $decoratedStatement;

    // TODO: What to do with this?
    private $sth;

    public function __construct(
        private readonly Connection $connection,
        $sth = null,
    ) {
        $this->sth = $sth ?: oci_new_cursor($connection->getNativeConnection());

        assert(is_resource($this->sth));

        $this->decoratedStatement = new DriverOCI8Statement(
            $this->connection,
            $this->sth,
            [],
            new ExecutionMode()
        );
    }

    public function bindParam($param, &$variable, $type = ParameterType::STRING, $length = null): bool
    {
        return $this->decoratedStatement->bindParam($param, $variable, $type, $length);
    }

    public function bindValue($param, $value, $type = ParameterType::STRING): bool
    {
        return $this->decoratedStatement->bindValue($param, $value, $type);
    }

    public function execute($params = null): Result
    {
        return $this->decoratedStatement->execute($params);
    }

    public function getStatementHandle()
    {
        return $this->sth;
    }
}
