# QueryArgs

This is a fork of [johnbillion/args](https://github.com/johnbillion/args), one wp-utilities is ready for PHP 7.4+
we will require that package and stub it here. Alternatively we could use [Rector](https://github.com/RectorPHP/Rector)
to auto bump support back down to PHP 7.3 ¯\_(ツ)_/¯.

```php
$query = new \WP_Query( [
	'post_type' => 'post',
	'category_something' => 'does this accept an integer or a string?',
	'number_of_...errr'
] );
```

This library provides well-documented classes which represent some of the array-type parameters that are used in
WordPress. Using these classes at the point where you're constructing the arguments to pass into a WordPress 
function means you get familiar autocompletion and intellisense in your code editor.

## Usage

```php
use TheFrosty\WpUtilities\Models\WpQuery\QueryArgs;
$args = new QueryArgs();
$args->tag = 'amazing';
$args->posts_per_page = 100;

$query = new \WP_Query($args->toArray());
```
