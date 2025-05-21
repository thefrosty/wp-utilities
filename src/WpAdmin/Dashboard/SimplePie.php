<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin\Dashboard;

use TheFrosty\WpUtilities\Api\Hash;
use function delete_transient;
use function fetch_feed;
use function function_exists;
use function is_array;
use function is_wp_error;

/**
 * Trait SimplePie
 * @package TheFrosty\WpUtilities\WpAdmin\Dashboard
 */
trait SimplePie
{

    use Hash;

    /**
     * Fetch RSS items from the feed.
     * @param int $max Number of items to fetch.
     * @param string $url The feed to fetch.
     * @return array|\SimplePie\Item[] Empty array on failure, Array of \SimplePie\Item's on success.
     */
    public function getFeedItems(int $max, string $url): array
    {
        if (!function_exists('fetch_feed')) {
            include_once ABSPATH . WPINC . DIRECTORY_SEPARATOR . 'feed.php';
        }

        /**
         * @param string $url
         * @return array|\SimplePie\SimplePie
         */
        $get_pie = static function (string $url) {
            $pie = fetch_feed($url);
            // Bail if feed doesn't work
            if (!($pie instanceof \SimplePie\SimplePie) || is_wp_error($pie)) {
                return [];
            }

            return $pie;
        };

        $pie = $get_pie($url);
        // Bail if feed doesn't work
        if (!($pie instanceof \SimplePie\SimplePie)) {
            return [];
        }

        /**
         * @param array|\SimplePie\SimplePie $pie
         * @return array|null $get_items
         */
        $get_items = static function ($pie) use ($max) {
            return ($pie instanceof \SimplePie\SimplePie) ? $pie->get_items(0, $pie->get_item_quantity($max)) : [];
        };

        $pie_items = $get_items($pie);
        // If the feed was erroneous
        if (!$pie_items) {
            $key = $this->getHashedKey($url);
            delete_transient('feed_' . $key);
            delete_transient('feed_mod_' . $key);
            $pie = $get_pie($url);
            $pie_items = $get_items($pie);
        }

        return is_array($pie_items) ? $pie_items : [];
    }
}
