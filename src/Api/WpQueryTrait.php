<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

use TheFrosty\WpUtilities\Plugin\Plugin;
use WP_Query;
use function _deprecated_argument;
use function apply_filters;
use function array_filter;
use function call_user_func;
use function esc_html__;
use function is_int;
use function json_encode;
use function md5;
use function sprintf;
use function wp_parse_args;
use function wp_reset_postdata;
use const MINUTE_IN_SECONDS;

/**
 * Trait WpQueryTrait.
 * @package TheFrosty\WpUtilities\Api
 */
trait WpQueryTrait
{

    use WpCacheTrait;

    /**
     * Return a new WP_Query object.
     * @param string $post_type The post type to query.
     * @param array $args Additional WP_Query parameters
     * @return WP_Query
     */
    protected function wpQuery(string $post_type, array $args = []): WP_Query
    {
        $defaults = $this->getDefaults($post_type);

        return new WP_Query(wp_parse_args($args, $defaults));
    }

    /**
     * Return a cached WP_Query object.
     * @param string $post_type
     * @param array $args Additional WP_Query parameters.
     * @param int|null $expiration The expiration time, defaults to `MINUTE_IN_SECONDS`.
     * @return WP_Query
     */
    protected function wpQueryCached(string $post_type, array $args = [], ?int $expiration = null): WP_Query
    {
        $defaults = $this->getDefaults($post_type);
        $cache_key = $this->setQueryCacheKey(
            $this->getHashedKey(
                sprintf('%s/query_%s', Plugin::TAG, md5(json_encode(wp_parse_args($args, $defaults))))
            )
        );
        $query = $this->getCache($cache_key);
        if (!($query instanceof WP_Query)) {
            $query = $this->wpQuery($post_type, $args);
            if ($query->have_posts()) {
                $this->setCache($cache_key, $query, $this->getCacheGroup(), $expiration ?? MINUTE_IN_SECONDS);
                wp_reset_postdata();
            }
        }

        return $query;
    }

    /**
     * Return an array of WP_Query post ID's.
     * @param string $post_type
     * @param array $args Additional WP_Query parameters.
     * @param int|null $expiration Deprecated, use `$this->wpQueryGetAllIdsCached()` instead.
     * @return int[] An array of all post type IDs
     */
    protected function wpQueryGetAllIds(string $post_type, array $args = [], ?int $expiration = null): array
    {
        if (is_int($expiration)) {
            _deprecated_argument(
                __FUNCTION__,
                '2.4.0',
                esc_html__( // phpcs:disable Generic.Files.LineLength.TooLong
                    'Usage of expiration is deprecated. Use `WpQueryTrait::wpQueryGetAllIdsCached` if cache is desired.',
                    'wp-utilities'
                ) // phpcs:enable
            );
        }

        return $this->wpGetAllIds([$this, 'wpQuery'], $post_type, $args, $expiration);
    }

    /**
     * Return an array of cached WP_Query post ID's.
     * @param string $post_type
     * @param array $args Additional WP_Query parameters.
     * @param int|null $expiration The expiration time, defaults to `MINUTE_IN_SECONDS`.
     * @return int[] An array of all post type IDs
     */
    protected function wpQueryGetAllIdsCached(string $post_type, array $args = [], ?int $expiration = null): array
    {
        return $this->wpGetAllIds([$this, 'wpQueryCached'], $post_type, $args, $expiration);
    }

    /**
     * Return an array of cached WP_Query post ID's. This will do a large loop to get *all* posts within
     * the `$post_type`. So when you are aware of thousands of posts, and might need them all use this method.
     * @param callable $callback
     * @param string $post_type
     * @param array $args Additional WP_Query parameters.
     * @param int|null $expiration The expiration time, defaults to `MINUTE_IN_SECONDS`.
     * @return int[] An array of all post type IDs
     * @SuppressWarnings(PHPMD.UndefinedVariable)
     */
    private function wpGetAllIds(
        callable $callback,
        string $post_type,
        array $args = [],
        ?int $expiration = null
    ): array {
        $paged = 0;
        $post_ids = [];
        do {
            $defaults = [
                'fields' => 'ids',
                'posts_per_page' => 100,
                'no_found_rows' => false, // We need pagination & the count for all posts found.
                'paged' => $paged++, // phpcs:ignore
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,
            ];
            $query = call_user_func($callback, $post_type, wp_parse_args($args, $defaults), $expiration);
            if ($query instanceof WP_Query && $query->have_posts()) {
                foreach ($query->posts as $id) {
                    $post_ids[] = $id;
                }
            }
        } while ($query->max_num_pages > $paged);

        return $post_ids;
    }

    /**
     * Get the default WP_Query arguments and allow them to be filtered
     * @param string|null $post_type The post_type
     * @return array
     */
    private function getDefaults(?string $post_type = null): array
    {
        return array_filter(
            apply_filters(
                sprintf('%s/wp_query_defaults', Plugin::TAG),
                [
                    'post_type' => $post_type,
                    'posts_per_page' => 100,
                    'post_status' => ['publish', 'future', 'draft'],
                    'ignore_sticky_posts' => true,
                    'no_found_rows' => true,
                ],
                $post_type
            )
        );
    }
}
