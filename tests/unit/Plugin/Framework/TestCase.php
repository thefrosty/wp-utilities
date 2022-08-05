<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin\Framework;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use ReflectionObject;
use TheFrosty\WpUtilities\Plugin\Container;
use TheFrosty\WpUtilities\Plugin\Plugin;
use WP_REST_Server;
use function array_diff;
use function array_merge;
use function did_action;
use function do_action;
use function get_class;
use function rest_get_server;

/**
 * Class TestCase
 * @package TheFrosty\WpUtilities\Tests\Plugin\Framework
 */
class TestCase extends PhpUnitTestCase
{

    public const METHOD_ADD_ACTION = 'addAction';
    public const METHOD_ADD_FILTER = 'addFilter';

    protected Container $container;
    protected Plugin $plugin;
    protected ReflectionObject $reflection;

    /**
     * Get the constants for the current class, excluding the inherited class constants.
     * @param ReflectionObject $reflection
     * @return array
     */
    protected function getClassConstants(ReflectionObject $reflection): array
    {
        return array_diff($reflection->getConstants(), $reflection->getParentClass()->getConstants());
    }

    /**
     * Mock `$className`.
     * @param string $className
     * @param array|null $constructorArgs
     * @param array|null $setMethods
     * @return MockObject
     */
    protected function getMockProvider(
        string $className,
        ?array $constructorArgs = null,
        ?array $setMethods = null
    ): MockObject {
        $mockBuilder = $this->getMockBuilder($className);
        if ($constructorArgs) {
            $mockBuilder->setConstructorArgs($constructorArgs);
        }
        $methods = [self::METHOD_ADD_FILTER];
        if ($setMethods) {
            $methods = array_merge($methods, $setMethods);
        }

        return $mockBuilder->onlyMethods($methods)->getMock();
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
     * Gets an instance of the ReflectionObject.
     * @param object $argument
     * @return ReflectionObject
     */
    protected function getReflection(object $argument): ReflectionObject
    {
        static $reflector;

        if (
            !isset($reflector[get_class($argument)]) ||
            !($reflector[get_class($argument)] instanceof ReflectionObject)
        ) {
            $reflector[get_class($argument)] = new ReflectionObject($argument);
        }

        return $reflector[get_class($argument)];
    }

    /**
     * Return an instance of the WP_REST_Server and initiate the tags action hook.
     * @return WP_REST_Server
     */
    protected function getRestApiServer(): WP_REST_Server
    {
        $wp_rest_server = rest_get_server();

        if (!did_action('rest_api_init')) {
            do_action('rest_api_init', $wp_rest_server);
        }

        return $wp_rest_server;
    }
}
