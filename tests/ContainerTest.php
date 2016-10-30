<?php

namespace PHPChunkit\Test;

use PHPChunkit\Container;

class ContainerTest extends BaseTest
{
    public function testContainer()
    {
        $container = new Container();
        $container['phpchunkit.root_dir'] = $this->getRootDir();
        $container->initialize();

        $configuration = $container['phpchunkit.configuration'];

        $this->assertEquals($this->getRootDir(), $configuration->getRootDir());
        $this->assertTrue($configuration->isSetup());
    }
}
