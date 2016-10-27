<?php

namespace PHPChunkit;

/**
 * @testClass PHPChunkit\Test\TestFinderTest
 */
class TestFinder
{
    private $testsDirectory;

    public function __construct(string $testsDirectory)
    {
        $this->testsDirectory = $testsDirectory;
    }

    public function findTestFilesByFilter(string $filter)
    {
        $command = sprintf(
            'find %s -name *%s* | grep "Test.php" | sort',
            $this->testsDirectory,
            $filter
        );

        return $this->shellExecute($command);
    }

    public function findChangedTestFiles() : array
    {
        $command = "git status --porcelain | grep -e '^\(.*\)Test.php$' | cut -c 3-";

        $output = trim(shell_exec($command));

        $files = $output ? array_map('trim', explode("\n", $output)) : [];

        return $files;
    }

    public function findTestFilesInGroups(array $groups) : array
    {
        $command = sprintf(
            'find %s -name *Test.php -print0 | xargs -0 egrep -l "%s" | sort',
            $this->testsDirectory,
            $this->prepareGroupParts($groups)
        );

        return $this->shellExecute($command);
    }

    public function findTestFilesExcludingGroups(array $excludeGroups) : array
    {
        $command = sprintf(
            'find %s -name *Test.php -print0 | xargs -0 egrep -L "%s" | sort',
            $this->testsDirectory,
            $this->prepareGroupParts($excludeGroups)
        );

        return $this->shellExecute($command);
    }

    public function findAllTestFiles() : array
    {
        $command = sprintf(
            'find %s -name *Test.php | sort',
            $this->testsDirectory
        );

        return $this->shellExecute($command);
    }

    private function prepareGroupParts(array $groups) : string
    {
        return implode('|', array_map(function(string $group) {
            return sprintf('@group %s', $group);
        }, $groups));
    }

    private function shellExecute($command)
    {
        $output = trim(shell_exec($command));

        return $output ? explode("\n", $output) : [];
    }
}
