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

### Default service provider

Most plugins register the same entries: the current HTTP request and a `View`. Register
`TheFrosty\WpUtilities\Plugin\DefaultServiceProvider` instead of defining them yourself:

```php
use Symfony\Component\HttpFoundation\Request;
use TheFrosty\WpUtilities\Plugin\DefaultServiceProvider;
use TheFrosty\WpUtilities\Plugin\PluginFactory;

$plugin = PluginFactory::create('plugin-slug');
$plugin->addServiceProvider(new DefaultServiceProvider());

$request = $plugin->getContainer()->get(DefaultServiceProvider::REQUEST); // Request::createFromGlobals()
```

| Entry ID | Value |
|---|---|
| `DefaultServiceProvider::REQUEST` (`'request'`) | `Request::createFromGlobals()`, shared. |
| `Request::class` | The same request, so PHP-DI autowiring injects the *current* request into constructors type-hinting `Request` (instead of an empty `new Request()`). |
| `DefaultServiceProvider::VIEW` (`'wp_utilities.view'`) | A shared `View` (as used by `Viewable::getView()`), with the paths from `getViewPaths()`. |

### Service providers

`TheFrosty\WpUtilities\Plugin\ServiceProviderInterface` replaces `Pimple\ServiceProviderInterface` and works with
either container. A provider returns its entries; closures are shared factories and receive the container when the
parameter is type-hinted `Psr\Container\ContainerInterface`.

The easiest way to write one is to extend `DefaultServiceProvider`, which keeps the request & view entries:
merge your entries into `parent::getDefinitions()` and override `getViewPaths()` for your templates.

```php
use Psr\Container\ContainerInterface;
use TheFrosty\WpUtilities\Plugin\DefaultServiceProvider;

class ServiceProvider extends DefaultServiceProvider
{
    public const string UPGRADE_PROVIDER = 'upgrade.provider';

    public function getDefinitions(): array
    {
        return [
            ...parent::getDefinitions(),
            self::UPGRADE_PROVIDER => static fn(ContainerInterface $container): Upgrade => new Upgrade($container),
        ];
    }

    protected function getViewPaths(): array
    {
        return [dirname(__DIR__) . '/views/'];
    }
}

$plugin = PluginFactory::create('plugin-slug');
$plugin->addServiceProvider(new ServiceProvider())
    ->add($plugin->getContainer()->get(ServiceProvider::UPGRADE_PROVIDER))
    ->initialize();
```

Or implement `ServiceProviderInterface` directly when you don't want the defaults.

### Container-aware classes

`Plugin`, `AbstractContainerProvider` (and anything else using `ContainerAwareTrait`) proxy container entries:

| Access | Behaviour (v4) |
|---|---|
| `$plugin->getContainer()` | The PSR-11 container, or `null`. |
| `$plugin->entry` | `$container->get('entry')`; throws a `NotFoundExceptionInterface` if missing, `null` with no container. |
| `isset($plugin->entry)` | `$container->has('entry')`. |
| `$plugin->entry(...$args)` | Calls the entry if it's callable; otherwise throws `BadMethodCallException`. |

Both containers call a closure to *build* an entry. To store a closure as a callable instead, wrap it:
`DI\value($callable)` with PHP-DI, `$pimple->protect($callable)` with Pimple.

`TheFrosty\WpUtilities\Plugin\Container` is the Pimple bridge (Pimple + PSR-11). It needs `pimple/pimple`, is
only used when PHP-DI isn't installed (or when passed to `PluginFactory::create()`), and is deprecated in v4.

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
| `$plugin->entry` returned `true`/`false` (bug) | returns the entry |
| `$plugin->missingMethod()` returned `false` | throws `BadMethodCallException` |
| `$pimple->protect($fn)` for a callable entry | `DI\value($fn)` |
| `$pimple['request'] = fn() => Request::createFromGlobals()` | `DefaultServiceProvider` (`DefaultServiceProvider::REQUEST`) |
| `$pimple['wp_utilities.view'] = fn() => new View()` + `addPath()` | `DefaultServiceProvider` + override `getViewPaths()` |

PHP-DI can also autowire classes with no definition at all: `$container->get(Upgrade::class)`.
