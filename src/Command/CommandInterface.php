<?php

namespace PHPChunkit\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

interface CommandInterface
{
    public function execute(InputInterface $input, OutputInterface $output);
}
