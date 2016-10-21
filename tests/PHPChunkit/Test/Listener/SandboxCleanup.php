<?php

namespace PHPChunkit\Test\Listener;

use PDO;
use PHPChunkit\Configuration;
use PHPChunkit\ListenerInterface;

class SandboxCleanup implements ListenerInterface
{
    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function execute()
    {
        $pdo = new PDO('mysql:host=localhost;', 'root', null);

        $configDir = sprintf('%s/bin/config', $this->configuration->getRootDir());
        $configFilePath = sprintf('%s/databases_test.ini', $configDir);
        $configFileBackupPath = sprintf('%s/databases_test.ini.bak', $configDir);
        $databases = parse_ini_file($configFilePath);

        foreach ($databases as $databaseName) {
            $pdo->exec(sprintf('DROP DATABASE IF EXISTS %s', $databaseName));
        }

        rename($configFileBackupPath, $configFilePath);
    }
}
