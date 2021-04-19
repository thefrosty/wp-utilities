# WordPress Utilities

![WP Utilities](.github/wp-utilities.jpg?raw=true "WordPress Utilities")

[![PHP from Packagist](https://img.shields.io/packagist/php-v/thefrosty/wp-utilities.svg)]()
[![Latest Stable Version](https://img.shields.io/packagist/v/thefrosty/wp-utilities.svg)](https://packagist.org/packages/thefrosty/wp-utilities)
[![Total Downloads](https://img.shields.io/packagist/dt/thefrosty/wp-utilities.svg)](https://packagist.org/packages/thefrosty/wp-utilities)
[![License](https://img.shields.io/packagist/l/thefrosty/wp-utilities.svg)](https://packagist.org/packages/thefrosty/wp-utilities)
[![Build Status](https://travis-ci.org/thefrosty/wp-utilities.svg?branch=master)](https://travis-ci.org/thefrosty/wp-utilities)
[![Beerpay](https://beerpay.io/thefrosty/wp-utilities/badge.svg?style=flat)](https://beerpay.io/thefrosty/wp-utilities)

A library containing my standard development resources.

### Requirements

```
PHP >= 7.4 OR >= 8.0
WordPress >= 5.6
```

For PHP < 7.1, use version 1.3.x.
For PHP >= 7.1 & < 7.3, use version 1.9.x.
For PHP = 7.3, use version 2.0.0.

The required WordPress version will always be the most recent point release of
the previous major release branch.

For both PHP and WordPress requirements, although this library may work with a
version below the required versions, they will not be supported and any
compatibility is entirely coincidental.

### Installation

To install this library, use Composer:

```
composer require thefrosty/wp-utilities:^2.1
```

Then follow examples in the [Plugin README](./src/Plugin/README.md)
