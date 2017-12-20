<?php

namespace PHPChunkit\Test\Command;

use PHPChunkit\Configuration;
use PHPChunkit\FileClassesHelper;
use PHPChunkit\TestRunner;
use PHPChunkit\Command\TestWatcher;
use PHPChunkit\Test\BaseTest;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestWatcherTest extends BaseTest
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
     * @var TestWatcher
     */
    private $testWatcher;

    protected function setUp()
    {
        $this->testRunner = $this->createMock(TestRunner::class);
        $this->configuration = (new Configuration())
            ->setWatchDirectories([realpath(__DIR__.'/../..')])
        ;
        $this->fileClassesHelper = $this->createMock(FileClassesHelper::class);

        $this->testWatcher = new TestWatcherStub(
            $this->testRunner,
            $this->configuration,
            $this->fileClassesHelper
        );
    }

    public function testExecute()
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $this->testWatcher->execute($input, $output);

        $this->assertEquals(1, $this->testWatcher->getCount());
    }
}

class TestWatcherStub extends TestWatcher
{
    /** @var int */
    private $count = 0;

    protected function sleep()
    {
    }

    public function getCount() : int
    {
        return $this->count;
    }

    /**
     * @return bool
     */
    protected function while() : bool
    {
        $this->count++;

        return $this->count < 1;
    }
}
