<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\RestApi;

use TheFrosty\WpUtilities\Plugin\HooksTrait;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Plugin\WpHooksInterface;

/**
 * Class PostTypeFilter
 *
 * @package TheFrosty\WpUtilities\RestApi
 */
class PostTypeFilter implements WpHooksInterface
{
    use HooksTrait;

    public const QUERY_PARAM = 'filter';
    private const QUERY_VARS = [
        'meta_query',
        'meta_compare',
        'meta_key',
        'meta_value',
        'post__in',
        'post_name__in',
    ];

    /**
     * Post Type names.
     *
     * @var string[]
     */
    private array $post_types;

    /**
     * Filters constructor.
     */
    public function __construct()
    {
        $this->post_types = \get_post_types(['show_in_rest' => true], 'names');
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
        $post_types = \array_filter(
            \apply_filters(\sprintf('%s/filter_post_types', Plugin::TAG), $this->post_types)
        );
        \array_walk($post_types, function (string $slug): void {
            $post_type = \get_post_type_object($slug);
            if ($post_type instanceof \WP_Post_Type) {
                $this->addFilter("rest_{$post_type->name}_query", [$this, 'addFilterParam'], 10, 2);
            }
        });
    }

    /**
     * Add the `filter` parameter to the REST call query which is then passed to WP_Query.
     *
     * @see https://developer.wordpress.org/reference/classes/wp_query/ For available params to pass to the filter.
     *
     * @param array $args The query arguments.
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return array $args.
     */
    protected function addFilterParam(array $args, \WP_REST_Request $request): array
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
        foreach ($vars as $var) {
            if (isset($filter[$var])) {
                $args[$var] = $filter[$var];
            }
        }

        return $args;
    }

    /**
     * Get WordPress' global public query vars, and merge them with our custom allowed vars.
     *
     * @param \WP_REST_Request|null $request Pass in the current WP_REST_Request object to the filter.
     * @return array
     */
    private function getPublicQueryVars(?\WP_REST_Request $request = null): array
    {
        $vars = \apply_filters('rest_query_vars', $GLOBALS['wp']->public_query_vars, $request);

        return \array_unique(\array_merge($vars, self::QUERY_VARS));
    }
}
