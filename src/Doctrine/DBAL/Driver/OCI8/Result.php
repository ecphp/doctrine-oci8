<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\DoctrineOci8\Doctrine\DBAL\Driver\OCI8;

use Doctrine\DBAL\Driver\FetchUtils;
use Doctrine\DBAL\Driver\OCI8\Exception\Error;
use Doctrine\DBAL\Driver\Result as ResultInterface;
use Doctrine\DBAL\FetchMode;

use function is_array;
use function is_resource;
use function oci_cancel;
use function oci_error;
use function oci_fetch_all;
use function oci_fetch_array;
use function oci_num_fields;
use function oci_num_rows;

use const OCI_ASSOC;
use const OCI_FETCHSTATEMENT_BY_COLUMN;
use const OCI_FETCHSTATEMENT_BY_ROW;
use const OCI_NUM;
use const OCI_RETURN_LOBS;
use const OCI_RETURN_NULLS;

final class Result implements ResultInterface
{
    private int $_defaultFetchMode = FetchMode::ASSOCIATIVE;

    private bool $checkedForCursorFields = false;

    private $connection;

    private array $cursorFields = [];

    /**
     * Used because parent::fetchAll() calls $this->fetch().
     */
    private bool $returningCursors = false;

    /**
     * Used because parent::fetchAll() calls $this->fetch().
     */
    private bool $returningResources = false;

    /**
     * @var resource
     */
    private $statement;

    /**
     * @internal The result can be only instantiated by its driver connection or statement.
     *
     * @param resource $statement
     * @param mixed $connection
     */
    public function __construct($statement, $connection)
    {
        $this->statement = $statement;
        $this->connection = $connection;
    }

    public function columnCount(): int
    {
        $count = oci_num_fields($this->statement);

        if (false !== $count) {
            return $count;
        }

        return 0;
    }

    public function fetchAllAssociative(): array
    {
        return $this->fetchAll(OCI_ASSOC, OCI_FETCHSTATEMENT_BY_ROW);
    }

    public function fetchAllNumeric(): array
    {
        return $this->fetchAll(OCI_NUM, OCI_FETCHSTATEMENT_BY_ROW);
    }

    public function fetchAssociative()
    {
        return $this->fetch(OCI_ASSOC);
    }

    public function fetchFirstColumn(): array
    {
        return $this->fetchAll(OCI_NUM, OCI_FETCHSTATEMENT_BY_COLUMN)[0];
    }

    public function fetchNumeric()
    {
        return $this->fetch(OCI_NUM);
    }

    public function fetchOne()
    {
        return FetchUtils::fetchOne($this);
    }

    public function free(): void
    {
        oci_cancel($this->statement);
    }

    public function rowCount(): int
    {
        $count = oci_num_rows($this->statement);

        if (false !== $count) {
            return $count;
        }

        return 0;
    }

    private function fetch(int $mode): array|false
    {
        [$fetchMode, $returnResources, $returnCursors] = $this->processFetchMode($mode, true);

        $row = oci_fetch_array($this->statement, $fetchMode | OCI_RETURN_NULLS | OCI_RETURN_LOBS);

        if (false === $row && oci_error($this->statement) !== false) {
            throw Error::new($this->statement);
        }

        if (!$returnResources) {
            $this->fetchCursorFields($row, $fetchMode, $returnCursors);
        }

        return $row;
    }

    /**
     * @return array<mixed>
     */
    private function fetchAll(int $mode, int $fetchStructure): array
    {
        oci_fetch_all(
            $this->statement,
            $result,
            0,
            -1,
            $mode | OCI_RETURN_NULLS | $fetchStructure | OCI_RETURN_LOBS,
        );

        return $result;
    }

    /**
     * @param array|mixed $row
     * @param int         $fetchMode
     * @param bool        $returnCursors
     *
     * @throws \Doctrine\DBAL\Driver\OCI8\OCI8Exception
     */
    private function fetchCursorFields(&$row, $fetchMode, $returnCursors): void
    {
        if (!is_array($row)) {
            $this->resetCursorFields();
        } elseif (!$this->checkedForCursorFields) {
            // This will also call fetchCursorField() on each cursor field of the first row.
            $this->findCursorFields($row, $fetchMode, $returnCursors);
        } elseif ($this->cursorFields) {
            $shared = [];

            foreach ($this->cursorFields as $field) {
                $key = (string) $row[$field];

                if (isset($shared[$key])) {
                    $row[$field] = $shared[$key];

                    continue;
                }
                $row[$field] = $this->fetchCursorValue($row[$field], $fetchMode, $returnCursors);
                $shared[$key] = &$row[$field];
            }
        }
    }

    /**
     * @param resource $resource
     * @param int $fetchMode
     * @param bool $returnCursor
     *
     * @throws \Doctrine\DBAL\Driver\OCI8\OCI8Exception
     *
     * @return array|mixed|OCI8Cursor
     */
    private function fetchCursorValue($resource, $fetchMode, $returnCursor)
    {
        /** @var OCI8Connection $conn Because my IDE complains. */
        $conn = $this->connection;
        $cursor = $conn->newCursor($resource);

        if ($returnCursor) {
            return $cursor;
        }

        $cursor->execute();
        $results = $cursor->fetchAll($fetchMode);
        $cursor->closeCursor();

        return $results;
    }

    /**
     * @param int   $fetchMode
     * @param bool  $returnCursors
     *
     * @throws \Doctrine\DBAL\Driver\OCI8\OCI8Exception
     */
    private function findCursorFields(array &$row, $fetchMode, $returnCursors): void
    {
        $shared = [];

        foreach ($row as $field => $value) {
            if (!is_resource($value)) {
                continue;
            }
            $this->cursorFields[] = $field;
            $key = (string) $value;

            if (isset($shared[$key])) {
                $row[$field] = $shared[$key];

                continue;
            }
            // We are already here, so might as well process it.
            $row[$field] = $this->fetchCursorValue($row[$field], $fetchMode, $returnCursors);
            $shared[$key] = &$row[$field];
        }
        $this->checkedForCursorFields = true;
    }

    /**
     * @param int  $fetchMode
     * @param bool $checkGlobal
     */
    private function processFetchMode($fetchMode, $checkGlobal = false): array
    {
        $returnResources = ($checkGlobal && $this->returningResources) || ($fetchMode & OCI8::RETURN_RESOURCES);
        $returnCursors = ($checkGlobal && $this->returningCursors) || ($fetchMode & OCI8::RETURN_CURSORS);
        // Must unset the flags or there will be an error.
        $fetchMode &= ~(OCI8::RETURN_RESOURCES + OCI8::RETURN_CURSORS);
        $fetchMode = (int) ($fetchMode ?: $this->_defaultFetchMode);

        return [$fetchMode, $returnResources, $returnCursors];
    }

    private function resetCursorFields(): void
    {
        $this->cursorFields = [];
        $this->checkedForCursorFields = false;
    }
}
