<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\PostTypes;

use TheFrosty\WpUtilities\Exceptions\Exception;
use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;
use function add_action;
use function did_action;
use function register_post_type;

/**
 * Class AbstractPostType.
 * @package TheFrosty\WpUtilities\PostTypes
 */
abstract class AbstractPostType extends AbstractHookProvider
{

    use PostTypeTrait;

    public const array|string|null POST_TYPE = null;
    public const string|null SLUG = null;
    public const string|null URL_SLUG = null;

    /**
     * PostType names array.
     * @var string[] $names
     */
    private array $names = [];

    /**
     * Post Type args array.
     * @var string[] $args
     */
    private array $args = [];

    /**
     * Post Type labels array.
     * @var string[] $labels
     */
    private array $labels = [];

    /**
     * AbstractPostType constructor.
     * @throws Exception When the constants aren't overridden in their parent classes.
     */
    public function __construct()
    {
        if (static::POST_TYPE === null || static::SLUG === null) {
            throw new Exception('Undefined constants.');
        }

        if ($this->names === [] || $this->args === [] || $this->labels === []) {
            throw new Exception('Required class properties not set.');
        }
    }

    /**
     * Register the post type with WordPress on `init` (immediately when `init` is running or has already fired).
     * @return void
     */
    protected function registerPostType(): void
    {
        if (did_action('init')) {
            $this->registerPostTypeNow();

            return;
        }

        add_action('init', [$this, 'registerPostTypeNow'], 10, 0);
    }

    /**
     * Register the post type with WordPress now.
     * @internal Called by `registerPostType()`; public only so it can be hooked.
     * @return void
     */
    public function registerPostTypeNow(): void
    {
        register_post_type((string)static::POST_TYPE, $this->getRegistrationArgs());
    }

    /**
     * Get the arguments passed to `register_post_type()`: the args, plus labels generated from the names
     * (overridden by any labels that were set).
     * @return array
     */
    protected function getRegistrationArgs(): array
    {
        $args = $this->getArgs();
        $args['labels'] ??= $this->buildLabels($this->getNames(), $this->getLabels());

        return $args;
    }

    /**
     * Gets the Post Type name(s).
     * @return string[]
     */
    protected function getNames(): array
    {
        return $this->names;
    }

    /**
     * Set's the Post Type name(s).
     * @param array $names
     */
    protected function setNames(array $names): void
    {
        $this->names = $this->setDefaultNames($names);
    }

    /**
     * Gets the Post Type args.
     * @return string[]
     */
    protected function getArgs(): array
    {
        return $this->args;
    }

    /**
     * Sets the Post Type args.
     * @param array $args
     */
    protected function setArgs(array $args): void
    {
        $this->args = $this->setDefaultArgs($args);
    }

    /**
     * Gets the Post Type labels.
     * @return string[]
     */
    protected function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * Sets the Post Type labels.
     * @param array $labels
     */
    protected function setLabels(array $labels): void
    {
        $this->labels = $labels;
    }
}
