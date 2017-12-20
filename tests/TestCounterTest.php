<?php

namespace PHPChunkit\Test;

use PHPChunkit\FileClassesHelper;
use PHPChunkit\TestCounter;
use PHPChunkit\TestFinder;

class TestCounterTest extends BaseTest
{
    /**
     * @var FileClassesHelper
     */
    private $fileClassesHelper;

    /**
     * @var TestRunner
     */
    private $testCounter;

    protected function setUp()
    {
        $this->fileClassesHelper = $this->createMock(FileClassesHelper::class);
        $this->testCounter = new TestCounterStub($this->fileClassesHelper);
        $this->testCounter->clearCache();
    }

    public function testCountNumTestsInFileCache()
    {
        $testCounter = new TestCounter($this->fileClassesHelper);
        $testCounter->clearCache();

        $this->fileClassesHelper->expects($this->exactly(1))
            ->method('getFileClasses')
            ->with(__FILE__)
            ->willReturn([
                TestCounterTest::class,
                AbstractTest::class
            ]);

        $this->assertEquals(9, $testCounter->countNumTestsInFile(__FILE__));
        $this->assertEquals(9, $testCounter->countNumTestsInFile(__FILE__));
    }

    public function testCountNumTestsInFile()
    {
        $this->fileClassesHelper->expects($this->once())
            ->method('getFileClasses')
            ->with(__FILE__)
            ->willReturn([
                TestCounterTest::class,
                AbstractTest::class
            ]);

        $this->assertEquals(9, $this->testCounter->countNumTestsInFile(__FILE__));
    }

    public function testCount1()
    {
        $this->assertTrue(true);
    }

    public function testCount2()
    {
        $this->assertTrue(true);
    }

    public function testCount3()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function methodWithoutTestPrefix()
    {
        $this->assertTrue(true);
    }

    /**
     * @dataProvider getTestWithDataProviderData
     */
    public function testWithDataProvider()
    {
        $this->assertTrue(true);
    }

    public function getTestWithDataProviderData()
    {
        return [
            [],
            [],
            [],
        ];
    }

    public function nonTestPublicMethod()
    {
        $this->assertTrue(true);
    }

    protected function nonTestProtectedMethod()
    {
        $this->assertTrue(true);
    }

    private function nonTestPrivateMethod()
    {
        $this->assertTrue(true);
    }
}

class TestCounterStub extends TestCounter
{
    protected function loadCache()
    {
    }

    protected function writeCache()
    {
    }
}

abstract class AbstractTest extends BaseTest
{
    public function testSomething()
    {
    }
}
