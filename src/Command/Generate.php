<?php

namespace PHPChunkit\Command;

use PHPChunkit\Events;
use PHPChunkit\GenerateTestClass;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Generate implements CommandInterface
{
    /**
     * @var GenerateTestClass
     */
    private $generateTestClass;

    /**
     * @param GenerateTestClass $generateTestClass
     */
    public function __construct(GenerateTestClass $generateTestClass)
    {
        $this->generateTestClass = $generateTestClass;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
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
