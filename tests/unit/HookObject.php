<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests;

use TheFrosty\WpUtilities\Plugin\WpHooksInterface;

class HookObject implements WpHooksInterface
{
    public function addHooks(): void
    {
    }
}
