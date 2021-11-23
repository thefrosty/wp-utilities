# Change Log

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## Unreleased

## [2.5.2] - 2021-11-23

- Fix #76: Require `psr/container:^2.0` which locks in the PSR-11 container implementation.

## [2.5.1] - 2021-11-20

- Fix: compatible with Psr\Container\ContainerInterface where certain legacy packages have composer.lock 
 at `psr/container:1.0.0`, fixes #73

## [2.5.0] - 2021-10-20

- Add new methods for deferring conditionally loading `WpHooksInterfaces` too `AbstractPlugin`:
   - `addIfConditionDeferred` & `addOnConditionDeferred`.
- Code cleanup on "Conditions always 'true' because something might be evaluated at this point".

## [2.4.2] - 2021-08-12

- Remove the `static` from the `$paged` variable in `WpQueryTrait::wpGetAllIds()` allowing multiple calls to 
  `wpGetAllIds` without affecting current scope count.

## [2.4.1] - 2021-07-01

- Set defaults in a method in `WpQueryTrait` that can be filtered with `thefrosty/wp_utilities/wp_query_defaults`. 
  Also apply defaults before `wpQueryCached` hash.
- Update johnbillion/args requirement from ^0.4.0 to ^0.7.0 [#63](https://github.com/thefrosty/wp-utilities/pull/63)

## [2.4.0] - 2021-06-16

- Add deprecation notice for parameter `$expiration` in `WpQueryTrait::wpQueryGetAllIds`.
- Add `WpQueryTrait::wpQueryGetAllIdsCached` to compliment `WpQueryTrait::wpQueryGetAllIds` without 
  utilizing cached query calls, if desired.
- Update johnbillion/args requirement from ^0.2.0 to ^0.4.0 [#57](https://github.com/thefrosty/wp-utilities/pull/57)

## [2.3.0] - 2021-06-08

- Update HooksTrait with new methods: `addFilterOnce`, `addActionOnce`, `doAction`, and `applyFilters`.
- Add `AbstractContainerProvider`.
- Add tests for the new class, and update base tests including replacement of deprecation PHPUnit methods.
- Update johnbillion/args requirement from ^0.0.4 to ^0.2.0 [#54](https://github.com/thefrosty/wp-utilities/pull/54)

## [2.2.1] - 2021-05-17

- Fix for "Typed property must not be accessed before initialization"
  [#52](https://github.com/thefrosty/wp-utilities/pull/52).

## [2.2.0] - 2021-05-13

- Update README h/t [szepeviktor](https://github.com/thefrosty/wp-utilities/pull/36).
- Fix Travis badge link h/t [szepeviktor](https://github.com/thefrosty/wp-utilities/pull/39).
- Add PHPStan to composer scripts (not added to complete test suite yet).
- Code cleanup from update PHPCS & PHPMD rules across the complete package.
- Bump PHPUnit to ^8 while utilizing `yoast/phpunit-polyfills`.
- Update `johnbillion/args` to `^0.4`.
- Moving constants from AbstractPlugin into the PluginInterface interface.
- Change PluginInterface::addOnCondition's third param to be a nullable array (rename it to $func_args).
- Add Init::getWpHookObject(string) to retrieve the "singleton" object of an initiated class.
- Update TemplateLoader, and it's interface for PHP ^7.4
- Update all unit tests & coding standards.

## [2.1.0] - 2021-04-19

### Updated

- **Bump PHP to `^7.4`!**
- Update PHPDoc block for `wpQueryGetAllIds` to show array in integers.
- Add WIP for `DisablePluginUpdateCheck` "Add checks for WordPress' API for plugin info calls and bypass removing the
  plugin from update checks if it uses github-updater."
- Allow PHP `^7.4|^8.0` to composer.json.

### Added

- Add third parameter `$expiration` to `wpQueryGetAllIds` to match `wpQueryCached`.
- Add new `AddPluginIcons` class for the `/WpAdmin/` classes.
- Add PHP 8 to Travis tests.
- Added [johnbillion/args](https://github.com/johnbillion/args)

### Changed

- Updated `TheFrosty\WpUtilities\Models\WpQuery\QueryArgs` to extend `Args\WP_Query`
  [@see Models/WpQuery/README](./src/Models/WpQuery/README.md).

## [2.0.0] - 2020-09-12

### Updated

- Require PHP => 7.3
- Update bin shell files.
- Update travis to test against WordPress 5.5.1
- Add getter method helper to BaseModel.

### Added

- New encrypt & decrypt methods to the Hash trait.
- New RestApi/Http/RouteService for easy REST route registration.

## [1.9.1] - 2020-04-30

### Updated

- WpCacheTrait's `setCache` $group param default to null.

### Added

- WpCacheTrait `deleteCache`.

## [1.9.0] - 2020-03-21

### Updated

- WpQueryTrait's default per_page value to 99 (previously 1000).

### Added

- Hash trait, used in WpCacheTrait.
- Transients trait.

## [1.8.0] - 2019-11-17

### Updated

- Added TRAVIS & CIRCLECI variable checks to the phpcs summary flag.
- Cleaned up view: dashboard-widget.php.
- Cleaned up DashboardWidget.

### Added

- New image to the README.
- New TemplateLoader class, _for loading template files in your plugin_.

## [1.7.3] - 2019-11-11

### Fixed

- Added $path parameter to `getFileTime` and change to pull from getPath which would be the absolute path to the file
  instead of the basename file, oops.

## [1.7.2] - 2019-11-11

### Changed

- The `addOnCondition` has a breaking change adding a new parameter after the callback, allowing an array of args to be
  passed to the callback. If empty `call_user_function` is used, else `call_user_func_array` is used.

### Updated

- `addOnCondition` is also using the new `classImplementsWpHooks` condition function before adding the hook to avoid
  possible errors when passing in the class string.
- Bash formatting cleanup on `bin/` directory shell linters.

## [1.7.1] - 2019-11-07

### Added

- Add `getFileTime` to `PluginInterface` which allows asset version attributes on modified file time over version
  constraints. Use `$plugin->getFileTime()` or a date formatted
  string `\date(\DateTime::::ISO8601, $plugin->getFileTime()`.
- Add `addIfCondition` to `PluginInterface` which allows instantiating the class instantly if the condition is met as
  opposed to `addOnCondition` which is only when the tag (action) is met.

## [1.7.0] - 2019-11-04

### Added

- `RestApi\PostTypeFilter`, `Api\WpCacheTrait` & `Api\WpQueryTrait`.
- `PostTypeFilter` can be initiated to add `filter[]` querying to the rest endpoints
  (enhanced rest filter of [WP-API/rest-filter](https://github.com/WP-API/rest-filter))
- Default action/filter prefix constant in `TheFrosty\WpUtilities\Plugin\Plugin` called `TAG`

### Updated

- Add missing dependency "phpcompatibility" for require-dev.
- Bumped WordPress version to 5.2 in travis.

## [1.6.2] - 2019-10-28

### Updated

- In the `BaseModel` class update the get method call to use the `getMethod` helper in `toArray`.

## [1.6.1] - 2019-10-02

### Added

- Added `getCustomDelimiters` method to the BaseModel to allow additional search/delimiters to be added to the getMethod
  replacement. Useful when properties contain more than just "_" and "-", like for instance "$" signs.

## [1.6.0] - 2019-08-27

### Updated

- `PluginFactory` now has a method: `PluginFactory::getInstance($slug)` to return the current instance of the created
  plugin object.

## [1.5.0] - 2019-03-14

### Added

- New method `addOnCondtion` allowed hooks to be instantiated only when a condition is met.

## [1.4.1] - 2019-02-13

### Fixed

- Too few arguments to
  function `TheFrosty\WpUtilities\WpAdmin\DisablePluginUpdateCheck::httpRequestRemovePluginBasename()`.

## [1.4] - 2019-02-13

### PHP version bumped to >= 7.1

### Added

- Add a new class `TheFrosty\WpUtilities\WpAdmin\DisablePluginUpdateCheck` to disable plugin update checks. All you have
  to do is instantiate it in a PluginInterface `add()` method.

## [1.3.1] - 2019-01-07

### Fixed

- Using multiple `addOnHook` methods clears the tag variable (add_filter(**$tag**, function() {})) rendering the second
  called method invalid on it's registered tag (unless it matched that of the first tag). The action tag is now passed
  down from the method to `initiateWpHooks` and `initializeOnHook` (which are private methods), so no changes required.

## [1.3.0] - 2018-11-21

### Added

- Added 5th parameter to `addOnHook` to pass to class constructor. using argument unpacking via `...`.
- An abstract Singleton class and interface.

## [1.2.2] - 2018-07-25

### Fixed

- Fatal error on incorrect return type.

### Updated

- Remove the return type from HttpFoundationRequest*::setRequest.
- Don't pass the Request object into the setRequest from the Init class.

## [1.2.1] - 2018-07-25

### Updated

- prefix function outside namespace for: `ContainerAwareTrait`, `BaseModel`, `Init` & `AbstractPlugin`.
- Change HttpFoundationRequestInterface::setRequest and `HttpFoundationRequestTrait` to return `self` instead
  of `parent`.

## [1.2.0] - 2018-06-07

### Added

- HttpFoundation as composer suggestion.
- HttpFoundation trait and interface.
- Instantiate the Request object in `Init` if the WpHook implements the HttpFoundation interface.

### Updated

- PluginAwareTrait; updated order of getter/setter.
- Move the Container initiated into it's own method in the PluginFactory class.

## [1.1.3] - 2018-04-18

### Fixed

- Sets a default priority constant. Passes an int value to avoid strict_types errors.

## [1.1.2] - 2018-04-18

### Updated

- Make sure the `addOnHook` action priority is set minus 2 so when `initializeOnHook` is called on the same hook tag it
  can execute.

## [1.1.1] - 2018-04-04

### Updated

- Added the `initialize` method in `AbstractPlugin` since it's no longer extending `Init`.

## [1.1.0] - 2018-04-04

### Updated

- Changed `Init` from an abstract class to final class.
- Removed extension of `Init` from `AbstractPlugin` and added as a DI.
- Updated `PluginInterface` and `PluginFactory` to reflect the new changes.

## [1.0.0] - 2018-03-30

## Breaking Update

### Added

- New `Plugin` builder (forked from [cedaro/wp-plugin](https://github.com/cedaro/wp-plugin)) + heavily modified for PHP
    7.
- Travis CI.
    - phpunit tests.
    - phpcs + phpmd.

### Updated

- Project namespace changed from `TheFrosty\WP\Utils` to `TheFrosty\WpUtilities`.
- Project now adheres to PSR2 coding standards.

## [0.2.3] - 2018-01-29

### Added

- Added `AbstractSingleton` class and `InterfaceSingleton` interface to the a new Utils directory.

### Updated

- Added `@throws` notice to PhpDoc in `BaseModel::toArrayDeep()`.

## [0.2.2] - 2017-07-28

### Updated

- Added `-` to BaseModel to allow array keys to be set as `example_one => 'Value'` or `example-one => 'Value`.

## [0.2.1] - 2017-07-21

### Added

- DashboardWidget class for managing feeds in the WordPress admin dashboard.

## [0.2.0] - 2017-05-09

### Updated

- Added dynamic 'initiated' property to the Init class to allow for multiple calls to the `initialize()` method.

## [0.1.0] - 2017-04-13

- Initial release.
