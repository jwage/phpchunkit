<?php

namespace JWage\Tester;

class Configuration
{
    /**
     * @var string
     */
    private $rootDir = '';

    /**
     * @var array
     */
    private $watchDirectories = [];

    /**
     * @var array
     */
    private $testsDirectory = '';

    public function setRootDir(string $rootDir) : Configuration
    {
        $this->rootDir = $rootDir;

        return $this;
    }

    public function getRootDir() : string
    {
        return $this->rootDir;
    }

    public function setWatchDirectories(array $watchDirectories) : Configuration
    {
        $this->watchDirectories = $watchDirectories;

        return $this;
    }

    public function getWatchDirectories() : array
    {
        return $this->watchDirectories;
    }

    public function setTestsDirectory(string $testsDirectory) : Configuration
    {
        $this->testsDirectory = $testsDirectory;

        return $this;
    }

    public function getTestsDirectory() : string
    {
        return $this->testsDirectory;
    }
}
