<?php

namespace PHPChunkit\Test;

use PHPChunkit\Configuration;
use PHPChunkit\DatabaseSandbox;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ConfigurationTest extends BaseTest
{
    /**
     * @var Configuration
     */
    private $configuration;

    protected function setUp()
    {
        $this->configuration = new Configuration();
    }

    public function testCreateFromXmlFile()
    {
        $configuration = Configuration::createFromXmlFile(
            sprintf('%s/phpchunkit.xml.dist', $this->getRootDir())
        );

        $this->assertEquals($this->getRootDir(), $configuration->getRootDir());
        $this->assertEquals(sprintf('%s/tests', $this->getRootDir()), $configuration->getTestsDirectory());

        $this->assertEquals([
            sprintf('%s/src', $this->getRootDir()),
            sprintf('%s/tests', $this->getRootDir())
        ], $configuration->getWatchDirectories());

        $this->assertEquals(
            sprintf('%s/vendor/phpunit/phpunit/phpunit', $this->getRootDir()),
            $configuration->getPhpunitPath()
        );

        $this->assertEquals(
            sprintf('%s/tests/phpchunkit_bootstrap.php', $this->getRootDir()),
            $configuration->getBootstrapPath()
        );

        $this->assertEquals('512M', $configuration->getMemoryLimit());
        $this->assertEquals('1', $configuration->getNumChunks());
        $this->assertEquals(['testdb1', 'testdb2'], $configuration->getDatabaseSandbox()->getDatabaseNames());
        $this->assertCount(3, $configuration->getEventDispatcher()->getListeners());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage XML file count not be found at path "invalid"
     */
    public function testCreateFromXmlFileThrowsInvalidArgumentException()
    {
        Configuration::createFromXmlFile('invalid');
    }

    public function testSetGetRootDir()
    {
        $this->assertEquals('', $this->configuration->getRootDir());

        $this->configuration->setRootDir(__DIR__);

        $this->assertEquals(__DIR__, $this->configuration->getRootDir());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Root directory "unknown" does not exist.
     */
    public function testSetRootDirThrowsInvalidArgumentException()
    {
        $this->configuration->setRootDir('unknown');
    }

    public function testSetGetWatchDirectories()
    {
        $this->assertEmpty($this->configuration->getWatchDirectories());

        $this->configuration->setWatchDirectories([__DIR__]);

        $this->assertEquals([__DIR__], $this->configuration->getWatchDirectories());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Watch directory "unknown" does not exist.
     */
    public function testSetWatchDirectoriesThrowsInvalidArgumentException()
    {
        $this->configuration->setWatchDirectories(['unknown']);
    }

    public function testSetGetTestsDirectory()
    {
        $this->assertEquals('', $this->configuration->getTestsDirectory());

        $this->configuration->setTestsDirectory(__DIR__);

        $this->assertEquals(__DIR__, $this->configuration->getTestsDirectory());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Tests directory "unknown" does not exist.
     */
    public function testSetGetTestsDirectoryThrowsInvalidArgumentException()
    {
        $this->configuration->setTestsDirectory('unknown');
    }

    public function testSetGetBootstrapPath()
    {
        $this->assertEquals('', $this->configuration->getBootstrapPath());

        $this->configuration->setBootstrapPath(__FILE__);

        $this->assertEquals(__FILE__, $this->configuration->getBootstrapPath());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Bootstrap path "unknown" does not exist.
     */
    public function testSetGetBootstrapPathThrowsInvalidArgumentException()
    {
        $this->configuration->setBootstrapPath('unknown');
    }

    public function testSetGetPhpunitPath()
    {
        $this->assertEquals('vendor/bin/phpunit', $this->configuration->getPhpunitPath());

        $this->configuration->setPhpunitPath(__FILE__);

        $this->assertEquals(__FILE__, $this->configuration->getPhpunitPath());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage PHPUnit path "unknown" does not exist.
     */
    public function testSetGetPhpunitPathThrowsInvalidArgumentException()
    {
        $this->configuration->setPhpunitPath('unknown');
    }

    public function testSetGetDatabaseSandbox()
    {
        $this->assertInstanceOf(DatabaseSandbox::class, $this->configuration->getDatabaseSandbox());

        $databaseSandbox = new DatabaseSandbox();

        $this->configuration->setDatabaseSandbox($databaseSandbox);

        $this->assertSame($databaseSandbox, $this->configuration->getDatabaseSandbox());
    }

    public function testSetDatabaseNames()
    {
        $databaseNames = ['testdb1', 'testdb2'];

        $this->configuration->setDatabaseNames($databaseNames);

        $this->assertEquals(
            $databaseNames,
            $this->configuration->getDatabaseSandbox()->getDatabaseNames()
        );
    }

    public function testSetGetEventDispatcher()
    {
        $this->assertInstanceOf(EventDispatcher::class, $this->configuration->getEventDispatcher());

        $eventDispatcher = new EventDispatcher();

        $this->configuration->setEventDispatcher($eventDispatcher);

        $this->assertSame($eventDispatcher, $this->configuration->getEventDispatcher());
    }
}
