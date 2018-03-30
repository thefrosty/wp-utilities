# Beachbody On Demand Plugin

## Adding a new hook provider object

Initiate the factory like so:

```php
( PluginFactory::create( 'slug' ) )
	->add( new Members() ) // Class extends `AbstractHookProvider` or implements `WpHooksInterface` 
	->add( new SomeOtherClass() ) 
	->initialize();
```

...or:

```php
$plugin = PluginFactory::create( 'slug' );
$plugin
    ->add( new SomeOtherClass() )
    ->add( new SomeOtherNewClass() )
	->initialize();
```

You can also use the latter statement with conditions available on  `plugins_loaded` like:

```php
if ( is_customize_preview() ) {
$plugin
    ->add( new SomeOtherCustomizeClass() )
	->initialize();
}
```

If you'd like to initalize a class on a specific action hook use `add_on_hook()` like:

```php
$plugin
    ->add( new SomeOtherClass() )
    ->add_on_hook( SomeClassToLoad::class, 'admin_init', 10, true )
	->initialize();
```