[![Latest Stable Version][latest stable version]][packagist]
 [![GitHub stars][github stars]][packagist]
 [![Total Downloads][total downloads]][packagist]
 [![GitHub Workflow Status][github workflow status]][github actions]
 [![Scrutinizer code quality][code quality]][scrutinizer code quality]
 [![Type Coverage][type coverage]][sheperd type coverage]
 [![Code Coverage][code coverage]][scrutinizer code quality]
 [![Read the Docs][badge readthedocs]][http readthedocs]
 [![License][license]][packagist]

# Doctrine OCI8 Driver

The Doctrine OCI8 driver with cursor support, for PHP >= 7.4.

This is a fork of the original package [develpup/doctrine-oci8-extended][http develpup/doctrine-oci8-extended] from Jason Hofer.

## Installation

`composer require ecphp/doctrine-oci8`

## Configuration

### Symfony 5

Use the [ecphp/doctrine-oci8-bundle][http ecphp/doctrine-oci8-bundle] to automatically configure the parameters.

If you prefer modifying the configuration, edit the `doctrine.yaml` as such:

```yaml
doctrine:
    dbal:
        driver_class: EcPhp\DoctrineOci8\Doctrine\DBAL\Driver\OCI8\Driver
        types:
            cursor:  EcPhp\DoctrineOci8\Doctrine\DBAL\Types\CursorType
```

## Usage

```php
<?php

namespace App;

use Doctrine\DBAL\Types\Type;
use EcPhp\DoctrineOci8\Doctrine\DBAL\Types\CursorType;
use EcPhp\DoctrineOci8\Doctrine\DBAL\Driver\OCI8\Driver;

include __DIR__ .'/vendor/autoload.php';

// Register the custom type.
if (false === Type::hasType('cursor')) {
    Type::addType('cursor', CursorType::class);
}

$config = new Doctrine\DBAL\Configuration();
$params = [
    'dbname'      => 'database_sid',
    'user'        => 'database_username',
    'password'    => 'database_password',
    'host'        => 'database.host',
    'port'        => 1521,
    'persistent'  => true,
    'driverClass' => Driver::class, // This is where we load the driver.
];
$conn = Doctrine\DBAL\DriverManager::getConnection($params, $config);

$stmt = $conn->prepare('BEGIN MY_STORED_PROCEDURE(:user_id, :cursor); END;');
$stmt->bindValue('user_id', 42);
$stmt->bindParam('cursor', $cursor, \PDO::PARAM_STMT);
$stmt->execute();

/** @var $cursor EcPhp\DoctrineOci8\Doctrine\DBAL\Driver\OCI8\OCI8Cursor */
$cursor->execute();

while ($row = $cursor->fetch()) {
    print_r($row);
    echo PHP_EOL;
}

$cursor->closeCursor();
$stmt->closeCursor();
```

## Types

For `OCI8` types that are not represented by `PDO::PARAM_` constants, pass
`OCI8::PARAM_` constants as the `type` argument of `bindValue()` and
`bindParam()`.

## Cursors

Cursors can be specified as `PDO::PARAM_STMT`, `OCI8::PARAM_CURSOR`, or just
`'cursor'`. Only the `bindParam()` method can be used to bind a cursor to
a statement.

## Sub-Cursors

Cursor resources returned in a column of a result set are automatically fetched.
You can change this behavior by passing in one of these *fetch mode* flags:

- `OCI8::RETURN_RESOURCES` to return the raw PHP resources.
- `OCI8::RETURN_CURSORS` to return the `OCI8Cursor` objects that have not
   yet been executed.

```php
use Doctrine\DBAL\Driver\OCI8\OCI8;

$rows = $stmt->fetchAll(\PDO::FETCH_ASSOC+OCI8::RETURN_CURSORS);
$rows = $stmt->fetchAll(\PDO::FETCH_BOTH+OCI8::RETURN_RESOURCES);
```

*Special thanks to Michal Tichý for his patch.*

## Tests

In order to have a working development environment, tests are Docker based.

To run the tests, do the following steps

1. `cp .env.example .env`
2. `docker-compose up -d`
3. `docker-compose exec php ./vendor/bin/phpunit`
4. `CTRL+C`
5. `docker-compose down`

[http develpup/doctrine-oci8-extended]: https://github.com/jasonhofer/doctrine-oci8-extended
[http ecphp/doctrine-oci8-bundle]: https://github.com/ecphp/doctrine-oci8-bundle
[packagist]: https://packagist.org/packages/ecphp/doctrine-oci8
[latest stable version]: https://img.shields.io/packagist/v/ecphp/doctrine-oci8.svg?style=flat-square
[github stars]: https://img.shields.io/github/stars/ecphp/doctrine-oci8.svg?style=flat-square
[total downloads]: https://img.shields.io/packagist/dt/ecphp/doctrine-oci8.svg?style=flat-square
[github workflow status]: https://img.shields.io/github/workflow/status/ecphp/doctrine-oci8/Continuous%20Integration?style=flat-square
[code quality]: https://img.shields.io/scrutinizer/quality/g/ecphp/doctrine-oci8/master.svg?style=flat-square
[scrutinizer code quality]: https://scrutinizer-ci.com/g/ecphp/doctrine-oci8/?branch=master
[type coverage]: https://img.shields.io/badge/dynamic/json?style=flat-square&color=color&label=Type%20coverage&query=message&url=https%3A%2F%2Fshepherd.dev%2Fgithub%2Fecphp%2Fdoctrine-oci8%2Fcoverage
[sheperd type coverage]: https://shepherd.dev/github/ecphp/doctrine-oci8
[code coverage]: https://img.shields.io/scrutinizer/coverage/g/ecphp/doctrine-oci8/master.svg?style=flat-square
[license]: https://img.shields.io/packagist/l/ecphp/doctrine-oci8.svg?style=flat-square
[github actions]: https://github.com/ecphp/doctrine-oci8/actions
[badge readthedocs]: https://img.shields.io/readthedocs/ecphp-doctrine-oci8?style=flat-square
[http readthedocs]: https://ecphp-doctrine-oci8.readthedocs.io/
