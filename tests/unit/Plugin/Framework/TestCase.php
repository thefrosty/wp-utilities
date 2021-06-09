<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin\Framework;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;

/**
 * Class TestCase
 *
 * @package TheFrosty\WpUtilities\Tests\Plugin\Framework
 */
class TestCase extends PhpUnitTestCase
{

    protected \ReflectionObject $reflection;

    /**
     * Get the class constants excluding parents.
     * @return array
     */
    protected function getClassConstants(): array
    {
        return \array_flip(\array_diff(
            $this->reflection->getConstants(),
            $this->reflection->getParentClass()->getConstants()
        ));
    }

    /**
     * Return a mocked `$className` object.
     * @param string $className
     * @return MockObject
     */
    protected function getMockProviderForAbstractClass(string $className): MockObject
    {
        return $this->getMockBuilder($className)->getMockForAbstractClass();
    }

    /**
     * Get's a mocked `$className` object.
     * @param string $className
     * @return MockObject
     */
    protected function getMockProvider(string $className): MockObject
    {
        return $this->getMockBuilder($className)->onlyMethods(['addFilter'])->getMock();
    }
}
