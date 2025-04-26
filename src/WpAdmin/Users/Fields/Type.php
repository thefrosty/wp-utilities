<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin\Users\Fields;

/**
 * Enum Type
 * @package TheFrosty\WpUtilities\WpAdmin\Users\Fields
 */
enum Type: string
{
    case CHECKBOX = 'checkbox';
    case TEXT = 'text';
}
