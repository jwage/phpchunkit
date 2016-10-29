<?php

declare(strict_types = 1);

namespace PHPChunkit;

use PHPChunkit\Command;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @testClass PHPChunkit\Test\PHPChunkitApplicationTest
 */
class PHPChunkitApplication
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var Application
     */
    private $symfonyApplication;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->symfonyApplication = $this->container['phpchunkit.symfony_application'];
    }

    public function run(InputInterface $input, OutputInterface $output) : int
    {
        $this->container['phpchunkit.application.input'] = $input;
        $this->container['phpchunkit.application.output'] = $output;

        $commands = [
            'sandbox' => 'phpchunkit.command.build_sandbox',
            'create-dbs' => 'phpchunkit.command.create_databases',
            'watch' => 'phpchunkit.command.test_watcher',
            'run' => 'phpchunkit.command.run',
            'generate' => 'phpchunkit.command.generate_test',
        ];

        foreach ($commands as $name => $service) {
            $service = $this->container[$service];

            $symfonyCommand = $this->register($name);

            $service->configure($symfonyCommand);

            $symfonyCommand->setCode([$service, 'execute']);
        }

        return $this->runSymfonyApplication($input, $output);
    }

    protected function runSymfonyApplication(
        InputInterface $input,
        OutputInterface $output) : int
    {
        return $this->symfonyApplication->run($input, $output);
    }

    private function register(string $name) : SymfonyCommand
    {
        return $this->symfonyApplication->register($name);
    }
}
