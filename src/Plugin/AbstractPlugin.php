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
    const DEFAULT_TAG = 'init';

    /**
     * Plugin basename.
     *
     * Ex: plugin-name/plugin-name.php
     *
     * @var string $basename
     */
    private $basename;

    /**
     * Absolute path to the main plugin directory.
     *
     * @var string $directory
     */
    private $directory;

    /**
     * Init object.
     *
     * @var Init $init
     */
    private $init;

    /**
     * Absolute path to the main plugin file.
     *
     * @var string $file
     */
    private $file;

    /**
     * Plugin identifier.
     *
     * @var string $slug
     */
    private $slug;

    /**
     * Array of action tags that have been registered.
     *
     * @var array $tags
     */
    private $tags = [];

    /**
     * URL to the main plugin directory.
     *
     * @var string $url
     */
    private $url;

    /**
     * Retrieve the absolute path for the main plugin file.
     *
     * {@inheritdoc}
     */
    public function getBasename() : string
    {
        return $this->basename;
    }

    /**
     * Set the plugin basename.
     *
     * {@inheritdoc}
     * @return $this
     */
    public function setBasename(string $basename) : PluginInterface
    {
        $this->basename = $basename;

        return $this;
    }

    /**
     * Retrieve the plugin directory.
     *
     * {@inheritdoc}
     */
    public function getDirectory() : string
    {
        return $this->directory;
    }

    /**
     * Set the plugin's directory.
     *
     * {@inheritdoc}
     * @return $this
     */
    public function setDirectory(string $directory) : PluginInterface
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
    public function getPath(string $path = '') : string
    {
        return $this->directory . \ltrim($path, '/');
    }

    /**
     * Return the Init object.
     *
     * {@inheritdoc}
     */
    public function getInit() : Init
    {
        return $this->init;
    }

    /**
     * {@inheritdoc}
     * @return $this
     */
    public function setInit(Init $init) : PluginInterface
    {
        $this->init = $init;

        return $this;
    }

    /**
     * Retrieve the absolute path for the main plugin file.
     *
     * @return string
     */
    public function getFile() : string
    {
        return $this->file;
    }

    /**
     * Set the path to the main plugin file.
     *
     * {@inheritdoc}
     * @return $this
     */
    public function setFile(string $file) : PluginInterface
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Retrieve the plugin identifier.
     *
     * {@inheritdoc}
     */
    public function getSlug() : string
    {
        return $this->slug;
    }

    /**
     * Set the plugin identifier.
     *
     * {@inheritdoc}
     * @return $this
     */
    public function setSlug(string $slug) : PluginInterface
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Retrieve the URL for a file in the plugin.
     *
     * {@inheritdoc}
     */
    public function getUrl(string $path = '') : string
    {
        return $this->url . \ltrim($path, '/');
    }

    /**
     * Set the URL for plugin directory root.
     *
     * {@inheritdoc}
     * @return $this
     */
    public function setUrl(string $url) : PluginInterface
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
    public function add(WpHooksInterface $wp_hooks) : PluginInterface
    {
        $this->getInit()->register($wp_hooks, $this);

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
        string $tag = null,
        int $priority = null,
        bool $admin_only = null
    ) : PluginInterface {
        $tag = $tag ?? self::DEFAULT_TAG;
        $this->setTags($tag);
        \add_action($tag, function () use ($wp_hook, $admin_only) {
            if ($admin_only === true && \is_admin()) {
                $this->initiateWpHooks($wp_hook);
            } elseif ($admin_only === false && ! \is_admin()) {
                $this->initiateWpHooks($wp_hook);
            } elseif ($admin_only === null) {
                $this->initiateWpHooks($wp_hook);
            }
        }, ($priority ?? 10) - 2);

        return $this;
    }

    /**
     * Initialize the Init `WpHooksInterface` objects.
     */
    public function initialize()
    {
        $this->getInit()->initialize();
    }

    /**
     * Initialize the late hook provider when it's been registered on an action hook.
     *
     * @param string $wp_hook String value of the WpHooksInterface hook provider.
     */
    private function initiateWpHooks(string $wp_hook)
    {
        $wp_hooks = new $wp_hook();
        if (! ($wp_hooks instanceof WpHooksInterface)) {
            throw new \InvalidArgumentException(
                'Expected a . ' . WpHooksInterface::class . ', got: ' . \get_class($wp_hook)
            );
        }
        /** @var WpHooksInterface $wp_hooks */
        $this->getInit()->register($wp_hooks, $this);
        $this->initializeOnHook();
    }

    /**
     * Iterate over all registered tag's and re-initialize the WpHooksInterface objects to
     * initiate their hooks on the appropriate registered action (tag).
     */
    private function initializeOnHook()
    {
        call_user_func_array(function ($tag) {
            \add_action($tag, function () {
                $this->getInit()->initialize();
            });
        }, $this->getTags());
    }

    /**
     * Return all the names of the action tags registered.
     *
     * @return array
     */
    private function getTags() : array
    {
        return array_unique($this->tags);
    }

    /**
     * Set the tag names for initialization later.
     *
     * @param string $tag The name of the action.
     */
    private function setTags(string $tag)
    {
        $this->tags[] = $tag;
    }
}
