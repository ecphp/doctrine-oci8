<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace tests\EcPhp\DoctrineOci8\Doctrine\DBAL\Test\Driver\OCI8;

use Doctrine\DBAL\Types\Type;
use EcPhp\DoctrineOci8\Doctrine\DBAL\Driver\OCI8\OCI8Connection;
use tests\EcPhp\DoctrineOci8\AbstractTestCase;

/**
 * @internal
 * @coversNothing
 */
final class DriverTest extends AbstractTestCase
{
    public function testDriverManagerReturnsWrappedOCI8Connection(): void
    {
        self::assertInstanceOf(
            OCI8Connection::class,
            $this->getConnection()->getWrappedConnection()
        );
    }

    public function testDriverRegistersCursorType(): void
    {
        $this->getConnection();

        self::assertTrue(Type::hasType('cursor'));
    }
}
