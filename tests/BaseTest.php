<?php

namespace PHPChunkit\Test;

use \PHPUnit\Framework\TestCase;

abstract class BaseTest extends TestCase
{
    protected function buildPartialMock($className, array $mockedMethods, array $constructorArgs = null)
    {
        $builder = $this->getMockBuilder($className)
            ->setMethods($mockedMethods);

        if ($constructorArgs) {
            $builder->setConstructorArgs($constructorArgs);
        } else {
            $builder->disableOriginalConstructor();
        }

        return $builder->getMock();
    }

    protected function getRootDir() : string
    {
        return realpath(__DIR__.'/../');
    }

    protected function getTestsDirectory() : string
    {
        return realpath(__DIR__.'/../tests');
    }

    /**
     * PHPUnit 5.x compat, see createMock vs getMock
     *
     * @param string $originalClassName
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createMock($originalClassName)
    {
        $builder = $this->getMockBuilder($originalClassName)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning();

        if (method_exists($builder, 'disallowMockingUnknownTypes')) {
            $builder->disallowMockingUnknownTypes();
        }

        return $builder->getMock();
    }
}
