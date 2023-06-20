<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

namespace tests\EcPhp\DoctrineOci8\Doctrine\DBAL\Test\Driver\OCI8;

use EcPhp\DoctrineOci8\Doctrine\DBAL\Driver\OCI8\Statement as OCI8Statement;
use tests\EcPhp\DoctrineOci8\AbstractTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class OCI8ConnectionTest extends AbstractTestCase
{
    public function testPrepareReturnsWrappedOCI8Statement(): void
    {
        $stmt = $this->getConnection()->prepare('SELECT * FROM SYS.DUAL');

        $driverStmt = $this->getPropertyValue($stmt, 'stmt');

        self::assertInstanceOf(OCI8Statement::class, $driverStmt);
    }
}
