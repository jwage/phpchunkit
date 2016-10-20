<?php

namespace JWage\Tester\Test;

use JWage\Tester\Configuration;
use JWage\Tester\DatabaseSandbox;
use JWage\Tester\Functional;
use JWage\Tester\TestRunner;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FunctionalTest extends BaseTest
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
     * @var Functional
     */
    private $functional;

    protected function setUp()
    {
        $this->testRunner = $this->createMock(TestRunner::class);
        $this->configuration = (new Configuration())
            ->setTestsDirectory($this->getTestsDirectory())
        ;

        $this->functional = new Functional(
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
            ->with('-c test.xml --group functional')
            ->willReturn(0);

        $this->assertEquals(0, $this->functional->execute($input, $output));
    }
}
