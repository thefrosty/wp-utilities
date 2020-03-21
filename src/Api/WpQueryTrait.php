<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

use TheFrosty\WpUtilities\Plugin\Plugin;
use WP_Query;

/**
 * Trait WpQueryTrait.
 *
 * @package TheFrosty\WpUtilities\Api
 */
trait WpQueryTrait
{

    use WpCacheTrait;

    /**
     * Return a new WP_Query object.
     *
     * @param string $post_type The post type to query.
     * @param array $args Additional WP_Query parameters
     *
     * @return WP_Query
     */
    protected function wpQuery(string $post_type, array $args = []): WP_Query
    {
        $defaults = [
            'post_type' => $post_type,
            'posts_per_page' => 99,
            'post_status' => ['publish', 'pending', 'future', 'draft'],
            'ignore_sticky_posts' => true,
            'no_found_rows' => true,
        ];

        return new WP_Query(\wp_parse_args($args, $defaults));
    }

    /**
     * Return a cached WP_Query object.
     *
     * @param string $post_type
     * @param array $args Additional WP_Query parameters.
     * @param int|null $expiration The expiration time, defaults to `MINUTE_IN_SECONDS`.
     *
     * @return WP_Query
     */
    protected function wpQueryCached(string $post_type, array $args = [], ?int $expiration = null): WP_Query
    {
        $cache_key = $this->getHashedKey(
            \sprintf('%s/query_%s_by_%s', Plugin::TAG, $post_type, \md5(\json_encode($args)))
        );
        $query = $this->getCache($cache_key);
        if ($query === false || !($query instanceof WP_Query)) {
            $query = $this->wpQuery($post_type, $args);
            if ($query->have_posts()) {
                $this->setCache($cache_key, $query, $this->getCacheGroup(), $expiration ?? \MINUTE_IN_SECONDS);
                \wp_reset_postdata();
            }
        }

        return $query;
    }

    /**
     * Return an array of cached WP_Query post ID's. This will do a large loop to get *all* posts within
     * the `$post_type`. So when you are aware of thousands of posts, and might need them all use this method.
     *
     * @param string $post_type
     * @param array $args Additional WP_Query parameters.
     *
     * @return array An array of all post type IDs
     */
    protected function wpQueryGetAllIds(string $post_type, array $args = []): array
    {
        static $paged;
        $post_ids = [];
        do {
            $paged++; // phpcs:ignore
            $defaults = [
                'fields' => 'ids',
                'posts_per_page' => 100,
                'no_found_rows' => false, // We need pagination & the count for all posts found.
                'paged' => $paged,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,
            ];
            $query = $this->wpQueryCached($post_type, \wp_parse_args($args, $defaults));
            if ($query->have_posts()) {
                foreach ($query->posts as $id) {
                    $post_ids[] = $id;
                }
            }
        } while ($query->max_num_pages > $paged);

        return $post_ids;
    }
}
