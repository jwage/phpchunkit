# PHPChunkit

This library sits on top of PHPUnit and adds sugar to make it easier to work with large test suites.
The primary feature is test chunking which gives you the ability to run your tests in parallel chunks.

![PHPChunkit Screenshot](https://raw.githubusercontent.com/jwage/PHPChunkit/master/docs/phpchunkit.png)

TODO:

- Dependencies need to be cleaned up.
- Move command line classes with execute() methods to own namespace.
- Try to identify and remove assumptions/hardcoded things that won't work for other people.

## Installation

Install with composer:

    composer require jwage/phpchunkit

## Example Commands

Run all tests:

    ./bin/phpchunkit all

Run just unit tests:

    ./bin/phpchunkit unit

Run all functional tests:

    ./bin/phpchunkit functional

Run a specific chunk of functional tests:

    ./bin/phpchunkit functional --chunk=1

Watch your code for changes and run tests:

    ./bin/phpchunkit watch

Run tests that match a filter:

    ./bin/phpchunkit filter BuildSandbox

Run a specific file:

    ./bin/phpchunkit file tests/PHPChunkit/Test/BuildSandboxTest.php

Run tests for changed files:

> Note: This relies on git to know which files have changed.

    ./bin/phpchunkit changed

Create test databases and schema:

    ./bin/phpchunkit create-dbs

With the `chunk`, `num-chunks`, `sandbox` and `create-dbs` options you can run multiple
chunks in parallel in sandboxed environments across multiple servers or even on a single server.
Here is an example:

    # Server 1
    ./bin/phpchunkit functional --num-chunks=4 --chunk=1 --sandbox --create-dbs
    ./bin/phpchunkit functional --num-chunks=4 --chunk=2 --sandbox --create-dbs

    # Server 2
    ./bin/phpchunkit functional --num-chunks=4 --chunk=3 --sandbox --create-dbs
    ./bin/phpchunkit functional --num-chunks=4 --chunk=4 --sandbox --create-dbs

Hook this up to something like Jenkins and you can scale your tests and keep them fast!
At [OpenSky](https://www.opensky.com) our test suite takes 25 to 30 minutes when ran serially
but when ran across 14 parallel jobs on a single Jenkins server they take ~2 minutes.

## Setup

It is important to note that you are responsible for implementing the sandbox preparation,
database creation and sandbox cleanup processes by adding [EventDispatcher](http://symfony.com/doc/current/components/event_dispatcher.html) listeners. You can listen for the following events:

- `sandbox.prepare` - Use the `Events::SANDBOX_PREPARE` constant.
- `sandbox.cleanup` - Use the `Events::SANDBOX_CLEANUP` constant.
- `databases.create` - Use the `Events::DATABASES_CREATE` constant.

Here is an example `phpchunkit.xml` file. Place this in the root of your project:

```xml
<?xml version="1.0" encoding="UTF-8"?>

<phpchunkit root-dir="./" tests-dir="./tests" phpunit-path="./vendor/bin/phpunit">
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
