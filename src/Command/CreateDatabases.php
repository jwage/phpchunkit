<?php

declare(strict_types=1);

namespace PHPChunkit\Command;

use PHPChunkit\Events;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @testClass PHPChunkit\Test\Command\CreateDatabasesTest
 */
class CreateDatabases implements CommandInterface
{
    /**
     * @var EventDispatcher
     */
    private $eventDispatcher;

    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function configure(Command $command)
    {
        $command
            ->setDescription('Create the test databases.')
            ->addOption('sandbox', null, InputOption::VALUE_NONE, 'Prepare sandbox before creating databases.')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->eventDispatcher->dispatch(Events::DATABASES_CREATE);

        if (!$input->getOption('quiet')) {
            $output->writeln('Done creating databases!');
        }
    }
}
