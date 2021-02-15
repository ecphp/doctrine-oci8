<?php

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

    /**
     * Gets the name of this type.
     */
    public function getName(): string
    {
        return 'cursor';
    }

    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param array                                     $fieldDeclaration The field declaration.
     * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform         The currently used database platform.
     *
     * @throws LogicException
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        throw new LogicException('Doctrine does not support SQL declarations for cursors.');
    }
}
