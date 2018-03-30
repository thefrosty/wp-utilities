<?php declare(strict_types=1);

namespace TheFrosty\PhpUnit\WpUtilities;

use TheFrosty\WpUtilities\Plugin\AbstractPlugin;
use TheFrosty\WpUtilities\Plugin\PluginInterface;
use TheFrosty\PhpUnit\WpUtilities\Framework\TestCase;

/**
 * Class AbstractPluginTest
 * @package TheFrosty\PhpUnit\WpUtilities
 */
class AbstractPluginTest extends TestCase
{

    /**
     * Test.
     */
    public function test_implements_plugin_interface()
    {
        $plugin = $this->get_mock_plugin();
        $this->assertInstanceOf(PluginInterface::class, $plugin);
    }

    /**
     * Test.
     */
    public function test_basename_accessor_and_mutator()
    {
        $basename = 'plugin/plugin.php';
        /** AbstractPlugin @var AbstractPlugin $plugin */
        $plugin = $this->get_mock_plugin();
        $plugin->setBasename($basename);
        $this->assertInstanceOf(get_class($plugin), $plugin);
        $this->assertSame($basename, $plugin->getBasename());
    }

    /**
     * Test.
     */
    public function test_directory_accessor_and_mutator()
    {
        /** AbstractPlugin @var AbstractPlugin $plugin */
        $plugin = $this->get_mock_plugin();
        $plugin->setDirectory('/wp-content/plugins');
        $this->assertInstanceOf(get_class($plugin), $plugin);
        $this->assertSame('/wp-content/plugins/', $plugin->getDirectory());

        // Test with trailing slash.
        $plugin->setDirectory('/wp-content/plugins/');
        $this->assertSame('/wp-content/plugins/', $plugin->getDirectory());
    }

    /**
     * Test.
     */
    public function test_file_accessor_and_mutator()
    {
        $file = '/wp-content/plugins/plugin/plugin.php';
        /** AbstractPlugin @var AbstractPlugin $plugin */
        $plugin = $this->get_mock_plugin();
        $plugin->setFile($file);
        $this->assertInstanceOf(get_class($plugin), $plugin);
        $this->assertSame($file, $plugin->getFile());
    }

    /**
     * Test.
     */
    public function test_path_accessor()
    {
        /** AbstractPlugin @var AbstractPlugin $plugin */
        $plugin = $this->get_mock_plugin();
        $plugin->setDirectory('/wp-content/plugins');
        $this->assertInstanceOf(get_class($plugin), $plugin);
        $this->assertSame('/wp-content/plugins/name', $plugin->getPath('name'));
        $this->assertSame('/wp-content/plugins/name', $plugin->getPath('/name'));
    }

    /**
     * Test.
     */
    public function test_slug_accessor_and_mutator()
    {
        $slug = 'crate';
        /** AbstractPlugin @var AbstractPlugin $plugin */
        $plugin = $this->get_mock_plugin();
        $plugin->setSlug($slug);
        $this->assertInstanceOf(get_class($plugin), $plugin);
        $this->assertSame($slug, $plugin->getSlug());
    }

    /**
     * Test.
     */
    public function test_url_accessor_and_mutator()
    {
        $url = 'https://example.com/wp-content/plugins/plugin';
        /** AbstractPlugin @var AbstractPlugin $plugin */
        $plugin = $this->get_mock_plugin();
        $plugin->setUrl($url);
        $this->assertInstanceOf(get_class($plugin), $plugin);
        $this->assertSame($url . '/', $plugin->getUrl());

        // Test with trailing slash.
        $url = 'https://example.com/wp-content/plugins/plugin/';
        $plugin->setUrl($url);
        $this->assertSame($url, $plugin->getUrl());
    }

    /**
     * Get mocked `AbstractPlugin` object.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function get_mock_plugin()
    {
        return $this->getMockBuilder(AbstractPlugin::class)->getMockForAbstractClass();
    }
}
