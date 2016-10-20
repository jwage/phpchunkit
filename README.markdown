# Tester

## TODO

- Rename the project.
- Create some kind of config object with the following:
    - Root dir
    - Path to tests
    - Path to phpunit.xml
    - Groups to consider functional tests.
    - Nothing should be hardcoded/assumed in the lib. Make it all configurable.
- Create abstraction for the create database/sandbox stuff. Functional tests need to be sandboxed.
- Remove dependency on PHP 7.
- Update author doc blocks to mention Kris Wallsmith on code he wrote.
- Blank TODO need to be handled. Things that used to be specific to OpenSky had to be removed. We need to replace with something more abstract and configurable.

## Setup

    ```php
    #!/usr/bin/env php
    <?php

    use JWage\Tester\Configuration;
    use JWage\Tester\TesterApplication;
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

    $testerApplication = new TesterApplication($app, $configuration);
    $testerApplication->run($input, $output);

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

    ./bin/tester file tests/JWage/Tester/Test/BuildSandboxTest.php

Run tests for changed files:

    ./bin/tester changed
