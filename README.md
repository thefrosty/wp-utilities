# Frosty's WordPress Utilities

![WP Utilities](.github/wp-utilities.jpg?raw=true "Frosty's WordPress Utilities")

[![PHP from Packagist](https://img.shields.io/packagist/php-v/thefrosty/wp-utilities.svg)]()
[![Latest Stable Version](https://img.shields.io/packagist/v/thefrosty/wp-utilities.svg)](https://packagist.org/packages/thefrosty/wp-utilities)
[![Total Downloads](https://img.shields.io/packagist/dt/thefrosty/wp-utilities.svg)](https://packagist.org/packages/thefrosty/wp-utilities)
[![License](https://img.shields.io/packagist/l/thefrosty/wp-utilities.svg)](https://packagist.org/packages/thefrosty/wp-utilities)
![Build Status](https://github.com/thefrosty/wp-utilities/actions/workflows/main.yml/badge.svg)
[![codecov](https://codecov.io/gh/thefrosty/wp-utilities/branch/develop/graph/badge.svg?token=UUBVKGTYTG)](https://codecov.io/gh/thefrosty/wp-utilities)
[![Donate with $DOGE](https://img.shields.io/static/v1?style=&logo=dogecoin&label=Donation&message=DFMbUjdxuQNJnbA622e7TNSJ3yxAdAWZEW&color=ba9f33)](#)  

A library containing my standard development resources to build high quality WordPress plugins.

### Requirements

```
PHP >= 8.4
WordPress >= 7.0
```

The required WordPress version will always be the most recent point release of
the previous major release branch.

For both PHP and WordPress requirements, although this library may work with a
version below the required versions, they will not be supported and any
compatibility is entirely coincidental.

### Installation

To install this library, use Composer:

```
composer require thefrosty/wp-utilities:^4.0
```

Then follow examples in the [Plugin README](./src/Plugin/README.md).

### Quick start

```php
use MyPlugin\Admin\SettingsPage;
use MyPlugin\Api\RestRoutes;
use MyPlugin\Cli\Commands;
use MyPlugin\Frontend\Assets;
use MyPlugin\Integrations\WooCommerce;
use MyPlugin\Integrations\WooCommerceAdmin;
use MyPlugin\PostTypes\Book;
use TheFrosty\WpUtilities\Plugin\DefaultServiceProvider;
use TheFrosty\WpUtilities\Plugin\PluginFactory;

$plugin = PluginFactory::create('plugin-slug');
// Registers the current HTTP request and a shared View in the plugin's container (PHP-DI or Pimple).
$plugin->addServiceProvider(new DefaultServiceProvider());
$plugin
    // An instance: registered now, its addHooks() runs on initialize().
    ->add(new Book())
    // Class names (WpHooksInterface implementations) from here on; the plugin instantiates them.
    // Registered now, only when the bool is true.
    ->addIfCondition(Commands::class, defined('WP_CLI') && WP_CLI)
    // Registered on `plugins_loaded` (the default $deferred_tag) when the bool is true.
    // The bool is evaluated now: use addOnConditionDeferred() when the check needs the deferred hook.
    ->addIfConditionDeferred(SettingsPage::class, is_admin(), deferred_tag: 'plugins_loaded')
    // The callable is called now (with $func_args unpacked); if it returns true, register it on $tag
    // (default `init`).
    ->addOnCondition(
        RestRoutes::class,
        'extension_loaded',
        func_args: ['intl'],
        tag: 'rest_api_init',
        priority: 10,
    )
    // On `plugins_loaded`, call the callable; if true, register it on `init` (admin only).
    ->addOnConditionDeferred(
        WooCommerceAdmin::class,
        static fn(): bool => class_exists(\WooCommerce::class),
        deferred_tag: 'plugins_loaded',
        admin_only: true,
    )
    // Registered on a specific hook; $args are unpacked into the class constructor.
    // admin_only: true = admin only, false = front-end only, null (default) = both.
    ->addOnHook(Assets::class, tag: 'wp_enqueue_scripts', priority: 20, admin_only: false, args: ['plugin-slug'])
    // On `init` (the default $deferred_tag), register it on `wp_loaded`.
    ->addOnHookDeferred(WooCommerce::class, deferred_tag: 'init', tag: 'wp_loaded', deferred_priority: 20)
    ->initialize();
```

See [Dependency injection container](./src/Plugin/README.md#dependency-injection-container) for extending
`DefaultServiceProvider` with your own entries.

### Guides

- [Plugin & hook providers](./src/Plugin/README.md)
- [Post types](./src/PostTypes/README.md) (`AbstractPostType`)
- [Taxonomies](./src/Taxonomies/README.md) (`AbstractTaxonomy`)
- [WP_Query models](./src/Models/WpQuery/README.md)

### Development scripts

The PHPCS/PHPCBF/PHPStan/Psalm/ESLint and WordPress test-suite scripts that used to live in `bin/` moved to
[thefrosty/wp-utilities-dev](https://github.com/thefrosty/wp-utilities-dev) in v4. If you called
`./vendor/thefrosty/wp-utilities/bin/<script>.sh`, run `composer require --dev thefrosty/wp-utilities-dev` and use
`./vendor/bin/<script>.sh` instead.
