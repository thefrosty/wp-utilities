<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin;

use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;

/**
 * Class disablePluginUpdateCheck
 * @package TheFrosty\WpUtilities\WpAdmin
 */
class DisablePluginUpdateCheck extends AbstractHookProvider
{
    private const WP_ORG_UPDATE_CHECK = 'https://api.wordpress.org/plugins/update-check/';

    /**
     * Add class hooks
     */
    public function addHooks(): void
    {
        $this->addFilter('http_request_args', [$this, 'httpRequestRemovePluginBasename'], 10, 2);
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
        if (\strpos($url, self::WP_ORG_UPDATE_CHECK) === 0) {
            if (!empty($args['body']['plugins'])) {
                $plugins = \json_decode($args['body']['plugins'], true);
                unset($plugins['plugins'][$this->getPlugin()->getBasename()]);
                $args['body']['plugins'] = \wp_json_encode($plugins);
            }
        }
        return $args;
    }

    /**
     * Remove this plugin from the transient value via the core filter.
     * @link https://gist.github.com/rniswonger/ee1b30e5fd3693bb5f92fbcfabe1654d
     * @param mixed $value
     * @return mixed
     */
    protected function transientRemovePluginBasename($value)
    {
        if (isset($value) && \is_object($value) && (!empty($value->response) && \is_array($value->response))) {
            unset($value->response[$this->getPlugin()->getBasename()]);
        }

        return $value;
    }
}
