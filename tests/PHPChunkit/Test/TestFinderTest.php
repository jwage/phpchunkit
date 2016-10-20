<?php

namespace PHPChunkit\Test;

use PHPChunkit\TestFinder;

class TestFinderTest extends BaseTest
{
    /**
     * @var string
     */
    private $testsDirectory;

    /**
     * @var TestFinder
     */
    private $testFinder;

    protected function setUp()
    {
        $this->testsDirectory = $this->getTestsDirectory();

        $this->testFinder = new TestFinder($this->testsDirectory);
    }

    public function testFindTestFiles()
    {
        $functionalTestFiles = $this->testFinder->findFunctionalTestFiles();

        $this->assertEquals([
            sprintf('%s/PHPChunkit/Test/FunctionalTest1Test.php', $this->testsDirectory),
            sprintf('%s/PHPChunkit/Test/FunctionalTest2Test.php', $this->testsDirectory),
            sprintf('%s/PHPChunkit/Test/FunctionalTest3Test.php', $this->testsDirectory),
            sprintf('%s/PHPChunkit/Test/FunctionalTest4Test.php', $this->testsDirectory),
            sprintf('%s/PHPChunkit/Test/FunctionalTest5Test.php', $this->testsDirectory),
            sprintf('%s/PHPChunkit/Test/FunctionalTest6Test.php', $this->testsDirectory),
            sprintf('%s/PHPChunkit/Test/FunctionalTest7Test.php', $this->testsDirectory),
            sprintf('%s/PHPChunkit/Test/FunctionalTest8Test.php', $this->testsDirectory),
        ], $functionalTestFiles);
    }
}
