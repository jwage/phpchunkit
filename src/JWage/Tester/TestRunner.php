<?php

namespace JWage\Tester;

use PHPUnit_Framework_TestCase;
use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

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
    public function __construct(Application $app, InputInterface $input, OutputInterface $output, Configuration $configuration)
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
     * @return []
     */
    public function getChangedFiles()
    {
        $command = "git status --porcelain | grep -e '^\(.*\).php$' | cut -c 3-";

        $process = $this->createProcess($command);
        $process->run();

        $output = $process->getOutput();
        $files = $output ? array_map('trim', explode("\n", $output)) : [];

        return $files;
    }

    /**
     * @param string $filter
     *
     * @return []
     */
    public function getFilteredFiles($filter)
    {
        $finder = Finder::create()
            ->files()
            ->name('*'.$filter.'*')
            ->in($this->getRootDir());

        $files = [];
        foreach ($finder as $file) {
            $files[] = $file->getRelativePathname();
        }

        return $files;
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
        register_shutdown_function(function () use ($config) {
            unlink($config);
        });

        unset($xml->testsuites[0]->testsuite);
        $suite = $xml->testsuites[0]->addChild('testsuite');

        foreach ($files as $key => $file) {
            if (strpos($file, 'tests') === false) {
                $testFile = str_replace('.php', 'Test.php', $file);
                $testFile = str_replace('src', 'tests', $testFile);
                $testFile = dirname($testFile).'/Test/'.basename($testFile);

                if (file_exists($testFile)) {
                    $files[$key] = $testFile;
                } else {
                    unset($files[$key]);
                }
            }
        }

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
     * @param array $env
     *
     * @return int
     */
    public function runChangedFiles(array $env = [])
    {
        $files = $this->getChangedFiles();

        return $this->runTestFiles($files, $env);
    }

    /**
     * @param string $filter
     * @param array  $env
     *
     * @return int
     */
    public function runFilteredFiles($filter, array $env = [])
    {
        $files = $this->getFilteredFiles($filter);

        return $this->runTestFiles($files, $env);
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
            $this->output->writeln(sprintf('Executing %s test(s)', count($files)));

            foreach ($files as $file) {
                $this->output->writeln(sprintf(' <info>%s</info>', $file));
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
        $command = sprintf('php %s %s %s',
            $this->configuration->getPhpunitPath(),
            $command,
            $this->flags($this->input, $this->output)
        );

        return $this->run($command, false, $env, $callback);
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
        foreach ($env as $key => $value) {
            $command = sprintf('export %s=%s && %s', $key, $value, $command);
        }

        $pretty = str_replace(
            [__DIR__, ' --colors', ' --ansi'],
            [basename(__DIR__), '', ''],
            $command
        );

        if ($this->output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->output->writeln('+ <comment>'.$pretty.'</comment>');
        }

        $process = $this->createProcess($command);

        if ($callback === null) {
            $callback = function ($output) {
                echo $output;
            };
        }

        $process->run(function ($type, $output) use ($callback) {
            $callback($output);
        });

        if ($process->getExitCode() && $throw) {
            throw new RuntimeException('The command did not exit successfully.');
        }

        return $process->getExitCode();
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
    public function flags()
    {
        $flags = '-d memory_limit='.escapeshellarg($this->input->getOption('memory-limit'));

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

        return $flags;
    }

    /**
     * @param string
     *
     * @return Process
     */
    protected function createProcess($command)
    {
        return new Process($command, null, null, null, null);
    }
}
