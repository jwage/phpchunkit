<?php

namespace JWage\Tester;

class TestChunker
{
    /**
     * @var string
     */
    private $testsDirectory;

    /**
     * @param string $testsDirectory
     */
    public function __construct(string $testsDirectory)
    {
        $this->testsDirectory = $testsDirectory;
    }

    /**
     * @param ChunkedFunctionalTests $chunkedFunctionalTests
     */
    public function chunkFunctionalTests(ChunkedFunctionalTests $chunkedFunctionalTests)
    {
        $chunk = $chunkedFunctionalTests->getChunk();
        $numChunks = $chunkedFunctionalTests->getNumChunks();

        $testFiles = $this->findTestFiles();

        $totalTests = $this->countTotalTestsInFiles($testFiles);

        $testsPerChunk = round($totalTests / $numChunks);

        $chunks = [[]];

        $numTestsInChunk = 0;
        foreach ($testFiles as $file) {
            $numTestsInFile = $this->countNumTestsInFile($file);

            $chunkFile = [
                'file' => $file,
                'numTests' => $numTestsInFile,
            ];

            // start a new chunk
            if ($numTestsInChunk > $testsPerChunk) {
                $chunks[] = [$chunkFile];
                $numTestsInChunk = $numTestsInFile;

            // add file to current chunk
            } else {
                $chunks[count($chunks) - 1][] = $chunkFile;
                $numTestsInChunk += $numTestsInFile;
            }
        }

        if ($chunk) {
            $chunkOffset = $chunk - 1;

            if (isset($chunks[$chunkOffset]) && $chunks[$chunkOffset]) {
                $chunks = [$chunkOffset => $chunks[$chunkOffset]];
            } else {
                $chunks = [];
            }
        }

        $chunkedFunctionalTests->setChunks($chunks);
        $chunkedFunctionalTests->setTotalTests($totalTests);
        $chunkedFunctionalTests->setTestsPerChunk($testsPerChunk);
    }

    private function countTotalTestsInFiles(array $files) : int
    {
        $totalTests = 0;

        foreach ($files as $file) {
            $totalTests += $this->countNumTestsInFile($file);
        }

        return $totalTests;
    }

    private function countNumTestsInFile(string $file) : int
    {
        $className = str_replace($this->testsDirectory, '', $file);
        $className = str_replace('.php', '', $className);
        $className = str_replace('/', '\\', $className);

        $numTestsInFile = 0;

        require_once $file;

        $reflectionClass = new \ReflectionClass($className);

        $methods = $reflectionClass->getMethods();

        foreach ($methods as $method) {
            if (strpos($method->getName(), 'test') === 0) {
                $docComment = $method->getDocComment();

                if ($docComment) {
                    preg_match_all('/@dataProvider\s([a-zA-Z0-9_]+)/', $docComment, $dataProvider);

                    if (isset($dataProvider[1][0])) {
                        $providerMethod = $dataProvider[1][0];

                        $test = new $className();

                        $numTestsInFile = $numTestsInFile + count($test->$providerMethod());

                        continue;
                    }
                }

                $numTestsInFile++;
            } else {
                $docComment = $method->getDocComment();

                preg_match_all('/@test/', $docComment, $tests);

                if ($tests[0]) {
                    $numTestsInFile = $numTestsInFile + count($tests[0]);
                }
            }
        }

        return $numTestsInFile;
    }

    private function findTestFiles() : array
    {
        $command = sprintf(
            'find %s -name *Test.php -print0 | xargs -0 grep -l "@group functional" | sort',
            $this->testsDirectory
        );
        $output = shell_exec($command);

        return explode("\n", trim($output));
    }
}
