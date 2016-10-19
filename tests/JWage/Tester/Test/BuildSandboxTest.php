<?php

namespace JWage\Tester\Test;

use JWage\Tester\BuildSandbox;
use JWage\Tester\DatabaseSandbox;
use JWage\Tester\TestRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildSandboxTest extends BaseTest
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
     * @var BuildSandbox
     */
    private $buildSandbox;

    protected function setUp()
    {
        $this->databaseSandbox = $this->createMock(DatabaseSandbox::class);
        $this->testRunner = $this->createMock(TestRunner::class);

        $this->buildSandbox = new BuildSandbox(
            $this->databaseSandbox,
            $this->testRunner
        );
    }

    public function testExecute()
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $rootDir = realpath(__DIR__.'/../../../..');

        $this->testRunner->expects($this->once())
            ->method('getRootDir')
            ->willReturn($rootDir);

        $input->expects($this->once())
            ->method('getOption')
            ->with('create-dbs')
            ->willReturn(true);

        $this->testRunner->expects($this->once())
            ->method('runTestCommand')
            ->with('create-dbs', [
                '--sandbox' => true,
            ]);

        $this->buildSandbox->execute(
            $input,
            $output
        );
    }
}
