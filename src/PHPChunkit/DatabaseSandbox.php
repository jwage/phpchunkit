<?php

namespace PHPChunkit;

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
    private $rawDatabaseNames = [];

    /**
     * @var []
     */
    private $databaseNames = [];

    /**
     * @param bool
     */
    public function __construct($sandboxEnabled = null, array $rawDatabaseNames = [])
    {
        $this->rawDatabaseNames = $rawDatabaseNames;

        if ($sandboxEnabled !== null) {
            $this->sandboxEnabled = $sandboxEnabled;
        } else {
            $this->sandboxEnabled = array_filter($_SERVER['argv'], function ($arg) {
                return strpos($arg, 'sandbox') !== false;
            }) ? true : false;
        }
    }

    /**
     * @param string $database
     *
     * @return string
     */
    public function getDatabaseName($databaseName)
    {
        $this->initialize();

        return $this->databaseNames[$databaseName];
    }

    /**
     * Gets the original test database names.
     *
     * @return []
     */
    public function getTestDatabaseNames()
    {
        $databaseNames = [];

        foreach ($this->rawDatabaseNames as $databaseName) {
            $databaseNames[$databaseName] = sprintf(self::SANDBOXED_DATABASE_NAME_PATTERN,
                $databaseName, 'test'
            );
        }

        return $databaseNames;
    }

    /**
     * Gets all the sandboxed database names.
     *
     * @return []
     */
    public function getSandboxedDatabaseNames()
    {
        $this->initialize();

        return $this->databaseNames;
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
        if (!$this->databaseNames) {
            $this->databaseNames = $this->generateDatabaseNames();
        }
    }

    /**
     * Generate sandboxed test database names.
     *
     * @return []
     */
    private function generateDatabaseNames()
    {
        $databaseNames = [];

        foreach ($this->rawDatabaseNames as $databaseName) {
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
