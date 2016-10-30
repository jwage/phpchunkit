<?php

declare(strict_types = 1);

namespace PHPChunkit;

use ReflectionClass;

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
     * @var array
     */
    private $cache = [];

    /**
     * @var string
     */
    private $cachePath = '';

    public function __construct(FileClassesHelper $fileClassesHelper)
    {
        $this->fileClassesHelper = $fileClassesHelper;
        $this->cachePath = sprintf('%s/testcounter.cache', sys_get_temp_dir());

        $this->loadCache();
    }

    public function __destruct()
    {
        $this->writeCache();
    }

    public function countNumTestsInFile(string $file) : int
    {
        $cacheKey = $file.@filemtime($file);

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $numTestsInFile = 0;

        $classes = $this->fileClassesHelper->getFileClasses($file);

        if (empty($classes)) {
            $this->cache[$cacheKey] = $numTestsInFile;

            return $numTestsInFile;
        }

        $className = $classes[0];

        require_once $file;

        foreach ($classes as $className) {
            $numTestsInFile += $this->countNumTestsInClass($className);
        }

        $this->cache[$cacheKey] = $numTestsInFile;

        return $numTestsInFile;
    }

    public function clearCache()
    {
        if (file_exists($this->cachePath)) {
            unlink($this->cachePath);
        }

        $this->cache = [];
    }

    protected function loadCache()
    {
        if (file_exists($this->cachePath)) {
            $this->cache = include($this->cachePath);
        }
    }

    protected function writeCache()
    {
        file_put_contents($this->cachePath, '<?php return '.var_export($this->cache, true).';');
    }

    private function countNumTestsInClass(string $className) : int
    {
        $reflectionClass = new ReflectionClass($className);

        if ($reflectionClass->isAbstract()) {
            return 0;
        }

        $numTests = 0;

        $methods = $reflectionClass->getMethods();

        foreach ($methods as $method) {
            if (strpos($method->name, 'test') === 0) {
                $docComment = $method->getDocComment();

                if ($docComment !== false) {
                    preg_match_all('/@dataProvider\s([a-zA-Z0-9_]+)/', $docComment, $dataProvider);

                    if (isset($dataProvider[1][0])) {
                        $providerMethod = $dataProvider[1][0];

                        $test = new $className();

                        $numTests = $numTests + count($test->$providerMethod());

                        continue;
                    }
                }

                $numTests++;
            } else {
                $docComment = $method->getDocComment();

                if ($docComment !== false) {
                    preg_match_all('/@test/', $docComment, $tests);

                    if ($tests[0]) {
                        $numTests = $numTests + count($tests[0]);
                    }
                }
            }
        }

        return $numTests;
    }
}
