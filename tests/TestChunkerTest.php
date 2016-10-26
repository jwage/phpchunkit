<?php

namespace PHPChunkit\Test;

use PHPChunkit\ChunkedTests;
use PHPChunkit\TestChunker;
use PHPChunkit\TestFinder;

class TestChunkerTest extends BaseTest
{
    /**
     * @var string
     */
    private $testsDirectory;

    /**
     * @var TestRunner
     */
    private $testChunker;

    protected function setUp()
    {
        $this->testsDirectory = $this->getTestsDirectory();

        $this->testChunker = new TestChunker($this->testsDirectory);
    }

    public function testChunkFunctionalTests()
    {
        $chunkFunctionalTests = (new ChunkedTests())
            ->setNumChunks(4)
        ;

        $testFiles = (new TestFinder($this->testsDirectory))
            ->findTestFilesInGroups(['functional']);

        $this->testChunker->chunkTestFiles($chunkFunctionalTests, $testFiles);

        $expectedChunks = [
            // chunk 1
            [
                [
                    'file' => sprintf('%s/FunctionalTest1Test.php', $this->testsDirectory),
                    'numTests' => 4,
                ],
                [
                    'file' => sprintf('%s/FunctionalTest2Test.php', $this->testsDirectory),
                    'numTests' => 4,
                ]
            ],

            // chunk 2
            [
                [
                    'file' => sprintf('%s/FunctionalTest3Test.php', $this->testsDirectory),
                    'numTests' => 4,
                ],
                [
                    'file' => sprintf('%s/FunctionalTest4Test.php', $this->testsDirectory),
                    'numTests' => 4,
                ]
            ],

            // chunk 3
            [
                [
                    'file' => sprintf('%s/FunctionalTest5Test.php', $this->testsDirectory),
                    'numTests' => 4,
                ],
                [
                    'file' => sprintf('%s/FunctionalTest6Test.php', $this->testsDirectory),
                    'numTests' => 4,
                ]
            ],

            // chunk 4
            [
                [
                    'file' => sprintf('%s/FunctionalTest7Test.php', $this->testsDirectory),
                    'numTests' => 4,
                ],
                [
                    'file' => sprintf('%s/FunctionalTest8Test.php', $this->testsDirectory),
                    'numTests' => 4,
                ]
            ]
        ];

        $this->assertEquals($expectedChunks, $chunkFunctionalTests->getChunks());
    }
}
