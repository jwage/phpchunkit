<?php

namespace JWage\Tester\Test;

use JWage\Tester\Configuration;
use JWage\Tester\TestRunner;
use JWage\Tester\TestWatcher;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @group functional
 */
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
            ->setWatchDirectories([realpath(__DIR__.'/../../../..')])
        ;

        $this->testWatcher = new TestWatcherStub(
            $this->testRunner,
            $this->configuration
        );
    }

    public function testExecute()
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $this->testWatcher->execute($input, $output);
    }
}

class TestWatcherStub extends TestWatcher
{
    /**
     * @var int
     */
    private $count = 0;

    /**
     * @return bool
     */
    protected function while()
    {
        $this->count++;

        return $this->count < 3;
    }
}
