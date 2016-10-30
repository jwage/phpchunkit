<?php

declare(strict_types = 1);

namespace PHPChunkit;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class Processes
{
    /**
     * @var ChunkResults
     */
    private $chunkResults;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var int
     */
    private $numProcesses;

    /**
     * @var bool
     */
    private $verbose;

    /**
     * @var bool
     */
    private $stop;

    /**
     * @var array
     */
    private $processes = [];

    public function __construct(
        ChunkResults $chunkResults,
        OutputInterface $output,
        int $numParallelProcesses = 1,
        bool $verbose = false,
        bool $stop = false)
    {
        $this->chunkResults = $chunkResults;
        $this->output = $output;
        $this->numParallelProcesses = $numParallelProcesses;
        $this->verbose = $verbose;
        $this->stop = $stop;
    }

    public function addProcess(Process $process)
    {
        $this->processes[] = $process;
    }

    public function wait()
    {
        if (count($this->processes) < $this->numParallelProcesses) {
            return;
        }

        while (count($this->processes)) {
            foreach ($this->processes as $i => $process) {
                $chunkNum = $i + 1;

                if ($process->isRunning()) {
                    continue;
                }

                unset($this->processes[$i]);

                $this->chunkResults->addCode($code = $process->getExitCode());

                if ($code > 0) {
                    $this->chunkResults->incrementNumChunkFailures();

                    $this->output->writeln(sprintf('Chunk #%s <error>FAILED</error>', $chunkNum));

                    $this->output->writeln('');
                    $this->output->write($process->getOutput());

                    if ($this->stop) {
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
}
