<?php

namespace PHPChunkit;

use InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @testClass PHPChunkit\Test\ConfigurationTest
 */
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
    private $bootstrapPath = '';

    /**
     * @var string
     */
    private $phpunitPath = 'vendor/bin/phpunit';

    /**
     * @var null|EventDispatcher
     */
    private $eventDispatcher;

    /**
     * @var null|DatabaseSandbox
     */
    private $databaseSandbox;

    /**
     * @var string
     */
    private $memoryLimit = '256M';

    /**
     * @var int
     */
    private $numChunks = 1;

    public static function createFromXmlFile(string $path) : self
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(sprintf('XML file count not be found at path "%s"', $path));
        }

        $configuration = new self();

        $xml = simplexml_load_file($path);
        $attributes = $xml->attributes();

        $xmlMappings = [
            'root-dir' => [
                'type' => 'string',
                'setter' => 'setRootDir'
            ],
            'bootstrap' => [
                'type' => 'string',
                'setter' => 'setBootstrapPath'
            ],
            'tests-dir' => [
                'type' => 'string',
                'setter' => 'setTestsDirectory'
            ],
            'phpunit-path' => [
                'type' => 'string',
                'setter' => 'setPhpunitPath'
            ],
            'memory-limit' => [
                'type' => 'string',
                'setter' => 'setMemoryLimit'
            ],
            'num-chunks' => [
                'type' => 'integer',
                'setter' => 'setNumChunks'
            ],
            'watch-directories' => [
                'type' => 'array',
                'setter' => 'setWatchDirectories',
                'xmlName' => 'watch-directory',
            ],
            'database-names' => [
                'type' => 'array',
                'setter' => 'setDatabaseNames',
                'xmlName' => 'database-name',
            ],
        ];

        foreach ($xmlMappings as $name => $mapping) {
            if ($mapping['type'] === 'array') {
                $value = (array) $xml->{$name}->{$mapping['xmlName']};
            } elseif (isset($attributes[$name])) {
                $value = $attributes[$name];

                settype($value, $mapping['type']);
            }

            $configuration->{$mapping['setter']}($value);
        }

        $events = (array) $xml->{'events'};
        $listeners = $events['listener'] ?? null;

        if ($listeners) {
            foreach ($listeners as $listener) {
                $configuration->addListener(
                    (string) $listener->attributes()['event'],
                    (string) $listener->class
                );
            }
        }

        return $configuration;
    }

    public function addListener(
        string $eventName,
        string $className,
        int $priority = 0) : ListenerInterface
    {
        $listener = new $className($this);

        if (!$listener instanceof ListenerInterface) {
            throw new InvalidArgumentException(
                sprintf('%s does not implement %s', $className, ListenerInterface::class)
            );
        }

        $this->getEventDispatcher()->addListener(
            $eventName, [$listener, 'execute']
        );

        return $listener;
    }

    public function setRootDir(string $rootDir) : self
    {
        if (!is_dir($rootDir)) {
            throw new \InvalidArgumentException(
                sprintf('Root directory "%s" does not exist.', $rootDir)
            );
        }

        $this->rootDir = realpath($rootDir);

        return $this;
    }

    public function getRootDir() : string
    {
        return $this->rootDir;
    }

    public function setWatchDirectories(array $watchDirectories) : self
    {
        foreach ($watchDirectories as $key => $watchDirectory) {
            if (!is_dir($watchDirectory)) {
                throw new \InvalidArgumentException(
                    sprintf('Watch directory "%s" does not exist.', $watchDirectory)
                );
            }

            $watchDirectories[$key] = realpath($watchDirectory);
        }

        $this->watchDirectories = $watchDirectories;

        return $this;
    }

    public function getWatchDirectories() : array
    {
        return $this->watchDirectories;
    }

    public function setTestsDirectory(string $testsDirectory) : self
    {
        if (!is_dir($testsDirectory)) {
            throw new \InvalidArgumentException(
                sprintf('Tests directory "%s" does not exist.', $testsDirectory)
            );
        }

        $this->testsDirectory = realpath($testsDirectory);

        return $this;
    }

    public function getTestsDirectory() : string
    {
        return $this->testsDirectory;
    }

    public function setBootstrapPath(string $bootstrapPath) : self
    {
        if (!file_exists($bootstrapPath)) {
            throw new \InvalidArgumentException(
                sprintf('Bootstrap path "%s" does not exist.', $bootstrapPath)
            );
        }

        $this->bootstrapPath = realpath($bootstrapPath);

        return $this;
    }

    public function getBootstrapPath() : string
    {
        return $this->bootstrapPath;
    }

    public function setPhpunitPath(string $phpunitPath) : self
    {
        if (!file_exists($phpunitPath)) {
            throw new \InvalidArgumentException(
                sprintf('PHPUnit path "%s" does not exist.', $phpunitPath)
            );
        }

        $this->phpunitPath = realpath($phpunitPath);

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

    public function setMemoryLimit(string $memoryLimit) : self
    {
        $this->memoryLimit = $memoryLimit;

        return $this;
    }

    public function getMemoryLimit() : string
    {
        return $this->memoryLimit;
    }

    public function setNumChunks(int $numChunks) : self
    {
        $this->numChunks = $numChunks;

        return $this;
    }

    public function getNumChunks() : int
    {
        return $this->numChunks;
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

    public function throwExceptionIfConfigurationIncomplete()
    {
        if (!$this->rootDir) {
            throw new \InvalidArgumentException('You must configure a root directory.');
        }

        if (empty($this->watchDirectories)) {
            throw new \InvalidArgumentException('You must configure a watch directory.');
        }

        if (!$this->testsDirectory) {
            throw new \InvalidArgumentException('You must configure a tests directory.');
        }

        if (!$this->phpunitPath) {
            throw new \InvalidArgumentException('You must configure a phpunit path.');
        }

        return true;
    }
}
