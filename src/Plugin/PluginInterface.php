<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

/**
 * Plugin interface.
 *
 * @package TheFrosty\WpUtilities\Plugin
 */
interface PluginInterface
{

    /**
     * Retrieve the relative path to the main plugin file from the main plugin
     * directory.
     *
     * @return string
     */
    public function getBasename() : string;

    /**
     * Set the plugin basename.
     *
     * @param string $basename Relative path from the main plugin directory.
     * @return $this
     */
    public function setBasename(string $basename) : self;

    /**
     * Retrieve the plugin directory.
     *
     * @return string
     */
    public function getDirectory() : string;

    /**
     * Set the plugin's directory.
     *
     * @param string $directory Absolute path to the main plugin directory.
     * @return $this
     */
    public function setDirectory(string $directory) : self;

    /**
     * Retrieve the path to a file in the plugin.
     *
     * @param string $path Optional. Path relative to the plugin root.
     * @return string
     */
    public function getPath(string $path = '') : string;

    /**
     * Gets to Init object.
     *
     * @return Init
     */
    public function getInit() : Init;

    /**
     * Sets to Init object.
     *
     * @param Init $init
     * @return $this
     */
    public function setInit(Init $init) : self;

    /**
     * Retrieve the absolute path for the main plugin file.
     *
     * @return string
     */
    public function getFile() : string;

    /**
     * Set the path to the main plugin file.
     *
     * @param string $file Absolute path to the main plugin file.
     * @return $this
     */
    public function setFile(string $file) : self;

    /**
     * Retrieve the plugin identifier.
     *
     * @return string
     */
    public function getSlug() : string;

    /**
     * Set the plugin identifier.
     *
     * @param string $slug Plugin identifier.
     * @return $this
     */
    public function setSlug(string $slug) : self;

    /**
     * Retrieve the URL for a file in the plugin.
     *
     * @param string $path Optional. Path relative to the plugin root.
     * @return string
     */
    public function getUrl(string $path = '') : string;

    /**
     * Set the URL for plugin directory root.
     *
     * @param string $url URL to the root of the plugin directory.
     * @return $this
     */
    public function setUrl(string $url) : self;

    /**
     * Register hooks for the plugin.
     *
     * @param WpHooksInterface $hooks Hook provider.
     * @return $this
     */
    public function add(WpHooksInterface $hooks) : self;

    /**
     * Register hooks for the plugin when a specific condition is met.
     *
     * @link https://codex.wordpress.org/Plugin_API/Action_Reference
     * @param string $wp_hook String value of the WpHooksInterface hook provider.
     * @param callable $condition The condition that needs to be met before adding the new hook provider.
     * @param string $tag Optional. The name of the action to which the $function_to_add is hooked. Default 'init'.
     * @param int $priority Optional. Used to specify the order in which the functions
     *                                  associated with a particular action are executed. Default 10.
     * @param bool $admin_only Optional. Whether to only initiate the object when `is_admin()` is true. Defaults to
     *     null.
     * @param array $args Argument unpacking via ... passed to the `$wp_hook` constructor.
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addOnCondition(
        string $wp_hook,
        callable $condition,
        string $tag = null,
        int $priority = null,
        bool $admin_only = null,
        array $args = []
    ) : self;

    /**
     * Register hooks for the plugin on a specific action tag.
     *
     * @link https://codex.wordpress.org/Plugin_API/Action_Reference
     * @param string $wp_hook String value of the WpHooksInterface hook provider.
     * @param string $tag Optional. The name of the action to which the $function_to_add is hooked. Default 'init'.
     * @param int $priority Optional. Used to specify the order in which the functions
     *                                  associated with a particular action are executed. Default 10.
     * @param bool $admin_only Optional. Whether to only initiate the object when `is_admin()` is true. Defaults to
     *     null.
     * @param array $args Argument unpacking via ... passed to the `$wp_hook` constructor.
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addOnHook(
        string $wp_hook,
        string $tag = null,
        int $priority = null,
        bool $admin_only = null,
        array $args = []
    ) : self;
}
