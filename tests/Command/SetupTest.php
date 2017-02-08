<?php

namespace PHPChunkit\Test\Command;

use PHPChunkit\Command\Setup;
use PHPChunkit\Test\BaseTest;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;

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
        $this->setup->getName();
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
        $output = new NullOutput();

        $this->setup->execute($input, $output);
    }
}
