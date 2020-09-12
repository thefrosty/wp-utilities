<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Plugin\PluginInterface;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class PluginTest
 *
 * @package TheFrosty\WpUtilities\Test\Plugin
 */
class PluginTest extends TestCase
{

    /**
     * Test PluginInterface.
     */
    public function test_implements_plugin_interface()
    {
        $plugin = new Plugin();
        $this->assertInstanceOf(PluginInterface::class, $plugin);
    }
}
