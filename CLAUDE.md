# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Setup

This is a PHP library for WordPress development that provides utilities for building high-quality WordPress plugins. It
requires PHP >= 8.3 and WordPress 6.7 or higher.

## Key Architecture Components

The library follows a plugin factory pattern with a container-based architecture:

1. **Plugin Factory System** - Uses `PluginFactory` to create and manage plugin instances
2. **Hook Registration** - Core `Init` class handles registration and initialization of hook providers
3. **Hook Provider Pattern** - Classes implement `WpHooksInterface` to register WordPress hooks
4. **Container Support** - Integration with PSR-11 containers (Pimple) for dependency injection

## How to Run Tests

Make sure Docker is running with: `docker compose up -d`.

To run the full PHPUnit test suite (with code coverage):

```
composer phpunit
```

To run a specific test:

```
./vendor/bin/phpunit tests/unit/Plugin/PluginTest.php
```

## Common Development Tasks

The library provides a fluent interface for hook registration:

```php
use TheFrosty\WpUtilities\Plugin\PluginFactory;
$plugin = PluginFactory::create('slug')
    ->add(new SomeHookProvider())
    ->add(new AnotherHookProvider())
    ->initialize();
```

Hook providers can be added conditionally or on specific hooks:

- `add()` - Register immediately
- `addIfCondition()` - Register when a condition is met
- `addOnHook()` - Register on a specific WordPress hook
- `addOnCondition()` - Register when a condition is met on a specific hook

## Code Structure

Key files:

- `src/Plugin/PluginFactory.php` - Plugin instance creation and management
- `src/Plugin/AbstractPlugin.php` - Base plugin class with hook registration methods
- `src/Plugin/Init.php` - Hook initialization manager
- `src/Plugin/WpHooksInterface.php` - Interface for hook providers
- `src/Plugin/AbstractHookProvider.php` - Base class for hook providers with traits

## Build and Linting

- Code standard checking: `composer phpcs`
- PHPUnit checks: `composer phpunit`

## How to Write PHPUnit Tests

- Current tests are for PHPUnit version 11.5.
- All tests should extend `TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase`, which is an abstraction of
  `PHPUnit\Framework\TestCase`.
- All tests should use a PHPUnit Attribute for what the test covers, see https://docs.phpunit.de/en/11.5/code-coverage.html

## Integration Pattern

The library uses a pattern where plugin instances are created via `PluginFactory::create()` and hook providers are
registered using the fluent interface. Providers can be registered with conditional logic, deferred execution, or
specific WordPress hooks.
