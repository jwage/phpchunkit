<?php

declare(strict_types = 1);

namespace PHPChunkit;

use Closure;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class ChunkRunner
{
    /**
     * @var ChunkedTests
     */
    private $chunkedTests;

    /**
     * @var ChunkResults
     */
    private $chunkResults;

    /**
     * @var TestRunner
     */
    private $testRunner;

    /**
     * @var Processes
     */
    private $processes;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var bool
     */
    private $verbose = false;

    /**
     * @var bool
     */
    private $parallel = false;

    public function __construct(
        ChunkedTests $chunkedTests,
        ChunkResults $chunkResults,
        TestRunner $testRunner,
        Processes $processes,
        InputInterface $input,
        OutputInterface $output,
        bool $verbose = false,
        bool $parallel = false)
    {
        $this->chunkedTests = $chunkedTests;
        $this->chunkResults = $chunkResults;
        $this->testRunner = $testRunner;
        $this->processes = $processes;
        $this->input = $input;
        $this->output = $output;
        $this->verbose = $verbose;
        $this->parallel = $parallel;
        $this->showProgressBar = !$this->verbose && !$this->parallel;
    }

    /**
     * @return null|integer
     */
    public function runChunks()
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
            $this->processes->wait();
        }
    }

    private function runChunk(int $chunkNum, array $chunk)
    {
        $numTests = $this->countNumTestsInChunk($chunk);

        $this->chunkResults->incrementTotalTestsRan($numTests);

        $process = $this->getChunkProcess($chunk);
        $this->processes->addProcess($process);

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

        $this->processes->wait();
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

        $this->chunkResults->addCode($code = $process->run($callback));

        if ($code > 0) {
            $this->chunkResults->incrementNumChunkFailures();

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

    private function extractDataFromPhpunitOutput(string $outputBuffer) : int
    {
        preg_match_all('/([0-9]+) assertions/', $outputBuffer, $matches);

        if (isset($matches[1][0])) {
            $this->chunkResults->incrementNumAssertions((int) $matches[1][0]);
        }

        preg_match_all('/Assertions: ([0-9]+)/', $outputBuffer, $matches);

        if (isset($matches[1][0])) {
            $this->chunkResults->incrementNumAssertions((int) $matches[1][0]);
        }

        preg_match_all('/Failures: ([0-9]+)/', $outputBuffer, $matches);

        if (isset($matches[1][0])) {
            $this->chunkResults->incrementNumFailures((int) $matches[1][0]);
        }

        return 0;
    }

    private function createChunkProgressBar(int $numTests) : ProgressBar
    {
        $progressBar = new ProgressBar($this->output, $numTests);
        $progressBar->setBarCharacter('<fg=green>=</>');
        $progressBar->setProgressCharacter("\xF0\x9F\x8C\xAD");

        return $progressBar;
    }
}
