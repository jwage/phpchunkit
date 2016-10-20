<?php

namespace PHPChunkit;

class ChunkedFunctionalTests
{
    /**
     * @var int
     */
    private $chunk;

    /**
     * @var int
     */
    private $numChunks;

    /**
     * @var int
     */
    private $testsPerChunk;

    /**
     * @var []
     */
    private $chunks = [];

    /**
     * @var int
     */
    private $totalTests = 0;

    /**
     * @return int
     */
    public function getChunk()
    {
        return $this->chunk;
    }

    /**
     * @param int $chunk
     *
     * @return self
     */
    public function setChunk($chunk)
    {
        $this->chunk = $chunk;

        return $this;
    }

    /**
     * @return int
     */
    public function getNumChunks()
    {
        return $this->numChunks;
    }

    /**
     * @param int $numChunks
     *
     * @return self
     */
    public function setNumChunks($numChunks)
    {
        $this->numChunks = $numChunks;

        return $this;
    }

    /**
     * @return int
     */
    public function getTestsPerChunk()
    {
        return $this->testsPerChunk;
    }

    /**
     * @param int $testsPerChunk
     *
     * @return self
     */
    public function setTestsPerChunk($testsPerChunk)
    {
        $this->testsPerChunk = $testsPerChunk;

        return $this;
    }

    /**
     * @return []
     */
    public function getChunks()
    {
        return $this->chunks;
    }

    /**
     * @param [] $chunks
     *
     * @return self
     */
    public function setChunks(array $chunks)
    {
        $this->chunks = $chunks;

        return $this;
    }

    /**
     * @return int
     */
    public function getTotalTests()
    {
        return $this->totalTests;
    }

    /**
     * @param int $totalTests
     *
     * @return self
     */
    public function setTotalTests($totalTests)
    {
        $this->totalTests = $totalTests;

        return $this;
    }
}
