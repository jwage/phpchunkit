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
     * @var TestRunner
     */
    private $testChunker;

    protected function setUp()
    {
        $this->rootDir = realpath(__DIR__.'/../../../..');

        $this->testChunker = new TestChunker(
            $this->rootDir
        );
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
