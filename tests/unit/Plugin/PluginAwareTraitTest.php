<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use ReflectionClass;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Plugin\PluginAwareTrait;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class PluginAwareTraitTest
 *
 * @package TheFrosty\WpUtilities\Test\Plugin
 */
class PluginAwareTraitTest extends TestCase
{

    /**
     * Test set_plugin()
     */
    public function testSetPlugin(): void
    {
        $provider = $this->getMockForTrait(PluginAwareTrait::class);

        $class = new ReflectionClass($provider);
        $property = $class->getProperty('plugin');
        $property->setAccessible(true);

        $plugin = new Plugin();
        /** PluginAwareTrait @var PluginAwareTrait $provider */
        $provider->setPlugin($plugin);

        $this->assertSame($plugin, $property->getValue($provider));
    }
}
