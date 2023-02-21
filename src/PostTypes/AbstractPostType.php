<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\PostTypes;

use Exception;
use PostTypes\PostType;
use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;

/**
 * Class AbstractPostType.
 * @package TheFrosty\WpUtilities\PostTypes
 */
abstract class AbstractPostType extends AbstractHookProvider
{

    use PostTypeTrait;

    public const POST_TYPE = null;
    public const SLUG = null;
    public const URL_SLUG = null;

    /**
     * PostType names array.
     * @var string[] $names
     */
    private array $names = [];

    /**
     * PostType args array.
     * @var string[] $args
     */
    private array $args = [];

    /**
     * PostType labels array.
     * @var string[] $labels
     */
    private array $labels = [];

    /**
     * AbstractTaxonomy constructor.
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
     * Register the Post Type.
     * @link https://posttypes.jjgrainger.co.uk/post-types/create-a-post-type
     * @return PostType
     */
    protected function registerPostType(): PostType
    {
        $post_type = new PostType($this->getNames(), $this->getArgs(), $this->getLabels());
        $post_type->register();

        return $post_type;
    }

    /**
     * Get's the Post Type name(s).
     * @return array
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
     * @return array
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
     * @return array
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
