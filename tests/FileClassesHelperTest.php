<?php

namespace PHPChunkit\Test;

use PHPChunkit\FileClassesHelper;

class FileClassesHelperTest extends BaseTest
{
    /**
     * @var FileClassesHelper
     */
    private $fileClassesHelper;

    protected function setUp()
    {
        $this->fileClassesHelper = new FileClassesHelper();
    }

    public function testGetFileClasses()
    {
        $this->assertEquals([
            self::class,
            TestClass::class
        ], $this->fileClassesHelper->getFileClasses(__FILE__));
    }

    public function testGetFileClassesNoClasses()
    {
        $this->assertEmpty($this->fileClassesHelper->getFileClasses('unknown'));
    }
}

class TestClass
{
}
