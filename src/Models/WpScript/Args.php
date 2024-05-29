<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Models\WpScript;

use Args\Shared\Base;

/**
 * Class Args
 * @package TheFrosty\WpUtilities\Models\WpScript
 */
class Args extends Base
{
    /**
     * The enqueued data strategy.
     * Optional. If provided, may be either 'defer' or 'async'.
     * @var string $strategy
     * @phpstan-var 'defer'|'async'
     * Default empty string.
     */
    public string $strategy;

    /**
     * Whether to print the script in the footer.
     * Optional. Whether to print the script in the footer. Default 'false'.
     * @var bool $in_footer
     * Default false.
     */
    public bool $in_footer = false;
}
