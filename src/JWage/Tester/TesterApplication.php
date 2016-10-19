<?php

namespace JWage\Tester;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TesterApplication
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

        $this->app->register('all')
            ->setDescription('Run the unit and functional test suites.')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Run tests in debug mode')
            ->addOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for each chunk', '1.5G')
            ->addOption('stop', null, InputOption::VALUE_NONE, 'Stop on failure or error')
            ->addOption('create-dbs', null, InputOption::VALUE_NONE, 'Create the test databases before running tests')
            ->addOption('sandbox', null, InputOption::VALUE_NONE, 'Configure unique names')
            ->addOption('chunk', null, InputOption::VALUE_REQUIRED, 'Run a specific chunk of tests.')
            ->addOption('num-chunks', null, InputOption::VALUE_REQUIRED, 'The number of chunks to run tests in.', 14)
            ->addOption('failed', null, InputOption::VALUE_NONE, 'Track tests that have failed')
            ->setCode([new All($testRunner), 'execute'])
        ;

        $this->app->register('changed')
            ->setDescription('Run the changed tests.')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Run tests in debug mode.')
            ->addOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for each chunk', '750M')
            ->addOption('stop', null, InputOption::VALUE_NONE, 'Stop on failure or error')
            ->addOption('failed', null, InputOption::VALUE_NONE, 'Track tests that have failed')
            ->setCode(function($input, $output) use ($testRunner) {
                return $testRunner->runChangedFiles();
            })
        ;

        $this->app->register('filter')
            ->setDescription('Run tests that match the filter.')
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
            ->setDescription('Run single test file.')
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
            ->setDescription('Watch for changes and run the tests.')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Run tests in debug mode')
            ->addOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for each chunk', '750M')
            ->addOption('stop', null, InputOption::VALUE_NONE, 'Stop on failure or error')
            ->addOption('failed', null, InputOption::VALUE_REQUIRED, 'Track tests that have failed', true)
            ->setCode([new TestWatcher($testRunner, $this->configuration), 'execute'])
        ;

        $this->app->register('unit')
            ->setDescription('Run the unit test suite.')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Run tests in debug mode')
            ->addOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for each chunk', '750M')
            ->addOption('stop', null, InputOption::VALUE_NONE, 'Stop on failure or error')
            ->addOption('failed', null, InputOption::VALUE_NONE, 'Track tests that have failed')
            ->setCode(function($input, $output) use ($testRunner) {
                return $testRunner->runPhpunit('--exclude-group functional');
            })
        ;

        $this->app->register('functional')
            ->setDescription('Run the functional test suite.')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Run tests in debug mode')
            ->addOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for each chunk', '1.5G')
            ->addOption('stop', null, InputOption::VALUE_NONE, 'Stop on failure or error')
            ->addOption('create-dbs', null, InputOption::VALUE_NONE, 'Create the test databases before running tests')
            ->addOption('sandbox', null, InputOption::VALUE_NONE, 'Configure unique names')
            ->addOption('chunk', null, InputOption::VALUE_REQUIRED, 'Run a specific chunk of tests.')
            ->addOption('num-chunks', null, InputOption::VALUE_REQUIRED, 'The number of chunks to run tests in.', 14)
            ->addOption('failed', null, InputOption::VALUE_NONE, 'Track tests that have failed')
            ->setCode([new Functional($databaseSandbox, $testRunner), 'execute'])
        ;

        $this->app->register('create-dbs')
            ->setDescription('Create the test databases.')
            ->addOption('sandbox', null, InputOption::VALUE_NONE, 'Configure unique names')
            ->setCode([new CreateDatabases($databaseSandbox, $testRunner), 'execute'])
        ;

        $this->app->register('sandbox')
            ->setDescription('Build a sandbox for a functional test run.')
            ->addOption('create-dbs', null, InputOption::VALUE_NONE, 'Create the test databases after building the sandbox.')
            ->setCode([new BuildSandbox($databaseSandbox, $testRunner), 'execute'])
        ;

        $this->app->run($input, $output);
    }
}
