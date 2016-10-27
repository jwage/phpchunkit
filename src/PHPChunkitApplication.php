<?php

namespace PHPChunkit;

use PHPChunkit\Command;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @testClass PHPChunkit\Test\PHPChunkitApplicationTest
 */
class PHPChunkitApplication
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Application
     */
    private $symfonyApplication;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->symfonyApplication = $this->container['phpchunkit.symfony_application'];
    }

    public function run(InputInterface $input, OutputInterface $output) : int
    {
        $this->container['phpchunkit.application.input'] = $input;
        $this->container['phpchunkit.application.output'] = $output;

        $this->register('watch')
            ->setDescription('Watch for changes to files and run the associated tests.')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Run tests in debug mode.')
            ->addOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for PHP.', '256M')
            ->addOption('stop', null, InputOption::VALUE_NONE, 'Stop on failure or error.')
            ->addOption('failed', null, InputOption::VALUE_REQUIRED, 'Track tests that have failed.', true)
            ->setCode([$this->container['phpchunkit.command.test_watcher'], 'execute'])
        ;

        $this->register('run')
            ->setDescription('Run tests.')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Run tests in debug mode.')
            ->addOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for PHP.', '256M')
            ->addOption('stop', null, InputOption::VALUE_NONE, 'Stop on failure or error.')
            ->addOption('failed', null, InputOption::VALUE_NONE, 'Track tests that have failed.')
            ->addOption('create-dbs', null, InputOption::VALUE_NONE, 'Create the test databases before running tests.')
            ->addOption('sandbox', null, InputOption::VALUE_NONE, 'Configure unique names.')
            ->addOption('chunk', null, InputOption::VALUE_REQUIRED, 'Run a specific chunk of tests.')
            ->addOption('num-chunks', null, InputOption::VALUE_REQUIRED, 'The number of chunks to run tests in.', 1)
            ->addOption('group', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Run all tests in these groups.')
            ->addOption('exclude-group', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Run all tests excluding these groups.')
            ->addOption('changed', null, InputOption::VALUE_NONE, 'Run changed tests.')
            ->addOption('filter', null, InputOption::VALUE_REQUIRED, 'Run tests that match the given filter.')
            ->addOption('file', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Run test file.')
            ->setCode([$this->container['phpchunkit.command.run'], 'execute'])
        ;

        $this->register('create-dbs')
            ->setDescription('Create the test databases.')
            ->addOption('sandbox', null, InputOption::VALUE_NONE, 'Prepare sandbox before creating databases.')
            ->setCode([$this->container['phpchunkit.command.create_databases'], 'execute'])
        ;

        $this->register('sandbox')
            ->setDescription('Build a sandbox for a test run.')
            ->addOption('create-dbs', null, InputOption::VALUE_NONE, 'Create the test databases after building the sandbox.')
            ->setCode([$this->container['phpchunkit.command.build_sandbox'], 'execute'])
        ;

        $this->register('generate')
            ->setDescription('Generate a test skeleton from a class.')
            ->addArgument('class', InputArgument::REQUIRED, 'Class to generate test for.')
            ->addOption('file', null, InputOption::VALUE_REQUIRED, 'File path to write test to.')
            ->setCode([$this->container['phpchunkit.command.generate_test'], 'execute'])
        ;

        return $this->runSymfonyApplication($input, $output);
    }

    protected function runSymfonyApplication(
        InputInterface $input,
        OutputInterface $output) : int
    {
        return $this->symfonyApplication->run($input, $output);
    }

    private function register(string $name) : SymfonyCommand
    {
        return $this->symfonyApplication->register($name);
    }
}
