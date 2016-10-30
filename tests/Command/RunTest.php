<?php

namespace PHPChunkit\Test\Command;

use PHPChunkit\ChunkedTests;
use PHPChunkit\Command\Run;
use PHPChunkit\Configuration;
use PHPChunkit\DatabaseSandbox;
use PHPChunkit\TestChunker;
use PHPChunkit\TestFinder;
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
        $this->testChunker = $this->createMock(TestChunker::class);
        $this->testFinder = $this->createMock(TestFinder::class);

        $this->run = new Run(
            $this->testRunner,
            $this->configuration,
            $this->testChunker,
            $this->testFinder
        );
    }

    public function testExecute()
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $this->testFinder->expects($this->once())
            ->method('getFiles')
            ->willReturn([__FILE__]);

        $input->expects($this->at(0))
            ->method('getOption')
            ->with('parallel')
            ->willReturn(false);

        $input->expects($this->at(1))
            ->method('getOption')
            ->with('num-chunks')
            ->willReturn(14);

        $input->expects($this->at(2))
            ->method('getOption')
            ->with('chunk')
            ->willReturn(null);

        $input->expects($this->at(3))
            ->method('getOption')
            ->with('file')
            ->willReturn(null);

        $input->expects($this->at(4))
            ->method('getOption')
            ->with('group')
            ->willReturn([]);

        $input->expects($this->at(5))
            ->method('getOption')
            ->with('exclude-group')
            ->willReturn([]);

        $input->expects($this->at(6))
            ->method('getOption')
            ->with('changed')
            ->willReturn(false);

        $input->expects($this->at(7))
            ->method('getOption')
            ->with('filter')
            ->willReturn([]);

        $input->expects($this->at(8))
            ->method('getOption')
            ->with('contains')
            ->willReturn([]);

        $input->expects($this->at(9))
            ->method('getOption')
            ->with('not-contains')
            ->willReturn([]);

        $input->expects($this->at(10))
            ->method('getOption')
            ->with('sandbox')
            ->willReturn(true);

        $this->testChunker->expects($this->once())
             ->method('chunkTestFiles')
             ->will($this->returnCallback(function(ChunkedTests $chunkedTests) {
                $chunkedTests->setTotalTests(1);
            }));

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
