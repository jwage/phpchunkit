<?php

namespace JWage\Tester\Test;

use JWage\Tester\ChunkedFunctionalTests;
use JWage\Tester\TestChunker;

/**
 * @group functional
 */
class TestChunkerTest extends BaseTest
{
    /**
     * @var string
     */
    private $testsDirectory;

    /**
     * @var TestRunner
     */
    private $testChunker;

    protected function setUp()
    {
        $this->testsDirectory = realpath(__DIR__.'/../../../../tests');

        $this->testChunker = new TestChunker($this->testsDirectory);
    }

    public function testChunkFunctionalTests()
    {
        $chunkFunctionalTests = (new ChunkedFunctionalTests())
            ->setNumChunks(14)
        ;

        $this->testChunker->chunkFunctionalTests($chunkFunctionalTests);

        $chunks = $chunkFunctionalTests->getChunks();

        $this->assertTrue(count($chunks) <= 14);
    }
}
