<?php

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
