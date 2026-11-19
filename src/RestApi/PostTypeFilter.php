<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\RestApi;

use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;
use WP_Post_Type;
use WP_REST_Request;
use function apply_filters;
use function array_filter;
use function array_merge;
use function array_unique;
use function array_walk;
use function current_user_can;
use function get_post_type_object;
use function get_post_types;
use function in_array;
use function is_string;
use function sprintf;

/**
 * Class PostTypeFilter
 * @package TheFrosty\WpUtilities\RestApi
 */
class PostTypeFilter implements WpHooksInterface
{
    use HooksTrait;

    public const string QUERY_PARAM = 'filter';
    public const string HOOK_ALLOW_META_QUERY = Plugin::TAG . '/filter_allow_meta_query';
    private const array QUERY_VARS = [
        'post__in',
        'post_name__in',
    ];

    /**
     * Meta query vars. These can be used as a boolean oracle to read private (`_` prefixed) meta values, so they
     * are only accepted from users who can edit the queried post type, unless explicitly allowed via the
     * `HOOK_ALLOW_META_QUERY` filter.
     */
    private const array META_QUERY_VARS = [
        'meta_query',
        'meta_compare',
        'meta_key',
        'meta_value',
    ];

    /**
     * Post Type names.
     * @var string[]
     */
    private array $post_types;

    /**
     * Filters constructor.
     */
    public function __construct()
    {
        $this->post_types = get_post_types(['show_in_rest' => true]);
    }

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $this->addAction('rest_api_init', [$this, 'addRestFilters']);
    }

    /**
     * Allow the `filter[]` to REST responses for our post types.
     * Taken from https://github.com/WP-API/rest-filter
     */
    protected function addRestFilters(): void
    {
        $post_types = array_filter(
            apply_filters(sprintf('%s/filter_post_types', Plugin::TAG), $this->post_types)
        );
        array_walk($post_types, function (string $slug): void {
            $post_type = get_post_type_object($slug);
            if ($post_type instanceof WP_Post_Type) {
                $this->addFilter("rest_{$post_type->name}_query", [$this, 'addFilterParam'], 10, 2);
            }
        });
    }

    /**
     * Add the `filter` parameter to the REST call query which is then passed to WP_Query.
     * @see https://developer.wordpress.org/reference/classes/wp_query/ For available params to pass to the filter.
     * @param array $args The query arguments.
     * @param WP_REST_Request $request Full details about the request.
     * @return array $args.
     */
    protected function addFilterParam(array $args, WP_REST_Request $request): array
    {
        // Bail out if no filter parameter is set.
        if (empty($request->get_params()) || empty($request->get_params()[self::QUERY_PARAM])) {
            return $args;
        }

        $filter = $request->get_params()[self::QUERY_PARAM];
        if (
            isset($filter['posts_per_page']) &&
            ((int)$filter['posts_per_page'] >= 1 && (int)$filter['posts_per_page'] <= 100)
        ) {
            $args['posts_per_page'] = $filter['posts_per_page'];
        }

        $vars = $this->getPublicQueryVars($request);
        $allow_meta = $this->canQueryMeta($args, $request);
        foreach ($vars as $var) {
            if (!isset($filter[$var])) {
                continue;
            }

            if (!$allow_meta && in_array($var, self::META_QUERY_VARS, true)) {
                continue;
            }

            $args[$var] = $filter[$var];
        }

        return $args;
    }

    /**
     * Can the current user filter the REST query by post meta?
     * @param array $args The query arguments.
     * @param WP_REST_Request $request Full details about the request.
     * @return bool
     */
    private function canQueryMeta(array $args, WP_REST_Request $request): bool
    {
        $post_type = is_string($args['post_type'] ?? null) ? $args['post_type'] : 'post';
        $post_type_object = get_post_type_object($post_type);
        $capability = $post_type_object instanceof WP_Post_Type ? $post_type_object->cap->edit_posts : 'edit_posts';

        /**
         * Filters whether the `filter[meta_*]` REST query vars are allowed for this request.
         * @param bool $allowed Defaults to whether the current user can edit posts of the queried post type.
         * @param string $post_type The queried post type.
         * @param WP_REST_Request $request The current request.
         */
        return apply_filters(self::HOOK_ALLOW_META_QUERY, current_user_can($capability), $post_type, $request) === true;
    }

    /**
     * Get WordPress' global public query vars, and merge them with our custom allowed vars.
     * @param WP_REST_Request|null $request Pass in the current WP_REST_Request object to the filter.
     * @return array
     */
    private function getPublicQueryVars(?WP_REST_Request $request = null): array
    {
        global $wp;
        $vars = apply_filters('rest_query_vars', $wp->public_query_vars, $request);

        return array_unique(array_merge($vars, self::QUERY_VARS, self::META_QUERY_VARS));
    }
}
