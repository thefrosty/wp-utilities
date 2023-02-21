<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\PostTypes\Columns\Api;

/**
 * Class ColumnsTrait
 * @package TheFrosty\WpUtilities\PostTypes\Columns\Api
 */
trait ColumnsTrait
{

    /**
     * Helper to return WordPress' HTML row actions.
     * @param int $post_id The post ID.
     * @param string $post_type The current post type string.
     * @param string $meta_key The meta key to check (plural or singular)
     */
    protected function rowActionsHtml(int $post_id, string $post_type, string $meta_key): void
    {
        \printf(
            '<a href="%1$s" title="%2$s">%3$s</a> |
<a href="javascript:;" data-select2-ajax="true" data-meta_key="%4$s" data-meta_value="%5$s">
<span title="%7$s">%6$s</span></a>',
            \esc_url(\get_edit_post_link($post_id)),
            \sprintf(
                \esc_attr__(
                    'Edit the &ldquo;%1$s&rdquo; %2$s',
                    'wp-utilities'
                ),
                \esc_attr(\get_the_title($post_id)),
                \esc_attr(\get_post_type_object(\get_post_type($post_id))->labels->singular_name)
            ),
            \esc_html__('Edit', 'wp-utilities'),
            \esc_attr($meta_key),
            \esc_attr($post_id),
            \esc_html__('Filter', 'wp-utilities'),
            \sprintf(
                \esc_attr_x(
                    'Filter %1$s by the &ldquo;%2$s&rdquo; %3$s',
                    'Title attribute to filter by a program.',
                    'wp-utilities'
                ),
                \esc_attr(\get_post_type_object($post_type)->labels->name ?? ''),
                \esc_attr(\get_the_title($post_id)),
                \esc_attr(\get_post_type_object(\get_post_type($post_id))->labels->singular_name)
            )
        );
    }
}
