<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use TheFrosty\WpUtilities\Plugin\AbstractPlugin;
use TheFrosty\WpUtilities\Plugin\PluginInterface;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class AbstractPluginTest
 * @package TheFrosty\WpUtilities\Test\Plugin
 */
class AbstractPluginTest extends TestCase
{

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
        /** AbstractPlugin @var AbstractPlugin $plugin */
        $plugin = $this->getMockProviderForAbstractClass(AbstractPlugin::class);
        $plugin->setBasename($basename);
        $this->assertInstanceOf(\get_class($plugin), $plugin);
        $this->assertSame($basename, $plugin->getBasename());
    }

    /**
     * Test getDirectory().
     */
    public function testGetDirectory(): void
    {
        /** AbstractPlugin @var AbstractPlugin $plugin */
        $plugin = $this->getMockProviderForAbstractClass(AbstractPlugin::class);
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
        /** AbstractPlugin @var AbstractPlugin $plugin */
        $plugin = $this->getMockProviderForAbstractClass(AbstractPlugin::class);
        $plugin->setFile($file);
        $this->assertInstanceOf(get_class($plugin), $plugin);
        $this->assertSame($file, $plugin->getFile());
    }

    /**
     * Test getPath().
     */
    public function testGetPath(): void
    {
        /** AbstractPlugin @var AbstractPlugin $plugin */
        $plugin = $this->getMockProviderForAbstractClass(AbstractPlugin::class);
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
        /** AbstractPlugin @var AbstractPlugin $plugin */
        $plugin = $this->getMockProviderForAbstractClass(AbstractPlugin::class);
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
        /** AbstractPlugin @var AbstractPlugin $plugin */
        $plugin = $this->getMockProviderForAbstractClass(AbstractPlugin::class);
        $plugin->setUrl($url);
        $this->assertInstanceOf(get_class($plugin), $plugin);
        $this->assertSame($url . '/', $plugin->getUrl());

        // Test with trailing slash.
        $url = 'https://example.com/wp-content/plugins/plugin/';
        $plugin->setUrl($url);
        $this->assertSame($url, $plugin->getUrl());
    }
}
