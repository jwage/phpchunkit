<?php

namespace PHPChunkit\Test\Command;

use PHPChunkit\Command\All;
use PHPChunkit\TestRunner;
use PHPChunkit\Test\BaseTest;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AllTest extends BaseTest
{
    /**
     * @var TestRunner
     */
    private $testRunner;

    /**
     * @var All
     */
    private $all;

    protected function setUp()
    {
        $this->testRunner = $this->createMock(TestRunner::class);

        $this->all = new All(
            $this->testRunner
        );
    }

    public function testExecute()
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $this->testRunner->expects($this->at(0))
            ->method('runTestCommand')
            ->with('unit', [
                '--debug' => null,
                '--memory-limit' => null,
                '--stop' => null,
                '--failed' => null,
            ])
            ->willReturn(0);

        $this->testRunner->expects($this->at(1))
            ->method('runTestCommand')
            ->with('functional', [
                '--debug' => null,
                '--memory-limit' => null,
                '--stop' => null,
                '--failed' => null,
                '--create-dbs' => null,
                '--sandbox' => null,
            ])
            ->willReturn(0);

        $this->assertEquals(0, $this->all->execute($input, $output));
    }
}
