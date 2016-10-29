<?php

namespace PHPChunkit;

use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

/**
 * @testClass PHPChunkit\Test\TestRunnerTest
 */
class TestRunner
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @param Application     $app
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param Configuration   $configuration
     */
    public function __construct(
        Application $app,
        InputInterface $input,
        OutputInterface $output,
        Configuration $configuration)
    {
        $this->app = $app;
        $this->input = $input;
        $this->output = $output;
        $this->configuration = $configuration;
    }

    /**
     * @return string
     */
    public function getRootDir()
    {
        return $this->configuration->getRootDir();
    }

    /**
     * @param [] $files
     *
     * @return string $path
     */
    public function generatePhpunitXml(array &$files)
    {
        // load the config into memory
        $phpunitXmlDistPath = is_file($file = $this->getRootDir().'/phpunit.xml') ? $file : $this->getRootDir().'/phpunit.xml.dist';
        $src = file_get_contents($phpunitXmlDistPath);
        $xml = simplexml_load_string(str_replace('./', $this->getRootDir().'/', $src));

        // temp config file
        $config = tempnam('/tmp', 'phpunitxml');
        register_shutdown_function(function() use ($config) {
            unlink($config);
        });

        unset($xml->testsuites[0]->testsuite);
        $suite = $xml->testsuites[0]->addChild('testsuite');

        if ($files) {
            $files = array_unique($files);

            foreach ($files as $file) {
                $path = strpos($file, '/') === 0
                    ? $file
                    : $this->getRootDir().'/'.$file;

                $suite->addChild('file', $path);
            }

            file_put_contents($config, $xml->asXml());

            return $config;
        }
    }

    /**
     * @param array $files
     * @param array $env
     *
     * @return int $code
     */
    public function runTestFiles(array $files, array $env = [])
    {
        $config = $this->generatePhpunitXml($files);

        if ($config) {
            $this->output->writeln('');

            foreach ($files as $file) {
                $this->output->writeln(sprintf(' - Executing <comment>%s</comment>', $file));
            }

            $this->output->writeln('');

            return $this->runPhpunit(sprintf('-c %s', escapeshellarg($config)), $env);
        } else {
            $this->output->writeln('No tests to run.');
        }
    }

    /**
     * @param string $command
     * @param array  $env
     *
     * @return int
     */
    public function runPhpunit($command, array $env = [], \Closure $callback = null)
    {
        $command = sprintf('%s %s %s',
            $this->configuration->getPhpunitPath(),
            $command,
            $this->flags()
        );

        return $this->run($command, false, $env, $callback);
    }

    /**
     * @param string $command
     * @param array  $env
     *
     * @return Process
     */
    public function getPhpunitProcess($command, array $env = []) : Process
    {
        $command = sprintf('%s %s %s',
            $this->configuration->getPhpunitPath(),
            $command,
            $this->flags()
        );

        return $this->getProcess($command, $env);
    }

    /**
     * @param string $command
     * @param bool   $throw
     * @param []     $env
     *
     * @return int
     */
    public function run($command, $throw = true, array $env = [], \Closure $callback = null)
    {
        $process = $this->getProcess($command, $env);

        if ($callback === null) {
            $callback = function($output) {
                echo $output;
            };
        }

        $process->run(function($type, $output) use ($callback) {
            $callback($output);
        });

        if ($process->getExitCode() && $throw) {
            throw new RuntimeException('The command did not exit successfully.');
        }

        return $process->getExitCode();
    }

    /**
     * @param string $command
     */
    public function getProcess($command, array $env = []) : Process
    {
        foreach ($env as $key => $value) {
            $command = sprintf('export %s=%s && %s', $key, $value, $command);
        }

        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->writeln('+ <comment>'.$command.'</comment>');
        }

        return $this->createProcess($command);
    }

    /**
     * @param string $command
     * @param array  $input
     *
     * @return int
     */
    public function runTestCommand($command, array $input = []) : int
    {
        $input['command'] = $command;

        return $this->app->find($command)
            ->run(new ArrayInput($input), $this->output);
    }

    /**
     * @return string
     */
    private function flags()
    {
        $memoryLimit = $this->input->getOption('memory-limit')
            ?: $this->configuration->getMemoryLimit();

        $flags = '-d memory_limit='.escapeshellarg($memoryLimit);

        if ($this->input->getOption('stop')) {
            $flags .= ' --stop-on-failure --stop-on-error';
        }

        if ($this->output->getVerbosity() > Output::VERBOSITY_NORMAL) {
            $flags .= ' --verbose';
        }

        if ($this->input->getOption('debug')) {
            $flags .= ' --debug';
        }

        if ($this->output->isDecorated()) {
            $flags .= ' --colors';
        }

        if ($this->input->hasOption('phpunit-opt')
            && $phpunitOptions = $this->input->getOption('phpunit-opt')) {
            $flags .= ' '.$phpunitOptions;
        }

        return $flags;
    }

    /**
     * @param string
     * @param string $command
     *
     * @return Process
     */
    protected function createProcess($command)
    {
        return new Process($command, null, null, null, null);
    }
}
