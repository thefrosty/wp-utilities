<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api\GitHub;

use AllowDynamicProperties;
use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;

/**
 * Class Updater
 *
 * GNU General Public License, Free Software Foundation
 * <http://creativecommons.org/licenses/GPL/2.0/>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * @package TheFrosty\WpUtilities\Api\GitHub
 */
#[AllowDynamicProperties]
class Updater implements WpHooksInterface
{
    use HooksTrait;

    private const VERSION = '2.0';

    /**
     * Updater config data array.
     * @var array $config
     * @access public
     */
    private array $config;

    /**
     * Missing required config data array.
     * @var array $missing_config any config that is missing from the initialization of this instance
     */
    private array $missing_config;

    /**
     * GitHub's response data.
     * @var array $github_data Temp data fetched from GitHub,
     * allows us to only load the data once per class instance
     */
    private array $github_data;

    /**
     * Updater constructor
     *
     * @param array $config the configuration required for the updater to work
     * @see hasMinimumConfig()
     */
    public function __construct(array $config = [])
    {
        $defaults = [
            'slug' => \plugin_basename(__FILE__),
            'proper_folder_name' => \dirname(\plugin_basename(__FILE__)),
            'sslverify' => true,
        ];
        $this->config = \wp_parse_args($config, $defaults);
        // if the minimum config isn't set, issue a warning and bail
        if (!$this->hasMinimumConfig()) {
            $message = \sprintf(
                'The `%s` was initialized without the minimum required configuration. The following params are missing: %s', // phpcs:ignore
                self::class,
                \implode(',', $this->missing_config)
            );
            \_doing_it_wrong(__CLASS__, $message, self::VERSION);
            return;
        }

        $this->setDefaults();
    }

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $this->addFilter('pre_set_site_transient_update_plugins', [$this, 'apiCheck']);
        $this->addFilter('plugins_api', [$this, 'getPluginInfo'], 10, 3);
        $this->addFilter('upgrader_post_install', [$this, 'upgraderPostInstall'], 10, 3);
        $this->addFilter('http_request_timeout', [$this, 'httpRequestTimeout']);
        $this->addFilter('http_request_args', [$this, 'httpRequestSslVerify'], 10, 2);
    }

    /**
     * Has the
     * @return bool
     */
    protected function hasMinimumConfig(): bool
    {
        $this->missing_config = [];
        $required_config_params = [
            'access_token',
            'api_url',
            'raw_url',
            'github_url',
            'zip_url',
            'requires',
            'tested',
            'readme',
        ];

        foreach ($required_config_params as $required_param) {
            if (empty($this->config[$required_param])) {
                $this->missing_config[] = $required_param;
            }
        }

        return empty($this->missing_config);
    }

    /**
     * Set config defaults.
     */
    protected function setDefaults(): void
    {
        if (!empty($this->config['custom_zip_url'])) {
            $this->config['zip_url'] = $this->config['custom_zip_url'];
        }

        if (!isset($this->config['new_version'])) {
            $this->config['new_version'] = $this->getNewVersionNumber();
        }

        if (!isset($this->config['last_updated'])) {
            $this->config['last_updated'] = $this->getDate();
        }

        if (!isset($this->config['description'])) {
            $this->config['description'] = $this->getDescription();
        }

        $plugin_data = $this->getPluginData();
        if (!isset($this->config['plugin_name'])) {
            $this->config['plugin_name'] = $plugin_data['Name'];
        }

        if (!isset($this->config['version'])) {
            $this->config['version'] = $plugin_data['Version'];
        }

        if (!isset($this->config['author'])) {
            $this->config['author'] = $plugin_data['Author'];
        }

        if (!isset($this->config['homepage'])) {
            $this->config['homepage'] = $plugin_data['PluginURI'];
        }

        if (!isset($this->config['readme'])) {
            $this->config['readme'] = 'README.md';
        }
    }

    /**
     * Callback for the http_request_timeout filter
     * @return int timeout value
     */
    protected function httpRequestTimeout(): int
    {
        return 2;
    }

    /**
     * Callback fn for the http_request_args filter
     * @param array $args
     * @param string $url
     * @return array
     */
    protected function httpRequestSslVerify(array $args, string $url): array
    {
        if ($this->config['zip_url'] === $url) {
            $args['sslverify'] = $this->config['sslverify'];
        }

        return $args;
    }

    /**
     * Hook into the plugin update check and connect to GitHub
     * @param mixed $value the plugin data transient
     * @return mixed|object $transient updated plugin data transient
     */
    protected function apiCheck($value)
    {
        if (!\is_object($value) || empty($value->response) || !\is_array($value->response) || empty($value->checked)) {
            return $value;
        }

        // check the version and decide if it's new
        $updated = \version_compare($this->config['new_version'], $this->config['version'], '>');

        if (!$updated) {
            return $value;
        }

        $response = [
            'new_version' => $this->config['new_version'],
            'slug' => $this->config['proper_folder_name'],
            'url' => $this->config['github_url'],
            'package' => $this->config['zip_url'],
        ];

        $value->response[$this->config['slug']] = (object)$response;

        return $value;
    }

    /**
     * Get Plugin info.
     *
     * @param false|object|array $result The result object or array. Default false.
     * @param string $action The type of information being requested from the Plugin Installation API.
     * @param object $args Plugin API arguments.
     * @return object
     */
    protected function getPluginInfo($result, string $action, $args)
    {
        if (!\is_object($args)) {
            return $result;
        }
        // Check if this call API is for the right plugin
        if (empty($args->slug) || $args->slug !== $this->config['slug']) {
            return $result;
        }

        $args->slug = $this->config['slug'];
        $args->plugin_name = $this->config['plugin_name'];
        $args->version = $this->config['new_version'];
        $args->author = $this->config['author'];
        $args->homepage = $this->config['homepage'];
        $args->requires = $this->config['requires'];
        $args->tested = $this->config['tested'];
        $args->downloaded = 0;
        $args->last_updated = $this->config['last_updated'];
        $args->sections = ['description' => $this->config['description']];
        $args->download_link = $this->config['zip_url'];

        return $args;
    }

    /**
     * Move & activate the plugin, echo the update message.
     *
     * @param bool $response Installation response.
     * @param array $hook_extra Extra arguments passed to hooked filters.
     * @param array $result Installation result data.
     * @return array|\WP_Error $result the result of the move
     */
    protected function upgraderPostInstall(bool $response, array $hook_extra, array $result)
    {
        global $wp_filesystem;

        // Move & Activate
        $proper_destination = \WP_PLUGIN_DIR . '/' . $this->config['proper_folder_name'];
        $wp_filesystem->move($result['destination'], $proper_destination);
        $result['destination'] = $proper_destination;
        $activate = \activate_plugin(\WP_PLUGIN_DIR . '/' . $this->config['slug']);
        if (\is_wp_error($activate)) {
            return $activate;
        }

        \esc_html_e('Plugin reactivated successfully.', 'wp-utilities');
        return $result;
    }

    /**
     * Get New Version from GitHub
     * @return string|null $version the version number
     */
    private function getNewVersionNumber(): ?string
    {
        $version = \get_site_transient(\md5($this->config['slug']) . '_new_version');
        if (empty($version) || !\is_array($version)) {
            $response = $this->remoteGet(\trailingslashit($this->config['raw_url']) . \basename($this->config['slug']));

            if (\is_wp_error($response)) {
                return null;
            }

            if (\is_array($response) && !empty($response['body'])) {
                \preg_match('/.*Version:\s*(.*)$/mi', $response['body'], $matches);
            }

            if (empty($matches[1])) {
                return null;
            }
            $version = $matches[1];

            // refresh every 12 hours
            if (!empty($version) && \is_numeric($version)) {
                \set_site_transient(\md5($this->config['slug']) . '_new_version', $version, \DAY_IN_SECONDS / 2);
            }
        }

        return \strval($version);
    }

    /**
     * Call our URL.
     * @param string $query
     * @return array|\WP_Error
     */
    private function remoteGet(string $query)
    {
        return \wp_remote_get($query, [
            'headers' => [
                'Authorization' => \sprintf('token %s', $this->config['access_token']),
            ],
            'sslverify' => $this->config['sslverify'],
        ]);
    }

    /**
     * Get the plugin data.
     * @return array $data the data
     */
    private function getPluginData(): array
    {
        if (!\function_exists('\get_plugin_data')) {
            include_once ABSPATH . '/wp-admin/includes/plugin.php';
        }
        return \get_plugin_data(\WP_PLUGIN_DIR . '/' . $this->config['slug']);
    }

    /**
     * Get update date.
     * @return string
     */
    private function getDate(): ?string
    {
        $data = $this->getGitHubData();
        return empty($data->updated_at) ? null : \date('Y-m-d', \strtotime($data->updated_at));
    }

    /**
     * Get plugin description
     * @return string
     */
    private function getDescription(): ?string
    {
        $data = $this->getGitHubData();
        return empty($data->description) ? null : \strval($data->description);
    }

    /**
     * Get GitHub Data from the specified repository
     * @return array|null $github_data the data
     */
    private function getGitHubData(): ?array
    {
        if (!empty($this->github_data) && \is_array($this->github_data)) {
            return $this->github_data;
        } else {
            $github_data = \get_site_transient(\md5($this->config['slug']) . '_github_data');

            if (empty($github_data) || !\is_array($github_data)) {
                $github_data = $this->remoteGet($this->config['api_url']);

                if (\is_wp_error($github_data)) {
                    return null;
                }

                $github_data = \json_decode($github_data['body'], true);

                // refresh every 12 hours
                \set_site_transient(\md5($this->config['slug']) . '_github_data', $github_data, \DAY_IN_SECONDS / 2);
            }

            // Store the data in this class instance for future calls
            $this->github_data = $github_data;
        }

        return $github_data;
    }
}
