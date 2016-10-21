<?php

namespace PHPChunkit;

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

    /**
     * @var null|DatabaseSandbox
     */
    private $databaseSandbox;

    public static function createFromXmlFile(string $path) : self
    {
        $xml = simplexml_load_file($path);
        $attributes = $xml->attributes();
        $rootDir = realpath($attributes['root-dir']);
        $testsDir = realpath($attributes['tests-dir']);
        $phpunitPath = realpath($attributes['phpunit-path']);
        $watchDirectories = (array) $xml->{'watch-directories'}->{'watch-directory'};
        $databaseNames = (array) $xml->{'database-names'}->{'database-name'};
        $events = (array) $xml->{'events'};
        $listeners = $events['listener'];

        $configuration = (new self())
            ->setRootDir($rootDir)
            ->setWatchDirectories($watchDirectories)
            ->setTestsDirectory($testsDir)
            ->setPhpunitPath($phpunitPath)
            ->setDatabaseNames($databaseNames)
        ;

        if ($listeners) {
            $eventDispatcher = $configuration->getEventDispatcher();

            foreach ($listeners as $listener) {
                $eventName = (string) $listener->attributes()['event'];
                $className = (string) $listener->class;

                $listenerInstance = new $className($configuration);

                $eventDispatcher->addListener($eventName, [$listenerInstance, 'execute']);
            }
        }

        return $configuration;
    }

    public function setRootDir(string $rootDir) : self
    {
        $this->rootDir = $rootDir;

        return $this;
    }

    public function getRootDir() : string
    {
        return $this->rootDir;
    }

    public function setWatchDirectories(array $watchDirectories) : self
    {
        $this->watchDirectories = $watchDirectories;

        return $this;
    }

    public function getWatchDirectories() : array
    {
        return $this->watchDirectories;
    }

    public function setTestsDirectory(string $testsDirectory) : self
    {
        $this->testsDirectory = $testsDirectory;

        return $this;
    }

    public function getTestsDirectory() : string
    {
        return $this->testsDirectory;
    }

    public function setPhpunitPath(string $phpunitPath) : self
    {
        $this->phpunitPath = $phpunitPath;

        return $this;
    }

    public function getPhpunitPath() : string
    {
        return $this->phpunitPath;
    }

    public function setDatabaseSandbox(DatabaseSandbox $databaseSandbox) : self
    {
        $this->databaseSandbox = $databaseSandbox;

        return $this;
    }

    public function getDatabaseSandbox() : DatabaseSandbox
    {
        if ($this->databaseSandbox === null) {
            $this->databaseSandbox = new DatabaseSandbox();
        }

        return $this->databaseSandbox;
    }

    public function setDatabaseNames(array $databaseNames) : self
    {
        $this->getDatabaseSandbox()->setDatabaseNames($databaseNames);

        return $this;
    }

    public function setSandboxEnabled(bool $sandboxEnabled) : self
    {
        $this->getDatabaseSandbox()->setSandboxEnabled($sandboxEnabled);

        return $this;
    }

    public function setEventDispatcher(EventDispatcher $eventDispatcher) : self
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
