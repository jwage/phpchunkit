<?php

namespace PHPChunkit;

class TestChunker
{
    /**
     * @var TestFinder
     */
    private $testFinder;

    /**
     * @var TestCounter
     */
    private $testCounter;

    /**
     * @param string $testsDirectory
     */
    public function __construct(string $testsDirectory)
    {
        $this->testFinder = new TestFinder($testsDirectory);
        $this->testCounter = new TestCounter($testsDirectory);
    }

    /**
     * @param ChunkedFunctionalTests $chunkedFunctionalTests
     */
    public function chunkFunctionalTests(ChunkedFunctionalTests $chunkedFunctionalTests)
    {
        $chunk = $chunkedFunctionalTests->getChunk();
        $numChunks = $chunkedFunctionalTests->getNumChunks();

        $testFiles = $this->testFinder->findFunctionalTestFiles();

        $totalTests = $this->testCounter->countTotalTestsInFiles($testFiles);

        $testsPerChunk = round($totalTests / $numChunks);

        $chunks = [[]];

        $numTestsInChunk = 0;
        foreach ($testFiles as $file) {
            $numTestsInFile = $this->testCounter->countNumTestsInFile($file);

            $chunkFile = [
                'file' => $file,
                'numTests' => $numTestsInFile,
            ];

            // start a new chunk
            if ($numTestsInChunk >= $testsPerChunk) {
                $chunks[] = [$chunkFile];
                $numTestsInChunk = $numTestsInFile;

            // add file to current chunk
            } else {
                $chunks[count($chunks) - 1][] = $chunkFile;
                $numTestsInChunk += $numTestsInFile;
            }
        }

        if ($chunk) {
            $chunkOffset = $chunk - 1;

            if (isset($chunks[$chunkOffset]) && $chunks[$chunkOffset]) {
                $chunks = [$chunkOffset => $chunks[$chunkOffset]];
            } else {
                $chunks = [];
            }
        }

        $chunkedFunctionalTests->setChunks($chunks);
        $chunkedFunctionalTests->setTotalTests($totalTests);
        $chunkedFunctionalTests->setTestsPerChunk($testsPerChunk);
    }
}
