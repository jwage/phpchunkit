<?php

namespace PHPChunkit\Test;

use PHPChunkit\TestCounter;
use PHPChunkit\TestFinder;

class TestCounterTest extends BaseTest
{
    /**
     * @var string
     */
    private $testsDirectory;

    /**
     * @var TestRunner
     */
    private $testCounter;

    /**
     * @var TestFinder
     */
    private $testFinder;

    protected function setUp()
    {
        $this->testsDirectory = $this->getTestsDirectory();

        $this->testCounter = new TestCounter($this->testsDirectory);
        $this->testFinder = new TestFinder($this->testsDirectory);
    }

    public function testCountNumTestsInFile()
    {
        $this->assertEquals(9, $this->testCounter->countNumTestsInFile(__FILE__));
    }

    public function testCountTotalTestsInFiles()
    {
        $this->assertEquals(9, $this->testCounter->countTotalTestsInFiles([__FILE__]));
        $this->assertEquals(32, $this->testCounter->countTotalTestsInFiles(
            $this->testFinder->findTestFilesInGroups(['functional'])
        ));
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
