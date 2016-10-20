# PHPChunkit

This library sits on top of PHPUnit and adds sugar to make it easier to work with large test suites.
The primary feature is test chunking which gives you the ability to run your tests in parallel chunks.

![PHPChunkit Screenshot](https://raw.githubusercontent.com/jwage/PHPChunkit/master/docs/phpchunkit.png)

TODO:

- Dependencies need to be cleaned up.
- Move command line classes with execute() methods to own namespace.
- Try to identify and remove assumptions/hardcoded things that won't work for other people.

## Example Commands

Run all tests:

    ./bin/tester all

Run just unit tests:

    ./bin/tester unit

Run all functional tests:

    ./bin/tester functional

Run a specific chunk of functional tests:

    ./bin/tester functional --chunk=1

Watch your code for changes and run tests:

    ./bin/tester watch

Run tests that match a filter:

    ./bin/tester filter BuildSandbox

Run a specific file:

    ./bin/tester file tests/PHPChunkit/Test/BuildSandboxTest.php

Run tests for changed files:

    ./bin/tester changed

Create test databases and schema:

    ./bin/tester create-dbs

With the `chunk`, `num-chunks`, `sandbox` and `create-dbs` options you can run multiple
chunks in parallel in sandboxed environments across multiple servers. Here is an
example:

    # Server 1
    ./bin/tester functional --num-chunks=4 --chunk=1 --sandbox --create-dbs
    ./bin/tester functional --num-chunks=4 --chunk=2 --sandbox --create-dbs

    # Server 2
    ./bin/tester functional --num-chunks=4 --chunk=3 --sandbox --create-dbs
    ./bin/tester functional --num-chunks=4 --chunk=4 --sandbox --create-dbs

Hook this up to something like Jenkins and you can scale your tests and keep them fast!
At [OpenSky](https://www.opensky.com) our test suite takes 25 to 30 minutes when ran serially
but when ran across 14 parallel jobs on a single Jenkins server they take ~2 minutes.

## Setup

It is important to note that you are responsible for implementing the sandbox preparation,
database creation and sandbox cleanup processes by adding [EventDispatcher](http://symfony.com/doc/current/components/event_dispatcher.html) listeners. You can listen for the following events:

- `sandbox.prepare` - Use the `Events::SANDBOX_PREPARE` constant.
- `sandbox.cleanup` - Use the `Events::SANDBOX_CLEANUP` constant.
- `databases.create` - Use the `Events::DATABASES_CREATE` constant.

Here is an example setup:

```php
#!/usr/bin/env php
<?php

use PHPChunkit\Configuration;
use PHPChunkit\Events;
use PHPChunkit\TesterApplication;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

$rootDir = realpath(__DIR__.'/..');
$sourceDir = sprintf('%s/src', $rootDir);
$testsDir = sprintf('%s/tests', $rootDir);
$phpunitPath = sprintf('%s/vendor/bin/phpunit', $rootDir);

require_once $rootDir.'/vendor/autoload.php';

$input = new ArgvInput();
$output = new ConsoleOutput();
$app = new Application();

$configuration = (new Configuration())
    ->setRootDir($rootDir)
    ->setWatchDirectories([$sourceDir, $testsDir])
    ->setTestsDirectory($testsDir)
    ->setPhpunitPath($phpunitPath)
;

$eventDispatcher = $configuration->getEventDispatcher();

$eventDispatcher->addListener(Events::SANDBOX_PREPARE, function() {
    // prepare a sandboxed environment
    // modify database configuration files here
});

$eventDispatcher->addListener(Events::SANDBOX_CLEANUP, function() {
    // cleanup modified database configuration file and cleanup sandboxed databases
});

$eventDispatcher->addListener(Events::DATABASES_CREATE, function() {
    // create test databases
});

$testerApplication = new TesterApplication($app, $configuration);
$testerApplication->run($input, $output);
```

Take a look at the example in [bin/phpchunkit](https://github.com/jwage/PHPChunkit/blob/master/bin/phpchunkit)
which has example listeners using MySQL.
