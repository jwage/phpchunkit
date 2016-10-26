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
        $stream = new PHP_Token_Stream($file);
        $classes = $stream->getClasses();

        if (!$classes) {
            return [];
        }

        $fileClasses = [];

        foreach ($classes as $className => $classInfo) {
            $fileClasses[] = $classInfo['package']['namespace'] . '\\' . $className;
        }

        return $fileClasses;
    }
}
