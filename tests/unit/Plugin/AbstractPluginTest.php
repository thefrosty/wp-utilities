<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use ReflectionObject;
use TheFrosty\WpUtilities\Plugin\AbstractPlugin;
use TheFrosty\WpUtilities\Plugin\Init;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Plugin\PluginInterface;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use function do_action;
use function get_class;

/**
 * Class AbstractPluginTest
 * @package TheFrosty\WpUtilities\Test\Plugin
 */
#[CoversClass(AbstractPlugin::class)]
#[UsesClass(Init::class)]
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
        $this->plugin->setBasename($basename);
        $this->assertInstanceOf(get_class($this->plugin), $this->plugin);
        $this->assertSame($basename, $this->plugin->getBasename());
    }

    /**
     * Test getDirectory().
     */
    public function testGetDirectory(): void
    {
        $this->plugin->setDirectory('/wp-content/plugins');
        $this->assertInstanceOf(get_class($this->plugin), $this->plugin);
        $this->assertSame('/wp-content/plugins/', $this->plugin->getDirectory());

        // Test with trailing slash.
        $this->plugin->setDirectory('/wp-content/plugins/');
        $this->assertSame('/wp-content/plugins/', $this->plugin->getDirectory());
    }

    /**
     * Test getFile().
     */
    public function testGetFile(): void
    {
        $file = '/wp-content/plugins/plugin/plugin.php';
        $this->plugin->setFile($file);
        $this->assertInstanceOf(get_class($this->plugin), $this->plugin);
        $this->assertSame($file, $this->plugin->getFile());
    }

    /**
     * Test getFile().
     */
    public function testGetFileTime(): void
    {
        $this->assertNull($this->plugin->getFileTime());
    }

    /**
     * Test getPath().
     */
    public function testGetPath(): void
    {
        $this->plugin->setDirectory('/wp-content/plugins');
        $this->assertInstanceOf(get_class($this->plugin), $this->plugin);
        $this->assertSame('/wp-content/plugins/name', $this->plugin->getPath('name'));
        $this->assertSame('/wp-content/plugins/name', $this->plugin->getPath('/name'));
    }

    /**
     * Test getSlug().
     */
    public function testGetSlug(): void
    {
        $slug = 'crate';
        $this->plugin->setSlug($slug);
        $this->assertInstanceOf(get_class($this->plugin), $this->plugin);
        $this->assertSame($slug, $this->plugin->getSlug());
    }

    /**
     * Test getUrl().
     */
    public function testGetUrl(): void
    {
        $url = 'https://example.com/wp-content/plugins/plugin';
        $this->plugin->setUrl($url);
        $this->assertInstanceOf(get_class($this->plugin), $this->plugin);
        $this->assertSame($url . '/', $this->plugin->getUrl());

        // Test with trailing slash.
        $url = 'https://example.com/wp-content/plugins/plugin/';
        $this->plugin->setUrl($url);
        $this->assertSame($url, $this->plugin->getUrl());
    }

    public function testAdd(): void
    {
        $class = new class implements WpHooksInterface {
            public function addHooks(): void
            {
            }
        };

        $this->plugin->setInit(new Init());
        $this->assertEmpty($this->plugin->getInit()->getWpHooks());
        $instance = $this->plugin->add($class);
        $this->assertInstanceOf(PluginInterface::class, $instance);
        $this->assertNotEmpty($this->plugin->getInit()->getWpHooks());
    }

    public function testAddIfCondition(): void
    {
        $class = new class implements WpHooksInterface {
            public function addHooks(): void
            {
            }
        };

        $this->plugin->setInit(new Init());
        $this->assertEmpty($this->plugin->getInit()->getWpHooks());
        $this->plugin->addIfCondition($class::class, false);
        $this->assertEmpty($this->plugin->getInit()->getWpHooks());
        $this->plugin->addIfCondition($class::class, true);
        $this->assertNotEmpty($this->plugin->getInit()->getWpHooks());
    }

    public function testAddIfConditionDeferred(): void
    {
        $class = new class implements WpHooksInterface {
            public function addHooks(): void
            {
            }
        };

        $this->plugin->setInit(new Init());
        $this->assertEmpty($this->plugin->getInit()->getWpHooks());
        $this->plugin->addIfConditionDeferred($class::class, false, 'init');
        do_action('init');
        $this->assertEmpty($this->plugin->getInit()->getWpHooks());
        $this->plugin->addIfConditionDeferred($class::class, true, 'init');
        do_action('init');
        $this->assertNotEmpty($this->plugin->getInit()->getWpHooks());
    }

    public function testAddOnCondition(): void
    {
        $class = new class implements WpHooksInterface {
            public function addHooks(): void
            {
            }
        };

        $this->plugin->setInit(new Init());
        $this->assertEmpty($this->plugin->getInit()->getWpHooks());
        $this->plugin->addOnCondition($class::class, static fn(): bool => false);
        do_action('init');
        $this->assertEmpty($this->plugin->getInit()->getWpHooks());
        $this->plugin->addOnCondition($class::class, static fn(): bool => true);
        do_action('init');
        $this->assertNotEmpty($this->plugin->getInit()->getWpHooks());
    }

    public function testAddOnConditionDeferred(): void
    {
        $class = new class implements WpHooksInterface {
            public function addHooks(): void
            {
            }
        };

        $this->plugin->setInit(new Init());
        $this->assertEmpty($this->plugin->getInit()->getWpHooks());
        $this->plugin->addOnConditionDeferred($class::class, '__return_false');
        do_action('plugins_loaded');
        do_action('init');
        $this->assertEmpty($this->plugin->getInit()->getWpHooks());
        $this->plugin->addOnConditionDeferred($class::class, '__return_true');
        do_action('plugins_loaded');
        do_action('init');
        $this->assertNotEmpty($this->plugin->getInit()->getWpHooks());
    }

    public function testAddOnHook(): void
    {
        $class = new class implements WpHooksInterface {
            public function addHooks(): void
            {
            }
        };

        $this->plugin->setInit(new Init());
        $this->assertEmpty($this->plugin->getInit()->getWpHooks());
        $this->plugin->addOnHook($class::class);
        $this->assertEmpty($this->plugin->getInit()->getWpHooks());
        $this->plugin->addOnHook($class::class);
        do_action('init');
        $this->assertNotEmpty($this->plugin->getInit()->getWpHooks());
        $this->plugin->addOnHook($class::class, admin_only: true);
        do_action('admin_init');
        $this->assertCount(2, $this->plugin->getInit()->getWpHooks());
    }

    public function testAddOnHookDeferred(): void
    {
        $class = new class implements WpHooksInterface {
            public function addHooks(): void
            {
            }
        };

        $this->plugin->setInit(new Init());
        $this->assertEmpty($this->plugin->getInit()->getWpHooks());
        $this->plugin->addOnHookDeferred($class::class);
        do_action('init');
        $this->assertEmpty($this->plugin->getInit()->getWpHooks());
        $this->plugin->addOnHookDeferred($class::class);
        do_action('init');
        $this->assertNotEmpty($this->plugin->getInit()->getWpHooks());
    }

    public function testInitialize(): void
    {
        $class = new class implements WpHooksInterface {
            public function addHooks(): void
            {
            }
        };
        $this->plugin->setInit(new Init());
        $this->plugin->add($class);
        $reflection = new ReflectionObject($this->plugin->getInit());
        $this->assertEmpty($reflection->getProperty('initiated')->getValue($this->plugin->getInit()));
        $this->plugin->initialize();
        $this->assertNotEmpty($reflection->getProperty('initiated')->getValue($this->plugin->getInit()));
    }

    public function testGetWpHook(): void
    {
        $class = new class implements WpHooksInterface {
            public function addHooks(): void
            {
            }
        };
        $this->plugin->setInit(new Init());
        $getWpHook = $this->reflection->getMethod('getWpHook');
        $this->assertInstanceOf(WpHooksInterface::class, $getWpHook->invoke($this->plugin, $class::class));

        $class = new class {
        };
        $this->expectException(InvalidArgumentException::class);
        $getWpHook->invoke($this->plugin, $class::class);
    }
}
