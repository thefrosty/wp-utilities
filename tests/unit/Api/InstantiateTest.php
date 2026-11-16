<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Api;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesTrait;
use TheFrosty\WpUtilities\Api\Instantiate;
use TheFrosty\WpUtilities\Plugin\Container;
use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Plugin\Init;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Plugin\PluginAwareTrait;
use TheFrosty\WpUtilities\Plugin\PluginFactory;
use TheFrosty\WpUtilities\Plugin\TemplateLoader;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;
use TheFrosty\WpUtilities\Tests\HookObject;
use TheFrosty\WpUtilities\Tests\PlainObject;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class InstantiateTest
 * @package TheFrosty\WpUtilities\Tests\Api
 */
#[CoversTrait(Instantiate::class)]
#[UsesTrait(HooksTrait::class)]
#[UsesTrait(PluginAwareTrait::class)]
#[UsesClass(Container::class)]
#[UsesClass(Init::class)]
#[UsesClass(Plugin::class)]
#[UsesClass(PluginFactory::class)]
#[UsesClass(TemplateLoader::class)]
#[Group('api')]
class InstantiateTest extends TestCase
{
    private InstantiateHost $host;

    protected function setUp(): void
    {
        parent::setUp();
        $this->plugin = PluginFactory::create('instantiate-test', __FILE__);
        $this->host = new InstantiateHost();
        $this->host->setPlugin($this->plugin);
    }

    public function testBuildClassName(): void
    {
        $this->assertSame(
            'TheFrosty\\WpUtilities\\Tests\\Api\\PlainObject',
            $this->host->exposeBuildClass('TheFrosty\\WpUtilities\\Tests\\Api', 'plain_object')
        );
        $this->assertSame(
            'TheFrosty\\WpUtilities\\Tests\\Api\\HookObject',
            $this->host->exposeBuildClass('TheFrosty\\WpUtilities\\Tests\\Api', 'hook-object')
        );
    }

    public function testInstantiateClassesSkipsMissingClasses(): void
    {
        $this->host->exposeInstantiate('TheFrosty\\WpUtilities\\Tests\\Api', 'missing_thing');

        $this->assertSame([], $this->host->exposeOrder($this->host::class));
    }

    public function testInstantiateClassesStoresObjectsAndRegistersHookProviders(): void
    {
        $this->host->exposeInstantiate('TheFrosty\\WpUtilities\\Tests\\Api', 'plain_object');
        $this->host->exposeInstantiate('TheFrosty\\WpUtilities\\Tests\\Api', 'hook-object');

        $order = $this->host->exposeOrder($this->host::class);
        $this->assertCount(2, $order);
        $this->assertInstanceOf(PlainObject::class, $order[0]);
        $this->assertInstanceOf(HookObject::class, $order[1]);
        $this->assertNotNull($this->plugin->getInit()->getWpHookObject(HookObject::class));
        $this->assertNotFalse(has_action('after_setup_theme'));
    }
}

class InstantiateHost
{
    use Instantiate;
    use HooksTrait;
    use PluginAwareTrait;

    public function exposeBuildClass(string $namespace, string $className): string
    {
        return $this->buildClass($namespace, $className);
    }

    public function exposeInstantiate(string $namespace, string $className): void
    {
        $this->instantiateClasses($namespace, $className);
    }

    public function exposeOrder(string $calledClass): array
    {
        return $this->getInstantiatedOrder($calledClass);
    }
}
