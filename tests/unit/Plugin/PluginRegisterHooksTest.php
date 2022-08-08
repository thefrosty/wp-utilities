<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use ReflectionClass;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;
use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class PluginRegisterHooksTest
 * @package TheFrosty\WpUtilities\Test\Plugin
 */
class PluginRegisterHooksTest extends TestCase
{

    /**
     * Test AbstractHookProvider
     */
    public function testRegisterHooks(): void
    {
        $provider = $this->getMockProviderForAbstractClass(AbstractHookProvider::class);

        try {
            $class = new ReflectionClass($provider);
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
}
