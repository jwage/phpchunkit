<?php

declare(strict_types=1);

namespace PHPChunkit;

/**
 * @testClass PHPChunkit\Test\ChunkedTestsTest
 */
class ChunkedTests
{
    /**
     * @var int
     */
    private $chunk;

    /**
     * @var int
     */
    private $numChunks = 1;

    /**
     * @var int
     */
    private $testsPerChunk = 0;

    /**
     * @var array
     */
    private $chunks = [];

    /**
     * @var int
     */
    private $totalTests = 0;

    public function getChunk()
    {
        return $this->chunk;
    }

    public function setChunk(int $chunk) : self
    {
        $this->chunk = $chunk;

        return $this;
    }

    public function getNumChunks() : int
    {
        return $this->numChunks;
    }

    public function setNumChunks(int $numChunks) : self
    {
        $this->numChunks = $numChunks;

        return $this;
    }

    public function getTestsPerChunk() : int
    {
        return $this->testsPerChunk;
    }

    public function setTestsPerChunk(int $testsPerChunk) : self
    {
        $this->testsPerChunk = $testsPerChunk;

        return $this;
    }

    public function getChunks() : array
    {
        return $this->chunks;
    }

    public function setChunks(array $chunks) : self
    {
        $this->chunks = $chunks;

        return $this;
    }

    public function getTotalTests() : int
    {
        return $this->totalTests;
    }

    public function setTotalTests(int $totalTests) : self
    {
        $this->totalTests = $totalTests;

        return $this;
    }
}
