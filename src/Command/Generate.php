<?php

declare(strict_types = 1);

namespace PHPChunkit\Command;

use PHPChunkit\Events;
use PHPChunkit\GenerateTestClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Generate implements CommandInterface
{
    const NAME = 'generate';

    /**
     * @var GenerateTestClass
     */
    private $generateTestClass;

    public function __construct(GenerateTestClass $generateTestClass)
    {
        $this->generateTestClass = $generateTestClass;
    }

    public function getName() : string
    {
        return self::NAME;
    }

    public function configure(Command $command)
    {
        $command
            ->setDescription('Generate a test skeleton from a class.')
            ->addArgument('class', InputArgument::REQUIRED, 'Class to generate test for.')
            ->addOption('file', null, InputOption::VALUE_REQUIRED, 'File path to write test to.')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $class = $input->getArgument('class');

        $code = $this->generateTestClass->generate($class);

        if ($file = $input->getOption('file')) {
            if (file_exists($file)) {
                throw new \InvalidArgumentException(sprintf('%s already exists.', $file));
            }

            $output->writeln(sprintf('Writing test to <info>%s</info>', $file));

            file_put_contents($file, $code);
        } else {
            $output->write($code);
        }
    }
}
