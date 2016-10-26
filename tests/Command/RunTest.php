<?php

namespace PHPChunkit\Test\Command;

use PHPChunkit\Command\Run;
use PHPChunkit\Configuration;
use PHPChunkit\DatabaseSandbox;
use PHPChunkit\TestRunner;
use PHPChunkit\Test\BaseTest;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunTest extends BaseTest
{
    /**
     * @var TestRunner
     */
    private $testRunner;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Run
     */
    private $run;

    protected function setUp()
    {
        $this->testRunner = $this->createMock(TestRunner::class);
        $this->configuration = (new Configuration())
            ->setTestsDirectory($this->getTestsDirectory())
        ;

        $this->run = new Run(
            $this->testRunner,
            $this->configuration
        );
    }

    public function testExecute()
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $input->expects($this->at(0))
            ->method('getOption')
            ->with('chunk')
            ->willReturn(null);

        $input->expects($this->at(1))
            ->method('getOption')
            ->with('num-chunks')
            ->willReturn(14);

        $input->expects($this->at(2))
            ->method('getOption')
            ->with('group')
            ->willReturn([]);

        $input->expects($this->at(3))
            ->method('getOption')
            ->with('exclude-group')
            ->willReturn([]);

        $input->expects($this->at(4))
            ->method('getOption')
            ->with('changed')
            ->willReturn(false);

        $input->expects($this->at(5))
            ->method('getOption')
            ->with('sandbox')
            ->willReturn(true);

        $this->testRunner->expects($this->once())
            ->method('runTestCommand')
            ->with('sandbox');

        $this->testRunner->expects($this->any())
            ->method('generatePhpunitXml')
            ->willReturn('test.xml');

        $this->testRunner->expects($this->any())
            ->method('runPhpunit')
            ->with('-c test.xml')
            ->willReturn(0);

        $this->assertEquals(0, $this->run->execute($input, $output));
    }
}
