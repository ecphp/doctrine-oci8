<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

$autoloader = require __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv(true);
$dotenv->loadEnv(__DIR__ . '/../.env');
