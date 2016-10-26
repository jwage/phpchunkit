<?php

namespace PHPChunkit;

class TestChunker
{
    /**
     * @var TestCounter
     */
    private $testCounter;

    /**
     * @param string $testsDirectory
     */
    public function __construct(string $testsDirectory)
    {
        $this->testCounter = new TestCounter($testsDirectory);
    }

    public function chunkTestFiles(ChunkedTests $chunkedTests, array $testFiles)
    {
        $chunk = $chunkedTests->getChunk();
        $numChunks = $chunkedTests->getNumChunks();

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

        $chunkedTests->setChunks($chunks);
        $chunkedTests->setTotalTests($totalTests);
        $chunkedTests->setTestsPerChunk($testsPerChunk);
    }
}
