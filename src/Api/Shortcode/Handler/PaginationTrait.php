<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api\Shortcode\Handler;

use WP_Query;
use function get_pagenum_link;
use function get_query_var;
use function max;
use function ob_get_clean;
use function ob_start;
use function paginate_links;
use function str_replace;
use const PHP_INT_MAX;

/**
 * Trait PaginationTrait
 * @package Dwnload\WpEmailDownload\Classes\ShortcodeApi
 */
trait PaginationTrait
{

    /**
     * Shortcode pagination.
     * @param WP_Query $wp_query Incoming query object
     * @return string
     */
    protected function pagination(WP_Query $wp_query): string
    {
        /**
         * For more options and info view the docs for paginate_links()
         * http://codex.wordpress.org/Function_Reference/paginate_links
         */
        $paginate_links = paginate_links([
            'base' => str_replace((string)PHP_INT_MAX, '%#%', get_pagenum_link(PHP_INT_MAX)),
            'current' => max(1, get_query_var('paged')),
            'total' => $wp_query->max_num_pages,
            'mid_size' => 5,
            'prev_text' => __('&laquo;', 'wp-utilities'),
            'next_text' => __('&raquo;', 'wp-utilities'),
            'type' => 'list',
        ]);

        ob_start();
        include 'templates/pagination.php';
        return ob_get_clean();
    }
}
