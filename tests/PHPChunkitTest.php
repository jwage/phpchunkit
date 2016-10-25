<?php

namespace PHPChunkit\Test;

use PHPChunkit\Configuration;
use PHPChunkit\DatabaseSandbox;
use PHPChunkit\TesterApplication;
use PHPChunkit\PHPChunkit;

class PHPChunkitTest extends BaseTest
{
    /**
     * @var PHPChunkit
     */
    private $phpChunkit;

    protected function setUp()
    {
        $this->phpChunkit = new PHPChunkit($this->getRootDir());
    }

    public function testGetConfiguration()
    {
        $configuration = $this->phpChunkit->getConfiguration();

        $this->assertInstanceOf(Configuration::class, $configuration);

        $this->assertNotNull($configuration->getRootDir());
        $this->assertNotNull($configuration->getTestsDirectory());
        $this->assertNotNull($configuration->getPhpunitPath());
        $this->assertNotNull($configuration->getBootstrapPath());
        $this->assertNotEmpty($configuration->getWatchDirectories());

        $databaseSandbox = $configuration->getDatabaseSandbox();

        $this->assertInstanceOf(DatabaseSandbox::class, $databaseSandbox);
        $this->assertEquals(['testdb1', 'testdb2'], $databaseSandbox->getDatabaseNames());
        $this->assertEquals(
            ['testdb1' => 'testdb1_test', 'testdb2' => 'testdb2_test'],
            $databaseSandbox->getTestDatabaseNames()
        );
    }

    public function testGetApplication()
    {
        $configuration = $this->phpChunkit->getConfiguration();
        $application = $this->phpChunkit->getApplication($configuration);

        $this->assertInstanceOf(TesterApplication::class, $application);
    }
}
