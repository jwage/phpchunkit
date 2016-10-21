<?php

use PHPChunkit\Events;

// Manipulate $configuration which is an instance of PHPChunkit\Configuration

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
