<?php

namespace PHPChunkit;

use PHP_Token_Stream;

/**
 * @testClass PHPChunkit\Test\TestCounterTest
 */
class TestCounter
{
    /**
     * @var FileClassesHelper
     */
    private $fileClassesHelper;

    /**
     * @param FileClassesHelper $fileClassesHelper
     */
    public function __construct(FileClassesHelper $fileClassesHelper)
    {
        $this->fileClassesHelper = $fileClassesHelper;
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

        $classes = $this->fileClassesHelper->getFileClasses($file);

        if (!$classes) {
            return $numTestsInFile;
        }

        $className = $classes[0];

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

