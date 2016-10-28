<?php

namespace PHPChunkit;

/**
 * @testClass PHPChunkit\Test\TestChunkerTest
 */
class TestChunker
{
    /**
     * @var TestCounter
     */
    private $testCounter;

    /**
     * @param TestCounter $testCounter
     */
    public function __construct(TestCounter $testCounter)
    {
        $this->testCounter = $testCounter;
    }

    public function chunkTestFiles(ChunkedTests $chunkedTests, array $testFiles)
    {
        $chunk = $chunkedTests->getChunk();
        $numChunks = $chunkedTests->getNumChunks();

        $fileTestCounts = $this->getTestFileCounts($testFiles);

        $totalTests = array_sum($fileTestCounts);
        $testsPerChunk = round($totalTests / $numChunks);

        $chunks = [[]];

        $numTestsInChunk = 0;
        foreach ($testFiles as $file) {
            $numTestsInFile = $fileTestCounts[$file];

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

        $chunkedTests->setChunks($chunks);
        $chunkedTests->setTotalTests($totalTests);
        $chunkedTests->setTestsPerChunk($testsPerChunk);
    }

    private function getTestFileCounts(array $testFiles) : array
    {
        $fileTestCounts = [];

        foreach ($testFiles as $file) {
            $fileTestCounts[$file] = $this->testCounter->countNumTestsInFile($file);
        }

        return $fileTestCounts;
    }
}
