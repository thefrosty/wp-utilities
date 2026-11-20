<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Utils;

use DI\ContainerBuilder;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesTrait;
use Psr\Container\ContainerInterface;
use RuntimeException;
use TheFrosty\WpUtilities\Plugin\Container;
use TheFrosty\WpUtilities\Plugin\ContainerAwareTrait;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\Utils\View;
use TheFrosty\WpUtilities\Utils\Viewable;

/**
 * Class ViewableTest
 * @package TheFrosty\WpUtilities\Tests\Utils
 */
#[CoversTrait(Viewable::class)]
#[UsesTrait(ContainerAwareTrait::class)]
#[UsesClass(Container::class)]
#[UsesClass(View::class)]
#[Group('utils')]
class ViewableTest extends TestCase
{
    public function testGetViewRequiresAContainer(): void
    {
        $host = new class {
            use Viewable;

            public function expose(string $id): ?View
            {
                return $this->getView($id);
            }
        };

        $this->expectException(RuntimeException::class);
        $host->expose('view');
    }

    public function testGetViewReturnsAndCachesTheContainerEntry(): void
    {
        $builds = 0;
        $container = new Container();
        $container['view'] = $container->factory(static function () use (&$builds): View {
            $builds++;

            return new View();
        });
        $host = new class($container) {
            use ContainerAwareTrait;
            use Viewable;

            public function __construct(ContainerInterface $container)
            {
                $this->setContainer($container);
            }

            public function expose(string $id): ?View
            {
                return $this->getView($id);
            }
        };

        $first = $host->expose('view');
        $second = $host->expose('view');
        $this->assertInstanceOf(View::class, $first);
        $this->assertSame($first, $second);
        $this->assertSame(1, $builds);

        $missing = new class(new Container()) {
            use ContainerAwareTrait;
            use Viewable;

            public function __construct(ContainerInterface $container)
            {
                $this->setContainer($container);
            }

            public function expose(string $id): ?View
            {
                return $this->getView($id);
            }
        };
        $this->assertNull($missing->expose('missing'));
    }

    public function testGetViewResolvesAPhpDiDefinition(): void
    {
        // The PHP-DI equivalent of a Pimple service provider's `$pimple['wp_utilities.view'] = fn() => new View()`.
        $container = new ContainerBuilder()->addDefinitions([
            'wp_utilities.view' => static fn(): View => new View(),
        ])->build();
        $host = new class($container) {
            use ContainerAwareTrait;
            use Viewable;

            public function __construct(ContainerInterface $container)
            {
                $this->setContainer($container);
            }

            public function expose(string $id): ?View
            {
                return $this->getView($id);
            }
        };

        $view = $host->expose('wp_utilities.view');
        $this->assertInstanceOf(View::class, $view);
        $this->assertSame($view, $container->get('wp_utilities.view'), 'PHP-DI entries are shared by default.');
    }
}
