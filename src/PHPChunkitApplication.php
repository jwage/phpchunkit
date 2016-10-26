<?php

namespace PHPChunkit;

use PHPChunkit\Command;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PHPChunkitApplication
{
    /**
     * @var Application
     */
    private $app;

    public function __construct(Application $app, Configuration $configuration)
    {
        $this->app = $app;
        $this->configuration = $configuration;
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        $databaseSandbox = new DatabaseSandbox();
        $testRunner = new TestRunner($this->app, $input, $output, $this->configuration);

        $this->app->register('filter')
            ->setDescription('Run tests that match a filter.')
            ->addArgument('filter', InputArgument::OPTIONAL, 'Filter to run.')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Run tests in debug mode')
            ->addOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for each chunk', '750M')
            ->addOption('stop', null, InputOption::VALUE_NONE, 'Stop on failure or error')
            ->addOption('failed', null, InputOption::VALUE_NONE, 'Track tests that have failed')
            ->setCode(function($input, $output) use ($testRunner) {
                return $testRunner->runFilteredFiles($input->getArgument('filter'));
            })
        ;

        $this->app->register('file')
            ->setDescription('Run a single test file.')
            ->addArgument('file', InputArgument::OPTIONAL, 'File to run.')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Run tests in debug mode')
            ->addOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for each chunk', '750M')
            ->addOption('stop', null, InputOption::VALUE_NONE, 'Stop on failure or error')
            ->addOption('failed', null, InputOption::VALUE_NONE, 'Track tests that have failed')
            ->setCode(function($input, $output) use ($testRunner) {
                return $testRunner->runPhpunit($input->getArgument('file'));
            })
        ;

        $this->app->register('watch')
            ->setDescription('Watch for changes to files and run the associated tests.')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Run tests in debug mode')
            ->addOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for each chunk', '750M')
            ->addOption('stop', null, InputOption::VALUE_NONE, 'Stop on failure or error')
            ->addOption('failed', null, InputOption::VALUE_REQUIRED, 'Track tests that have failed', true)
            ->setCode([new Command\TestWatcher($testRunner, $this->configuration), 'execute'])
        ;

        $this->app->register('run')
            ->setDescription('Run tests.')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Run tests in debug mode')
            ->addOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for each chunk', '1.5G')
            ->addOption('stop', null, InputOption::VALUE_NONE, 'Stop on failure or error')
            ->addOption('create-dbs', null, InputOption::VALUE_NONE, 'Create the test databases before running tests')
            ->addOption('sandbox', null, InputOption::VALUE_NONE, 'Configure unique names')
            ->addOption('chunk', null, InputOption::VALUE_REQUIRED, 'Run a specific chunk of tests.')
            ->addOption('num-chunks', null, InputOption::VALUE_REQUIRED, 'The number of chunks to run tests in.', 1)
            ->addOption('failed', null, InputOption::VALUE_NONE, 'Track tests that have failed')
            ->addOption('group', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Run all tests in these groups.')
            ->addOption('exclude-group', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Run all tests excluding these groups.')
            ->addOption('changed', null, InputOption::VALUE_NONE, 'Run changed tests.')
            ->setCode([new Command\Run($testRunner, $this->configuration), 'execute'])
        ;

        $this->app->register('create-dbs')
            ->setDescription('Create the test databases.')
            ->addOption('sandbox', null, InputOption::VALUE_NONE, 'Configure unique names')
            ->setCode([new Command\CreateDatabases($this->configuration->getEventDispatcher()), 'execute'])
        ;

        $this->app->register('sandbox')
            ->setDescription('Build a sandbox for a test run.')
            ->addOption('create-dbs', null, InputOption::VALUE_NONE, 'Create the test databases after building the sandbox.')
            ->setCode([new Command\BuildSandbox($testRunner, $this->configuration->getEventDispatcher()), 'execute'])
        ;

        $this->app->run($input, $output);
    }
}
