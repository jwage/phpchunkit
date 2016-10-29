<?php

declare(strict_types=1);

namespace PHPChunkit;

use Symfony\Component\Finder\Finder;

/**
 * @testClass PHPChunkit\Test\TestFinderTest
 */
class TestFinder
{
    /**
     * @var string
     */
    private $testsDirectory;

    /**
     * @var bool
     */
    private $changed = false;

    /**
     * @var Finder
     */
    private $finder;

    public function __construct(string $testsDirectory)
    {
        $this->testsDirectory = $testsDirectory;

        $this->finder = Finder::create()
            ->files()
            ->name('*Test.php')
            ->in($this->testsDirectory)
            ->sortByName();
    }

    public function changed(bool $changed = true) : self
    {
        $this->changed = $changed;

        return $this;
    }

    public function filter(string $filter = null) : self
    {
        $this->finder->path($filter);

        return $this;
    }

    public function contains(string $contains = null) : self
    {
        $this->finder->contains($contains);

        return $this;
    }

    public function notContains(string $notContains = null) : self
    {
        $this->finder->notContains($notContains);

        return $this;
    }

    public function inGroup(string $group = null) : self
    {
        $this->finder->contains(sprintf('@group %s', $group));

        return $this;
    }

    public function inGroups(array $groups = []) : self
    {
        foreach ($groups as $group) {
            $this->inGroup($group);
        }

        return $this;
    }

    public function notInGroup(string $group = null) : self
    {
        $this->finder->notContains(sprintf('@group %s', $group));

        return $this;
    }

    public function notInGroups(array $groups = []) : self
    {
        foreach ($groups as $group) {
            $this->notInGroup($group);
        }

        return $this;
    }

    public function findTestFilesByFilter(string $filter) : array
    {
        $this->filter($filter);

        return $this->buildFilesArrayFromFinder();
    }

    public function findTestFilesInGroups(array $groups) : array
    {
        $this->inGroups($groups);

        return $this->buildFilesArrayFromFinder();
    }

    public function findTestFilesExcludingGroups(array $excludeGroups) : array
    {
        $this->notInGroups($excludeGroups);

        return $this->buildFilesArrayFromFinder();
    }

    public function findAllTestFiles() : array
    {
        return $this->buildFilesArrayFromFinder();
    }

    public function findChangedTestFiles() : array
    {
        $command = "git status --porcelain | grep -e '^\(.*\)Test.php$' | cut -c 3-";

        return $this->buildFilesArrayFromFindCommand($command);
    }

    public function getFiles() : array
    {
        if ($this->changed) {
            return $this->findChangedTestFiles();
        }

        return $this->buildFilesArrayFromFinder();
    }

    private function buildFilesArrayFromFinder() : array
    {
        return array_values(array_map(function($file) {
            return $file->getPathName();
        }, iterator_to_array($this->finder)));
    }

    private function buildFilesArrayFromFindCommand(string $command) : array
    {
        $output = trim(shell_exec($command));

        return $output ? array_map('trim', explode("\n", $output)) : [];
    }
}
