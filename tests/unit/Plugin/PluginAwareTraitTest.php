<?php declare(strict_types=1);

namespace TheFrosty\PhpUnit\WpUtilities;

use TheFrosty\WpUtilities\Plugin\Init;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Plugin\PluginAwareTrait;
use TheFrosty\PhpUnit\WpUtilities\Framework\TestCase;

/**
 * Class PluginAwareTraitTest
 *
 * @package TheFrosty\PhpUnit\WpUtilities
 */
class PluginAwareTraitTest extends TestCase
{

    /**
     * Test set_plugin()
     */
    public function test_set_plugin()
    {
        $provider = $this->getMockForTrait(PluginAwareTrait::class);

        try {
            $class = new \ReflectionClass($provider);
            $property = $class->getProperty('plugin');
            $property->setAccessible(true);
        } catch (\ReflectionException $exception) {
            $this->assertInstanceOf(\ReflectionException::class, $exception);

            return;
        }

        $plugin = new Plugin();
        /** PluginAwareTrait @var PluginAwareTrait $provider */
        $provider->setPlugin($plugin);

        $this->assertSame($plugin, $property->getValue($provider));
    }
}
