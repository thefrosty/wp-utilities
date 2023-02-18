<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\PostTypes\Columns\Api;

use TheFrosty\WpUtilities\Api\ObjectRegistrarManager;
use function apply_filters;
use function array_filter;
use const ARRAY_FILTER_USE_KEY;

/**
 * Class ColumnsRegistrar
 * @package TheFrosty\WpUtilities\PostTypes\Columns\Api
 */
abstract class ColumnsRegistrar extends ObjectRegistrarManager
{

    public const TAG_COLUMNS_MANAGER_REGISTRAR = 'wp-utilities/post_types/columns_manager/registrar';

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $this->addFilter(self::TAG_COLUMNS_MANAGER_REGISTRAR, [$this, 'registerColumns']);
        parent::addHooks(); // Initiate the parent hooks.
    }

    /**
     * Get all registered fields for post_types from our filter.
     * @return array
     */
    public function getObjectClasses(): array
    {
        return array_filter(
            apply_filters(self::TAG_COLUMNS_MANAGER_REGISTRAR, []),
            fn(string $column): bool => !empty($column),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Return the array of columns to register.
     * @param array $columns
     * @return array
     */
    abstract protected function registerColumns(array $columns = []): array;
}
