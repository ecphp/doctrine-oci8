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

use function oci_new_cursor;

final class OCI8Cursor extends OCI8Statement
{
    public function __construct($dbh, OCI8Connection $conn, $sth = null)
    {
        $this->_dbh = $dbh;
        $this->_conn = $conn;
        $this->_sth = $sth ?: oci_new_cursor($dbh);
    }
}
