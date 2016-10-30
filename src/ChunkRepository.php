<?php

declare(strict_types = 1);

namespace PHPChunkit;

use Symfony\Component\Console\Input\InputInterface;

class ChunkRepository
{
    /**
     * @var TestFinder
     */
    private $testFinder;

    /**
     * @var TestChunker
     */
    private $testChunker;

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(TestFinder $testFinder, TestChunker $testChunker, Configuration $configuration)
    {
        $this->testFinder = $testFinder;
        $this->testChunker = $testChunker;
        $this->configuration = $configuration;
    }

    public function getChunkedTests(InputInterface $input) : ChunkedTests
    {
        $chunk = (int) $input->getOption('chunk');

        $testFiles = $this->findTestFiles($input);

        $chunkedTests = (new ChunkedTests())
            ->setNumChunks($this->getNumChunks($input))
            ->setChunk($chunk)
        ;

        if (empty($testFiles)) {
            return $chunkedTests;
        }

        $this->testChunker->chunkTestFiles($chunkedTests, $testFiles);

        return $chunkedTests;
    }

    private function findTestFiles(InputInterface $input)
    {
        $files = $input->getOption('file');

        if (!empty($files)) {
            return $files;
        }

        $groups = $input->getOption('group');
        $excludeGroups = $input->getOption('exclude-group');
        $changed = $input->getOption('changed');
        $filters = $input->getOption('filter');
        $contains = $input->getOption('contains');
        $notContains = $input->getOption('not-contains');

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


    private function getNumChunks(InputInterface $input) : int
    {
        return (int) $input->getOption('num-chunks')
            ?: $this->configuration->getNumChunks() ?: 1;
    }

}
