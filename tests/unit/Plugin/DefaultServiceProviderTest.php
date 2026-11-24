<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use DI\ContainerBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesTrait;
use Psr\Container\ContainerInterface;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use TheFrosty\WpUtilities\Plugin\AbstractPlugin;
use TheFrosty\WpUtilities\Plugin\Container;
use TheFrosty\WpUtilities\Plugin\ContainerAwareTrait;
use TheFrosty\WpUtilities\Plugin\ContainerWriter;
use TheFrosty\WpUtilities\Plugin\DefaultServiceProvider;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\Utils\View;

use function file_put_contents;
use function mkdir;
use function sys_get_temp_dir;
use function uniqid;

/**
 * Class DefaultServiceProviderTest
 * @package TheFrosty\WpUtilities\Tests\Plugin
 */
#[CoversClass(DefaultServiceProvider::class)]
#[UsesClass(AbstractPlugin::class)]
#[UsesClass(Container::class)]
#[UsesClass(ContainerWriter::class)]
#[UsesClass(Plugin::class)]
#[UsesClass(View::class)]
#[UsesTrait(ContainerAwareTrait::class)]
#[Group('plugin')]
class DefaultServiceProviderTest extends TestCase
{
    private const string QUERY_VAR = 'wpu_default_provider';

    /**
     * @return array<string, array{ContainerInterface}>
     */
    public static function containers(): array
    {
        return [
            'PHP-DI' => [new ContainerBuilder()->build()],
            'Pimple' => [new Container()],
        ];
    }

    protected function tearDown(): void
    {
        unset($_GET[self::QUERY_VAR]);
        parent::tearDown();
    }

    #[DataProvider('containers')]
    public function testRegistersTheCurrentRequest(ContainerInterface $container): void
    {
        $_GET[self::QUERY_VAR] = 'yes';
        $this->plugin($container)->addServiceProvider(new DefaultServiceProvider());

        $request = $container->get(DefaultServiceProvider::REQUEST);
        $this->assertInstanceOf(Request::class, $request);
        $this->assertSame('yes', $request->query->get(self::QUERY_VAR), 'Created from the globals.');
        $this->assertSame($request, $container->get(DefaultServiceProvider::REQUEST), 'Shared.');
        $this->assertSame($request, $container->get(Request::class), 'Aliased by class name.');
    }

    #[DataProvider('containers')]
    public function testRegistersASharedView(ContainerInterface $container): void
    {
        $this->plugin($container)->addServiceProvider(new DefaultServiceProvider());

        $view = $container->get(DefaultServiceProvider::VIEW);
        $this->assertInstanceOf(View::class, $view);
        $this->assertSame($view, $container->get(DefaultServiceProvider::VIEW));
    }

    #[DataProvider('containers')]
    public function testCanBeExtendedWithEntriesAndViewPaths(ContainerInterface $container): void
    {
        $directory = sys_get_temp_dir() . '/' . uniqid('wpu-default-provider-', true);
        mkdir($directory);
        file_put_contents($directory . '/greeting.php', '<?php echo "Hello " . $name;');

        $provider = new class ($directory) extends DefaultServiceProvider {
            public const string SERVICE = 'my.service';

            public function __construct(private readonly string $directory)
            {
            }

            public function getDefinitions(): array
            {
                return [...parent::getDefinitions(), self::SERVICE => static fn(): stdClass => new stdClass()];
            }

            protected function getViewPaths(): array
            {
                return [$this->directory];
            }
        };
        $this->plugin($container)->addServiceProvider($provider);

        $this->assertInstanceOf(stdClass::class, $container->get($provider::SERVICE));
        $this->assertInstanceOf(Request::class, $container->get(DefaultServiceProvider::REQUEST));
        $this->assertSame('Hello Austin', $container->get(DefaultServiceProvider::VIEW)->retrieve(
            'greeting',
            ['name' => 'Austin']
        ));
    }

    public function testPhpDiAutowiringInjectsTheCurrentRequest(): void
    {
        $_GET[self::QUERY_VAR] = 'autowired';
        $container = new ContainerBuilder()->useAutowiring(true)->build();
        $this->plugin($container)->addServiceProvider(new DefaultServiceProvider());

        $service = $container->get(RequestAwareService::class);

        $this->assertSame($container->get(DefaultServiceProvider::REQUEST), $service->request);
        $this->assertSame('autowired', $service->request->query->get(self::QUERY_VAR));
    }

    private function plugin(ContainerInterface $container): Plugin
    {
        $plugin = new Plugin()->setSlug('default-service-provider');
        $plugin->setContainer($container);

        return $plugin;
    }
}
