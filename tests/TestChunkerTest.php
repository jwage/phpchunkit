<?php

namespace PHPChunkit\Test;

use PHPChunkit\ChunkedFunctionalTests;
use PHPChunkit\TestChunker;

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
        $chunkFunctionalTests = (new ChunkedFunctionalTests())
            ->setNumChunks(4)
        ;

        $this->testChunker->chunkFunctionalTests($chunkFunctionalTests);

        $expectedChunks = [
            // chunk 1
            [
                [
                    'file' => sprintf('%s/PHPChunkit/Test/FunctionalTest1Test.php', $this->testsDirectory),
                    'numTests' => 4,
                ],
                [
                    'file' => sprintf('%s/PHPChunkit/Test/FunctionalTest2Test.php', $this->testsDirectory),
                    'numTests' => 4,
                ]
            ],

            // chunk 2
            [
                [
                    'file' => sprintf('%s/PHPChunkit/Test/FunctionalTest3Test.php', $this->testsDirectory),
                    'numTests' => 4,
                ],
                [
                    'file' => sprintf('%s/PHPChunkit/Test/FunctionalTest4Test.php', $this->testsDirectory),
                    'numTests' => 4,
                ]
            ],

            // chunk 3
            [
                [
                    'file' => sprintf('%s/PHPChunkit/Test/FunctionalTest5Test.php', $this->testsDirectory),
                    'numTests' => 4,
                ],
                [
                    'file' => sprintf('%s/PHPChunkit/Test/FunctionalTest6Test.php', $this->testsDirectory),
                    'numTests' => 4,
                ]
            ],

            // chunk 4
            [
                [
                    'file' => sprintf('%s/PHPChunkit/Test/FunctionalTest7Test.php', $this->testsDirectory),
                    'numTests' => 4,
                ],
                [
                    'file' => sprintf('%s/PHPChunkit/Test/FunctionalTest8Test.php', $this->testsDirectory),
                    'numTests' => 4,
                ]
            ]
        ];

        $this->assertEquals($expectedChunks, $chunkFunctionalTests->getChunks());
    }
}
