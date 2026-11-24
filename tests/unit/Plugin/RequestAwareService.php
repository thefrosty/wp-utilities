<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class RequestAwareService
 * Fixture: a class with a `Request` constructor dependency, resolved by PHP-DI autowiring.
 * @package TheFrosty\WpUtilities\Tests\Plugin
 */
class RequestAwareService
{
    public function __construct(public readonly Request $request)
    {
    }
}
