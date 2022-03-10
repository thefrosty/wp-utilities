<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

use function apply_filters;
use function array_filter;
use function array_merge;
use function esc_url;
use function get_bloginfo;
use function get_transient;
use function is_wp_error;
use function sprintf;
use function wp_remote_get;
use const DAY_IN_SECONDS;

/**
 * Trait WpRemote
 * @package TheFrosty\WpUtilities\Api
 */
trait WpRemote
{

    use WpCacheTrait;

    /**
     * Get the remote GET request body.
     * @param string $url
     * @param array $args
     * @return mixed
     */
    public function retrieveBody(string $url, array $args = [])
    {
        $response = wp_remote_retrieve_body(wp_remote_get(esc_url($url), $this->buildRequestArgs($args)));
        if (!is_wp_error($response) && $response !== '') {
            $body = json_decode($response);
            if ($body === null) {
                return false;
            }
        }

        return $body ?? false;
    }

    /**
     * Get the remote GET request body cached.
     * @param string $url
     * @param int|null $expiration
     * @param string|null $user_agent
     * @param string|null $version
     * @return false|mixed
     */
    public function retrieveBodyCached(
        string $url,
        ?int $expiration = 0,
        ?string $user_agent = null,
        ?string $version = null
    ) {
        if ($version === null) {
            $version = $GLOBALS['wp_version'];
        }

        $key = $this->getHashedKey($url);
        $body = $this->getCache($key);
        if (empty($body)) {
            if ($user_agent !== null) {
                $args['user-agent'] = sprintf('%s/%s; %s', $user_agent, $version, get_bloginfo('url'));
            }
            $body = $this->retrieveBody($url, $args ?? []);
            if (empty($body)) {
                return false;
            }
            $this->setCache($key, $body, null, $expiration ?? DAY_IN_SECONDS);

            return $body;
        }

        return $body;
    }

    /**
     * Build Request args.
     * @param array $args
     * @return array
     */
    private function buildRequestArgs(array $args): array
    {
        $defaults = [
            'timeout' => apply_filters('cl_wp_remote_get_timeout', 15),
        ];

        return array_filter(array_merge($defaults, $args));
    }
}
