<?php

declare(strict_types = 1);

namespace PHPChunkit;

/**
 * @testClass PHPChunkit\Test\DatabaseSandboxTest
 */
class DatabaseSandbox
{
    const SANDBOXED_DATABASE_NAME_PATTERN = '%s_%s';

    /**
     * @var bool
     */
    private $sandboxEnabled = true;

    /**
     * @var array
     */
    private $databaseNames = [];

    /**
     * @var array
     */
    private $sandboxDatabaseNames = [];

    public function __construct(bool $sandboxEnabled = false, array $databaseNames = [])
    {
        $this->sandboxEnabled = $sandboxEnabled;
        $this->databaseNames = $databaseNames;
    }

    public function getSandboxEnabled() : bool
    {
        return $this->sandboxEnabled;
    }

    public function setSandboxEnabled(bool $sandboxEnabled)
    {
        $this->sandboxEnabled = $sandboxEnabled;
    }

    public function getDatabaseNames() : array
    {
        return $this->databaseNames;
    }

    public function setDatabaseNames(array $databaseNames)
    {
        $this->databaseNames = $databaseNames;
    }

    public function getTestDatabaseNames() : array
    {
        $databaseNames = [];

        foreach ($this->databaseNames as $databaseName) {
            $databaseNames[$databaseName] = sprintf(self::SANDBOXED_DATABASE_NAME_PATTERN,
                $databaseName, 'test'
            );
        }

        return $databaseNames;
    }

    public function getSandboxedDatabaseNames() : array
    {
        $this->initialize();

        return $this->sandboxDatabaseNames;
    }

    protected function generateUniqueId() : string
    {
        return uniqid();
    }

    private function initialize()
    {
        if (empty($this->sandboxDatabaseNames)) {
            $this->sandboxDatabaseNames = $this->generateDatabaseNames();
        }
    }

    private function generateDatabaseNames() : array
    {
        $databaseNames = [];

        foreach ($this->databaseNames as $databaseName) {
            if ($this->sandboxEnabled) {
                $databaseNames[$databaseName] = sprintf(self::SANDBOXED_DATABASE_NAME_PATTERN,
                    $databaseName, $this->generateUniqueId()
                );
            } else {
                $databaseNames[$databaseName] = sprintf(self::SANDBOXED_DATABASE_NAME_PATTERN,
                    $databaseName, 'test'
                );
            }
        }

        return $databaseNames;
    }
}
