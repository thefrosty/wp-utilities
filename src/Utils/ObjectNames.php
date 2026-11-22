<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Utils;

use function str_ends_with;
use function str_replace;
use function strtolower;
use function substr;
use function ucwords;

/**
 * Class ObjectNames
 * Human-friendly singular & plural names for post types and taxonomies, derived the same way as
 * `jjgrainger/posttypes` v2 did when `singular`/`plural` aren't given (e.g. `book_genre` => "Book Genre(s)").
 * @package TheFrosty\WpUtilities\Utils
 */
final class ObjectNames
{

    /**
     * Get the singular name.
     * @param array $names The names array (`name`, optional `singular`).
     * @return string
     */
    public static function singular(array $names): string
    {
        return (string)($names['singular'] ?? self::humanize((string)($names['name'] ?? '')));
    }

    /**
     * Get the plural name.
     * @param array $names The names array (`name`, optional `plural`).
     * @return string
     */
    public static function plural(array $names): string
    {
        return (string)($names['plural'] ?? self::pluralize(self::humanize((string)($names['name'] ?? ''))));
    }

    /**
     * `book_genre` => "Book Genre".
     * @param string $name
     * @return string
     */
    private static function humanize(string $name): string
    {
        return ucwords(strtolower(str_replace(['-', '_'], ' ', $name)));
    }

    /**
     * "Category" => "Categories", "Book" => "Books".
     * @param string $name
     * @return string
     */
    private static function pluralize(string $name): string
    {
        if ($name === '') {
            return '';
        }

        return str_ends_with($name, 'y') ? substr($name, 0, -1) . 'ies' : $name . 's';
    }
}
