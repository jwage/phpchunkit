<?php

namespace PHPChunkit;

/**
 * @testClass PHPChunkit\Test\PHPChunkitTest
 */
class PHPChunkit
{
    public function __construct(string $rootDir, Container $container = null)
    {
        $this->container = $container ?: new Container();
        $this->container['phpchunkit'] = $this;
        $this->container['phpchunkit.root_dir'] = $rootDir;
        $this->container->initialize();
    }

    public function getContainer() : Container
    {
        return $this->container;
    }
}
