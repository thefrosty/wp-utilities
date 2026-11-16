<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\RestApi\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TheFrosty\WpUtilities\RestApi\Http\RegisterPostRoute;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use WP_REST_Server;

/**
 * Class RegisterPostRouteTest
 * @package TheFrosty\WpUtilities\Tests\RestApi\Http
 */
#[CoversClass(RegisterPostRoute::class)]
#[Group('rest-api')]
class RegisterPostRouteTest extends TestCase
{
    public function testRegisterRouteUsesCreatableMethods(): void
    {
        $route = new class extends RegisterPostRoute {
            public function initializeRoute(WP_REST_Server $server): void
            {
            }
        };
        $route->registerRoute('wp-utilities/v1', '/creatable', static fn(): array => ['ok' => true]);

        $routes = rest_get_server()->get_routes();
        $this->assertArrayHasKey('/wp-utilities/v1/creatable', $routes);
        $methods = $routes['/wp-utilities/v1/creatable'][0]['methods'];
        $this->assertArrayHasKey('POST', $methods);
    }
}
