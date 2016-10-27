<?php

namespace PHPChunkit\Test;

use PHPChunkit\Configuration;
use PHPChunkit\TestRunner;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class TestRunnerTest extends BaseTest
{
    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Process
     */
    private $process;

    /**
     * @var string
     */
    private $configuration;

    /**
     * @var TestRunner
     */
    private $testRunner;

    protected function setUp()
    {
        $this->app = $this->createMock(Application::class);
        $this->input = $this->createMock(InputInterface::class);
        $this->output = $this->createMock(OutputInterface::class);
        $this->process = $this->createMock(Process::class);
        $this->configuration = (new Configuration())
            ->setRootDir(realpath(__DIR__.'/..'))
            ->setPhpunitPath(realpath(__DIR__.'/../vendor/bin/phpunit'))
        ;

        $this->testRunner = new TestRunnerStub(
            $this->app,
            $this->input,
            $this->output,
            $this->configuration
        );
        $this->testRunner->process = $this->process;
    }

    public function testGeneratePhpunitXml()
    {
        $files = [
            'tests/Command/AllTest.php',
            'tests/TestCounterTest.php',
        ];

        $path = $this->testRunner->generatePhpunitXml($files);

        $xmlSource = file_get_contents($path);

        $xml = simplexml_load_string($xmlSource);

        $suite = $xml->testsuites[0]->testsuite;
        $suiteFiles = (array) $suite->file;

        $expectedFiles = [
            $this->configuration->getRootDir().'/tests/Command/AllTest.php',
            $this->configuration->getRootDir().'/tests/TestCounterTest.php',
        ];

        $this->assertEquals($expectedFiles, $suiteFiles);
    }

    public function testRunTestFiles()
    {
        $files = [
            'tests/AllTest.php',
            'src/All.php',
        ];

        $testRunner = $this->buildPartialMock(
            TestRunnerStub::class,
            [
                'generatePhpunitXml',
                'runPhpunit',
            ],
            [
                $this->app,
                $this->input,
                $this->output,
                $this->configuration,
            ]
        );

        $testRunner->expects($this->once())
            ->method('generatePhpunitXml')
            ->will($this->returnValue('/path/to/phpunit.xml'));

        $testRunner->expects($this->once())
            ->method('runPhpunit')
            ->with("-c '/path/to/phpunit.xml'")
            ->will($this->returnValue(0));

        $this->assertEquals(0, $testRunner->runTestFiles($files));
    }

    public function testRunPhpunit()
    {
        $testRunner = $this->buildPartialMock(
            TestRunnerStub::class,
            [
                'run',
            ],
            [
                $this->app,
                $this->input,
                $this->output,
                $this->configuration,
            ]
        );

        $testRunner->expects($this->once())
            ->method('run')
            ->with(sprintf("%s --exclude-group=functional -d memory_limit=''", $this->configuration->getPhpunitPath()))
            ->will($this->returnValue(0));

        $this->assertEquals(0, $testRunner->runPhpunit('--exclude-group=functional'));
    }

    public function testRun()
    {
        $this->testRunner->passthruResponse = 0;
        $this->assertEquals(0, $this->testRunner->run('ls -la'));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The command did not exit successfully.
     */
    public function testRunThrow()
    {
        $this->process->expects($this->once())
            ->method('getExitCode')
            ->willReturn(1);

        $this->assertEquals(0, $this->testRunner->run('ls -la'));
    }

    public function testRunTestCommand()
    {
        $command = $this->createMock(Command::class);

        $this->app->expects($this->once())
            ->method('find')
            ->with('test')
            ->willReturn($command);

        $command->expects($this->once())
            ->method('run')
            ->with(new ArrayInput(['command' => 'test', 'test' => true]))
            ->willReturn(0);

        $this->assertEquals(0, $this->testRunner->runTestCommand('test', ['test' => true]));
    }
}

class TestRunnerStub extends TestRunner
{
    /**
     * @var Process
     */
    public $process;

    /**
     * @param string $command
     *
     * @return Process
     */
    protected function createProcess($command)
    {
        return $this->process;
    }
}
