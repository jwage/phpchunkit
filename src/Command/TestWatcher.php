<?php

namespace PHPChunkit\Command;

use PHPChunkit\FileClassesHelper;
use PHPChunkit\TestRunner;
use PHPChunkit\Configuration;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @testClass PHPChunkit\Test\Command\TestWatcherTest
 */
class TestWatcher implements CommandInterface
{
    /**
     * @var TestRunner
     */
    private $testRunner;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var FileClassesHelper
     */
    private $fileClassesHelper;

    /**
     * @param TestRunner     $testRunner
     * @param Configuration  $configuration
     * @param FileClassesHelper  $fileClassesHelper
     */
    public function __construct(
        TestRunner $testRunner,
        Configuration $configuration,
        FileClassesHelper $fileClassesHelper)
    {
        $this->testRunner = $testRunner;
        $this->configuration = $configuration;
        $this->fileClassesHelper = $fileClassesHelper;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Watching for changes to your code.</info>');

        $lastTime = time();

        while ($this->while()) {
            $this->sleep();

            $finder = $this->createFinder();

            foreach ($finder as $file) {
                $lastTime = $this->checkFile($file, $lastTime);
            }
        }
    }

    protected function sleep()
    {
        sleep(.5);
    }

    protected function while () : bool
    {
        return true;
    }

    private function checkFile(SplFileInfo $file, int $lastTime) : int
    {
        $fileLastModified = $file->getMTime();

        if ($fileLastModified > $lastTime) {

            $lastTime = $fileLastModified;

            if (!$this->isTestFile($file)) {
                // TODO figure out a better way
                // We have to wait a litte bit to look at the contents of the
                // file because it might be empty because of the save operation.
                usleep(10000);

                $files = $this->findAssociatedTestFiles($file);

                if (!$files) {
                    return $lastTime;
                }
            } else {
                $files = [$file->getPathName()];
            }

            $this->testRunner->runTestFiles($files);
        }

        return $lastTime;
    }

    private function createFinder() : Finder
    {
        return Finder::create()
            ->files()
            ->name('*.php')
            ->in($this->configuration->getWatchDirectories())
        ;
    }

    private function isTestFile(SplFileInfo $file) : bool
    {
        return strpos($file->getPathName(), 'Test.php') !== false;
    }

    private function findAssociatedTestFiles(SplFileInfo $file) : array
    {
        $classes = $this->getClassesInsideFile($file->getPathName());

        $testFiles = [];

        foreach ($classes as $className) {

            $reflectionClass = new \ReflectionClass($className);

            $docComment = $reflectionClass->getDocComment();

            preg_match_all('/@testClass\s(.*)/', $docComment, $testClasses);

            if (isset($testClasses[1]) && $testClasses[1]) {
                foreach ($testClasses[1] as $className) {
                    $testFiles[] = (new \ReflectionClass($className))->getFileName();
                }
            }
        }

        return $testFiles;
    }

    private function getClassesInsideFile(string $file) : array
    {
        return $this->fileClassesHelper->getFileClasses($file);
    }
}
