<?php

namespace PHPChunkit\Command;

use PHPChunkit\ChunkedTests;
use PHPChunkit\TestChunker;
use PHPChunkit\TestFinder;
use PHPChunkit\TestRunner;
use PHPChunkit\Configuration;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @testClass PHPChunkit\Test\Command\RunTest
 */
class Run implements CommandInterface
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
     * @var TestChunker
     */
    private $testChunker;

    /**
     * @var TestFinder
     */
    private $testFinder;

    /**
     * @param TestRunner      $testRunner
     * @param Configuration   $configuration
     * @param TestChunker     $testChunker
     * @param TestFinder      $testFinder
     */
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

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('Tests');

        $verbose = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;
        $parallel = $input->getOption('parallel');
        $showProgressBar = !$verbose && !$parallel;

        $chunkedTests = $this->chunkTestFiles($input, $output);

        $chunks = $chunkedTests->getChunks();
        $testsPerChunk = $chunkedTests->getTestsPerChunk();
        $totalTests = $chunkedTests->getTotalTests();
        $numChunks = $chunkedTests->getNumChunks();

        if (!$totalTests) {
            $output->writeln('<error>No tests found to run.</error>');

            return;
        }

        $output->writeln(sprintf('Total Tests: <info>%s</info>', $totalTests));
        $output->writeln(sprintf('Number of Chunks Configured: <info>%s</info>', $numChunks));
        $output->writeln(sprintf('Number of Chunks Produced: <info>%s</info>', count($chunks)));
        $output->writeln(sprintf('Tests Per Chunk: <info>~%s</info>', $testsPerChunk));

        if ($chunk = $chunkedTests->getChunk()) {
            $output->writeln(sprintf('Chunk: <info>%s</info>', $chunk));
        }

        $output->writeln('-----------');
        $output->writeln('');

        // sandbox this run
        if ($input->getOption('sandbox')) {
            $this->testRunner->runTestCommand('sandbox');
        }

        // environment vars
        $env = [];

        if (!$chunks) {
            $output->writeln('<error>No tests to run.</error>');
        }

        $codes = [];
        $processes = [];
        $numChunkFailures = 0;
        $totalTestsRan = 0;
        $numProcesses = $parallel;

        foreach ($chunks as $i => $chunk) {
            $chunkNum = $i + 1;

            // drop and recreate dbs before running this chunk of tests
            if ($input->getOption('create-dbs')) {
                $this->testRunner->runTestCommand('create-dbs', [
                    '--quiet' => true,
                ]);
            }

            $numTests = $this->countNumTestsInChunk($chunk);

            $totalTestsRan += $numTests;

            $progressBar = $showProgressBar
                ? $this->createChunkProgressBar($output, $chunk, $numTests)
                : null
            ;

            if ($showProgressBar) {
                $callback = $this->createProgressCallback($progressBar);
            } else {
                if ($verbose) {
                    $callback = function($type, $out) use ($output) {
                        $output->write($out);
                    };
                } else {
                    $callback = null;
                }
            }

            $processes[$chunkNum] = $process = $this->getChunkProcess(
                $chunk, $env
            );

            if ($parallel) {
                $output->writeln(sprintf('Starting chunk <info>#%s</info>', $chunkNum));

                $process->start($callback);

                if (count($processes) >= $numProcesses) {
                    $this->waitForProcesses(
                        $processes,
                        $input,
                        $output,
                        $verbose,
                        $codes,
                        $numChunkFailures
                    );
                }

            } else {
                if ($verbose) {
                    $output->writeln('');
                    $output->writeln(sprintf('Running chunk <info>#%s</info>', $chunkNum));
                }

                $codes[] = $code = $process->run($callback);

                if ($code) {
                    $numChunkFailures++;

                    if ($verbose) {
                        $output->writeln(sprintf('Chunk #%s <error>FAILED</error>', $chunkNum));
                    }

                    if ($input->getOption('stop')) {
                        $output->writeln('');
                        $output->writeln($process->getOutput());

                        return $code;
                    }
                }

                if (!$verbose) {
                    $progressBar->finish();
                    $output->writeln('');
                }

                if ($code) {
                    $output->writeln('');

                    if (!$verbose) {
                        $output->writeln($process->getOutput());
                    }
                }
            }
        }

        if ($parallel) {
            $this->waitForProcesses(
                $processes,
                $input,
                $output,
                $verbose,
                $codes,
                $numChunkFailures
            );
        }

        $failed = array_sum($codes) ? true : false;

        $event = $stopwatch->stop('Tests');

        $output->writeln('');
        $output->writeln(sprintf('Time: %s seconds, Memory: %s',
            round($event->getDuration() / 1000, 2),
            $this->formatBytes($event->getMemory())
        ));

        $output->writeln('');
        $output->writeln(sprintf('%s (%s chunks, %s tests%s)',
            $failed ? '<error>FAILED</error>' : '<info>PASSED</info>',
            count($chunks),
            $totalTestsRan,
            $failed ? sprintf(', Failed chunks: %s', $numChunkFailures) : ''
        ));

        return $failed ? 1 : 0;
    }

    private function waitForProcesses(
        array &$processes,
        InputInterface $input,
        OutputInterface $output,
        bool $verbose,
        &$codes,
        &$numChunkFailures)
    {
        while (count($processes)) {
            foreach ($processes as $chunkNum => $process) {
                if ($process->isRunning()) {
                    continue;
                }

                unset($processes[$chunkNum]);

                $codes[] = $code = $process->getExitCode();

                if ($code) {
                    $numChunkFailures++;

                    $output->writeln(sprintf('Chunk #%s <error>FAILED</error>', $chunkNum));

                    $output->writeln('');
                    $output->write($process->getOutput());

                    if ($input->getOption('stop')) {
                        return $code;
                    }
                } else {
                    $output->writeln(sprintf('Chunk #%s <info>PASSED</info>', $chunkNum));

                    if ($verbose) {
                        $output->writeln('');
                        $output->write($process->getOutput());
                    }
                }
            }
        }
    }

    private function formatBytes($size, $precision = 2)
    {
        if (!$size) {
            return 0;
        }

        $base = log($size, 1024);
        $suffixes = ['', 'KB', 'MB', 'GB', 'TB'];

        return round(pow(1024, $base - floor($base)), $precision).$suffixes[floor($base)];
    }

    private function getChunkProcess(array $chunk, array $env) : Process
    {
        $command = $this->createChunkCommand($chunk);

        return $this->testRunner->getPhpunitProcess($command, $env);
    }

    private function chunkTestFiles(InputInterface $input) : ChunkedTests
    {
        $testFiles = $this->findTestFiles($input);

        $numChunks = $input->getOption('num-chunks')
            ?: $this->configuration->getNumChunks() ?: 1;
        $chunk = $input->getOption('chunk');

        $chunkedTests = (new ChunkedTests())
            ->setNumChunks($numChunks)
            ->setChunk($chunk)
        ;

        if (!$testFiles) {
            return $chunkedTests;
        }

        $this->testChunker->chunkTestFiles($chunkedTests, $testFiles);

        return $chunkedTests;
    }

    private function findTestFiles(InputInterface $input)
    {
        $groups = $input->getOption('group');
        $excludeGroups = $input->getOption('exclude-group');
        $changed = $input->getOption('changed');
        $filter = $input->getOption('filter');
        $files = $input->getOption('file');

        if ($groups) {
            $testFiles = $this->testFinder->findTestFilesInGroups($groups);
        } elseif ($excludeGroups) {
            $testFiles = $this->testFinder->findTestFilesExcludingGroups($excludeGroups);
        } elseif ($changed) {
            $testFiles = $this->testFinder->findChangedTestFiles();
        } elseif ($filter) {
            $testFiles = $this->testFinder->findTestFilesByFilter($filter);
        } elseif ($files) {
            $testFiles = $files;
        } else {
            $testFiles = $this->testFinder->findAllTestFiles();
        }

        return $testFiles;
    }

    private function createChunkCommand(array $chunk) : string
    {
        $files = $this->buildFilesFromChunk($chunk);

        $config = $this->testRunner->generatePhpunitXml($files);

        return sprintf('-c %s', $config);
    }

    private function countNumTestsInChunk(array $chunk) : int
    {
        return array_sum(array_map(function (array $chunkFile) {
            return $chunkFile['numTests'];
        }, $chunk));
    }

    private function buildFilesFromChunk(array $chunk) : array
    {
        return array_map(function (array $chunkFile) {
            return $chunkFile['file'];
        }, $chunk);
    }

    private function createChunkProgressBar(
        OutputInterface $output,
        array $chunk,
        int $numTests) : ProgressBar
    {
        $progressBar = new ProgressBar($output, $numTests);
        $progressBar->setBarCharacter('<fg=green>=</>');
        $progressBar->setProgressCharacter("\xF0\x9F\x8C\xAD");

        return $progressBar;
    }

    private function createProgressCallback(ProgressBar $progressBar)
    {
        return function ($type, $buffer) use ($progressBar) {
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
}
