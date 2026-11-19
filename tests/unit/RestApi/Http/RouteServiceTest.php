<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\RestApi\Http;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use TheFrosty\WpUtilities\RestApi\Http\RouteService;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use WP_REST_Request;
use WP_REST_Server;

/**
 * Class RouteServiceTest
 * @package TheFrosty\WpUtilities\Tests\RestApi\Http
 */
#[CoversClass(RouteService::class)]
#[Group('rest-api')]
class RouteServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        remove_all_actions('rest_api_init');
        wp_set_current_user(0);
        parent::tearDown();
    }

    public function testAddHooksAndRegisterRestRoute(): void
    {
        $route = new class extends RouteService {
            public bool $initialized = false;

            public function initializeRoute(WP_REST_Server $server): void
            {
                $this->initialized = true;
            }

            public function registerRoute(string $namespace, string $route, callable $callback, array $args = []): void
            {
            }

            public function expose(string $namespace, string $route, callable $callback, string $method): bool
            {
                return $this->registerRestRoute($namespace, $route, $callback, $method);
            }
        };

        $route->addHooks();
        do_action('rest_api_init', rest_get_server());
        $this->assertTrue($route->initialized);
        $this->assertTrue($route->expose('wp-utilities/v1', '/service', static fn(): array => ['ok' => true], 'GET'));
        $this->assertArrayHasKey('/wp-utilities/v1/service', rest_get_server()->get_routes());
    }

    public function testRoutesRequireALoggedInUserByDefault(): void
    {
        $route = $this->route();
        do_action('rest_api_init', rest_get_server());
        $route->expose('wp-utilities/v1', '/private', static fn(): array => ['ok' => true], 'GET');
        $route->expose(
            'wp-utilities/v1',
            '/public',
            static fn(): array => ['ok' => true],
            'GET',
            ['permission_callback' => '__return_true']
        );

        wp_set_current_user(0);
        $this->assertSame(401, rest_do_request(new WP_REST_Request('GET', '/wp-utilities/v1/private'))->get_status());
        $this->assertSame(200, rest_do_request(new WP_REST_Request('GET', '/wp-utilities/v1/public'))->get_status());

        wp_set_current_user(1);
        $this->assertSame(200, rest_do_request(new WP_REST_Request('GET', '/wp-utilities/v1/private'))->get_status());
    }

    public function testPermissionCallback(): void
    {
        $route = $this->route();
        $request = new WP_REST_Request('GET', '/');

        wp_set_current_user(0);
        $this->assertFalse($route->permissionCallback($request));
        wp_set_current_user(1);
        $this->assertTrue($route->permissionCallback($request));
    }

    private function route(): RouteService
    {
        return new class extends RouteService {
            public function initializeRoute(WP_REST_Server $server): void
            {
            }

            public function registerRoute(string $namespace, string $route, callable $callback, array $args = []): void
            {
            }

            public function expose(
                string $namespace,
                string $route,
                callable $callback,
                string $method,
                array $args = []
            ): bool {
                return $this->registerRestRoute($namespace, $route, $callback, $method, $args);
            }
        };
    }
}
