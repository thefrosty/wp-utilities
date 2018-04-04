<?php declare(strict_types=1);

namespace TheFrosty\PhpUnit\WpUtilities;

use TheFrosty\WpUtilities\Plugin\Init;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Plugin\PluginInterface;
use TheFrosty\PhpUnit\WpUtilities\Framework\TestCase;

/**
 * Class PluginTest
 *
 * @package TheFrosty\PhpUnit\WpUtilities
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
