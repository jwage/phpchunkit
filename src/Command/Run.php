<?php

declare(strict_types = 1);

namespace PHPChunkit\Command;

use Closure;
use PHPChunkit\ChunkRunner;
use PHPChunkit\ChunkedTests;
use PHPChunkit\ChunkRepository;
use PHPChunkit\ChunkResults;
use PHPChunkit\Processes;
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
    private $stop = false;

    /**
     * @var ChunkedTests
     */
    private $chunkedTests;

    /**
     * @var integer
     */
    private $numParallelProcesses = 1;

    /**
     * @var ChunkRepository
     */
    private $chunkRepository;

    /**
     * @var ChunkRunner
     */
    private $chunkRunner;

    /**
     * @var ChunkResults
     */
    private $chunkResults;

    /**
     * @var Processes
     */
    private $processes;

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

        $this->chunkedTests = $this->chunkRepository->getChunkedTests(
            $this->input
        );

        $this->chunkRunner = new ChunkRunner(
            $this->chunkedTests,
            $this->chunkResults,
            $this->testRunner,
            $this->processes,
            $this->input,
            $this->output,
            $this->verbose,
            $this->parallel
        );

        if (!$this->chunkedTests->hasTests()) {
            $this->output->writeln('<error>No tests found to run.</error>');

            return;
        }

        $this->outputHeader();

        $this->setupSandbox();

        $this->chunkRunner->runChunks();

        $this->outputFooter();

        return $this->chunkResults->hasFailed() ? 1 : 0;
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
        $this->stop = (bool) $this->input->getOption('stop');

        $this->chunkRepository = new ChunkRepository(
            $this->testFinder,
            $this->testChunker,
            $this->configuration
        );
        $this->chunkResults = new ChunkResults();
        $this->processes = new Processes(
            $this->chunkResults,
            $this->output,
            $this->numParallelProcesses,
            $this->verbose,
            $this->stop
        );
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

    private function outputFooter()
    {
        $chunks = $this->chunkedTests->getChunks();

        $failed = $this->chunkResults->hasFailed();

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
            $this->chunkResults->getTotalTestsRan(),
            $this->chunkResults->getNumAssertions(),
            $this->chunkResults->getNumFailures(),
            $failed ? sprintf(', Failed chunks: %s', $this->chunkResults->getNumChunkFailures()) : ''
        ));
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
