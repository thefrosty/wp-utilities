<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\RestApi\Http;

use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;

/**
 * Class RouteService
 *
 * @package TheFrosty\WpUtilities\RestApi\Http
 */
abstract class RouteService implements RestRouteInterface, WpHooksInterface
{
    use HooksTrait;

    protected const ARG_METHODS = 'methods';
    protected const ARG_CALLBACK = 'callback';
    protected const ARG_PERMISSION_CALLBACK = 'permission_callback';

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
            self::ARG_PERMISSION_CALLBACK => '__return_true',
        ];
        $args = \wp_parse_args($args, $defaults);

        return \register_rest_route($namespace, $route, $args);
    }

    /**
     * {@inheritdoc}
     */
    abstract public function initializeRoute(\WP_REST_Server $server): void;

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
