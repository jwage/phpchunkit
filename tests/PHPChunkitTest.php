<?php

namespace PHPChunkit\Test;

use PHPChunkit\Configuration;
use PHPChunkit\Container;
use PHPChunkit\DatabaseSandbox;
use PHPChunkit\PHPChunkitApplication;
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

    public function testGetContainer()
    {
        $container = $this->phpChunkit->getContainer();

        $this->assertInstanceOf(Container::class, $container);
    }
}
