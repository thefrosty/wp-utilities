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
2. [Pimple](https://github.com/silexphp/Pimple), when `pimple/pimple` is installed (the v3 behaviour).
3. [PHP-DI](https://php-di.org), when `php-di/php-di` is installed (autowiring enabled).
4. No container.

Library code only relies on `get()`/`has()` (e.g. `Viewable::getView()`), so any container works.

### Moving a plugin from Pimple to PHP-DI

```php
// Before (Pimple)
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    public function register(Container $pimple): void
    {
        $pimple['wp_utilities.view'] = static fn(): View => (new View())->addPath(__DIR__ . '/views/');
    }
}

$plugin->getContainer()->register(new ServiceProvider());
$plugin->add($plugin->getContainer()['upgrade.provider']);
```

```php
// After (PHP-DI): definitions are shared by default, like Pimple services.
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

$container = (new ContainerBuilder())
    ->addDefinitions([
        'wp_utilities.view' => static function (): View {
            $view = new View();
            $view->addPath(__DIR__ . '/views/');

            return $view;
        },
        // Autowired: constructor dependencies are resolved from their type hints.
        'upgrade.provider' => \DI\autowire(Upgrade::class),
    ])
    ->build();

$plugin = PluginFactory::create('plugin-slug', __FILE__, $container);
$plugin->add($container->get('upgrade.provider'));
```
