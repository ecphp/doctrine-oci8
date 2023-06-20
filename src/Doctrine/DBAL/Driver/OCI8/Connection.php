<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\DoctrineOci8\Doctrine\DBAL\Driver\OCI8;

use Doctrine\DBAL\Driver\OCI8\Connection as DBALOCI8Connection;
use Doctrine\DBAL\Driver\OCI8\ConvertPositionalToNamedPlaceholders;
use Doctrine\DBAL\Driver\OCI8\ExecutionMode;
use Doctrine\DBAL\Driver\OCI8\Statement as DBALOCI8Statement;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\ServerInfoAwareConnection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\SQL\Parser;

use function assert;
use function is_resource;

final class Connection implements ServerInfoAwareConnection
{
    private DBALOCI8Connection $connection;

    private ExecutionMode $executionMode;

    private Parser $parser;

    public function __construct(DBALOCI8Connection $connection)
    {
        $this->connection = $connection;
        $this->parser = new Parser(false);
        $this->executionMode = new ExecutionMode();
    }

    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    public function commit()
    {
        return $this->connection->commit();
    }

    public function exec(string $sql): int
    {
        return $this->connection->exec($sql);
    }

    public function getNativeConnection()
    {
        return $this->connection->getNativeConnection();
    }

    public function getServerVersion()
    {
        return $this->connection->getServerVersion();
    }

    public function lastInsertId($name = null)
    {
        return $this->connection->lastInsertId($name);
    }

    public function newCursor($sth = null): Cursor
    {
        return new Cursor(
            $this,
            $sth,
        );
    }

    public function prepare(string $sql): Statement
    {
        $visitor = new ConvertPositionalToNamedPlaceholders();

        $this->parser->parse($sql, $visitor);

        $statement = oci_parse($this->connection->getNativeConnection(), $visitor->getSQL());
        assert(is_resource($statement));

        $parameterMap = $visitor->getParameterMap();

        return new Statement(
            $this,
            $statement,
            $parameterMap,
            $this->executionMode,
            new DBALOCI8Statement(
                $this->connection,
                $statement,
                $parameterMap,
                $this->executionMode
            )
        );
    }

    public function query(string $sql): Result
    {
        return $this->connection->query($sql);
    }

    public function quote($value, $type = ParameterType::STRING)
    {
        return $this->connection->quote($value, $type);
    }

    public function rollBack()
    {
        return $this->connection->rollBack();
    }
}
