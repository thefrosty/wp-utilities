# Plugin

## Adding a new hook provider object

Initiate the factory like so:

```php
use TheFrosty\WpUtilities\Plugin\PluginFactory;
(PluginFactory::create('plugin-slug'))
	->add(new Members()) // Class extends `AbstractHookProvider` or implements `WpHooksInterface` 
	->add(new SomeOtherClass()) 
	->initialize();
```

...or:

```php
use TheFrosty\WpUtilities\Plugin\PluginFactory;
$plugin = PluginFactory::create('plugin-slug');
$plugin
    ->add(new SomeOtherClass())
    ->add(new SomeOtherNewClass())
	->initialize();
```

You can also use the latter statement with conditions available on `plugins_loaded` (or use the new `addIfCondition` method) like:

```php
if (\is_customize_preview()) {
    /** @var \TheFrosty\WpUtilities\Plugin\Plugin $plugin */
    $plugin
        ->add(new SomeOtherCustomizeClass())
        ->initialize();
}
```

If you'd like to initialize a class on a specific action hook use `addOnHook()` like:

```php
$plugin
    ->add(new SomeOtherClass())
    ->addOnHook(SomeClassToLoad::class, tag: 'admin_init', priority: 10, admin_only: true)
	->initialize();
```

If you'd like to initialize a class on a specific action hook use and meet a condition `addOnCondition()` like:

```php
$plugin
    ->add(new SomeOtherClass())
    ->addOnCondition(SomeClassToLoad::class, function: static fn(): bool => true, tag: 'admin_init', priority: 10, admin_only: true)
	->initialize();
```

If you'd like to initialize a class right away if a condition is met use `addIfCondition()` like:

```php
$plugin
    ->add(new SomeOtherClass())
    ->addIfCondition(SomeClassToLoad::class, condition: \class_exists('SomeClassThatIsRequired'))
	->initialize();
```

## Dependency injection container

`PluginFactory::create()` attaches a [PSR-11](https://www.php-fig.org/psr/psr-11/) container to the plugin
and registers the plugin in it under its slug. The container is resolved in this order:

1. A container you pass in: `PluginFactory::create('plugin-slug', __FILE__, $container)` (any PSR-11 implementation).
2. [PHP-DI](https://php-di.org), when `php-di/php-di` is installed (autowiring enabled). **Recommended.**
3. [Pimple](https://github.com/silexphp/Pimple), when `pimple/pimple` is installed (the v3 default).
4. No container.

Library code only relies on `get()`/`has()` (e.g. `Viewable::getView()`), so any container works.

### Service providers

`TheFrosty\WpUtilities\Plugin\ServiceProviderInterface` replaces `Pimple\ServiceProviderInterface` and works with
either container. A provider returns its entries; closures are shared factories and receive the container when the
parameter is type-hinted `Psr\Container\ContainerInterface`:

```php
use Psr\Container\ContainerInterface;
use TheFrosty\WpUtilities\Plugin\ServiceProviderInterface;
use TheFrosty\WpUtilities\Utils\View;

class ServiceProvider implements ServiceProviderInterface
{
    public const string WP_UTILITIES_VIEW = 'wp_utilities.view';
    public const string UPGRADE_PROVIDER = 'upgrade.provider';

    public function getDefinitions(): array
    {
        return [
            self::WP_UTILITIES_VIEW => static function (): View {
                $view = new View();
                $view->addPath(__DIR__ . '/views/');

                return $view;
            },
            self::UPGRADE_PROVIDER => static fn(ContainerInterface $container): Upgrade => new Upgrade($container),
        ];
    }
}

$plugin = PluginFactory::create('plugin-slug');
$plugin->addServiceProvider(new ServiceProvider())
    ->add($plugin->getContainer()->get(ServiceProvider::UPGRADE_PROVIDER))
    ->initialize();
```

### Migrating a v3 (Pimple) plugin

| v3 (Pimple) | v4 |
|---|---|
| `class ServiceProvider implements Pimple\ServiceProviderInterface` | `implements TheFrosty\WpUtilities\Plugin\ServiceProviderInterface` |
| `public function register(Container $pimple): void { $pimple['id'] = fn() => …; }` | `public function getDefinitions(): array { return ['id' => fn() => …]; }` |
| `$plugin->getContainer()->register(new ServiceProvider())` | `$plugin->addServiceProvider(new ServiceProvider())` |
| `$container['id']` | `$container->get('id')` |
| `$container['id'] = fn() => …` | `ContainerWriter::set($container, 'id', fn() => …)` (or the container's own API) |
| type-hint `Pimple\Container` | type-hint `Psr\Container\ContainerInterface` |
| require `pimple/pimple` | require `php-di/php-di` |

PHP-DI can also autowire classes with no definition at all: `$container->get(Upgrade::class)`.
