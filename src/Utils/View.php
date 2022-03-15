<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Utils;

use function extract;
use function file_exists;

/**
 * Class View
 * @package TheFrosty\WpUtilities\Utils
 */
final class View
{

    /**
     * Render a view file.
     * @param string $view The view file to render from the `views` directory.
     * @return string
     */
    public static function get(string $view): string
    {
        return __DIR__ . "../../views/$view";
    }

    /**
     * Render a view file.
     * @param string $view The view file to render from the `views` directory.
     * @param array $args
     */
    public static function render(string $view, array $args = []): void
    {
        $view = self::get($view);
        if (file_exists($view)) {
            extract($args);
            include $view;
        }
    }
}
