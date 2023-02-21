<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\PostTypes\CustomFields\Api;

/**
 * Interface CustomFieldsRegistrarInterface
 * @package TheFrosty\WpUtilities\PostTypes\CustomFields\Api
 */
interface CustomFieldsRegistrarInterface
{

    /**
     * Return an array of metaboxes.
     * @return array
     */
    public function getMetaBoxes(): array;
}
