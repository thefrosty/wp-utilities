<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

/**
 * Trait HooksTrait
 *
 * @package TheFrosty\WpUtilities\Plugin
 * @link https://github.com/johnpbloch/wordpress-dev/blob/master/src/Hooks.php
 */
trait HooksTrait
{

    /**
     * Internal property to track closures attached to WordPress hooks.
     * @var \Closure[]
     */
    protected array $filter_map = [];

    /**
     * Add a WordPress filter.
     *
     * @param string $hook The name of the filter to hook the $function_to_add callback to.
     * @param callable $method he callback to be run when the filter is applied.
     * @param int $priority Optional. Used to specify the order in which the functions
     *      associated with a particular action are executed. Default 10.
     * @param int $arg_count Optional. The number of arguments the function accepts. Default 1.
     * @return bool true
     */
    protected function addFilter(string $hook, callable $method, int $priority = 10, int $arg_count = 1): bool
    {
        $filter = \add_filter(
            $hook,
            $this->mapFilter($this->getWpFilterId($hook, $method, $priority), $method, $arg_count),
            $priority,
            $arg_count
        );

        return $filter === true;
    }

    /**
     * Register a filter to run exactly one time.
     *
     * The arguments match that of add_filter(), but this function will also register a second
     * callback designed to remove the first immediately after it runs.
     *
     * @param string $hook The name of the filter to hook the $function_to_add callback to.
     * @param callable $method he callback to be run when the filter is applied.
     * @param int $priority Optional. Used to specify the order in which the functions
     *      associated with a particular action are executed. Default 10.
     * @param int $arg_count Optional. The number of arguments the function accepts. Default 1.
     * @return bool
     */
    protected function addFilterOnce(string $hook, callable $method, int $priority = 10, int $arg_count = 1): bool
    {
        $singular = function () use ($hook, $method, $priority, $arg_count, &$singular): \Closure {
            $filter = $this->mapFilter($this->getWpFilterId($hook, $method, $priority), $method, $arg_count);
            $this->removeFilter($hook, $singular, $priority);

            return $filter;
        };

        return $this->addFilter($hook, $singular, $priority, $arg_count);
    }

    /**
     * Add a WordPress action.
     *
     * This is an alias of add_filter().
     *
     * @param string $hook The name of the filter to hook the $function_to_add callback to.
     * @param callable $method he callback to be run when the filter is applied.
     * @param int $priority Optional. Used to specify the order in which the functions
     *      associated with a particular action are executed. Default 10.
     * @param int $arg_count Optional. The number of arguments the function accepts. Default 1.
     * @return bool true
     */
    protected function addAction(string $hook, callable $method, int $priority = 10, int $arg_count = 1): bool
    {
        return $this->addFilter($hook, $method, $priority, $arg_count);
    }

    /**
     * Register an action to run exactly one time.
     *
     * The arguments match that of add_action(), but this function will also register a second
     * callback designed to remove the first immediately after it runs.
     *
     * @param string $hook The name of the filter to hook the $function_to_add callback to.
     * @param callable $method he callback to be run when the filter is applied.
     * @param int $priority Optional. Used to specify the order in which the functions
     *      associated with a particular action are executed. Default 10.
     * @param int $arg_count Optional. The number of arguments the function accepts. Default 1.
     * @return bool
     */
    protected function addActionOnce(string $hook, callable $method, int $priority = 10, int $arg_count = 1): bool
    {
        $singular = function () use ($hook, $method, $priority, $arg_count, &$singular): \Closure {
            $filter = $this->mapFilter($this->getWpFilterId($hook, $method, $priority), $method, $arg_count);
            $this->removeAction($hook, $singular, $priority);

            return $filter;
        };

        return $this->addAction($hook, $singular, $priority, $arg_count);
    }

    /**
     * Remove a WordPress filter.
     *
     * @param string $hook The name of the filter to hook the $function_to_add callback to.
     * @param callable $method he callback to be run when the filter is applied.
     * @param int $priority Optional. Used to specify the order in which the functions
     *      associated with a particular action are executed. Default 10.
     * @param int $arg_count Optional. The number of arguments the function accepts. Default 1.
     * @return bool Whether the function existed before it was removed.
     */
    protected function removeFilter(string $hook, callable $method, int $priority = 10, int $arg_count = 1): bool
    {
        return \remove_filter(
            $hook,
            $this->mapFilter($this->getWpFilterId($hook, $method, $priority), $method, $arg_count),
            $priority
        );
    }

    /**
     * Remove a WordPress action.
     *
     * This is an alias of remove_filter().
     *
     * @param string $hook The name of the filter to hook the $function_to_add callback to.
     * @param callable $method he callback to be run when the filter is applied.
     * @param int $priority Optional. Used to specify the order in which the functions
     *      associated with a particular action are executed. Default 10.
     * @param int $arg_count Optional. The number of arguments the function accepts. Default 1.
     * @return bool Whether the function is removed.
     */
    protected function removeAction(string $hook, callable $method, int $priority = 10, int $arg_count = 1): bool
    {
        return $this->removeFilter($hook, $method, $priority, $arg_count);
    }

    /**
     * Run do_action.
     *
     * @param string $action The action to run
     * @param array ...$args Any extra arguments to pass to do_action
     */
    protected function doAction(string $action, array ...$args): void
    {
        \do_action($action, ...$args);
    }

    /**
     * Run apply_filters.
     *
     * @param string $filter The filter to run
     * @param mixed $value The value to filter
     * @param array ...$args Any extra values to send through the filter
     * @return mixed
     */
    protected function applyFilters(string $filter, $value, array ...$args)
    {
        return \apply_filters($filter, $value, ...$args);
    }

    /**
     * Get a unique ID for a hook based on the internal method, hook, and priority.
     *
     * @param string $hook The name of the filter to hook the $function_to_add callback to.
     * @param callable $method he callback to be run when the filter is applied.
     * @param int $priority Optional. Used to specify the order in which the functions
     *      associated with a particular action are executed. Default 10.
     * @return string
     */
    protected function getWpFilterId(string $hook, callable $method, int $priority): string
    {
        return \_wp_filter_build_unique_id($hook, $method, $priority);
    }

    /**
     * Map a filter to a closure that inherits the class' internal scope.
     *
     * This allows hooks to use protected and private methods.
     *
     * @param string $filter_id The name of the filter to hook the $function_to_add callback to.
     * @param callable $method he callback to be run when the filter is applied.
     * @param int $arg_count Optional. The number of arguments the function accepts. Default 1.
     * @return \Closure The callable actually attached to a WP hook
     */
    private function mapFilter(string $filter_id, callable $method, int $arg_count = 1): \Closure
    {
        if (empty($this->filter_map[$filter_id])) {
            $this->filter_map[$filter_id] = function () use ($method, $arg_count) {
                return \call_user_func_array($method, \array_slice(\func_get_args(), 0, $arg_count));
            };
        }

        return $this->filter_map[$filter_id];
    }
}
