<?php

namespace PHPChunkit\Test;

use PHPChunkit\Command\CommandInterface;
use PHPChunkit\Container;
use PHPChunkit\PHPChunkitApplication;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;;
use Symfony\Component\Console\Output\OutputInterface;

class PHPChunkitApplicationTest extends BaseTest
{
    public function testRun()
    {
        $symfonyApplication = new Application();
        $container = new Container();
        $container['phpchunkit.root_dir'] = $this->getRootDir();
        $container['phpchunkit.symfony_application'] = $symfonyApplication;
        $container['phpchunkit.command.test_watcher'] = $this->createMock(CommandInterface::class);
        $container['phpchunkit.command.run'] = $this->createMock(CommandInterface::class);
        $container['phpchunkit.command.build_sandbox'] = $this->createMock(CommandInterface::class);

        $phpChunkitApplication =  new PHPChunkitApplicationStub($container);

        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $phpChunkitApplication->run($input, $output);

        $this->assertTrue($phpChunkitApplication->ran);

        $this->assertCount(6, $symfonyApplication->all());
        $this->assertTrue($symfonyApplication->has('watch'));
        $this->assertTrue($symfonyApplication->has('run'));
        $this->assertTrue($symfonyApplication->has('create-dbs'));
        $this->assertTrue($symfonyApplication->has('sandbox'));
    }
}

class PHPChunkitApplicationStub extends PHPChunkitApplication
{
    public $ran = false;

    protected function runSymfonyApplication(
        InputInterface $input,
        OutputInterface $output) : int
    {
        $this->ran = true;

        return 0;
    }
}
