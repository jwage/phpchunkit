<?php

declare(strict_types = 1);

namespace PHPChunkit;

use PHPChunkit\Command;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\ArrayInput;
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

    private static $commands = [
        'phpchunkit.command.setup',
        'phpchunkit.command.build_sandbox',
        'phpchunkit.command.create_databases',
        'phpchunkit.command.test_watcher',
        'phpchunkit.command.run',
        'phpchunkit.command.generate_test',
    ];

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->symfonyApplication = $this->container['phpchunkit.symfony_application'];
        $this->symfonyApplication->setAutoExit(false);
    }

    public function run(InputInterface $input, OutputInterface $output) : int
    {
        $this->container['phpchunkit.application.input'] = $input;
        $this->container['phpchunkit.application.output'] = $output;

        foreach (self::$commands as $serviceName) {
            $this->registerCommand($serviceName);
        }

        return $this->runSymfonyApplication($input, $output);
    }

    public function registerCommand(string $serviceName)
    {
        $service = $this->container[$serviceName];

        $symfonyCommand = $this->register($service->getName());

        $service->configure($symfonyCommand);

        $symfonyCommand->setCode(function($input, $output) use ($service) {
            if (!$service instanceof Command\Setup) {
                $configuration = $this->container['phpchunkit.configuration'];

                if (!$configuration->isSetup()) {
                    return $this->symfonyApplication
                        ->find('setup')->run($input, $output);
                }
            }

            return call_user_func_array([$service, 'execute'], [$input, $output]);
        });
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
