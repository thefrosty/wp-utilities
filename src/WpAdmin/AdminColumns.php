<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin;

use TheFrosty\WpUtilities\Plugin\AbstractContainerProvider;
use TheFrosty\WpUtilities\Plugin\Plugin;
use function apply_filters;
use function array_filter;
use function array_values;
use function array_walk;
use function current_filter;
use function do_action;
use function str_replace;
use function wp_parse_args;

/**
 * Class AdminColumns
 * @package TheFrosty\WpUtilities\WpAdmin
 */
class AdminColumns extends AbstractContainerProvider
{

    public const TAG_MANAGE_POST_TYPES = Plugin::TAG . '/manage_post_types';
    public const TAG_MANAGE_POSTS_COLUMNS = Plugin::TAG . '/manage_posts_columns';
    public const TAG_MANAGE_POSTS_CUSTOM_COLUMN = Plugin::TAG . '/manage_posts_custom_column';
    public const TAG_MANAGE_MANAGE_EDIT_SORTABLE_COLUMNS = Plugin::TAG . '/manage_edit_sortable_columns';

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $post_types = array_filter(array_values(apply_filters(self::TAG_MANAGE_POST_TYPES, [])));
        array_walk($post_types, [$this, 'instantiatePostTypeManageFilters']);
    }

    /**
     * Apply column filters to all Post Types.
     * @param string $post_type
     */
    protected function instantiatePostTypeManageFilters(string $post_type): void
    {
        $this->addFilter('manage_' . $post_type . '_posts_columns', [$this, 'postsColumns'], 99);
        $this->addAction('manage_' . $post_type . '_posts_custom_column', [$this, 'postsCustomColumn'], 10, 2);
        $this->addFilter('manage_edit-' . $post_type . '_sortable_columns', [$this, 'sortableColumns']);
    }

    /**
     * Manage all Post Type columns, by adding our custom filter allowing new columns.
     * @param array $columns
     * @return array
     */
    protected function postsColumns(array $columns): array
    {
        $post_type = str_replace(['manage_', '_posts_columns'], '', current_filter());
        if (isset($columns['date'])) {
            $date = $columns['date'];
            unset($columns['date']);
        }
        $columns = apply_filters(self::TAG_MANAGE_POSTS_COLUMNS, $columns, $post_type);
        if (isset($date)) {
            $columns['date'] = $date;
        }

        return $columns;
    }

    /**
     * Manage all Post Type columns, by adding our custom action allowing manipulation of the column data.
     * @param string $column
     * @param int $post_id
     */
    protected function postsCustomColumn(string $column, int $post_id): void
    {
        $post_type = str_replace(['manage_', '_posts_custom_column'], '', current_filter());
        do_action(self::TAG_MANAGE_POSTS_CUSTOM_COLUMN, $column, $post_id, $post_type);
    }

    /**
     * Allow filtering of which columns are "sortable".
     * @param array $columns
     * @return array
     */
    protected function sortableColumns(array $columns): array
    {
        $post_type = str_replace(['manage_edit-', '_sortable_columns'], '', current_filter());
        $filter = apply_filters(self::TAG_MANAGE_MANAGE_EDIT_SORTABLE_COLUMNS, $columns, $post_type);

        return wp_parse_args($columns, $filter);
    }
}
