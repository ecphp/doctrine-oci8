<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace EcPhp\DoctrineOci8\Doctrine\DBAL\Driver\OCI8;

use Doctrine\DBAL\Driver\OCI8\Exception\Error;
use Doctrine\DBAL\Driver\OCI8\ExecutionMode;
use Doctrine\DBAL\Driver\OCI8\Statement as DBALOCI8Statement;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\Deprecations\Deprecation;
use LogicException;
use PDO;

use function assert;
use function count;
use function is_array;
use function is_int;
use function is_numeric;
use function is_resource;
use function max;
use function oci_bind_array_by_name;
use function oci_bind_by_name;

use const OCI_B_BLOB;
use const OCI_B_CLOB;
use const OCI_B_CURSOR;
use const OCI_COMMIT_ON_SUCCESS;
use const OCI_NO_AUTO_COMMIT;
use const SQLT_AFC;
use const SQLT_CHR;
use const SQLT_INT;

final class Statement implements StatementInterface
{
    private array $references = [];

    public function __construct(
        private Connection $connection,
        private $statement,
        private array $parameterMap,
        private readonly ExecutionMode $executionMode,
        private readonly DBALOCI8Statement $decoratedStatement
    ) {
        assert(is_resource($statement));
    }

    public function bindParam($column, &$variable, $type = ParameterType::STRING, $length = null): bool
    {
        $origCol = $column;

        $column = $this->parameterMap[$column] ?? $column;

        [$type, $ociType] = $this->normalizeType($type);

        // Type: Cursor.
        if (PDO::PARAM_STMT === $type || OCI_B_CURSOR === $ociType) {
            $variable = $this->connection->newCursor();
            $sth = $variable->getStatementHandle();

            return $this->bindByName($column, $sth, -1, OCI_B_CURSOR);
        }

        // Type: Null. (Must come *after* types that can expect $variable to be null, like 'cursor'.)
        if (null === $variable) {
            return $this->bindByName($column, $variable);
        }

        // Type: Array.
        if (is_array($variable)) {
            $length = $length ?? -1;

            if (!$ociType) {
                $ociType = PDO::PARAM_INT === $type ? SQLT_INT : SQLT_CHR;
            }

            return $this->bindArrayByName(
                $column,
                $variable,
                max(count($variable), 1),
                empty($variable) ? 0 : $length,
                $ociType
            );
        }

        // Type: Lob
        if (OCI_B_CLOB === $ociType || OCI_B_BLOB === $ociType) {
            $type = PDO::PARAM_LOB;
        } elseif ($ociType) {
            return $this->bindByName($column, $variable, $length ?? -1, $ociType);
        }

        return $this->decoratedStatement->bindParam($origCol, $variable, $type, $length);
    }

    public function bindValue($param, $value, $type = ParameterType::STRING): bool
    {
        [$type, $ociType] = $this->normalizeType($type);

        if (PDO::PARAM_STMT === $type || OCI_B_CURSOR === $ociType) {
            throw new LogicException('You must call "bindParam()" to bind a cursor.');
        }

        return $this->decoratedStatement->bindValue($param, $value, $type);
    }

    public function execute($params = null): Result
    {
        if (null !== $params) {
            Deprecation::trigger(
                'doctrine/dbal',
                'https://github.com/doctrine/dbal/pull/5556',
                'Passing $params to Statement::execute() is deprecated. Bind parameters using'
                    . ' Statement::bindParam() or Statement::bindValue() instead.',
            );

            foreach ($params as $key => $val) {
                if (is_int($key)) {
                    $this->bindValue($key + 1, $val, ParameterType::STRING);
                } else {
                    $this->bindValue($key, $val, ParameterType::STRING);
                }
            }
        }

        if ($this->executionMode->isAutoCommitEnabled()) {
            $mode = OCI_COMMIT_ON_SUCCESS;
        } else {
            $mode = OCI_NO_AUTO_COMMIT;
        }

        $ret = oci_execute($this->statement, $mode);

        if (!$ret) {
            throw Error::new($this->statement);
        }

        return new Result($this->statement, $this->connection);
    }

    /**
     * @param string $column
     * @param mixed  $variable
     * @param int    $maxTableLength
     * @param int    $maxItemLength
     * @param int    $type
     */
    private function bindArrayByName(
        $column,
        &$variable,
        $maxTableLength,
        $maxItemLength = -1,
        $type = SQLT_AFC
    ): bool {
        // For PHP 7's OCI8 extension (prevents garbage collection).
        $this->references[$column] = &$variable;

        return oci_bind_array_by_name($this->statement, $column, $variable, $maxTableLength, $maxItemLength, $type);
    }

    /**
     * @param string $column
     * @param mixed  $variable
     * @param int    $maxLength
     * @param int    $type
     */
    private function bindByName($column, &$variable, $maxLength = -1, $type = SQLT_CHR): bool
    {
        // For PHP 7's OCI8 extension (prevents garbage collection).
        $this->references[$column] = &$variable;

        return oci_bind_by_name($this->statement, $column, $variable, $maxLength, $type);
    }

    /**
     * @return array{0: array-key, int|null}
     */
    private function normalizeType(int|string $type): array
    {
        return match (true) {
            is_numeric($type) => [(int) $type, OCI8::isParamConstant((int) $type) ? OCI8::decodeParamConstant((int) $type) : null],
            'cursor' === strtolower($type) => [PDO::PARAM_STMT, OCI_B_CURSOR],
            default => [$type, null],
        };
    }
}
