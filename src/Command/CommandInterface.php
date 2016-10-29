<?php

declare(strict_types=1);

namespace PHPChunkit\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface CommandInterface
{
    public function configure(Command $command);
    public function execute(InputInterface $input, OutputInterface $output);
}
