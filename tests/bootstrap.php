<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ecphp
 */

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

$autoloader = require __DIR__ . '/../vendor/autoload.php';

(new Dotenv())->loadEnv(__DIR__ . '/../.env');
