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
            sprintf('%s/FunctionalTest1Test.php', $this->testsDirectory),
            sprintf('%s/FunctionalTest2Test.php', $this->testsDirectory),
            sprintf('%s/FunctionalTest3Test.php', $this->testsDirectory),
            sprintf('%s/FunctionalTest4Test.php', $this->testsDirectory),
            sprintf('%s/FunctionalTest5Test.php', $this->testsDirectory),
            sprintf('%s/FunctionalTest6Test.php', $this->testsDirectory),
            sprintf('%s/FunctionalTest7Test.php', $this->testsDirectory),
            sprintf('%s/FunctionalTest8Test.php', $this->testsDirectory),
        ], $functionalTestFiles);
    }
}
