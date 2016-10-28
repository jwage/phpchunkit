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
     * @var array
     */
    private $cache = [];

    /**
     * @var string
     */
    private $cachePath = '';

    /**
     * @param FileClassesHelper $fileClassesHelper
     */
    public function __construct(FileClassesHelper $fileClassesHelper)
    {
        $this->fileClassesHelper = $fileClassesHelper;
        $this->cachePath = sprintf('%s/testcounter.cache', sys_get_temp_dir());

        $this->loadCache();
    }

    public function countNumTestsInFile(string $file) : int
    {
        $cacheKey = $file.@filemtime($file);

        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

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

        $this->cache[$cacheKey] = $numTestsInFile;

        return $numTestsInFile;
    }

    public function clearCache()
    {
        @unlink($this->cachePath);
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

    public function __destruct()
    {
        $this->writeCache();
    }
}
