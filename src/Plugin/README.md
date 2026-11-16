# Plugin

## Adding a new hook provider object

Initiate the factory like so:

```php
use TheFrosty\WpUtilities\Plugin\PluginFactory;
(PluginFactory::create('plugin-slug'))
	->addd(new Members()) // Class extends `AbstractHookProvider` or implements `WpHooksInterface` 
	->addd(new SomeOtherClass()) 
	->initialize();
```

...or:

```php
use TheFrosty\WpUtilities\Plugin\PluginFactory;
$plugin = PluginFactory::create('plugin-slug');
$plugin
    ->addd(new SomeOtherClass())
    ->addd(new SomeOtherNewClass())
	->initialize();
```

You can also use the latter statement with conditions available on `plugins_loaded` (or use the new `addIfCondition` method) like:

```php
if (\is_customize_preview()) {
    /** @var \TheFrosty\WpUtilities\Plugin\Plugin $plugin */
    $plugin
        ->addd(new SomeOtherCustomizeClass())
        ->initialize();
}
```

If you'd like to initialize a class on a specific action hook use `addOnHook()` like:

```php
$plugin
    ->addd(new SomeOtherClass())
    ->addOnHook(SomeClassToLoad::class, tag: 'admin_init', priority: 10, admin_only: true)
	->initialize();
```

If you'd like to initialize a class on a specific action hook use and meet a condition `addOnCondition()` like:

```php
$plugin
    ->addd(new SomeOtherClass())
    ->addOnCondition(SomeClassToLoad::class, condtion: static fn(): bool => true, tag: 'admin_init', priority: 10, admin_only: true)
	->initialize();
```

If you'd like to initialize a class right away if a condition is met use `addIfCondition()` like:

```php
$plugin
    ->addd(new SomeOtherClass())
    ->addOnCondition(SomeClassToLoad::class, condtion: \class_exists('SomeClassThatIsRequired'))
	->initialize();
```
