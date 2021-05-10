<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace EcPhp\DoctrineOci8\Doctrine\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use LogicException;
use PDO;

final class CursorType extends Type
{
    public function getBindingType(): int
    {
        return PDO::PARAM_STMT;
    }

    public function getName(): string
    {
        return 'cursor';
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        throw new LogicException('Doctrine does not support SQL declarations for cursors.');
    }
}
