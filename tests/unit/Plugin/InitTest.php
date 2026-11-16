<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesTrait;
use Symfony\Component\HttpFoundation\Request;
use TheFrosty\WpUtilities\Plugin\Container;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestInterface;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestTrait;
use TheFrosty\WpUtilities\Plugin\Init;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Plugin\PluginAwareInterface;
use TheFrosty\WpUtilities\Plugin\PluginAwareTrait;
use TheFrosty\WpUtilities\Plugin\PluginFactory;
use TheFrosty\WpUtilities\Plugin\TemplateLoader;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class InitTest
 * @package TheFrosty\WpUtilities\Tests\Plugin
 */
#[CoversClass(Init::class)]
#[UsesClass(Container::class)]
#[UsesClass(Plugin::class)]
#[UsesClass(PluginFactory::class)]
#[UsesClass(TemplateLoader::class)]
#[UsesTrait(HttpFoundationRequestTrait::class)]
#[UsesTrait(PluginAwareTrait::class)]
#[Group('plugin')]
class InitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->plugin = PluginFactory::create('init-test', __FILE__);
    }

    protected function tearDown(): void
    {
        $property = new \ReflectionProperty(InitRequestProvider::class, 'request');
        $property->setValue(null, null);
        parent::tearDown();
    }

    public function testRegisterInitializeAndLookup(): void
    {
        $init = new Init();
        $provider = new InitHookProvider();
        $requestProvider = new InitRequestProvider();

        $this->assertSame($this->plugin, $init->register($provider, $this->plugin));
        $init->register($requestProvider, $this->plugin);
        $this->assertCount(2, $init->getWpHooks());
        $this->assertSame($this->plugin, $provider->getPlugin());
        $this->assertInstanceOf(Request::class, $requestProvider->getRequest());

        $init->initialize();
        $init->initialize();
        $this->assertSame(1, $provider->hooks);

        $this->assertSame($provider, $init->getWpHookObject(InitHookProvider::class));
        $this->assertNull($init->getWpHookObject(self::class));
        $this->assertCount(2, iterator_to_array($init->getIterator()));
    }
}

class InitHookProvider implements PluginAwareInterface, WpHooksInterface
{
    use PluginAwareTrait;

    public int $hooks = 0;

    public function addHooks(): void
    {
        $this->hooks++;
    }
}

class InitRequestProvider implements HttpFoundationRequestInterface, WpHooksInterface
{
    use HttpFoundationRequestTrait;

    public function addHooks(): void
    {
    }
}
