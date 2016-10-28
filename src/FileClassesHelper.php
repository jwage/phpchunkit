<?php

namespace PHPChunkit;

use PHP_Token_Stream;

/**
 * @testClass PHPChunkit\Test\FileClassesHelperTest
 */
class FileClassesHelper
{
    public function getFileClasses(string $file) : array
    {
        // TODO: Figure out a better and/or faster way to do this.
        // using php tokens to parse the information out is significantly slower.
        $code = @file_get_contents($file);

        if (!$code) {
            return [];
        }

        preg_match_all('/namespace\s(.*);/', $code, $namespaces);

        if (isset($namespaces[1][0])) {
            $namespace = $namespaces[1][0];
        }

        if (!isset($namespace)) {
            throw new \RuntimeException(sprintf('%s is missing a PHP namespace.'));
        }

        preg_match_all('/class\s([a-zA-Z0-9_]+)/', $code, $classes);

        if (isset($classes[1])) {
            foreach ($classes[1] as $className) {
                $classNames[] = $namespace.'\\'.$className;
            }
        }

        return $classNames;
    }
}
