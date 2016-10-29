<?php

declare(strict_types = 1);

namespace PHPChunkit;

/**
 * @testClass PHPChunkit\Test\PHPChunkitTest
 */
class PHPChunkit
{
    /**
     * @var Container
     */
    private $container;

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
