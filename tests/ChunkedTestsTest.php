<?php

namespace PHPChunkit\Test;

use PHPChunkit\ChunkedTests;

class ChunkedTestsTest extends BaseTest
{
    /**
     * @var ChunkedTests
     */
    private $chunkedTests;

    protected function setUp()
    {
        $this->chunkedTests = new ChunkedTests();
    }

    public function testSetGetChunk()
    {
        $this->assertNull($this->chunkedTests->getChunk());

        $this->assertSame($this->chunkedTests, $this->chunkedTests->setChunk(1));

        $this->assertEquals(1, $this->chunkedTests->getChunk());
    }

    public function testSetGetNumChunks()
    {
        $this->assertEquals(1, $this->chunkedTests->getNumChunks());

        $this->assertSame($this->chunkedTests, $this->chunkedTests->setNumChunks(2));

        $this->assertEquals(2, $this->chunkedTests->getNumChunks());
    }

    public function testSetGetTestsPerChunk()
    {
        $this->assertEquals(0, $this->chunkedTests->getTestsPerChunk());

        $this->assertSame($this->chunkedTests, $this->chunkedTests->setTestsPerChunk(1));

        $this->assertEquals(1, $this->chunkedTests->getTestsPerChunk());
    }

    public function testSetGetChunks()
    {
        $this->assertEmpty($this->chunkedTests->getChunks());

        $this->assertSame($this->chunkedTests, $this->chunkedTests->setChunks(['test']));

        $this->assertEquals(['test'], $this->chunkedTests->getChunks());
    }

    public function testSetGetTotalTests()
    {
        $this->assertEquals(0, $this->chunkedTests->getTotalTests());

        $this->assertSame($this->chunkedTests, $this->chunkedTests->setTotalTests(1));

        $this->assertEquals(1, $this->chunkedTests->getTotalTests());
    }
}
