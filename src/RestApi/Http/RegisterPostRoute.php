<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\RestApi\Http;

/**
 * Class RegisterGetRoute
 * @package TheFrosty\WpUtilities\RestApi\Http
 */
abstract class RegisterPostRoute extends RouteService
{
    /**
     * {@inheritdoc}
     */
    public function registerRoute(string $namespace, string $route, callable $callback, array $args = []): void
    {
        $this->registerRestRoute($namespace, $route, $callback, \WP_REST_Server::CREATABLE, $args);
    }
}
