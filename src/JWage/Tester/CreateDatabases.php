<?php

namespace JWage\Tester;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateDatabases
{
    /**
     * @var DatabaseSandbox
     */
    private $databaseSandbox;

    /**
     * @var TestRunner
     */
    private $testRunner;

    /**
     * @param DatabaseSandbox $databaseSandbox
     * @param TestRunner      $testRunner
     */
    public function __construct(DatabaseSandbox $databaseSandbox, TestRunner $testRunner)
    {
        $this->databaseSandbox = $databaseSandbox;
        $this->testRunner = $testRunner;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        // todo

        if (!$input->getOption('quiet')) {
            $output->writeln('Done creating databases!');
        }
    }
}
