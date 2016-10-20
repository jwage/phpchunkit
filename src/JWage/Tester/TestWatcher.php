<?php

namespace JWage\Tester;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class TestWatcher
{
    /**
     * @var TestRunner
     */
    private $testRunner;

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @param TestRunner     $testRunner
     * @param Configuration  $configuration
     */
    public function __construct(TestRunner $testRunner, Configuration $configuration)
    {
        $this->testRunner = $testRunner;
        $this->configuration = $configuration;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Watching for PHP changes to run the tests for.');

        $lastTime = time();

        while ($this->while()) {
            sleep(.5);

            $finder = $this->createFinder();

            foreach ($finder as $file) {
                $lastTime = $this->checkFile($file, $lastTime);
            }
        }
    }

    /**
     * @return bool
     */
    protected function while()
    {
        return true;
    }

    /**
     * @param $file
     * @param int $lastTime
     */
    private function checkFile($file, $lastTime)
    {
        $fileLastModified = $file->getMTime();

        if ($fileLastModified > $lastTime) {
            $lastTime = $fileLastModified;

            $files = [$file->getPathName()];

            $this->testRunner->runTestFiles($files);
        }

        return $lastTime;
    }

    /**
     * @return Finder
     */
    private function createFinder()
    {
        return Finder::create()
            ->files()
            ->name('*.php')
            ->in($this->configuration->getWatchDirectories())
        ;
    }
}
