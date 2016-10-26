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

    public function testFindTestFilesInGroups()
    {
        $functionalTestFiles = $this->testFinder->findTestFilesInGroups(['functional']);

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

    public function testFindTestFilesInGroupsUnknownGroup()
    {
        $this->assertEmpty($this->testFinder->findTestFilesInGroups(['unknown']));
    }

    public function testFindTestFilesExcludingGroups()
    {
        $testFiles = $this->testFinder->findTestFilesExcludingGroups(['functional']);

        $this->assertFalse(in_array(sprintf('%s/FunctionalTest1Test.php', $this->testsDirectory), $testFiles));
        $this->assertTrue(in_array(sprintf('%s/DatabaseSandboxTest.php', $this->testsDirectory), $testFiles));
    }

    public function testFindAllTestFiles()
    {
        $testFiles = $this->testFinder->findAllTestFiles();

        $this->assertTrue(in_array(sprintf('%s/FunctionalTest1Test.php', $this->testsDirectory), $testFiles));
        $this->assertTrue(in_array(sprintf('%s/DatabaseSandboxTest.php', $this->testsDirectory), $testFiles));
    }
}
