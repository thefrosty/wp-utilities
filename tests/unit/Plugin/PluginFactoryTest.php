<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use DI\ContainerBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use Psr\Container\ContainerInterface;
use ReflectionProperty;
use RuntimeException;
use TheFrosty\WpUtilities\Plugin\Container;
use TheFrosty\WpUtilities\Plugin\Init;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Plugin\PluginFactory;
use TheFrosty\WpUtilities\Plugin\TemplateLoader;
use TheFrosty\WpUtilities\Tests\PlainObject;
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

    public function testCreateAcceptsAnyPsr11Container(): void
    {
        $container = new class implements ContainerInterface {
            public function get(string $id): mixed
            {
                throw new RuntimeException($id);
            }

            public function has(string $id): bool
            {
                return false;
            }
        };

        $plugin = PluginFactory::create('factory-custom-container', __FILE__, $container);

        // A read-only PSR-11 container is attached as-is (there is no way to register the plugin in it).
        $this->assertSame($container, $plugin->getContainer());
    }

    public function testCreateRegistersThePluginInAPhpDiContainer(): void
    {
        $container = new ContainerBuilder()->useAutowiring(true)->build();

        $plugin = PluginFactory::create('factory-php-di', __FILE__, $container);

        $this->assertSame($container, $plugin->getContainer());
        $this->assertSame($plugin, $container->get('factory-php-di'));
        // Autowiring: classes resolve without a definition.
        $this->assertInstanceOf(PlainObject::class, $plugin->getContainer()->get(PlainObject::class));
    }

    public function testPimpleRemainsTheDefaultWhenInstalled(): void
    {
        $plugin = PluginFactory::create('factory-default', __FILE__);

        $this->assertInstanceOf(Container::class, $plugin->getContainer());
        $this->assertSame($plugin, $plugin->getContainer()['factory-default']);
    }
}
