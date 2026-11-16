<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\RestApi\Http;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use TheFrosty\WpUtilities\RestApi\Http\RestRouteInterface;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Class RestRouteInterfaceTest
 * @package TheFrosty\WpUtilities\Tests\RestApi\Http
 */
#[CoversNothing]
#[Group('rest-api')]
class RestRouteInterfaceTest extends TestCase
{
    public function testInterfaceContract(): void
    {
        $this->assertTrue(interface_exists(RestRouteInterface::class));
        $methods = get_class_methods(RestRouteInterface::class);
        $this->assertContains('registerRestRoute', $methods);
        $this->assertContains('initializeRoute', $methods);
        $this->assertContains('registerRoute', $methods);
    }
}
