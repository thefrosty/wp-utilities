<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

/**
 * Base plugin class.
 *
 * @package TheFrosty\WpUtilities\Plugin
 * @link https://github.com/johnpbloch/wordpress-dev
 */
abstract class AbstractPlugin implements PluginInterface
{
    public const DEFAULT_PRIORITY = 10;
    public const DEFAULT_TAG = 'init';

    /**
     * Plugin basename.
     *
     * Ex: plugin-name/plugin-name.php
     * @var string $basename
     */
    private string $basename;

    /**
     * Absolute path to the main plugin directory.
     * @var string $directory
     */
    private string $directory;

    /**
     * TemplateLoaderInterface object.
     * @var TemplateLoaderInterface $template_loader
     */
    private TemplateLoaderInterface $template_loader;

    /**
     * Init object.
     * @var Init $init
     */
    private Init $init;

    /**
     * Absolute path to the main plugin file.
     * @var string $file
     */
    private string $file;

    /**
     * Plugin identifier.
     * @var string $slug
     */
    private string $slug;

    /**
     * URL to the main plugin directory.
     * @var string $url
     */
    private string $url;

    /**
     * Retrieve the absolute path for the main plugin file.
     *
     * {@inheritdoc}
     */
    public function getBasename(): string
    {
        return $this->basename;
    }

    /**
     * Set the plugin basename.
     *
     * {@inheritdoc}
     * @return $this
     */
    public function setBasename(string $basename): PluginInterface
    {
        $this->basename = $basename;

        return $this;
    }

    /**
     * Retrieve the plugin directory.
     *
     * {@inheritdoc}
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * Set the plugin's directory.
     *
     * {@inheritdoc}
     * @return $this
     */
    public function setDirectory(string $directory): PluginInterface
    {
        $this->directory = \rtrim($directory, '/') . '/';

        return $this;
    }

    /**
     * Retrieve the path to a file in the plugin.
     *
     * {@inheritdoc}
     * @return string
     */
    public function getPath(string $path = ''): string
    {
        return $this->directory . \ltrim($path, '/');
    }

    /**
     * Retrieve the TemplateLoaderInterface object.
     *
     * {@inheritdoc}
     * @return TemplateLoaderInterface
     */
    public function getTemplateLoader(): TemplateLoaderInterface
    {
        return $this->template_loader;
    }

    /**
     * Set the TemplateLoaderInterface object.
     *
     * {@inheritdoc}
     * @return $this
     */
    public function setTemplateLoader(TemplateLoaderInterface $template_loader): PluginInterface
    {
        $this->template_loader = $template_loader;
        return $this;
    }

    /**
     * Return the Init object.
     *
     * {@inheritdoc}
     */
    public function getInit(): Init
    {
        return $this->init;
    }

    /**
     * {@inheritdoc}
     * @return $this
     */
    public function setInit(Init $init): PluginInterface
    {
        $this->init = $init;

        return $this;
    }

    /**
     * Retrieve the absolute path for the main plugin file.
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * Returns the time the file was last modified, or FALSE on failure.
     * The time is returned as a Unix timestamp, which is suitable for the date() function.
     *
     * @param string $path Optional. Path relative to the plugin root.
     * @return string|null
     */
    public function getFileTime(string $path = ''): ?string
    {
        $file_time = \filemtime($this->getPath($path));

        return $file_time ? \strval($file_time) : null;
    }

    /**
     * Set the path to the main plugin file.
     *
     * {@inheritdoc}
     * @return $this
     */
    public function setFile(string $file): PluginInterface
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Retrieve the plugin identifier.
     *
     * {@inheritdoc}
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * Set the plugin identifier.
     *
     * {@inheritdoc}
     * @return $this
     */
    public function setSlug(string $slug): PluginInterface
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Retrieve the URL for a file in the plugin.
     *
     * {@inheritdoc}
     */
    public function getUrl(string $path = ''): string
    {
        return $this->url . \ltrim($path, '/');
    }

    /**
     * Set the URL for plugin directory root.
     *
     * {@inheritdoc}
     * @return $this
     */
    public function setUrl(string $url): PluginInterface
    {
        $this->url = \rtrim($url, '/') . '/';

        return $this;
    }

    /**
     * Register a hook provider.
     *
     * {@inheritdoc}
     * @return $this
     */
    public function add(WpHooksInterface $wp_hooks): PluginInterface
    {
        $this->getInit()->register($wp_hooks, $this);

        return $this;
    }

    /**
     * Register a hook provider when a specific condition is met.
     *
     * {@inheritdoc}
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addIfCondition(string $wp_hook, bool $condition): PluginInterface
    {
        if ($condition && $this->classImplementsWpHooks($wp_hook)) {
            $this->getInit()->register(new $wp_hook(), $this);
        }

        return $this;
    }

    /**
     * Register a hook provider when a specific condition is met on a custom hook.
     *
     * {@inheritdoc}
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addOnCondition(
        string $wp_hook,
        callable $function,
        array $param_arr = [],
        string $tag = null,
        int $priority = null,
        bool $admin_only = null,
        array $args = []
    ): PluginInterface {
        $condition = empty($param_arr) ? \call_user_func($function) : \call_user_func_array($function, $param_arr);
        if ($condition && $this->classImplementsWpHooks($wp_hook)) {
            return $this->addOnHook($wp_hook, $tag, $priority, $admin_only, $args);
        }

        return $this;
    }

    /**
     * Register a hook provider on a specific action.
     *
     * {@inheritdoc}
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function addOnHook(
        string $wp_hook,
        ?string $tag = null,
        ?int $priority = null,
        ?bool $admin_only = null,
        array $args = []
    ): PluginInterface {
        $tag = $tag ?? self::DEFAULT_TAG;
        \add_action($tag, function () use ($wp_hook, $admin_only, $priority, $args, $tag) {
            $priority = ($priority ?? self::DEFAULT_PRIORITY) + 2;
            if ($admin_only === true && \is_admin()) {
                $this->initiateWpHooks($wp_hook, $priority, $args, $tag);
            } elseif ($admin_only === false && !\is_admin()) {
                $this->initiateWpHooks($wp_hook, $priority, $args, $tag);
            } elseif ($admin_only === null) {
                $this->initiateWpHooks($wp_hook, $priority, $args, $tag);
            }
        }, ($priority ?? self::DEFAULT_PRIORITY) - 2);

        return $this;
    }

    /**
     * Initialize the Init `WpHooksInterface` objects.
     */
    public function initialize(): void
    {
        $this->getInit()->initialize();
    }

    /**
     * Initialize the late hook provider when it's been registered on an action hook.
     *
     * @param string $wp_hook String value of the WpHooksInterface hook provider.
     * @param int|null $priority Optional. Used to specify the order in which the functions
     *                                  associated with a particular action are executed. Default 10.
     * @param array $args Argument unpacking via `...`.
     * @param string|null $tag The name of the action passed in from `addOnHook()`.
     * @SuppressWarnings(PHPMD.MissingImport)
     */
    private function initiateWpHooks(
        string $wp_hook,
        ?int $priority = null,
        array $args = [],
        ?string $tag = null
    ): void {
        $wp_hooks = empty($args) ? new $wp_hook() : new $wp_hook(...$args);
        if (!($wp_hooks instanceof WpHooksInterface)) {
            throw new \InvalidArgumentException(
                \sprintf('Expected a %s interface, but got %s', WpHooksInterface::class, $wp_hook)
            );
        }
        $this->getInit()->register($wp_hooks, $this);
        $this->initializeOnHook($tag ?? self::DEFAULT_TAG, $priority ?? self::DEFAULT_PRIORITY);
    }

    /**
     * Iterate over all registered tag's and re-initialize the WpHooksInterface objects to
     * initiate their hooks on the appropriate registered action (tag).
     *
     * @param string $tag The name of the action to which the $function_to_add is hooked.
     * @param int $priority Optional. Used to specify the order in which the functions
     *                                  associated with a particular action are executed. Default 10.
     */
    private function initializeOnHook(string $tag, int $priority): void
    {
        \call_user_func(function ($tag) use ($priority): void {
            \add_action($tag, function (): void {
                $this->getInit()->initialize();
            }, $priority + 2);
        }, $tag);
    }

    /**
     * Does the class implement the required `WpHooksInterface` class interface?
     *
     * @param string $wp_hook
     * @return bool
     */
    private function classImplementsWpHooks(string $wp_hook): bool
    {
        return \in_array(WpHooksInterface::class, \class_implements($wp_hook), true);
    }
}
