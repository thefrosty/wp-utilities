<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\PostTypes\CustomFields\Api;

use TheFrosty\WpUtilities\Api\ObjectRegistrarManager;
use function apply_filters;
use function array_filter;
use const ARRAY_FILTER_USE_KEY;

/**
 * Class CustomFieldsRegistrar
 * @package TheFrosty\WpUtilities\PostTypes\CustomFields\Api
 */
abstract class CustomFieldsRegistrar extends ObjectRegistrarManager
{

    public const TAG_CUSTOM_FIELDS_MANAGER_REGISTRAR = 'wp-utilities/post_types/custom_fields_manager/registrar';

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $this->addFilter(self::TAG_CUSTOM_FIELDS_MANAGER_REGISTRAR, [$this, 'registerCustomFields']);
        parent::addHooks(); // Initiate the parent hooks.
    }

    /**
     * Get all registered fields for post_types from our filter.
     * @return array
     */
    public function getObjectClasses(): array
    {
        return array_filter(
            apply_filters(self::TAG_CUSTOM_FIELDS_MANAGER_REGISTRAR, []),
            fn(string $custom_field): bool => !empty($custom_field),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Return the array of custom_fields to register.
     * @param array $custom_fields
     * @return array
     */
    abstract protected function registerCustomFields(array $custom_fields = []): array;
}
