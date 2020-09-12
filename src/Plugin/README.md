# Plugin

## Adding a new hook provider object

Initiate the factory like so:

```php
use TheFrosty\WpUtilities\Plugin\PluginFactory;
( PluginFactory::create( 'slug' ) )
	->add( new Members() ) // Class extends `AbstractHookProvider` or implements `WpHooksInterface` 
	->add( new SomeOtherClass() ) 
	->initialize();
```

...or:

```php
use TheFrosty\WpUtilities\Plugin\PluginFactory;
$plugin = PluginFactory::create( 'slug' );
$plugin
    ->add( new SomeOtherClass() )
    ->add( new SomeOtherNewClass() )
	->initialize();
```

You can also use the latter statement with conditions available on `plugins_loaded` (or use the new `addIfCondition` method) like:

```php
/** @var heFrosty\WpUtilities\Plugin\Plugin $plugin */
if ( \is_customize_preview() ) {
$plugin
    ->add( new SomeOtherCustomizeClass() )
	->initialize();
}
```

If you'd like to initialize a class on a specific action hook use `addOnHook()` like:

```php
$plugin
    ->add( new SomeOtherClass() )
    ->addOnHook( SomeClassToLoad::class, $tag = 'admin_init', $priority = 10, $admin_only = true )
	->initialize();
```

If you'd like to initialize a class on a specific action hook use and meet a condition `addOnCondition()` like:

```php
$plugin
    ->add( new SomeOtherClass() )
    ->addOnCondition( SomeClassToLoad::class, $condition = static function() { return true; }, $tag = 'admin_init', $priority = 10, $admin_only = true )
	->initialize();
```

If you'd like to initialize a class right away if a condition is met use `addIfCondition()` like:

```php
$plugin
    ->add( new SomeOtherClass() )
    ->addOnCondition( SomeClassToLoad::class, $condition = \class_exists( 'SomeClassThatIsRequired' ) )
	->initialize();
```
