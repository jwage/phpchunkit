<?php

declare(strict_types = 1);

namespace PHPChunkit\Command;

use Closure;
use PHPChunkit\ChunkedTests;
use PHPChunkit\TestChunker;
use PHPChunkit\TestFinder;
use PHPChunkit\TestRunner;
use PHPChunkit\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @testClass PHPChunkit\Test\Command\RunTest
 */
class Run implements CommandInterface
{
    const NAME = 'run';

    /**
     * @var TestRunner
     */
    private $testRunner;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var TestChunker
     */
    private $testChunker;

    /**
     * @var TestFinder
     */
    private $testFinder;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * @var bool
     */
    private $verbose = false;

    /**
     * @var bool
     */
    private $parallel = false;

    /**
     * @var bool
     */
    private $showProgressBar = true;

    /**
     * @var null|ProgressBar
     */
    private $progressBar;

    /**
     * @var integer
     */
    private $numChunks = 1;

    /**
     * @var null|integer
     */
    private $chunk = null;

    /**
     * @var ChunkedTests
     */
    private $chunkedTests;

    /**
     * @var integer
     */
    private $numParallelProcesses = 1;

    /**
     * @var integer
     */
    private $numAssertions = 0;

    /**
     * @var integer
     */
    private $numFailures = 0;

    /**
     * @var integer
     */
    private $numChunkFailures = 0;

    /**
     * @var integer
     */
    private $totalTestsRan = 0;

    /**
     * @var array
     */
    private $codes = [];

    /**
     * @var array
     */
    private $processes = [];

    public function __construct(
        TestRunner $testRunner,
        Configuration $configuration,
        TestChunker $testChunker,
        TestFinder $testFinder)
    {
        $this->testRunner = $testRunner;
        $this->configuration = $configuration;
        $this->testChunker = $testChunker;
        $this->testFinder = $testFinder;
    }

    public function getName() : string
    {
        return self::NAME;
    }

    public function configure(Command $command)
    {
        $command
            ->setDescription('Run tests.')
            ->addOption('debug', null, InputOption::VALUE_NONE, 'Run tests in debug mode.')
            ->addOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for PHP.')
            ->addOption('stop', null, InputOption::VALUE_NONE, 'Stop on failure or error.')
            ->addOption('failed', null, InputOption::VALUE_NONE, 'Track tests that have failed.')
            ->addOption('create-dbs', null, InputOption::VALUE_NONE, 'Create the test databases before running tests.')
            ->addOption('sandbox', null, InputOption::VALUE_NONE, 'Configure unique names.')
            ->addOption('chunk', null, InputOption::VALUE_REQUIRED, 'Run a specific chunk of tests.')
            ->addOption('num-chunks', null, InputOption::VALUE_REQUIRED, 'The number of chunks to run tests in.')
            ->addOption('group', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Run all tests in these groups.')
            ->addOption('exclude-group', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Run all tests excluding these groups.')
            ->addOption('changed', null, InputOption::VALUE_NONE, 'Run changed tests.')
            ->addOption('parallel', null, InputOption::VALUE_REQUIRED, 'Run test chunks in parallel.')
            ->addOption('filter', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Filter tests by path/file name and run them.')
            ->addOption('contains', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Run tests that match the given content.')
            ->addOption('not-contains', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Run tests that do not match the given content.')
            ->addOption('file', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Run test file.')
            ->addOption('phpunit-opt', null, InputOption::VALUE_REQUIRED, 'Pass through phpunit options.')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->initialize($input, $output);

        $this->chunkedTests = $this->chunkTestFiles(
            $this->findTestFiles()
        );

        if (!$this->chunkedTests->hasTests()) {
            $this->output->writeln('<error>No tests found to run.</error>');

            return;
        }

        $this->outputHeader();

        $this->setupSandbox();

        $this->runChunks();

        $this->outputFooter();

        return $this->hasFailed() ? 1 : 0;
    }

    private function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->stopwatch = new Stopwatch();
        $this->stopwatch->start('Tests');

        $this->input = $input;
        $this->output = $output;
        $this->verbose = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
        $this->numParallelProcesses = (int) $this->input->getOption('parallel');
        $this->parallel = $this->numParallelProcesses > 1;
        $this->showProgressBar = !$this->verbose && !$this->parallel;
        $this->numChunks = $this->getNumChunks();
        $this->chunk = (int) $this->input->getOption('chunk');
    }

    private function getNumChunks() : int
    {
        return (int) $this->input->getOption('num-chunks')
            ?: $this->configuration->getNumChunks() ?: 1;
    }

    private function findTestFiles()
    {
        $files = $this->input->getOption('file');

        if (!empty($files)) {
            return $files;
        }

        $groups = $this->input->getOption('group');
        $excludeGroups = $this->input->getOption('exclude-group');
        $changed = $this->input->getOption('changed');
        $filters = $this->input->getOption('filter');
        $contains = $this->input->getOption('contains');
        $notContains = $this->input->getOption('not-contains');

        $this->testFinder
            ->inGroups($groups)
            ->notInGroups($excludeGroups)
            ->changed($changed)
        ;

        foreach ($filters as $filter) {
            $this->testFinder->filter($filter);
        }

        foreach ($contains as $contain) {
            $this->testFinder->contains($contain);
        }

        foreach ($notContains as $notContain) {
            $this->testFinder->notContains($notContain);
        }

        return $this->testFinder->getFiles();
    }

    private function chunkTestFiles(array $testFiles) : ChunkedTests
    {
        $chunkedTests = (new ChunkedTests())
            ->setNumChunks($this->numChunks)
            ->setChunk($this->chunk)
        ;

        if (empty($testFiles)) {
            return $chunkedTests;
        }

        $this->testChunker->chunkTestFiles($chunkedTests, $testFiles);

        return $chunkedTests;
    }

    private function outputHeader()
    {
        $chunks = $this->chunkedTests->getChunks();
        $testsPerChunk = $this->chunkedTests->getTestsPerChunk();
        $totalTests = $this->chunkedTests->getTotalTests();
        $numChunks = $this->chunkedTests->getNumChunks();

        $this->output->writeln(sprintf('Total Tests: <info>%s</info>', $totalTests));
        $this->output->writeln(sprintf('Number of Chunks Configured: <info>%s</info>', $numChunks));
        $this->output->writeln(sprintf('Number of Chunks Produced: <info>%s</info>', count($chunks)));
        $this->output->writeln(sprintf('Tests Per Chunk: <info>~%s</info>', $testsPerChunk));

        if ($chunk = $this->chunkedTests->getChunk()) {
            $this->output->writeln(sprintf('Chunk: <info>%s</info>', $chunk));
        }

        $this->output->writeln('-----------');
        $this->output->writeln('');
    }

    private function setupSandbox()
    {
        if ($this->input->getOption('sandbox')) {
            $this->testRunner->runTestCommand('sandbox');
        }
    }

    /**
     * @return null|integer
     */
    private function runChunks()
    {
        $chunks = $this->chunkedTests->getChunks();

        foreach ($chunks as $i => $chunk) {
            // drop and recreate dbs before running this chunk of tests
            if ($this->input->getOption('create-dbs')) {
                $this->testRunner->runTestCommand('create-dbs', [
                    '--quiet' => true,
                ]);
            }

            $chunkNum = $i + 1;

            $code = $this->runChunk($chunkNum, $chunk);

            if ($code > 0) {
                return $code;
            }
        }

        if ($this->parallel) {
            $this->waitForProcesses();
        }
    }

    private function runChunk(int $chunkNum, array $chunk)
    {
        $numTests = $this->countNumTestsInChunk($chunk);

        $this->totalTestsRan += $numTests;

        $process = $this->getChunkProcess($chunk);
        $this->processes[$chunkNum] = $process;

        $callback = $this->createProgressCallback($numTests);

        if ($this->parallel) {
            return $this->runChunkProcessParallel(
                $chunkNum, $process, $callback
            );
        }

        return $this->runChunkProcessSerial(
            $chunkNum, $process, $callback
        );
    }

    private function getChunkProcess(array $chunk) : Process
    {
        $files = $this->buildFilesFromChunk($chunk);

        $config = $this->testRunner->generatePhpunitXml($files);

        $command = sprintf('-c %s', $config);

        return $this->testRunner->getPhpunitProcess($command);
    }

    private function createProgressCallback(int $numTests) : Closure
    {
        if ($this->showProgressBar) {
            $this->progressBar = $this->createChunkProgressBar($numTests);

            return $this->createProgressBarCallback($this->progressBar);
        }

        if ($this->verbose) {
            return function($type, $out) {
                $this->extractDataFromPhpunitOutput($out);

                $this->output->write($out);
            };
        }

        return function($type, $out) {
            $this->extractDataFromPhpunitOutput($out);
        };
    }

    private function createProgressBarCallback(ProgressBar $progressBar)
    {
        return function(string $type, string $buffer) use ($progressBar) {
            $this->extractDataFromPhpunitOutput($buffer);

            if ($progressBar) {
                if (in_array($buffer, ['F', 'E'])) {
                    $progressBar->setBarCharacter('<fg=red>=</>');
                }

                if (in_array($buffer, ['F', 'E', 'S', '.'])) {
                    $progressBar->advance();
                }
            }
        };
    }

    private function runChunkProcessParallel(
        int $chunkNum,
        Process $process,
        Closure $callback)
    {
        $this->output->writeln(sprintf('Starting chunk <info>#%s</info>', $chunkNum));

        $process->start($callback);

        if (count($this->processes) >= $this->numParallelProcesses) {
            $this->waitForProcesses();
        }
    }

    private function waitForProcesses()
    {
        while (count($this->processes)) {
            foreach ($this->processes as $chunkNum => $process) {
                if ($process->isRunning()) {
                    continue;
                }

                unset($this->processes[$chunkNum]);

                $this->codes[] = $code = $process->getExitCode();

                if ($code > 0) {
                    $this->numChunkFailures++;

                    $this->output->writeln(sprintf('Chunk #%s <error>FAILED</error>', $chunkNum));

                    $this->output->writeln('');
                    $this->output->write($process->getOutput());

                    if ($this->input->getOption('stop')) {
                        return $code;
                    }
                } else {
                    $this->output->writeln(sprintf('Chunk #%s <info>PASSED</info>', $chunkNum));

                    if ($this->verbose) {
                        $this->output->writeln('');
                        $this->output->write($process->getOutput());
                    }
                }
            }
        }
    }

    private function runChunkProcessSerial(
        int $chunkNum,
        Process $process,
        Closure $callback)
    {
        if ($this->verbose) {
            $this->output->writeln('');
            $this->output->writeln(sprintf('Running chunk <info>#%s</info>', $chunkNum));
        }

        $this->codes[] = $code = $process->run($callback);

        if ($code > 0) {
            $this->numChunkFailures++;

            if ($this->verbose) {
                $this->output->writeln(sprintf('Chunk #%s <error>FAILED</error>', $chunkNum));
            }

            if ($this->input->getOption('stop')) {
                $this->output->writeln('');
                $this->output->writeln($process->getOutput());

                return $code;
            }
        }

        if (!$this->verbose) {
            $this->progressBar->finish();
            $this->output->writeln('');
        }

        if ($code > 0) {
            $this->output->writeln('');

            if (!$this->verbose) {
                $this->output->writeln($process->getOutput());
            }
        }
    }

    private function outputFooter()
    {
        $chunks = $this->chunkedTests->getChunks();

        $failed = $this->hasFailed();

        $event = $this->stopwatch->stop('Tests');

        $this->output->writeln('');
        $this->output->writeln(sprintf('Time: %s seconds, Memory: %s',
            round($event->getDuration() / 1000, 2),
            $this->formatBytes($event->getMemory())
        ));

        $this->output->writeln('');
        $this->output->writeln(sprintf('%s (%s chunks, %s tests, %s assertions, %s failures%s)',
            $failed ? '<error>FAILED</error>' : '<info>PASSED</info>',
            count($chunks),
            $this->totalTestsRan,
            $this->numAssertions,
            $this->numFailures,
            $failed ? sprintf(', Failed chunks: %s', $this->numChunkFailures) : ''
        ));
    }

    private function hasFailed() : bool
    {
        return array_sum($this->codes) ? true : false;
    }

    private function extractDataFromPhpunitOutput(string $outputBuffer) : int
    {
        preg_match_all('/([0-9]+) assertions/', $outputBuffer, $matches);

        if (isset($matches[1][0])) {
            $this->numAssertions += (int) $matches[1][0];
        }

        preg_match_all('/Assertions: ([0-9]+)/', $outputBuffer, $matches);

        if (isset($matches[1][0])) {
            $this->numAssertions += (int) $matches[1][0];
        }

        preg_match_all('/Failures: ([0-9]+)/', $outputBuffer, $matches);

        if (isset($matches[1][0])) {
            $this->numFailures += (int) $matches[1][0];
        }

        return 0;
    }

    private function countNumTestsInChunk(array $chunk) : int
    {
        return array_sum(array_map(function(array $chunkFile) {
            return $chunkFile['numTests'];
        }, $chunk));
    }

    private function buildFilesFromChunk(array $chunk) : array
    {
        return array_map(function(array $chunkFile) {
            return $chunkFile['file'];
        }, $chunk);
    }

    private function createChunkProgressBar(int $numTests) : ProgressBar
    {
        $progressBar = new ProgressBar($this->output, $numTests);
        $progressBar->setBarCharacter('<fg=green>=</>');
        $progressBar->setProgressCharacter("\xF0\x9F\x8C\xAD");

        return $progressBar;
    }

    private function formatBytes(int $size, int $precision = 2) : string
    {
        if (!$size) {
            return 0;
        }

        $base = log($size, 1024);
        $suffixes = ['', 'KB', 'MB', 'GB', 'TB'];

        return round(pow(1024, $base - floor($base)), $precision).$suffixes[floor($base)];
    }
}
