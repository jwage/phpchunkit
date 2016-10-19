<?php

namespace JWage\Tester;

class Configuration
{
    /**
     * @var string
     */
    private $rootDir = '';

    public function setRootDir(string $rootDir) : Configuration
    {
        $this->rootDir = $rootDir;

        return $this;
    }

    public function getRootDir() : string
    {
        return $this->rootDir;
    }
}
