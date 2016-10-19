<?php

namespace JWage\Tester\Test;

use JWage\Tester\CreateDatabases;
use JWage\Tester\DatabaseSandbox;
use JWage\Tester\TestRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateDatabasesTest extends BaseTest
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
     * @var CreateDatabases
     */
    private $createDatabases;

    protected function setUp()
    {
        $this->databaseSandbox = $this->createMock(DatabaseSandbox::class);
        $this->testRunner = $this->createMock(TestRunner::class);

        $this->createDatabases = new CreateDatabases(
            $this->databaseSandbox,
            $this->testRunner
        );
    }

    public function testExecute()
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        // TODO

        $this->createDatabases->execute(
            $input,
            $output
        );
    }
}
