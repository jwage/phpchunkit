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
}
