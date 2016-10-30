<?php

namespace PHPChunkit\Test;

use PHPChunkit\Command\CommandInterface;
use PHPChunkit\Configuration;
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
        $symfonyApplication = $this->createMock(Application::class);

        $command = $this->createMock(Command::class);

        $command->expects($this->any())
            ->method('setCode');

        $symfonyApplication->expects($this->any())
            ->method('register')
            ->willReturn($command);

        $container = new Container();
        $container['phpchunkit.root_dir'] = $this->getRootDir();
        $container['phpchunkit.configuration'] = $this->createMock(Configuration::class);
        $container['phpchunkit.symfony_application'] = $symfonyApplication;
        $container['phpchunkit.command.setup'] = $this->createMock(CommandInterface::class);
        $container['phpchunkit.command.test_watcher'] = $this->createMock(CommandInterface::class);
        $container['phpchunkit.command.run'] = $this->createMock(CommandInterface::class);
        $container['phpchunkit.command.build_sandbox'] = $this->createMock(CommandInterface::class);
        $container['phpchunkit.command.create_databases'] = $this->createMock(CommandInterface::class);
        $container['phpchunkit.command.generate_test'] = $this->createMock(CommandInterface::class);

        $phpChunkitApplication =  new PHPChunkitApplicationStub($container);

        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);

        $phpChunkitApplication->run($input, $output);

        $this->assertTrue($phpChunkitApplication->ran);
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
