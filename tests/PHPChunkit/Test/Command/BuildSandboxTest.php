<?php

namespace PHPChunkit\Test\Command;

use PHPChunkit\Command\BuildSandbox;
use PHPChunkit\DatabaseSandbox;
use PHPChunkit\Events;
use PHPChunkit\TestRunner;
use PHPChunkit\Test\BaseTest;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class BuildSandboxTest extends BaseTest
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
     * @var BuildSandbox
     */
    private $buildSandbox;

    protected function setUp()
    {
        $this->testRunner = $this->createMock(TestRunner::class);
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);

        $this->buildSandbox = new BuildSandbox(
            $this->testRunner,
            $this->eventDispatcher
        );
    }

    public function testExecute()
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $this->eventDispatcher->expects($this->at(0))
            ->method('dispatch')
            ->with(Events::SANDBOX_PREPARE);

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
