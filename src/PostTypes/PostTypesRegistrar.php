<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\PostTypes;

use TheFrosty\WpUtilities\Api\ObjectRegistrarManager;
use function apply_filters;
use function array_filter;
use const ARRAY_FILTER_USE_KEY;

/**
 * Class PostTypesRegistrar
 * @package TheFrosty\WpUtilities\PostTypes
 */
abstract class PostTypesRegistrar extends ObjectRegistrarManager
{

    public const TAG_POST_TYPE_MANAGER_REGISTRAR = 'wp-utilities/post_types/post_type_manager/registrar';

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $this->addFilter(self::TAG_POST_TYPE_MANAGER_REGISTRAR, [$this, 'registerPostTypes']);
        parent::addHooks(); // Initiate the parent hooks.
    }

    /**
     * Get all registered post_types from our filter.
     * @return array
     */
    public function getObjectClasses(): array
    {
        return array_filter(
            apply_filters(self::TAG_POST_TYPE_MANAGER_REGISTRAR, []),
            fn(string $post_type): bool => !empty($post_type),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Return the array of post_types to register.
     * @param array $post_types
     * @return array
     */
    abstract protected function registerPostTypes(array $post_types = []): array;
}
