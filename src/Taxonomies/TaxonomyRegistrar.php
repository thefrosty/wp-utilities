<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Taxonomies;

use TheFrosty\WpUtilities\Api\ObjectRegistrarManager;
use function apply_filters;
use function array_filter;
use const ARRAY_FILTER_USE_KEY;

/**
 * Class TaxonomyRegistrar
 * @package TheFrosty\WpUtilities\Taxonomies
 */
abstract class TaxonomyRegistrar extends ObjectRegistrarManager
{

    public const TAG_TAXONOMY_MANAGER_REGISTRAR = 'wp-utilities/taxonomies/taxonomy_manager/registrar';

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $this->addFilter(self::TAG_TAXONOMY_MANAGER_REGISTRAR, [$this, 'registerTaxonomies']);
        parent::addHooks(); // Initiate the parent hooks.
    }

    /**
     * Get all registered taxonomies from our filter.
     * @return string[]
     */
    public function getObjectClasses(): array
    {
        return array_filter(
            apply_filters(self::TAG_TAXONOMY_MANAGER_REGISTRAR, []),
            fn(string $taxonomy): bool => !empty($taxonomy),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Return the array of taxonomies to register.
     * @param array $taxonomies
     * @return array
     */
    abstract protected function registerTaxonomies(array $taxonomies = []): array;
}
