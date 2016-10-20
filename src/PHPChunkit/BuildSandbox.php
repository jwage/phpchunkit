<?php

namespace PHPChunkit;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class BuildSandbox
{
    /**
     * @var TestRunner
     */
    private $testRunner;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @param TestRunner      $testRunner
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(TestRunner $testRunner, EventDispatcher $eventDispatcher)
    {
        $this->testRunner = $testRunner;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->eventDispatcher->dispatch(Events::SANDBOX_PREPARE);

        if ($input->getOption('create-dbs')) {
            $this->testRunner->runTestCommand('create-dbs', [
                '--sandbox' => true,
            ]);
        }

        register_shutdown_function(function () use ($output) {
            $output->writeln('<info>Cleaning up sandbox...</info>');

            $this->eventDispatcher->dispatch(Events::SANDBOX_CLEANUP);
        });
    }
}
