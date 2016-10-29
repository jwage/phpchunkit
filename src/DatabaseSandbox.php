<?php

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
     * @var []
     */
    private $databaseNames = [];

    /**
     * @var []
     */
    private $sandboxDatabaseNames = [];

    /**
     * @param bool
     */
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

    /**
     * Gets the original test database names.
     *
     * @return array
     */
    public function getTestDatabaseNames()
    {
        $databaseNames = [];

        foreach ($this->databaseNames as $databaseName) {
            $databaseNames[$databaseName] = sprintf(self::SANDBOXED_DATABASE_NAME_PATTERN,
                $databaseName, 'test'
            );
        }

        return $databaseNames;
    }

    /**
     * Gets all the sandboxed database names.
     *
     * @return array
     */
    public function getSandboxedDatabaseNames()
    {
        $this->initialize();

        return $this->sandboxDatabaseNames;
    }

    /**
     * @return string
     */
    protected function generateUniqueId()
    {
        return uniqid();
    }

    /**
     * Initialize database names.
     */
    private function initialize()
    {
        if (empty($this->sandboxDatabaseNames)) {
            $this->sandboxDatabaseNames = $this->generateDatabaseNames();
        }
    }

    /**
     * Generate sandboxed test database names.
     *
     * @return array
     */
    private function generateDatabaseNames()
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
