<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Integration\CarbonFields;

/**
 * Interface TypeInterface
 * @package TheFrosty\WpUtilities\Integration\CarbonFields
 */
interface TypeInterface
{
    final public const string COMMENT_META = 'comment_meta';
    final public const string POST_META = 'post_meta';
    final public const string TERM_META = 'term_meta';
    final public const string THEME_OPTIONS = 'theme_options';
    final public const string USER_META = 'user_meta';
}
