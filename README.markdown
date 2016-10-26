# PHPChunkit

[![Build Status](https://secure.travis-ci.org/jwage/phpchunkit.png?branch=master)](http://travis-ci.org/jwage/phpchunkit)

PHPChunkit is a library that sits on top of PHPUnit and adds additional
functionality to make it easier to work with large unit and functional
test suites. The primary feature is test chunking and database sandboxing
which gives you the ability to run your tests in parallel chunks on the
same server or across multiple servers.

In order to run functional tests in parallel on the same server, you need to
have a concept of database sandboxing. You are responsible for implementing
the sandbox preparation, database creation, and sandbox cleanup. PHPChunkit
provides a framework for you to hook in to so you can prepare your application
environment sandbox.

## Parallel Execution Example

Imagine you have 100 tests and each test takes 1 second. When the tests are
ran serially, it will take 100 seconds to complete. But if you split the 100
tests in to 10 even chunks and run the chunks in parallel, it will in theory
take only 10 seconds to complete.

Now imagine you have a two node Jenkins cluster. You can spread the run of each
chunk across the 2 servers with 5 parallel jobs running on each server:

### Jenkins Server #1 with 5 job workers

    ./bin/phpchunkit run --num-chunks=10 --chunk=1 --sandbox --create-dbs
    ./bin/phpchunkit run --num-chunks=10 --chunk=2 --sandbox --create-dbs
    ./bin/phpchunkit run --num-chunks=10 --chunk=3 --sandbox --create-dbs
    ./bin/phpchunkit run --num-chunks=10 --chunk=4 --sandbox --create-dbs
    ./bin/phpchunkit run --num-chunks=10 --chunk=5 --sandbox --create-dbs

### Jenkins Server #2 with 5 job workers

    ./bin/phpchunkit run --num-chunks=10 --chunk=6 --sandbox --create-dbs
    ./bin/phpchunkit run --num-chunks=10 --chunk=7 --sandbox --create-dbs
    ./bin/phpchunkit run --num-chunks=10 --chunk=8 --sandbox --create-dbs
    ./bin/phpchunkit run --num-chunks=10 --chunk=9 --sandbox --create-dbs
    ./bin/phpchunkit run --num-chunks=10 --chunk=10 --sandbox --create-dbs

## Installation

Install with composer:

    composer require jwage/phpchunkit

## Setup

As mentioned above in the introduction, you are responsible for implementing
the sandbox preparation, database creation and sandbox cleanup processes
by adding [EventDispatcher](http://symfony.com/doc/current/components/event_dispatcher.html)
listeners. You can listen for the following events:

- `sandbox.prepare` - Use the `Events::SANDBOX_PREPARE` constant.
- `databases.create` - Use the `Events::DATABASES_CREATE` constant.
- `sandbox.cleanup` - Use the `Events::SANDBOX_CLEANUP` constant.

Take a look at the listeners implemented in this projects test suite for an example:

- [SandboxPrepare.php](https://github.com/jwage/phpchunkit/blob/master/tests/PHPChunkit/Test/Listener/SandboxPrepare.php)
- [DatabasesCreate.php](https://github.com/jwage/phpchunkit/blob/master/tests/PHPChunkit/Test/Listener/DatabasesCreate.php)
- [SandboxCleanup.php](https://github.com/jwage/phpchunkit/blob/master/tests/PHPChunkit/Test/Listener/SandboxCleanup.php)

### Configuration

Here is an example `phpchunkit.xml` file. Place this in the root of your project:

```xml
<?xml version="1.0" encoding="UTF-8"?>

<phpchunkit bootstrap="./tests/phpchunkit_bootstrap.php" root-dir="./" tests-dir="./tests" phpunit-path="./vendor/bin/phpunit">
    <watch-directories>
        <watch-directory>./src</watch-directory>
        <watch-directory>./tests</watch-directory>
    </watch-directories>

    <database-names>
        <database-name>testdb1</database-name>
        <database-name>testdb2</database-name>
    </database-names>

    <events>
        <listener event="sandbox.prepare">
            <class>PHPChunkit\Test\Listener\SandboxPrepare</class>
        </listener>

        <listener event="sandbox.cleanup">
            <class>PHPChunkit\Test\Listener\SandboxCleanup</class>
        </listener>

        <listener event="databases.create">
            <class>PHPChunkit\Test\Listener\DatabasesCreate</class>
        </listener>
    </events>
</phpchunkit>
```

The `tests/phpchunkit_bootstrap.php` file is loaded after the XML is loaded
and gives you the ability to do more advanced things with the [Configuration](https://github.com/jwage/phpchunkit/blob/master/src/PHPChunkit/Configuration.php).

Here is an example:

```php
<?php

use PHPChunkit\Events;

// Manipulate $configuration which is an instance of PHPChunkit\Configuration

$rootDir = $configuration->getRootDir();

$configuration = $configuration
    ->setWatchDirectories([
        sprintf('%s/src', $rootDir),
        sprintf('%s/tests', $rootDir)
    ])
    ->setTestsDirectory(sprintf('%s/tests', $rootDir))
    ->setPhpunitPath(sprintf('%s/vendor/bin/phpunit', $rootDir))
    ->setDatabaseNames(['testdb1', 'testdb2'])
;

$eventDispatcher = $configuration->getEventDispatcher();

$eventDispatcher->addListener(Events::SANDBOX_PREPARE, function() {
    // prepare the sandbox
});

$eventDispatcher->addListener(Events::SANDBOX_CLEANUP, function() {
    // cleanup the sandbox
});

$eventDispatcher->addListener(Events::DATABASES_CREATE, function() {
    // create databases
});
```

## Functional Tests

Currently it is required that functional tests be annotated with `@group functional`. Here is an example:

```php
<?php

namespace MyProject/Test;

use PHPUnit_Framework_TestCase;

/**
 * @group functional
 */
class MyTest extends PHPUnit_Framework_TestCase
{
    // ...
}
```

## Available Commands

Run all tests:

    ./bin/phpchunkit run

Run just unit tests:

    ./bin/phpchunkit run --exclude-group=functional

Run all functional tests:

    ./bin/phpchunkit run --group=functional

Run a specific chunk of functional tests:

    ./bin/phpchunkit run --num-chunks=5 --chunk=1

Watch your code for changes and run tests:

    ./bin/phpchunkit watch

Run tests that match a filter:

    ./bin/phpchunkit filter BuildSandbox

Run a specific file:

    ./bin/phpchunkit file tests/Command/BuildSandboxTest.php

Run tests for changed files:

> Note: This relies on git to know which files have changed.

    ./bin/phpchunkit run --changed

Create databases:

    ./bin/phpchunkit create-dbs

## Screenshot

![PHPChunkit Screenshot](https://raw.githubusercontent.com/jwage/PHPChunkit/master/docs/phpchunkit.png)

## Demo Project

Take a look at [jwage/phpchunkit-demo](https://github.com/jwage/phpchunkit-demo) to see how it can be integrated in to an existing PHPUnit project.
