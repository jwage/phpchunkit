<?php

namespace PHPChunkit\Test;

use PHPChunkit\GenerateTestClass;
use PHPUnit\Framework\TestCase;

class GenerateTestClassTest extends BaseTest
{
    const EXPECTED_ADVANCED_CLASS = <<<EOF
<?php

namespace PHPChunkit\Test;

use PHPChunkit\Test\Seller;
use PHPChunkit\Test\TestAdvancedClass;
use PHPChunkit\Test\TestDependency1;
use PHPChunkit\Test\TestDependency2;
use PHPChunkit\Test\User;
use PHPUnit\Framework\TestCase;

class TestAdvancedClassTest extends TestCase
{
    /**
     * @var TestDependency1
     */
    private \$testDependency1;

    /**
     * @var TestDependency2
     */
    private \$testDependency2;

    /**
     * @var TODO
     */
    private \$value1;

    /**
     * @var TODO
     */
    private \$value2;

    /**
     * @var TODO
     */
    private \$value3;

    /**
     * @var TestAdvancedClass
     */
    private \$testAdvancedClass;

    protected function setUp()
    {
        \$this->testDependency1 = \$this->createMock(TestDependency1::class);
        \$this->testDependency2 = \$this->createMock(TestDependency2::class);
        \$this->value1 = ''; // TODO
        \$this->value2 = ''; // TODO
        \$this->value3 = ''; // TODO

        \$this->testAdvancedClass = new TestAdvancedClass(
            \$this->testDependency1,
            \$this->testDependency2,
            \$this->value1,
            \$this->value2,
            \$this->value3
        );
    }

    public function testGetSomething1()
    {
        \$user = \$this->createMock(User::class);
        \$test1 = \$this->createMock(TestDependency1::class);
        \$test2 = \$this->createMock(TestDependency2::class);
        \$test3 = '';

        \$this->testAdvancedClass->getSomething1(
            \$user,
            \$test1,
            \$test2,
            \$test3
        );
    }

    public function testGetSomething2()
    {
        \$seller = \$this->createMock(Seller::class);
        \$test1 = \$this->createMock(TestDependency1::class);
        \$test2 = \$this->createMock(TestDependency2::class);
        \$test3 = '';

        \$this->testAdvancedClass->getSomething2(
            \$seller,
            \$test1,
            \$test2,
            \$test3
        );
    }
}

EOF;

    const EXPECTED_SIMPLE_CLASS = <<<EOF
<?php

namespace PHPChunkit\Test;

use PHPChunkit\Test\TestSimpleClass;
use PHPUnit\Framework\TestCase;

class TestSimpleClassTest extends TestCase
{
    /**
     * @var TestSimpleClass
     */
    private \$testSimpleClass;

    protected function setUp()
    {
        \$this->testSimpleClass = new TestSimpleClass();
    }

    public function testGetSomething1()
    {
        \$this->testSimpleClass->getSomething1();
    }

    public function testGetSomething2()
    {
        \$this->testSimpleClass->getSomething2();
    }
}

EOF;


    /**
     * @var GenerateTestClass
     */
    private $generateTestClass;

    protected function setUp()
    {
        $this->generateTestClass = new GenerateTestClass();
    }

    public function testGenerateAdvancedClass()
    {
        try {
            new \ReflectionMethod(TestCase::class, 'createMock');
        } catch (\ReflectionException $e) {
            $this->markTestSkipped('PHPUnit >= 5.4 is required.');
        }

        $this->checkGeneratedTestClass(self::EXPECTED_ADVANCED_CLASS, TestAdvancedClass::class);

        $test = new \PHPChunkit\Test\TestAdvancedClassTest();
        $test->setUp();
        $test->testGetSomething1();
        $test->testGetSomething2();
    }

    public function testGenerateAdvancedClassPHPUnitCompat()
    {
        try {
            new \ReflectionMethod(TestCase::class, 'createMock');
            $this->markTestSkipped('PHPUnit < 5.4 is required.');
        } catch (\ReflectionException $e) {
        }

        $this->checkGeneratedTestClass(
            str_replace('createMock', 'getMock', self::EXPECTED_ADVANCED_CLASS),
            TestAdvancedClass::class
        );

        $test = new \PHPChunkit\Test\TestAdvancedClassTest();
        $test->setUp();
        $test->testGetSomething1();
        $test->testGetSomething2();
    }

    public function testGenerateSimpleClass()
    {
        $this->checkGeneratedTestClass(self::EXPECTED_SIMPLE_CLASS, TestSimpleClass::class);

        $test = new \PHPChunkit\Test\TestSimpleClassTest();
        $test->setUp();
        $test->testGetSomething1();
        $test->testGetSomething2();
    }

    /**
     * @param string $expected
     * @param string $className
     */
    private function checkGeneratedTestClass($expected, $className)
    {
        $code = $this->generateTestClass->generate($className);

        $this->assertEquals($expected, $code);

        // make sure it can be executed
        eval(str_replace('<?php', '', $code));
    }
}

class TestAdvancedClass
{
    /**
     * @var TestDependency1
     */
    private $testDependency1;

    /**
     * @var TestDependency2
     */
    private $testDependency2;

    /**
     * @var string
     */
    private $value1;

    /**
     * @var int
     */
    private $value2;

    /**
     * @var float
     */
    private $value3;

    /**
     * @param TestDependency1 $testDependency1
     * @param TestDependency2 $testDependency2
     * @param string          $value1
     * @param int             $value2
     * @param float           $value3
     */
    public function __construct(
        TestDependency1 $testDependency1,
        TestDependency2 $testDependency2,
        $value1,
        $value2,
        $value3
    ) {
    }

    public function getSomething1(User $user, TestDependency1 $test1, TestDependency2 $test2, $test3)
    {
    }

    public function getSomething2(Seller $seller, TestDependency1 $test1, TestDependency2 $test2, $test3)
    {
    }
}

class TestSimpleClass
{
    public function getSomething1()
    {
    }

    public function getSomething2()
    {
    }
}

class TestDependency1
{
}

class TestDependency2
{
}

class Seller
{
}

class User
{
}
