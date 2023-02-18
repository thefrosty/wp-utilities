<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\PostTypes;

/**
 * Trait PostTypeTrait
 * @package TheFrosty\WpUtilities\PostTypes
 */
trait PostTypeTrait
{

    /**
     * Set default post type names.
     * @param array $names
     * @return array
     */
    protected function setDefaultNames(array $names): array
    {
        return \wp_parse_args($names, [
            'name' => static::POST_TYPE,
            'slug' => static::SLUG,
        ]);
    }

    /**
     * Set default post type args.
     * @param array $args
     * @return array
     */
    protected function setDefaultArgs(array $args): array
    {
        return \wp_parse_args($args, [
            'public' => true,
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_nav_menus' => false,
            'show_in_admin_bar' => false,
            'show_in_rest' => true,
            'rest_base' => $this->getUrlSlug(),
            'capability_type' => 'post',
            'hierarchical' => false,
            'has_archive' => true,
            'query_var' => true,
            'can_export' => true,
            'rewrite_no_front' => false,
            'supports' => ['title'],
            'rewrite' => [
                'slug' => $this->getUrlSlug(),
            ],
            'delete_with_user' => false,
        ]);
    }

    /**
     * Build a data image svg+xml encoded for background image usage in place of dashicons for
     * the `menu_icon` field.
     * Visit the GitHub URL: https://github.com/FortAwesome/Font-Awesome/tree/master/svgs/solid and grab the raw
     * SVG HTML to pass into this method.
     * @link https://stackoverflow.com/a/42265057
     * @return string
     */
    protected function buildBase64DataImage(): string
    {
        return \sprintf(
            'data:image/svg+xml;base64, %s',
            \base64_encode(
                \str_replace(
                    ['<svg', '<path'],
                    ['<svg width="20" height="20"', '<path fill="black"'],
                    $this->getSvg()
                )
            )
        );
    }

    /**
     * Return the raw SVG HTML.
     * Overwrite this in the inherited class.
     * @return string
     */
    protected function getSvg(): string
    {
        return '';
    }

    /**
     * Get the post types URL slug. Convert post type underscores and dashed to camelCase.
     * @return string
     */
    private function getUrlSlug(): string
    {
        if (static::URL_SLUG !== null) {
            return static::URL_SLUG;
        }

        return \str_replace(['_', '-'], '', \lcfirst(\ucwords(static::SLUG, '_-')));
    }
}
