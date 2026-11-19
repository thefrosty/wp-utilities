<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\RestApi\Http;

use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;
use WP_REST_Request;
use WP_REST_Server;
use function is_user_logged_in;
use function register_rest_route;
use function wp_parse_args;

/**
 * Class RouteService
 * phpcs:disable Squiz.Commenting.FunctionComment.MissingParamTag
 * @package TheFrosty\WpUtilities\RestApi\Http
 */
abstract class RouteService implements RestRouteInterface, WpHooksInterface
{
    use HooksTrait;

    protected const string ARG_METHODS = 'methods';
    protected const string ARG_CALLBACK = 'callback';
    protected const string ARG_PERMISSION_CALLBACK = 'permission_callback';

    /**
     * Add class hook(s).
     */
    public function addHooks(): void
    {
        $this->addAction('rest_api_init', [$this, 'initializeRoute']);
    }

    /**
     * {@inheritdoc}
     */
    public function registerRestRoute(
        string $namespace,
        string $route,
        callable $callback,
        string $method,
        array $args = []
    ): bool {
        $defaults = [
            self::ARG_METHODS => $method,
            self::ARG_CALLBACK => $callback,
            // Secure by default: routes must opt in to public access by passing their own `permission_callback`
            // (e.g. `'__return_true'`) or by overriding `permissionCallback()`.
            self::ARG_PERMISSION_CALLBACK => [$this, 'permissionCallback'],
        ];
        $args = wp_parse_args($args, $defaults);

        return register_rest_route($namespace, $route, $args);
    }

    /**
     * The default permission callback, used when a route doesn't pass its own `permission_callback`.
     * Defaults to requiring a logged-in user; override in a child class to change the default for its routes.
     * @param WP_REST_Request $request The current request.
     * @return bool
     */
    public function permissionCallback(WP_REST_Request $request): bool
    {
        return is_user_logged_in();
    }

    /**
     * {@inheritdoc}
     */
    abstract public function initializeRoute(WP_REST_Server $server): void;

    /**
     * {@inheritdoc}
     */
    abstract public function registerRoute(
        string $namespace,
        string $route,
        callable $callback,
        array $args = []
    ): void;
}
