<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\WpAdmin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use TheFrosty\WpUtilities\Plugin\Container;
use TheFrosty\WpUtilities\Plugin\Init;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Plugin\PluginFactory;
use TheFrosty\WpUtilities\Plugin\TemplateLoader;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\WpAdmin\AddPluginIcons;

/**
 * Class AddPluginIconsTest
 * @package TheFrosty\WpUtilities\Tests\WpAdmin
 */
#[CoversClass(AddPluginIcons::class)]
#[UsesClass(Container::class)]
#[UsesClass(Init::class)]
#[UsesClass(Plugin::class)]
#[UsesClass(PluginFactory::class)]
#[UsesClass(TemplateLoader::class)]
#[Group('wp-admin')]
class AddPluginIconsTest extends TestCase
{
    public function testEmptyIconsDoNotRegisterTheFilter(): void
    {
        remove_all_filters('all_plugins');
        $icons = new AddPluginIcons([]);
        $icons->addHooks();

        $this->assertFalse(has_filter('all_plugins'));
    }

    public function testFilterAddsKnownIconSizes(): void
    {
        $this->plugin = PluginFactory::create('plugin-icons', __FILE__);
        $icons = new AddPluginIcons([
            'svg' => 'https://example.com/icon.svg',
            '1x' => 'https://example.com/icon-128.png',
            'ignored' => 'https://example.com/nope.png',
        ]);
        $icons->setPlugin($this->plugin);
        $icons->addHooks();

        $plugins = apply_filters('all_plugins', [
            $this->plugin->getBasename() => ['Name' => 'Utilities'],
            'other/other.php' => ['Name' => 'Other'],
        ]);

        $this->assertSame('https://example.com/icon.svg', $plugins[$this->plugin->getBasename()]['icons']['svg']);
        $this->assertSame('https://example.com/icon-128.png', $plugins[$this->plugin->getBasename()]['icons']['1x']);
        $this->assertArrayNotHasKey('ignored', $plugins[$this->plugin->getBasename()]['icons']);
        $this->assertArrayNotHasKey('icons', $plugins['other/other.php']);
    }
}
