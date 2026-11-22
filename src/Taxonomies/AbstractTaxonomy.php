<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Taxonomies;

use ReflectionClass;
use TheFrosty\WpUtilities\Exceptions\Exception;
use function add_action;
use function array_filter;
use function did_action;
use function register_taxonomy;
use function str_contains;
use const ARRAY_FILTER_USE_KEY;

/**
 * Class AbstractTaxonomy.
 * @package TheFrosty\WpUtilities\Taxonomies
 */
abstract class AbstractTaxonomy
{

    use TaxonomyTrait;

    public const string|null TAXONOMY_TYPE = null;
    public const array|string|null POST_TYPE = null;
    public const string|null SLUG = null;
    public const string|null URL_SLUG = null;

    /**
     * Taxonomy names array.
     * @var string[] $names
     */
    private array $names = [];

    /**
     * Taxonomy args array.
     * @var string[] $args
     */
    private array $args = [];

    /**
     * Taxonomy labels array.
     * @var string[] $labels
     */
    private array $labels = [];

    /**
     * AbstractTaxonomy constructor.
     * @throws Exception When the constants aren't overridden in their parent classes.
     */
    public function __construct()
    {
        if (static::TAXONOMY_TYPE === null || static::POST_TYPE === null || static::SLUG === null) {
            throw new Exception('Undefined constants.');
        }

        if ($this->names === [] || $this->args === [] || $this->labels === []) {
            throw new Exception('Required class properties not set.');
        }
    }

    /**
     * Get all `TERM_` prefixed constants in an array key/value pair.
     * @param object $argument
     * @return array
     */
    public static function getTerms(object $argument): array
    {
        return array_filter(
            new ReflectionClass($argument)->getConstants(),
            static fn(string $key): bool => str_contains($key, 'TERM_'),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Register the taxonomy with WordPress on `init` priority 9, so it exists before post types register at 10
     * (immediately when `init` is running or has already fired).
     * @return void
     */
    protected function registerTaxonomy(): void
    {
        if (did_action('init')) {
            $this->registerTaxonomyNow();

            return;
        }

        add_action('init', [$this, 'registerTaxonomyNow'], 9, 0);
    }

    /**
     * Register the taxonomy with WordPress now, for each of the `POST_TYPE` object types.
     * @internal Called by `registerTaxonomy()`; public only so it can be hooked.
     * @return void
     */
    public function registerTaxonomyNow(): void
    {
        register_taxonomy((string)static::TAXONOMY_TYPE, $this->getObjectTypes(), $this->getRegistrationArgs());
    }

    /**
     * Get the arguments passed to `register_taxonomy()`: the args, plus labels generated from the names
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
     * Gets the Taxonomy name(s).
     * @return string[]
     */
    protected function getNames(): array
    {
        return $this->names;
    }

    /**
     * Sets the Taxonomy name(s).
     * @param array $names
     */
    protected function setNames(array $names): void
    {
        $this->names = $this->setDefaultNames($names);
    }

    /**
     * Gets the Taxonomy args.
     * @return string[]
     */
    protected function getArgs(): array
    {
        return $this->args;
    }

    /**
     * Sets the Taxonomy args.
     * @param array $args
     */
    protected function setArgs(array $args): void
    {
        $this->args = $this->setDefaultArgs($args);
    }

    /**
     * Gets the Taxonomy labels.
     * @return string[]
     */
    protected function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * Set's the Taxonomy labels.
     * @param array $labels
     */
    protected function setLabels(array $labels): void
    {
        $this->labels = $labels;
    }

    /**
     * The post type(s) the taxonomy is registered for.
     * @return string[]
     */
    private function getObjectTypes(): array
    {
        return (array)static::POST_TYPE;
    }
}
