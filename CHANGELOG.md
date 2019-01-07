# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## Unreleased
PHP Version >= `7.2` will be required by version `1.4`.

## [1.3.1] - 2019-01-07
### Fixed
- Using multiple `addOnHook` methods clears the tag variable (add_filter(**$tag**, function() {})) rendering the second
called method invalid on it's registered tag (unless it matched that of the first tag). The action tag is now passed down
from the method to `initiateWpHooks` and `initializeOnHook` (which are private methods), so no changes required.

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
- Change HttpFoundationRequestInterface::setRequest and `HttpFoundationRequestTrait` to return `self` instead of `parent`.

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
