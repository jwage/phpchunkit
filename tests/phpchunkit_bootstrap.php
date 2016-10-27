<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

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
