<?php declare(strict_types=1);

namespace TheFrosty\PhpUnit\WpUtilities;

use TheFrosty\WpUtilities\Plugin\WpHooksInterface;
use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\PhpUnit\WpUtilities\Framework\TestCase;

/**
 * Class PluginRegisterHooksTest
 *
 * @package TheFrosty\PhpUnit\WpUtilities
 */
class PluginRegisterHooksTest extends TestCase
{

    /**
     * Test register_hooks()
     */
    public function test_register_hooks()
    {
        $provider = $this->get_mock_provider();

        try {
            $class = new \ReflectionClass($provider);
            $property = $class->getProperty('plugin');
            $property->setAccessible(true);
        } catch (\ReflectionException $exception) {
            $this->assertInstanceOf(\ReflectionException::class, $exception);

            return;
        }

        $provider->expects($this->exactly(1))->method('addHooks');

        $plugin = new Plugin();
        /** WpHooksInterface @var WpHooksInterface $provider */
        $plugin->add($provider);

        $this->assertSame($plugin, $property->getValue($provider));
    }

    /**
     * Get's a mocked `AbstractHookProvider` object.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function get_mock_provider()
    {
        return $this->getMockBuilder(AbstractHookProvider::class)->getMockForAbstractClass();
    }
}
