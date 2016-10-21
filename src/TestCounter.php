<?php

namespace PHPChunkit;

class TestCounter
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

    public function countTotalTestsInFiles(array $files) : int
    {
        $totalTests = 0;

        foreach ($files as $file) {
            $totalTests += $this->countNumTestsInFile($file);
        }

        return $totalTests;
    }

    public function countNumTestsInFile(string $file) : int
    {
        // @FIXME This implies PSR-0 loading, does not work if namespace is not completely reflected in path
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
}
