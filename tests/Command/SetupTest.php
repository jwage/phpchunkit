<?php

namespace PHPChunkit\Test\Command;

use PHPChunkit\Command\Setup;
use PHPChunkit\Test\BaseTest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetupTest extends BaseTest
{
    /**
     * @var Setup
     */
    private $setup;

    protected function setUp()
    {
        $this->setup = new Setup();
    }

    public function testGetName()
    {
        $this->assertEquals('setup', $this->setup->getName());
    }

    public function testConfigure()
    {
        $command = $this->createMock(Command::class);

        $command->expects($this->once())
            ->method('setDescription')
            ->with('Help with setting up PHPChunkit.');

        $this->setup->configure($command);
    }

    public function testExecute()
    {
        $input = $this->createMock(InputInterface::class);
        $output = $this->createMock(OutputInterface::class);
        $formatter = $this->createMock(OutputFormatterInterface::class);

        $output->expects($this->any())
            ->method('getFormatter')
            ->willReturn($formatter);

        $output->expects($this->at(4))
            ->method('writeln')
            ->with([
                '<comment>PHPChunkit (0.0.1)</>',
                '<comment></>'
            ]);

        $output->expects($this->at(6))
            ->method('writeln')
            ->with(' PHPChunkit - An advanced PHP test runner built on top of PHPUnit.');

        $this->setup->execute($input, $output);
    }
}
