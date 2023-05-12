<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin;

use TheFrosty\WpUtilities\Plugin\Plugin;

/**
 * Interface RestrictPostsInterface
 * @package TheFrosty\WpUtilities\WpAdmin
 */
interface RestrictPostsInterface
{

    public const ADMIN_FILTER_FIELD_NAME = '_filter_meta_key';
    public const ADMIN_FILTER_FIELD_VALUE = '_filter_meta_value';
    public const ADMIN_SEARCH_FIELD_VALUE = '_search_meta_value';
    public const HANDLE = 'restrict-manage-posts';
    public const HANDLE_UTILITY_FUNCTIONS = 'utility-functions';

    public const TAG_FILTER_META_KEYS = Plugin::TAG . '/restrict_manage_posts/meta_keys';
    public const TAG_FILTER_META_VALUES = Plugin::TAG . '/restrict_manage_posts/meta_values';
}
