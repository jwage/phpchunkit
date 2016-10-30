<?php

declare(strict_types = 1);

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
            $value = null;

            if ($mapping['type'] === 'array') {
                $value = (array) $xml->{$name}->{$mapping['xmlName']};
            } elseif (isset($attributes[$name])) {
                $value = $attributes[$name];

                settype($value, $mapping['type']);
            }

            if ($value !== null) {
                $configuration->{$mapping['setter']}($value);
            }
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
            $eventName, [$listener, 'execute'], $priority
        );

        return $listener;
    }

    public function setRootDir(string $rootDir) : self
    {
        return $this->setPath('rootDir', $rootDir);
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
        return $this->setPath('testsDirectory', $testsDirectory);
    }

    public function getTestsDirectory() : string
    {
        return $this->testsDirectory;
    }

    public function setBootstrapPath(string $bootstrapPath) : self
    {
        return $this->setPath('bootstrapPath', $bootstrapPath);
    }

    public function getBootstrapPath() : string
    {
        return $this->bootstrapPath;
    }

    public function setPhpunitPath(string $phpunitPath) : self
    {
        return $this->setPath('phpunitPath', $phpunitPath);
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

    public function isSetup()
    {
        $setup = true;

        if (!$this->rootDir) {
            $setup = false;
        }

        if (!$this->testsDirectory) {
            $setup = false;
        }

        return $setup;
    }

    private function setPath(string $name, string $path) : self
    {
        if (!file_exists($path)) {
            throw new \InvalidArgumentException(
                sprintf('%s "%s" does not exist.', $name, $path)
            );
        }

        $this->$name = realpath($path);

        return $this;
    }
}
