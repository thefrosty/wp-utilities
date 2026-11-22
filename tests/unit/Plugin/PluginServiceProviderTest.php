<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use DI\ContainerBuilder;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesTrait;
use Psr\Container\ContainerInterface;
use TheFrosty\WpUtilities\Plugin\AbstractPlugin;
use TheFrosty\WpUtilities\Plugin\Container;
use TheFrosty\WpUtilities\Plugin\ContainerAwareTrait;
use TheFrosty\WpUtilities\Plugin\ContainerWriter;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Plugin\ServiceProviderInterface;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\Utils\View;

/**
 * Class PluginServiceProviderTest
 * @package TheFrosty\WpUtilities\Tests\Plugin
 */
#[CoversClass(Plugin::class)]
#[UsesClass(AbstractPlugin::class)]
#[UsesClass(Container::class)]
#[UsesClass(ContainerWriter::class)]
#[UsesClass(View::class)]
#[UsesTrait(ContainerAwareTrait::class)]
#[Group('plugin')]
class PluginServiceProviderTest extends TestCase
{
    public function testServiceProviderEntriesWorkWithPhpDiAndPimple(): void
    {
        foreach ([new ContainerBuilder()->build(), new Container()] as $container) {
            $plugin = new Plugin()->setSlug('service-provider-test');
            $plugin->setContainer($container);

            $this->assertSame($plugin, $plugin->addServiceProvider($this->provider()));

            $view = $container->get('wp_utilities.view');
            $this->assertInstanceOf(View::class, $view, $container::class);
            $this->assertSame($view, $container->get('wp_utilities.view'), 'Entries are shared.');
            $this->assertSame('views/', $container->get('views.path'));
            $this->assertSame('views/ (container)', $container->get('uses.container'));
        }
    }

    public function testAddServiceProviderRequiresAContainer(): void
    {
        $plugin = new Plugin()->setSlug('service-provider-no-container');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The "service-provider-no-container" plugin has no container.');
        $plugin->addServiceProvider($this->provider());
    }

    private function provider(): ServiceProviderInterface
    {
        return new class implements ServiceProviderInterface {
            public function getDefinitions(): array
            {
                return [
                    'views.path' => 'views/',
                    'wp_utilities.view' => static fn(): View => new View(),
                    'uses.container' => static fn(ContainerInterface $container): string =>
                        $container->get('views.path') . ' (container)',
                ];
            }
        };
    }
}
