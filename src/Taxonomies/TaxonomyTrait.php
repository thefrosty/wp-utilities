<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Taxonomies;

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
