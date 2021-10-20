<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

/**
 * Plugin interface.
 *
 * @package TheFrosty\WpUtilities\Plugin
 */
interface PluginInterface
{
    public const DEFAULT_PRIORITY = 10;
    public const DEFAULT_TAG = 'init';

    /**
     * Retrieve the relative path to the main plugin file from the main plugin
     * directory.
     *
     * @return string
     */
    public function getBasename(): string;

    /**
     * Set the plugin basename.
     *
     * @param string $basename Relative path from the main plugin directory.
     * @return $this
     */
    public function setBasename(string $basename): self;

    /**
     * Retrieve the plugin directory.
     *
     * @return string
     */
    public function getDirectory(): string;

    /**
     * Set the plugin's directory.
     *
     * @param string $directory Absolute path to the main plugin directory.
     * @return $this
     */
    public function setDirectory(string $directory): self;

    /**
     * Retrieve the path to a file in the plugin.
     *
     * @param string $path Optional. Path relative to the plugin root.
     * @return string
     */
    public function getPath(string $path = ''): string;

    /**
     * Retrieve the TemplateLoaderInterface object.
     *
     * @return TemplateLoaderInterface
     */
    public function getTemplateLoader(): TemplateLoaderInterface;

    /**
     * Set the TemplateLoaderInterface object.
     *
     * @param TemplateLoaderInterface $template_loader
     *
     * @return $this
     */
    public function setTemplateLoader(TemplateLoaderInterface $template_loader): self;

    /**
     * Gets to Init object.
     *
     * @return Init
     */
    public function getInit(): Init;

    /**
     * Sets to Init object.
     *
     * @param Init $init
     * @return $this
     */
    public function setInit(Init $init): self;

    /**
     * Retrieve the absolute path for the main plugin file.
     *
     * @return string
     */
    public function getFile(): string;

    /**
     * Returns the time the file was last modified, or FALSE on failure.
     * The time is returned as a Unix timestamp, which is suitable for the date() function.
     *
     * @param string $path Optional. Path relative to the plugin root.
     * @return string|null
     */
    public function getFileTime(string $path = ''): ?string;

    /**
     * Set the path to the main plugin file.
     *
     * @param string $file Absolute path to the main plugin file.
     * @return $this
     */
    public function setFile(string $file): self;

    /**
     * Retrieve the plugin identifier.
     *
     * @return string
     */
    public function getSlug(): string;

    /**
     * Set the plugin identifier.
     *
     * @param string $slug Plugin identifier.
     * @return $this
     */
    public function setSlug(string $slug): self;

    /**
     * Retrieve the URL for a file in the plugin.
     *
     * @param string $path Optional. Path relative to the plugin root.
     * @return string
     */
    public function getUrl(string $path = ''): string;

    /**
     * Set the URL for plugin directory root.
     *
     * @param string $url URL to the root of the plugin directory.
     * @return $this
     */
    public function setUrl(string $url): self;

    /**
     * Register hooks for the plugin.
     *
     * @param WpHooksInterface $wp_hooks Hook provider.
     * @return $this
     */
    public function add(WpHooksInterface $wp_hooks): self;

    /**
     * Register hooks for the plugin when a specific condition is met.
     * This instantiates the `WpHooksInterface` if the condition is met as opposed to `addOnCondition()` which
     * instantiates the `WpHooksInterface` on the supplied $tag (action hook).
     *
     * @link https://codex.wordpress.org/Plugin_API/Action_Reference
     * @param string $wp_hook String value of the WpHooksInterface hook provider.
     * @param bool $condition The condition that needs to be met before adding the new hook provider.
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addIfCondition(
        string $wp_hook,
        bool $condition
    ): self;

    /**
     * Register hooks for the plugin when a specific condition is met.
     * This instantiates the `WpHooksInterface` if the condition is met as opposed to `addOnCondition()` which
     * instantiates the `WpHooksInterface` on the supplied $tag (action hook).
     *
     * @link https://codex.wordpress.org/Plugin_API/Action_Reference
     * @param string $wp_hook String value of the WpHooksInterface hook provider.
     * @param bool $condition The condition that needs to be met before adding the new hook provider.
     * @param string $deferred_tag The name of the action to deffer the $function is hooked. Default 'plugins_loaded'.
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addIfConditionDeferred(
        string $wp_hook,
        bool $condition,
        string $deferred_tag = 'plugins_loaded'
    ): self;

    /**
     * Register hooks for the plugin when a specific condition is met on a custom hook.
     * This instantiates the `WpHooksInterface` if the condition is met and the current action
     * is equal to the $tag (action hook).
     *
     * @link https://codex.wordpress.org/Plugin_API/Action_Reference
     * @param string $wp_hook String value of the WpHooksInterface hook provider.
     * @param callable $function The condition that needs to be met before adding the new hook provider.
     * @param array|null $func_args The parameters to be passed to the function, as an indexed array.
     * @param string|null $tag Optional. The name of the action to which the $function_to_add is hooked. Default 'init'.
     * @param int|null $priority Optional. Used to specify the order in which the functions
     *                                  associated with a particular action are executed. Default 10.
     * @param bool|null $admin_only Optional. Whether to only initiate the object when `is_admin()` is true. Defaults to
     *     null.
     * @param array $args Argument unpacking via ... passed to the `$wp_hook` constructor.
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addOnCondition(
        string $wp_hook,
        callable $function,
        ?array $func_args = null,
        ?string $tag = null,
        ?int $priority = null,
        ?bool $admin_only = null,
        array $args = []
    ): self;

    /**
     * Register hooks for the plugin when a specific condition is met on a custom hook.
     * This instantiates the `WpHooksInterface` if the condition is met and the current action
     * is equal to the $tag (action hook).
     *
     * @link https://codex.wordpress.org/Plugin_API/Action_Reference
     * @param string $wp_hook String value of the WpHooksInterface hook provider.
     * @param callable $function The condition that needs to be met before adding the new hook provider.
     * @param array|null $func_args The parameters to be passed to the function, as an indexed array.
     * @param string $deferred_tag The name of the action to deffer the $function is hooked. Default 'plugins_loaded'.
     * @param string|null $tag Optional. The name of the action to which the $function_to_add is hooked. Default 'init'.
     * @param int|null $priority Optional. Used to specify the order in which the functions
     *                                  associated with a particular action are executed. Default 10.
     * @param bool|null $admin_only Optional. Whether to only initiate the object when `is_admin()` is true. Defaults to
     *     null.
     * @param array $args Argument unpacking via ... passed to the `$wp_hook` constructor.
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addOnConditionDeferred(
        string $wp_hook,
        callable $function,
        ?array $func_args = null,
        string $deferred_tag = 'plugins_loaded',
        ?string $tag = null,
        ?int $priority = null,
        ?bool $admin_only = null,
        array $args = []
    ): self;

    /**
     * Register hooks for the plugin on a specific action tag.
     *
     * @link https://codex.wordpress.org/Plugin_API/Action_Reference
     * @param string $wp_hook String value of the WpHooksInterface hook provider.
     * @param string|null $tag Optional. The name of the action to which the $function_to_add is hooked. Default 'init'.
     * @param int|null $priority Optional. Used to specify the order in which the functions
     *                                  associated with a particular action are executed. Default 10.
     * @param bool|null $admin_only Optional. Whether to only initiate the object when `is_admin()` is true. Defaults to
     *     null.
     * @param array $args Argument unpacking via ... passed to the `$wp_hook` constructor.
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addOnHook(
        string $wp_hook,
        ?string $tag = null,
        ?int $priority = null,
        ?bool $admin_only = null,
        array $args = []
    ): self;
}
