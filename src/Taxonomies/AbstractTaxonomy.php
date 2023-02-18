<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Taxonomies;

use TheFrosty\WpUtilities\Exceptions\Exception;
use PostTypes\Taxonomy;
use ReflectionClass;

/**
 * Class AbstractTaxonomy.
 * @package TheFrosty\WpUtilities\Taxonomies
 */
abstract class AbstractTaxonomy
{

    use TaxonomyTrait;

    public const TAXONOMY_TYPE = null;
    public const POST_TYPE = null;
    public const SLUG = null;
    public const URL_SLUG = null;

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
        return \array_filter((new ReflectionClass($argument))->getConstants(), static function (string $key): bool {
            return \strpos($key, 'TERM_') !== false;
        }, \ARRAY_FILTER_USE_KEY);
    }

    /**
     * Register the Taxonomy.
     * @link https://posttypes.jjgrainger.co.uk/taxonomies
     * @return Taxonomy
     */
    protected function registerTaxonomy(): Taxonomy
    {
        $taxonomy = new Taxonomy($this->getNames(), $this->getArgs(), $this->getLabels());
        $this->registerPostTypes($taxonomy);
        $taxonomy->register();

        return $taxonomy;
    }

    /**
     * Gets the Taxonomy name(s).
     * @return array
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
     * @return array
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
     * Ges the Taxonomy labels.
     * @return array
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
     * Register the taxonomy to a post type.
     * @param Taxonomy $taxonomy Taxonomy object.
     */
    private function registerPostTypes(Taxonomy $taxonomy): void
    {
        $post_types = static::POST_TYPE;
        if (!\is_array($post_types)) {
            $post_types = [$post_types];
        }

        $taxonomy->posttype($post_types);
    }
}
