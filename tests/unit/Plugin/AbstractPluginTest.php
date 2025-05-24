<?php
declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use ReflectionObject;
use TheFrosty\WpUtilities\Plugin\AbstractPlugin;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Plugin\PluginInterface;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class AbstractPluginTest
 * @package TheFrosty\WpUtilities\Test\Plugin
 */
class AbstractPluginTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        $this->plugin = new Plugin();
        $this->reflection = new ReflectionObject($this->plugin);
    }

    /**
     * Test AbstractPlugin.
     */
    public function testImplementsPluginInterface(): void
    {
        $plugin = $this->getMockProviderForAbstractClass(AbstractPlugin::class);
        $this->assertInstanceOf(PluginInterface::class, $plugin);
    }

    /**
     * Test getBasename().
     */
    public function testGetBasename(): void
    {
        $basename = 'plugin/plugin.php';
        $plugin = $this->plugin;
        $plugin->setBasename($basename);
        $this->assertInstanceOf(\get_class($plugin), $plugin);
        $this->assertSame($basename, $plugin->getBasename());
    }

    /**
     * Test getDirectory().
     */
    public function testGetDirectory(): void
    {
        $plugin = $this->plugin;
        $plugin->setDirectory('/wp-content/plugins');
        $this->assertInstanceOf(get_class($plugin), $plugin);
        $this->assertSame('/wp-content/plugins/', $plugin->getDirectory());

        // Test with trailing slash.
        $plugin->setDirectory('/wp-content/plugins/');
        $this->assertSame('/wp-content/plugins/', $plugin->getDirectory());
    }

    /**
     * Test getFile().
     */
    public function testGetFile(): void
    {
        $file = '/wp-content/plugins/plugin/plugin.php';
        $plugin = $this->plugin;
        $plugin->setFile($file);
        $this->assertInstanceOf(get_class($plugin), $plugin);
        $this->assertSame($file, $plugin->getFile());
    }

    /**
     * Test getPath().
     */
    public function testGetPath(): void
    {
        $plugin = $this->plugin;
        $plugin->setDirectory('/wp-content/plugins');
        $this->assertInstanceOf(get_class($plugin), $plugin);
        $this->assertSame('/wp-content/plugins/name', $plugin->getPath('name'));
        $this->assertSame('/wp-content/plugins/name', $plugin->getPath('/name'));
    }

    /**
     * Test getSlug().
     */
    public function testGetSlug(): void
    {
        $slug = 'crate';
        $plugin = $this->plugin;
        $plugin->setSlug($slug);
        $this->assertInstanceOf(get_class($plugin), $plugin);
        $this->assertSame($slug, $plugin->getSlug());
    }

    /**
     * Test getUrl().
     */
    public function testGetUrl(): void
    {
        $url = 'https://example.com/wp-content/plugins/plugin';
        $plugin = $this->plugin;
        $plugin->setUrl($url);
        $this->assertInstanceOf(get_class($plugin), $plugin);
        $this->assertSame($url . '/', $plugin->getUrl());

        // Test with trailing slash.
        $url = 'https://example.com/wp-content/plugins/plugin/';
        $plugin->setUrl($url);
        $this->assertSame($url, $plugin->getUrl());
    }
}
