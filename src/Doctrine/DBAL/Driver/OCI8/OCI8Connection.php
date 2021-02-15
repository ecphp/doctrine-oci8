<?php

declare(strict_types=1);

namespace EcPhp\DoctrineOci8\Doctrine\DBAL\Driver\OCI8;

use Doctrine\DBAL\Driver\OCI8\OCI8Connection as BaseConnection;

final class OCI8Connection extends BaseConnection
{
    /**
     * @param resource $sth
     */
    public function newCursor($sth = null): OCI8Cursor
    {
        return new OCI8Cursor($this->dbh, $this, $sth);
    }

    /**
     * @param string $prepareString
     */
    public function prepare($prepareString): OCI8Statement
    {
        return new OCI8Statement($this->dbh, $prepareString, $this);
    }
}
