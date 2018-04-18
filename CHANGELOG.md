# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [1.1.2] - 2018-04-18
### Updated
- Make sure the `addOnHook` action priority is set minus 2 so when `initializeOnHook` is called on the
same hook tag it can execute.

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
- New `Plugin` builder (forked from [cedaro/wp-plugin](https://github.com/cedaro/wp-plugin)) + heavily modified for PHP 7.
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
- Added dynamic 'initiated' property to the Init class to allow for multiple
    calls to the `initialize()` method.

## [0.1.0] - 2017-04-13
- Initial release.
