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
            sprintf('%s/tests/FunctionalTest1Test.php', $this->testsDirectory),
            sprintf('%s/tests/FunctionalTest2Test.php', $this->testsDirectory),
            sprintf('%s/tests/FunctionalTest3Test.php', $this->testsDirectory),
            sprintf('%s/tests/FunctionalTest4Test.php', $this->testsDirectory),
            sprintf('%s/tests/FunctionalTest5Test.php', $this->testsDirectory),
            sprintf('%s/tests/FunctionalTest6Test.php', $this->testsDirectory),
            sprintf('%s/tests/FunctionalTest7Test.php', $this->testsDirectory),
            sprintf('%s/tests/FunctionalTest8Test.php', $this->testsDirectory),
        ], $functionalTestFiles);
    }
}
