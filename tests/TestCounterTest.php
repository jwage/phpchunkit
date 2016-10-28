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
        $this->testCounter = new TestCounter($this->fileClassesHelper);
    }

    public function testCountNumTestsInFile()
    {
        $this->fileClassesHelper->expects($this->once())
            ->method('getFileClasses')
            ->with(__FILE__)
            ->willReturn([
                TestCounterTest::class
            ]);

        $this->assertEquals(8, $this->testCounter->countNumTestsInFile(__FILE__));
    }

    public function testCount1()
    {
    }

    public function testCount2()
    {
    }

    public function testCount3()
    {
    }

    /**
     * @test
     */
    public function methodWithoutTestPrefix()
    {
    }

    /**
     * @dataProvider getTestWithDataProviderData
     */
    public function testWithDataProvider()
    {
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
    }

    protected function nonTestProtectedMethod()
    {
    }

    private function nonTestPrivateMethod()
    {
    }
}
