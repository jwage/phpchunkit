<?php

namespace JWage\Tester\Test;

use JWage\Tester\TestFinder;

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
            sprintf('%s/JWage/Tester/Test/FunctionalTest1Test.php', $this->testsDirectory),
            sprintf('%s/JWage/Tester/Test/FunctionalTest2Test.php', $this->testsDirectory),
            sprintf('%s/JWage/Tester/Test/FunctionalTest3Test.php', $this->testsDirectory),
            sprintf('%s/JWage/Tester/Test/FunctionalTest4Test.php', $this->testsDirectory),
            sprintf('%s/JWage/Tester/Test/FunctionalTest5Test.php', $this->testsDirectory),
            sprintf('%s/JWage/Tester/Test/FunctionalTest6Test.php', $this->testsDirectory),
            sprintf('%s/JWage/Tester/Test/FunctionalTest7Test.php', $this->testsDirectory),
            sprintf('%s/JWage/Tester/Test/FunctionalTest8Test.php', $this->testsDirectory),
        ], $functionalTestFiles);
    }
}
