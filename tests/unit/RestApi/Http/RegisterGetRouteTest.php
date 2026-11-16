<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\RestApi\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use TheFrosty\WpUtilities\Plugin\TemplateLoader;
use TheFrosty\WpUtilities\RestApi\Http\RegisterGetRoute;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use WP_REST_Server;

/**
 * Class RegisterGetRouteTest
 * @package TheFrosty\WpUtilities\Tests\RestApi\Http
 */
#[CoversClass(RegisterGetRoute::class)]
#[UsesClass(TemplateLoader::class)]
#[Group('rest-api')]
class RegisterGetRouteTest extends TestCase
{
    public function testRegisterRouteUsesReadableMethods(): void
    {
        $route = new class extends RegisterGetRoute {
            public function initializeRoute(WP_REST_Server $server): void
            {
            }
        };
        $route->registerRoute('wp-utilities/v1', '/readable', static fn(): array => ['ok' => true]);

        $routes = rest_get_server()->get_routes();
        $this->assertArrayHasKey('/wp-utilities/v1/readable', $routes);
        $methods = $routes['/wp-utilities/v1/readable'][0]['methods'];
        $this->assertArrayHasKey('GET', $methods);
    }
}
