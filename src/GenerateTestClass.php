<?php

declare(strict_types = 1);

namespace PHPChunkit;

use Doctrine\Common\Inflector\Inflector;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * @testClass PHPChunkit\Test\GenerateTestClassTest
 */
class GenerateTestClass
{
    const CLASS_TEMPLATE = <<<EOF
<?php

namespace {{ namespace }};

{{ useStatements }}

class {{ shortName }} extends \PHPUnit\Framework\TestCase
{
{{ properties }}

{{ setUpCode }}

{{ methods }}
}

EOF;

    const COMMAND_TEMPLATE = <<<EOF
<?php

namespace {{ namespace }};

{{ useStatements }}
use OpenSky\Bundle\MainBundle\Tests\OpenSkyCommandTestCase;
use Symfony\Component\DependencyInjection\Container;

class {{ shortName }} extends OpenSkyCommandTestCase
{
    private \$command;
    private \$container;

    protected function setUp()
    {
       \$this->container = new Container();
       \$this->command = new {{ classShortName }}();
       \$this->command->setContainer(\$this->container);
    }

    public function testExecute()
    {
    }
}

EOF;

    /**
     * @var ReflectionClass
     */
    private $reflectionClass;

    /**
     * @var string
     */
    private $classShortName;

    /**
     * @var string
     */
    private $classCamelCaseName;

    /**
     * @var string
     */
    private $testNamespace;

    /**
     * @var string
     */
    private $testClassShortName;

    /**
     * @var string
     */
    private $useStatementsCode;

    /**
     * @var string
     */
    private $testPropertiesCode;

    /**
     * @var string
     */
    private $setUpCode;

    /**
     * @var string
     */
    private $testMethodsCode;

    public function generate(string $className) : string
    {
        $this->reflectionClass = new ReflectionClass($className);

        $this->classShortName = $this->reflectionClass->getShortName();
        $this->classCamelCaseName = Inflector::camelize($this->classShortName);
        $this->testNamespace = preg_replace('/(.*)Bundle/', '$0\Tests', $this->reflectionClass->getNamespaceName());
        $this->testClassShortName = $this->classShortName.'Test';

        $this->useStatementsCode = $this->generateUseStatements();
        $this->testPropertiesCode = $this->generateClassProperties();
        $this->setUpCode = $this->generateSetUp();
        $this->testMethodsCode = $this->generateTestMethods();

        $twig = new \Twig_Environment(new \Twig_Loader_String(), [
            'autoescape' => false,
        ]);

        $template = self::CLASS_TEMPLATE;

        return $twig->render($template, [
            'classShortName' => $this->classShortName,
            'classCamelCaseName' => $this->classCamelCaseName,
            'namespace' => $this->testNamespace,
            'shortName' => $this->testClassShortName,
            'methods' => $this->testMethodsCode,
            'properties' => $this->testPropertiesCode,
            'useStatements' => $this->useStatementsCode,
            'setUpCode' => $this->setUpCode,
        ]);
    }

    private function generateClassProperties() : string
    {
        $testPropertiesCode = [];

        if ($parameters = $this->getConstructorParameters()) {
            foreach ($parameters as $key => $parameter) {
                $isLast = $key === count($parameters) - 1;

                if ($parameterClass = $parameter->getClass()) {
                    $testPropertiesCode[] = '    /**';
                    $testPropertiesCode[] = '     * @var '.$parameterClass->getShortName();
                    $testPropertiesCode[] = '     */';
                    $testPropertiesCode[] = '    private $'.$parameter->name.';';

                    if (!$isLast) {
                        $testPropertiesCode[] = '';
                    }
                } else {
                    $testPropertiesCode[] = '    /**';
                    $testPropertiesCode[] = '     * @var TODO';
                    $testPropertiesCode[] = '     */';
                    $testPropertiesCode[] = '    private $'.$parameter->name.';';

                    if (!$isLast) {
                        $testPropertiesCode[] = '';
                    }
                }
            }
        }

        if (!empty($parameters)) {
            $testPropertiesCode[] = '';
        }

        $testPropertiesCode[] = '    /**';
        $testPropertiesCode[] = '     * @var '.$this->classShortName;
        $testPropertiesCode[] = '     */';
        $testPropertiesCode[] = '    private $'.$this->classCamelCaseName.';';

        return implode("\n", $testPropertiesCode);
    }

    private function generateSetUp() : string
    {
        $classShortName = $this->reflectionClass->getShortName();
        $classCamelCaseName = Inflector::camelize($classShortName);

        $setUpCode = [];
        $setUpCode[] = '    protected function setUp()';
        $setUpCode[] = '    {';

        if ($parameters = $this->getConstructorParameters()) {
            foreach ($parameters as $parameter) {
                if ($parameterClass = $parameter->getClass()) {
                    $setUpCode[] = sprintf('        $this->%s = $this->%s(%s::class);',
                        $parameter->name,
                        $this->getPHPUnitMockMethod(),
                        $parameterClass->getShortName()
                    );
                } else {
                    $setUpCode[] = sprintf("        \$this->%s = ''; // TODO",
                        $parameter->name
                    );
                }
            }

            $setUpCode[] = '';
            $setUpCode[] = sprintf('        $this->%s = new %s(', $classCamelCaseName, $classShortName);

            // arguments for class being tested
            $setUpCodeArguments = [];
            foreach ($parameters as $parameter) {
                $setUpCodeArguments[] = sprintf('            $this->%s', $parameter->name);
            }
            $setUpCode[] = implode(",\n", $setUpCodeArguments);

            $setUpCode[] = '        );';
        } else {
            $setUpCode[] = sprintf('        $this->%s = new %s();', $classCamelCaseName, $classShortName);
        }

        $setUpCode[] = '    }';

        return implode("\n", $setUpCode);
    }

    private function getConstructorParameters() : array
    {
        $constructor = $this->reflectionClass->getConstructor();

        if ($constructor) {
            return $constructor->getParameters();
        }

        return [];
    }

    private function generateTestMethods() : string
    {
        $testMethodsCode = [];

        foreach ($this->reflectionClass->getMethods() as $method) {
            if (!$this->isMethodTestable($method)) {
                continue;
            }

            $testMethodsCode[] = sprintf('    public function test%s()', ucfirst($method->name));
            $testMethodsCode[] = '    {';
            $testMethodsCode[] = $this->generateTestMethodBody($method);
            $testMethodsCode[] = '    }';
            $testMethodsCode[] = '';
        }

        return '    '.trim(implode("\n", $testMethodsCode));
    }

    private function generateTestMethodBody(ReflectionMethod $method) : string
    {
        $parameters = $method->getParameters();

        $testMethodBodyCode = [];

        if (!empty($parameters)) {
            foreach ($parameters as $parameter) {
                if ($parameterClass = $parameter->getClass()) {
                    $testMethodBodyCode[] = sprintf(
                        '        $%s = $this->%s(%s::class);',
                        $parameter->name,
                        $this->getPHPUnitMockMethod(),
                        $parameterClass->getShortName()
                    );
                } else {
                    $testMethodBodyCode[] = sprintf("        \$%s = '';", $parameter->name);
                }
            }

            $testMethodBodyCode[] = '';
            $testMethodBodyCode[] = sprintf('        $this->%s->%s(', $this->classCamelCaseName, $method->name);

            $testMethodParameters = [];
            foreach ($parameters as $parameter) {
                $testMethodParameters[] = sprintf('$%s', $parameter->name);
            }

            $testMethodBodyCode[] = '            '.implode(",\n            ", $testMethodParameters);
            $testMethodBodyCode[] = '        );';
        } else {
            $testMethodBodyCode[] = sprintf('        $this->%s->%s();', $this->classCamelCaseName, $method->name);
        }

        return implode("\n", $testMethodBodyCode);
    }

    private function generateUseStatements() : string
    {
        $dependencies = [];
        $dependencies[] = $this->reflectionClass->name;
        $dependencies[] = TestCase::class;

        if ($parameters = $this->getConstructorParameters()) {
            foreach ($parameters as $parameter) {
                if (!$parameterClass = $parameter->getClass()) {
                    continue;
                }

                $dependencies[] = $parameterClass->getName();
            }
        }

        foreach ($this->reflectionClass->getMethods() as $method) {
            if (!$this->isMethodTestable($method)) {
                continue;
            }

            foreach ($method->getParameters() as $parameter) {
                if (!$parameterClass = $parameter->getClass()) {
                    continue;
                }

                $dependencies[] = $parameterClass->getName();
            }
        }

        sort($dependencies);

        $dependencies = array_unique($dependencies);

        $useStatementsCode = array_map(function($dependency) {
            return sprintf('use %s;', $dependency);
        }, $dependencies);

        return implode("\n", $useStatementsCode);
    }

    private function isMethodTestable(ReflectionMethod $method) : bool
    {
        if ($this->reflectionClass->name !== $method->class) {
            return false;
        }

        return substr($method->name, 0, 2) !== '__' && $method->isPublic();
    }

    /**
     * @return string
     */
    private function getPHPUnitMockMethod()
    {
        foreach (['createMock', 'getMock'] as $method) {
            try {
                new \ReflectionMethod(TestCase::class, $method);
                return $method;
            } catch (\ReflectionException $e) {
            }
        }

        throw new \RuntimeException('Unable to detect PHPUnit version');
    }
}
