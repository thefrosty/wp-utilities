<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin;

use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;
use TheFrosty\WpUtilities\Plugin\Plugin;
use WP_Error;

/**
 * Class DisablePluginUpdateCheck
 * @package TheFrosty\WpUtilities\WpAdmin
 */
class DisablePluginUpdateCheck extends AbstractHookProvider
{

    private const BYPASS_KEY = 'bypass-http-request';
    private const WP_ORG_UPDATE_CHECK = 'https://api.wordpress.org/plugins/update-check/';
    private const WP_ORG_PLUGINS_INFO = 'https://api.wordpress.org/plugins/info/';

    /**
     * Add class hooks
     */
    public function addHooks(): void
    {
        $this->addFilter('http_request_args', [$this, 'httpRequestRemovePluginBasename'], 10, 2);
        $this->addFilter('pre_http_request', [$this, 'bypassHttpRequest'], 10, 3);
        $this->addFilter('site_transient_update_plugins', [$this, 'transientRemovePluginBasename']);
    }

    /**
     * Disable plugin update checks for the current plugin
     * @link https://stackoverflow.com/a/39217270/558561
     * @param array $args An array of HTTP request arguments.
     * @param string $url The request URL.
     * @return array
     */
    protected function httpRequestRemovePluginBasename(array $args, string $url): array
    {
        if (\str_starts_with($url, self::WP_ORG_UPDATE_CHECK)) {
            if (!empty($args['body']['plugins'])) {
                $plugins = \json_decode($args['body']['plugins'], true);
                unset($plugins['plugins'][$this->getPlugin()->getBasename()]);
                $args['body']['plugins'] = \wp_json_encode($plugins);
            }
        }
        if (
            \str_starts_with($url, self::WP_ORG_PLUGINS_INFO) &&
            \is_string(\parse_url($url, \PHP_URL_QUERY))
        ) {
            \parse_str(\parse_url($url, \PHP_URL_QUERY), $result);
            if (
                !empty($result['request']) &&
                !empty($result['request']['slug']) &&
                $result['request']['slug'] === $this->getPlugin()->getSlug() &&
                \__return_true() === false // Re-enable when we can validate this works.
            ) {
                $args[self::BYPASS_KEY] = $this->getPlugin()->getSlug();
            }
        }

        return $args;
    }

    /**
     * Attempt to bypass the HTTP Request if the bypass key is present.
     * @todo I was initially investigating certain plugins throwing 404's when they should not be called by the
     * info API endpoint, but don't see an easy way to validate this, so I will "disable" the key in the
     * `httpRequestRemovePluginBasename` method.
     * @param false|array|WP_Error $preempt A preemptive return value of an HTTP request. Default false.
     * @param array $parsed_args HTTP request arguments.
     * @param string $url The request URL.
     * @return mixed
     */
    protected function bypassHttpRequest($preempt, array $parsed_args, string $url): mixed
    {
        if (
            str_starts_with($url, self::WP_ORG_PLUGINS_INFO) &&
            !empty($parsed_args[self::BYPASS_KEY]) &&
            $parsed_args[self::BYPASS_KEY] === $this->getPlugin()->getSlug()
        ) {
            return new WP_Error(
                'bypass_http_request',
                \sprintf(
                    \esc_html__(
                        'The plugin `%s` has requested to bypass api.wp.org/plugin/info.',
                        'wp-utilities'
                    ),
                    $this->getPlugin()->getSlug(),
                ),
                ['status' => \WP_Http::NOT_FOUND]
            );
        }

        return $preempt;
    }

    /**
     * Remove this plugin from the transient value via the core filter.
     * Ignoring those looking for updating via GitHub Updater.
     * @link https://gist.github.com/rniswonger/ee1b30e5fd3693bb5f92fbcfabe1654d
     * @param mixed $value
     * @return mixed
     */
    protected function transientRemovePluginBasename(mixed $value): mixed
    {
        if (isset($value) && \is_object($value) && (!empty($value->response) && \is_array($value->response))) {
            if (!$this->hasGitHubUpdater()) {
                unset($value->response[$this->getPlugin()->getBasename()]);
            }
        }

        return $value;
    }

    /**
     * Does the current plugin use GitHub Updater?
     * @link https://github.com/afragen/github-updater
     * @return bool
     */
    private function hasGitHubUpdater(): bool
    {
        $key = \sprintf('%s/get_file_data_%s', Plugin::TAG, \sanitize_key($this->getPlugin()->getSlug()));
        $data = \wp_cache_get($key, 'wp-utilities');
        if ($data === false) {
            $data = \get_file_data($this->getPlugin()->getFile(), ['GitHubPluginURI' => 'GitHub Plugin URI'], 'plugin');
            \wp_cache_set($key, $data, 'wp-utilities', \DAY_IN_SECONDS);
        }

        return \is_array($data) && !empty($data['GitHubPluginURI']);
    }
}
