<?php

namespace JWage\Tester;

use Symfony\Component\EventDispatcher\EventDispatcher;

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
     * @var string
     */
    private $testsDirectory = '';

    /**
     * @var string
     */
    private $phpunitPath = '';

    /**
     * @var null|EventDispatcher
     */
    private $eventDispatcher;

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

    public function setPhpunitPath(string $phpunitPath) : Configuration
    {
        $this->phpunitPath = $phpunitPath;

        return $this;
    }

    public function getPhpunitPath() : string
    {
        return $this->phpunitPath;
    }

    public function setEventDispatcher(EventDispatcher $eventDispatcher) : Configuration
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    public function getEventDispatcher() : EventDispatcher
    {
        if ($this->eventDispatcher === null) {
            $this->eventDispatcher = new EventDispatcher();
        }

        return $this->eventDispatcher;
    }
}
