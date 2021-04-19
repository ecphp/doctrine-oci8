<?php

/**
 * Copyright (c) 2017-2021, ECPHP
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\DoctrineOci8\Doctrine\DBAL\Driver\OCI8;

use Doctrine\DBAL\Driver\OCI8\OCI8Connection as BaseConnection;

final class OCI8Connection extends BaseConnection
{
    public function newCursor($sth = null): OCI8Cursor
    {
        return new OCI8Cursor($this->dbh, $this, $sth);
    }

    public function prepare($prepareString): OCI8Statement
    {
        return new OCI8Statement($this->dbh, $prepareString, $this);
    }
}
