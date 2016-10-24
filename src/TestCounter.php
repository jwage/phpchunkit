<?php

namespace PHPChunkit;

use PHP_Token_Stream;

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
        $numTestsInFile = 0;

        $stream = new PHP_Token_Stream($file);
        $classes = $stream->getClasses();

        if (!count($classes))
        {
            return $numTestsInFile;
        }

        // @todo Checking first class as per original functionality. Check all classes instead
        $class = array_keys($classes)[0];
        $className = $classes[$class]['package']['namespace'] . '\\' . $class;

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

