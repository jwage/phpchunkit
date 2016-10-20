<?php

namespace JWage\Tester;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Automatically builds an environment with the necessary config changes
 * so that all databases get a unique name generated and are sandboxed.
 */
class BuildSandbox
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
        $databaseConfigPath = ''; // TODO

        // update database configs
        $this->replaceConfigDatabaseNames($output, $databaseConfigPath);

        // create databases after making above config changes
        if ($input->getOption('create-dbs')) {
            $this->testRunner->runTestCommand('create-dbs', [
                '--sandbox' => true,
            ]);
        }

        // cleanup after ourselves. This runs after the tests are finished.
        register_shutdown_function(function () use ($databaseConfigPath, $output) {
            $output->writeln('<info>Cleaning up sandbox...</info>');

            // TODO: cleanup sandbox
            // Revert config changes
            // Drop the sandboxed databases
        });
    }

    /**
     * @param OutputInterface $output
     * @param string          $path
     */
    private function replaceConfigDatabaseNames(OutputInterface $output, $path)
    {
        // TODO
    }
}
