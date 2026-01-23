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

    public const string ADMIN_FILTER_FIELD_NAME = '_filter_meta_key';
    public const string ADMIN_FILTER_FIELD_VALUE = '_filter_meta_value';
    public const string ADMIN_SEARCH_FIELD_VALUE = '_search_meta_value';
    public const string HANDLE = 'restrict-manage-posts';
    public const string HANDLE_UTILITY_FUNCTIONS = 'utility-functions';
    public const string TAG_FILTER_ADVANCED_SEARCH = Plugin::TAG . '/restrict_manage_posts/advanced_search';
    public const string TAG_FILTER_ENABLE_SCRIPTS = Plugin::TAG . '/restrict_manage_posts/enable_scripts';

    public const string TAG_FILTER_META_KEYS = Plugin::TAG . '/restrict_manage_posts/meta_keys';
    public const string TAG_FILTER_META_VALUES = Plugin::TAG . '/restrict_manage_posts/meta_values';
}
