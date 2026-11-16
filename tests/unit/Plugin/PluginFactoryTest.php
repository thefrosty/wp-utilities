<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use ReflectionProperty;
use TheFrosty\WpUtilities\Plugin\Container;
use TheFrosty\WpUtilities\Plugin\Init;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Plugin\PluginFactory;
use TheFrosty\WpUtilities\Plugin\TemplateLoader;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class PluginFactoryTest
 * @package TheFrosty\WpUtilities\Tests\Plugin
 */
#[CoversClass(PluginFactory::class)]
#[UsesClass(Container::class)]
#[UsesClass(Init::class)]
#[UsesClass(Plugin::class)]
#[UsesClass(TemplateLoader::class)]
#[Group('plugin')]
class PluginFactoryTest extends TestCase
{
    /** @var array<string, Plugin> */
    private array $previous = [];

    protected function setUp(): void
    {
        parent::setUp();
        $property = new ReflectionProperty(PluginFactory::class, 'instances');
        $this->previous = $property->getValue();
    }

    protected function tearDown(): void
    {
        $property = new ReflectionProperty(PluginFactory::class, 'instances');
        $property->setValue(null, $this->previous);
        parent::tearDown();
    }

    public function testCreateAndGetInstanceReuseTheSamePlugin(): void
    {
        $plugin = PluginFactory::create('factory-test', __FILE__);

        $this->assertSame('factory-test', $plugin->getSlug());
        $this->assertSame(__FILE__, $plugin->getFile());
        $this->assertInstanceOf(Init::class, $plugin->getInit());
        $this->assertInstanceOf(TemplateLoader::class, $plugin->getTemplateLoader());
        $this->assertInstanceOf(Container::class, $plugin->getContainer());
        $this->assertSame($plugin, $plugin->getContainer()->get('factory-test'));
        $this->assertSame($plugin, PluginFactory::getInstance('factory-test'));
    }

    public function testCreateWithoutAFilenameUsesTheCallingFile(): void
    {
        $plugin = PluginFactory::create('factory-backtrace');

        $this->assertSame(__FILE__, $plugin->getFile());
        $this->assertSame($plugin, PluginFactory::create('factory-backtrace', '/tmp/ignored.php'));
    }
}
