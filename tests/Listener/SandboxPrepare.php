<?php

namespace PHPChunkit\Test\Listener;

use PHPChunkit\Configuration;
use PHPChunkit\ListenerInterface;

class SandboxPrepare implements ListenerInterface
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
        $configDir = sprintf('%s/bin/config', $this->configuration->getRootDir());
        $configFilePath = sprintf('%s/databases_test.ini', $configDir);
        $configFileBackupPath = sprintf('%s/databases_test.ini.bak', $configDir);

        copy($configFilePath, $configFileBackupPath);

        $configContent = file_get_contents($configFilePath);

        $databaseSandbox = $this->configuration->getDatabaseSandbox();

        $modifiedConfigContent = str_replace(
            $databaseSandbox->getTestDatabaseNames(),
            $databaseSandbox->getSandboxedDatabaseNames(),
            $configContent
        );

        file_put_contents($configFilePath, $modifiedConfigContent);
    }
}
