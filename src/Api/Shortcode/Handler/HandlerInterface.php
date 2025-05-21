<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api\Shortcode\Handler;

/**
 * Interface HandlerInterface
 * @package TheFrosty\WpUtilities\Api\Shortcode\Handler
 */
interface HandlerInterface
{

    /**
     * @param string $tag
     */
    public function setTag(string $tag): void;

    /**
     * Returns the array of defaults for the shortcode's attributes
     * @return array
     */
    public function getDefaults(): array;

    /**
     * Returns the html that WordPress will display to the user.
     * @link https://codex.wordpress.org/Shortcode_API#Output
     * @param array|string $atts an associative array of attributes, or an empty string if no attributes are given
     * @param string|null $content the enclosed content (if the shortcode is used in its enclosing form)
     * @param string $tag the shortcode tag, useful for shared callback functions
     * @return string
     */
    public function handler(array|string $atts, ?string $content, string $tag): string;
}
