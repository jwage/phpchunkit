<?php

namespace PHPChunkit\Test;

use PDO;

/**
 * @group functional
 */
class FunctionalTest1Test extends BaseTest
{
    public function testTest1()
    {
        $this->assertTrue(true);

        $databases = parse_ini_file(realpath(__DIR__.'/../../../bin/config/databases_test.ini'));

        foreach ($databases as $database) {
            $pdo = new PDO(sprintf('mysql:host=localhost;dbname=%s', $database), 'root', null);
        }
    }

    public function testTest2()
    {
        $this->assertTrue(true);
    }

    public function testTest3()
    {
        $this->assertTrue(true);
    }

    public function testTest4()
    {
        $this->assertTrue(true);
    }
}
