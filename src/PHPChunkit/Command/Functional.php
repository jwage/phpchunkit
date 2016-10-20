<?php

namespace PHPChunkit\Command;

use PHPChunkit\ChunkedFunctionalTests;
use PHPChunkit\TestChunker;
use PHPChunkit\TestRunner;
use PHPChunkit\Configuration;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class Functional
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
     * @param TestRunner      $testRunner
     * @param Configuration   $configuration
     */
    public function __construct(
        TestRunner $testRunner,
        Configuration $configuration)
    {
        $this->testRunner = $testRunner;
        $this->configuration = $configuration;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('Functional Tests');

        $verbose = $output->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;

        $chunk = $input->getOption('chunk');
        $numChunks = $input->getOption('num-chunks');

        $chunkedFunctionalTests = $this->chunkFunctionalTests(
            $numChunks, $chunk
        );

        $chunks = $chunkedFunctionalTests->getChunks();
        $testsPerChunk = $chunkedFunctionalTests->getTestsPerChunk();
        $totalTests = $chunkedFunctionalTests->getTotalTests();

        $output->writeln(sprintf('Total Tests: <info>%s</info>', $totalTests));
        $output->writeln(sprintf('Number of Chunks Configured: <info>%s</info>', $numChunks));
        $output->writeln(sprintf('Number of Chunks Produced: <info>%s</info>', count($chunks)));
        $output->writeln(sprintf('Tests Per Chunk: <info>~%s</info>', $testsPerChunk));

        if ($chunk) {
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

        foreach ($chunks as $i => $chunk) {
            $chunkNum = $i + 1;

            $output->writeln(sprintf('Running chunk <info>#%s</info>', $chunkNum));

            // drop and recreate dbs before running this chunk of tests
            if ($input->getOption('create-dbs')) {
                $this->testRunner->runTestCommand('create-dbs', [
                    '--quiet' => true,
                ]);
            }

            $outputBuffer = '';

            $progressBar = !$verbose ? $this->createChunkProgressBar($output, $chunk) : null;

            $codes[] = $code = $this->runChunk(
                $progressBar, $chunk, $outputBuffer, $verbose, $env
            );

            if ($code && $input->getOption('stop')) {
                $output->writeln('');
                $output->writeln($outputBuffer);

                return $code;
            }

            if (!$verbose) {
                $progressBar->finish();
                $output->writeln('');
            }

            if ($code) {
                $output->writeln($outputBuffer);
            }
        }

        $event = $stopwatch->stop('Functional Tests');

        $output->writeln('');
        $output->writeln(sprintf('Time: %s seconds, Memory: %s',
            round($event->getDuration() / 1000, 2),
            $this->formatBytes($event->getMemory())
        ));

        $output->writeln('');
        $output->writeln(sprintf('OK (%s chunks, %s tests)', $numChunks, $totalTests));

        return array_sum($codes) ? 1 : 0;
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

    private function runChunk(
        ProgressBar $progressBar = null,
        array $chunk,
        &$outputBuffer,
        bool $verbose,
        array $env) : int
    {
        $callback = $this->createProgressCallback(
            $progressBar, $outputBuffer, $verbose
        );

        $command = $this->createFunctionalChunkCommand($chunk);

        return $this->testRunner->runPhpunit($command, $env, $callback);
    }

    private function chunkFunctionalTests(int $numChunks, int $chunk = null) : ChunkedFunctionalTests
    {
        $chunkedFunctionalTests = (new ChunkedFunctionalTests())
            ->setNumChunks($numChunks)
            ->setChunk($chunk)
        ;

        $testChunker = new TestChunker($this->configuration->getTestsDirectory());

        $testChunker->chunkFunctionalTests($chunkedFunctionalTests);

        return $chunkedFunctionalTests;
    }

    private function createFunctionalChunkCommand(array $chunk) : string
    {
        $files = $this->buildFilesFromChunk($chunk);

        $config = $this->testRunner->generatePhpunitXml($files);

        return sprintf('-c %s --group functional', $config);
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

    private function createChunkProgressBar(OutputInterface $output, array $chunk) : ProgressBar
    {
        $numTests = $this->countNumTestsInChunk($chunk);

        $progressBar = new ProgressBar($output, $numTests);
        $progressBar->setBarCharacter('<fg=green>=</>');
        $progressBar->setProgressCharacter("\xF0\x9F\x8C\xAD");

        return $progressBar;
    }

    private function createProgressCallback(ProgressBar $progressBar = null, &$outputBuffer, $verbose)
    {
        return function ($buffer) use ($progressBar, &$outputBuffer, $verbose) {
            if ($verbose) {
                echo $buffer;
            } else {
                $outputBuffer .= $buffer;

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
