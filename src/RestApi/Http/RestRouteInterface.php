<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\RestApi\Http;

/**
 * Interface RestRouteInterface
 * @package TheFrosty\WpUtilities\RestApi\Http
 */
interface RestRouteInterface
{
    /**
     * Registers a REST API route.
     * @param string $namespace The first URL segment after core prefix. Should be unique to your package/plugin.
     * @param string $route The base URL for route you are adding.
     * @param callable $callback Callback method to run when endpoint is accessed
     * @param string $method The HTTP method to be processed by the callback function
     * @param array $args
     * @return bool True on success, false on error.
     * @uses register_rest_route()
     */
    public function registerRestRoute(
        string $namespace,
        string $route,
        callable $callback,
        string $method,
        array $args = []
    ): bool;

    /**
     * Initialize the route on `rest_api_init`.
     * @param \WP_REST_Server $server The instantiated Server object.
     */
    public function initializeRoute(\WP_REST_Server $server): void;

    /**
     * Register the route call.
     * @param string $namespace
     * @param string $route
     * @param callable $callback
     * @param array $args
     */
    public function registerRoute(string $namespace, string $route, callable $callback, array $args = []): void;
}
