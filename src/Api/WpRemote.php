<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

use TheFrosty\WpUtilities\Plugin\Plugin;
use WP_Error;
use function apply_filters;
use function array_filter;
use function array_merge;
use function esc_attr;
use function esc_url;
use function get_bloginfo;
use function is_wp_error;
use function sprintf;
use function strtolower;
use function ucfirst;
use function wp_remote_get;
use function wp_remote_post;
use const DAY_IN_SECONDS;

/**
 * Trait WpRemote
 * @package TheFrosty\WpUtilities\Api
 */
trait WpRemote
{

    use TransientsTrait;

    /**
     * Get the remote GET request body.
     * @param string $url The URL to make a remote request too.
     * @param array $args Additional request args.
     * @param string $method The method type (supports GET & POST only).
     * @return mixed
     */
    public function retrieveBody(string $url, array $args = [], string $method = 'GET'): mixed
    {
        if (!in_array($method, ['GET', 'POST'], true)) {
            return false;
        }
        $function = sprintf('wpRemote%1$s', ucfirst(strtolower($method)));
        $response = wp_remote_retrieve_body($this->$function(esc_url($url), $this->buildRequestArgs($args)));
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
    ): mixed {
        $transient = $this->getTransientKey($url);
        $body = $this->getTransient($transient);
        if (empty($body)) {
            if ($user_agent !== null) {
                $args = [
                    'user-agent' => esc_attr(
                        sprintf(
                            '%s/%s; %s',
                            $user_agent,
                            $version ?? $GLOBALS['wp_version'], // phpcs:ignore
                            get_bloginfo('url')
                        )
                    ),
                ];
            }
            $body = $this->retrieveBody($url, $args ?? []);
            if (!empty($body)) {
                $this->setTransient($transient, $body, $expiration ?? DAY_IN_SECONDS);
            }

            return $body;
        }

        return $body;
    }

    /**
     * Return a remote GET request.
     * @param string $url
     * @param array $args
     * @return array|WP_Error
     */
    public function wpRemoteGet(string $url, array $args = []): WP_Error|array
    {
        return wp_remote_get(esc_url($url), $this->buildRequestArgs($args));
    }

    /**
     * Return a remote POST request.
     * @param string $url
     * @param array $args
     * @return array|WP_Error
     */
    public function wpRemotePost(string $url, array $args = []): WP_Error|array
    {
        return wp_remote_post(esc_url($url), $this->buildRequestArgs($args));
    }

    /**
     * Build Request args.
     * @param array $args
     * @return array
     */
    private function buildRequestArgs(array $args): array
    {
        $defaults = [
            'timeout' => apply_filters(Plugin::TAG . 'wp_remote_timeout', 2),
        ];

        return array_filter(array_merge($defaults, $args));
    }
}
