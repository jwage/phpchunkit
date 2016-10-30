<?php

declare(strict_types = 1);

namespace PHPChunkit;

class ChunkResults
{
    /**
     * @var integer
     */
    private $numAssertions = 0;

    /**
     * @var integer
     */
    private $numFailures = 0;

    /**
     * @var integer
     */
    private $numChunkFailures = 0;

    /**
     * @var integer
     */
    private $totalTestsRan = 0;

    /**
     * @var array
     */
    private $codes = [];

    public function incrementNumAssertions(int $num = 1)
    {
        $this->numAssertions += $num;
    }

    public function getNumAssertions() : int
    {
        return $this->numAssertions;
    }

    public function incrementNumFailures(int $num = 1)
    {
        $this->numFailures += $num;
    }

    public function getNumFailures() : int
    {
        return $this->numFailures;
    }

    public function incrementNumChunkFailures(int $num = 1)
    {
        $this->numChunkFailures += $num;
    }

    public function getNumChunkFailures() : int
    {
        return $this->numChunkFailures;
    }

    public function incrementTotalTestsRan(int $num = 1)
    {
        $this->totalTestsRan += $num;
    }

    public function getTotalTestsRan() : int
    {
        return $this->totalTestsRan;
    }

    public function addCode(int $code)
    {
        $this->codes[] = $code;
    }

    public function getCodes() : array
    {
        return $this->codes;
    }

    public function hasFailed() : bool
    {
        return array_sum($this->codes) ? true : false;
    }
}
