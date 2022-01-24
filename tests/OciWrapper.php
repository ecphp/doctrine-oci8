<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace tests\EcPhp\DoctrineOci8;

use const OCI_DEFAULT;

final class OciWrapper
{
    private $dbh;

    public function __construct()
    {
        // Prevent OCI_SUCCESS_WITH_INFO: ORA-28002: the password will expire within 7 days
        $this->execute('ALTER PROFILE DEFAULT LIMIT PASSWORD_LIFE_TIME UNLIMITED');

        // We must change the password in order to take this in account.
        oci_password_change(
            $this->connect(),
            getenv('DB_USER'),
            getenv('DB_PASSWORD'),
            getenv('DB_PASSWORD')
        );
    }

    public function close(): bool
    {
        $result = oci_close($this->dbh);
        $this->dbh = null;

        return $result;
    }

    public function connect()
    {
        if (!$this->dbh) {
            $this->dbh = oci_connect(
                getenv('DB_USER'),
                getenv('DB_PASSWORD'),
                '//' . getenv('DB_HOST') . ':' . getenv('DB_PORT') . '/' . getenv('DB_SCHEMA'),
                getenv('DB_CHARSET'),
                OCI_DEFAULT
            );

            if (!$this->dbh) {
                /** @var array $m */
                $m = oci_error();

                throw new RuntimeException($m['message']);
            }
        }

        return $this->dbh;
    }

    public function createTable($name, array $columns)
    {
        $this->drop('table', $name);

        return $this->execute(sprintf('CREATE TABLE %s (%s)', $name, implode(', ', $columns)));
    }

    /**
     * https://stackoverflow.com/questions/1799128/oracle-if-table-exists.
     *
     * @param string $type
     * @param string $name
     */
    public function drop($type, $name): bool
    {
        static $codes = [
            'COLUMN' => '-904',
            'TABLE' => '-942',
            'CONSTRAINT' => '-2443',
            'FUNCTION' => '-4043',
            'PACKAGE' => '-4043',
            'PROCEDURE' => '-4043',
        ];
        $type = strtoupper($type);
        $code = $codes[$type];

        if (false !== strpos('COLUMN CONSTRAINT', $type)) {
            $pos = strrpos($name, '.');
            $table = substr($name, 0, $pos);  // "PACKAGE_NAME.TABLE_NAME" or just "TABLE_NAME"
            $column = substr($name, $pos + 1); // "COLUMN_NAME"
            $query = "ALTER TABLE {$table} DROP {$type} {$column}";
        } else {
            $query = "DROP {$type} {$name}";
        }
        $sql = "
BEGIN
EXECUTE IMMEDIATE '{$query}';
EXCEPTION
WHEN OTHERS THEN
IF SQLCODE != {$code} THEN
RAISE;
END IF;
END;
";

        return (bool) $this->execute($sql);
    }

    public function execute($sql)
    {
        $stmt = $this->parse($sql);

        return oci_execute($stmt) ? $stmt : false;
    }

    public function parse($sql)
    {
        return oci_parse($this->connect(), $sql);
    }
}
