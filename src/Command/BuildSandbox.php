<?php

declare(strict_types = 1);

namespace PHPChunkit\Command;

use PHPChunkit\Events;
use PHPChunkit\TestRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @testClass PHPChunkit\Test\Command\BuildSandboxTest
 */
class BuildSandbox implements CommandInterface
{
    /**
     * @var TestRunner
     */
    private $testRunner;

    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct(TestRunner $testRunner, EventDispatcher $eventDispatcher)
    {
        $this->testRunner = $testRunner;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function configure(Command $command)
    {
        $command
            ->setDescription('Build a sandbox for a test run.')
            ->addOption('create-dbs', null, InputOption::VALUE_NONE, 'Create the test databases after building the sandbox.')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->eventDispatcher->dispatch(Events::SANDBOX_PREPARE);

        if ($input->getOption('create-dbs')) {
            $this->testRunner->runTestCommand('create-dbs', [
                '--sandbox' => true,
            ]);
        }

        register_shutdown_function(function() use ($output) {
            $output->writeln('<info>Cleaning up sandbox...</info>');

            $this->eventDispatcher->dispatch(Events::SANDBOX_CLEANUP);
        });
    }
}
