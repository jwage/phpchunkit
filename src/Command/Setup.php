<?php

declare(strict_types = 1);

namespace PHPChunkit\Command;

use PHPChunkit\Container;
use PHPChunkit\Events;
use PHPChunkit\GenerateTestClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Setup implements CommandInterface
{
    const NAME = 'setup';

    public function getName() : string
    {
        return self::NAME;
    }

    public function configure(Command $command)
    {
        $command->setDescription('Help with setting up PHPChunkit.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->title(sprintf('%s (%s)', Container::NAME, Container::VERSION));

        $io->text('PHPChunkit - An advanced PHP test runner built on top of PHPUnit.');

        $io->section('Setup PHPChunkit to get started!');

        $io->text('Place the XML below in <info>phpchunkit.xml.dist</info> in the root of your project.');

        $io->text('');

        $io->text(explode("\n", <<<CONFIG
<comment><?xml version="1.0" encoding="UTF-8"?>

<phpchunkit
    bootstrap="./tests/phpchunkit_bootstrap.php"
    root-dir="./"
    tests-dir="./tests"
    phpunit-path="./vendor/bin/phpunit"
    memory-limit="512M"
    num-chunks="1"
>
    <watch-directories>
        <watch-directory>./src</watch-directory>
        <watch-directory>./tests</watch-directory>
    </watch-directories>
</phpchunkit>
</comment>
CONFIG
));

        $io->text('Place the PHP below in <info>tests/phpchunkit_bootstrap.php</info> in the root of your project to do more advanced configuration');


        $io->text(explode("\n", <<<CONFIG
<comment>
<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Manipulate \$configuration which is an instance of PHPChunkit\Configuration
</comment>
CONFIG
));
    }
}
