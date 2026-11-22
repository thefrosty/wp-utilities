<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Taxonomies;

use TheFrosty\WpUtilities\Utils\ObjectNames;
use function array_replace;
use function lcfirst;
use function str_replace;
use function ucwords;
use function wp_parse_args;

/**
 * Trait TaxonomyTrait
 * @package TheFrosty\WpUtilities\Taxonomies
 */
trait TaxonomyTrait
{

    /**
     * Set default taxonomy names.
     * @param array $names
     * @return array
     */
    protected function setDefaultNames(array $names): array
    {
        return wp_parse_args($names, [
            'name' => static::TAXONOMY_TYPE,
            'slug' => static::SLUG,
        ]);
    }

    /**
     * Set default taxonomy args.
     * @param array $args
     * @return array
     */
    protected function setDefaultArgs(array $args): array
    {
        return wp_parse_args($args, [
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => false,
            'show_in_rest' => true,
            'rest_base' => $this->getUrlSlug(),
            'show_in_quick_edit' => false,
            'meta_box_cb' => false,
            'show_admin_column' => true,
            'hierarchical' => false,
            'query_var' => true,
            'rewrite' => [
                'slug' => $this->getUrlSlug(),
            ],
        ]);
    }

    /**
     * Build the taxonomy labels from the singular & plural names; labels that were set take precedence.
     * @param array $names The names array (`name`, `singular`, `plural`).
     * @param array $labels The labels that were set.
     * @return array
     */
    protected function buildLabels(array $names, array $labels = []): array
    {
        $singular = ObjectNames::singular($names);
        $plural = ObjectNames::plural($names);

        return array_replace([
            'name' => $plural,
            'singular_name' => $singular,
            'menu_name' => $plural,
            'all_items' => "All {$plural}",
            'edit_item' => "Edit {$singular}",
            'view_item' => "View {$singular}",
            'update_item' => "Update {$singular}",
            'add_new_item' => "Add New {$singular}",
            'new_item_name' => "New {$singular} Name",
            'parent_item' => "Parent {$plural}",
            'parent_item_colon' => "Parent {$plural}:",
            'search_items' => "Search {$plural}",
            'popular_items' => "Popular {$plural}",
            'separate_items_with_commas' => "Separate {$plural} with commas",
            'add_or_remove_items' => "Add or remove {$plural}",
            'choose_from_most_used' => "Choose from most used {$plural}",
            'not_found' => "No {$plural} found",
        ], $labels);
    }

    /**
     * Get the taxonomy URL slug. Convert taxonomy underscores and dashed to camelCase.
     * @return string
     */
    private function getUrlSlug(): string
    {
        if (static::URL_SLUG !== null) {
            return static::URL_SLUG;
        }

        return str_replace(['_', '-'], '', lcfirst(ucwords(static::SLUG, '_-')));
    }
}
