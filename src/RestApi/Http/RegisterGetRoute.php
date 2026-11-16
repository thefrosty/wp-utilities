<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\RestApi\Http;

use WP_REST_Server;

/**
 * Class RegisterGetRoute
 * phpcs:disable Squiz.Commenting.FunctionComment.MissingParamTag
 * @package TheFrosty\WpUtilities\RestApi\Http
 */
abstract class RegisterGetRoute extends RouteService
{
    /**
     * {@inheritdoc}
     */
    public function registerRoute(string $namespace, string $route, callable $callback, array $args = []): void
    {
        $this->registerRestRoute($namespace, $route, $callback, WP_REST_Server::READABLE, $args);
    }
}
