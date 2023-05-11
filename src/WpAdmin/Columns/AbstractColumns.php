<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin\Columns;

use Symfony\Component\HttpFoundation\Request;
use TheFrosty\WpUtilities\Api\WpQueryTrait;
use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestInterface;
use TheFrosty\WpUtilities\WpAdmin\Models\OptionValueLabel;
use TheFrosty\WpUtilities\WpAdmin\RestrictPostsInterface;
use function array_filter;
use function array_shift;
use function array_unique;
use function array_walk;
use function esc_html;
use function get_post;
use function is_array;
use function is_numeric;
use function sprintf;
use function strval;
use function time;
use function wp_next_scheduled;
use function wp_schedule_single_event;

/**
 * Class AbstractColumns
 * @package TheFrosty\WpUtilities\WpAdmin\Columns
 */
abstract class AbstractColumns extends AbstractHookProvider
{

    use WpQueryTrait;

    private const HOOK_WP_QUERY_GET_ALL_IDS = '%s_wp_query_get_all_ids';

    /**
     * Add Post Type Columns
     * @param array $columns Columns
     * @return array
     */
    abstract protected function addPostTypeColumns(array $columns): array;

    /**
     * Add Post Type Column Content
     * @param string $column Column
     * @param int $post_id Post ID
     */
    abstract protected function addPostTypeColumnContent(string $column, int $post_id): void;

    /**
     * Get Column Filter
     * @param string $post_type Post Type
     * @return string
     */
    protected function getColumnFilter(string $post_type): string
    {
        return sprintf('manage_%s_posts_columns', $post_type);
    }

    /**
     * Get Column Content Action
     * @param string $post_type Post Type
     * @return string
     */
    protected function getColumnContentAction(string $post_type): string
    {
        return sprintf('manage_%s_posts_custom_column', $post_type);
    }

    /**
     * Attach Column Methods
     * @param string $post_type Post Type
     */
    protected function addColumnHooks(string $post_type): void
    {
        $this->addAction($this->getColumnContentAction($post_type), [$this, 'addPostTypeColumnContent'], 10, 2);
        $this->addFilter($this->getColumnFilter($post_type), [$this, 'addPostTypeColumns']);
        $this->addAction(
            sprintf(self::HOOK_WP_QUERY_GET_ALL_IDS, $post_type),
            [$this, 'setWpQueryGetAllIdsCache'],
            10,
            4
        );
    }

    /**
     * Set the option(s) key values by reference to the array.
     * @param array $data The array of data (usually meta values)
     * @param string $key The Meta Key
     * @param array $options The passed options array for the collection of fields
     */
    protected function setOptionKeyValues(array $data, string $key, array &$options = []): void
    {
        array_walk($data, static function (mixed $value, int $key) use (&$data): void {
            $data[$key] = is_array($value) ? array_shift($value) : $value;
        });
        foreach (array_filter(array_unique($data)) as $item) {
            if (is_numeric($item) && get_post($item)) {
                $title = get_post($item)->post_title;
            }
            $options[$key][] = new OptionValueLabel(strval($item), esc_html($title ?? $item));
        }
    }

    /**
     * Gets all cached posts by post type (deferred by a single background CRON task).
     * @param string $post_type The Post Type.
     * @param array|null $args Optional (different) query args.
     * @param int $expiration Optional expiration cache time. Defaults to `WEEK_IN_SECONDS`.
     * @return array
     */
    protected function getCachedPostsDeferred(
        string $post_type,
        ?array $args = null,
        int $expiration = \WEEK_IN_SECONDS
    ): array {
        $args ??= [];
        $key = $this->getHashedKey(sprintf('%s-%s', __METHOD__, $post_type));
        $post_ids = $this->getCache($key, self::class);
        if (is_array($post_ids) && !empty($post_ids)) {
            return $post_ids;
        }
        // If we don't have the cache, trigger a single cron event to populate it in the background.
        $hook = sprintf(self::HOOK_WP_QUERY_GET_ALL_IDS, $post_type);
        if (!wp_next_scheduled($hook, [$key, $post_type, $args, $expiration])) {
            wp_schedule_single_event(time(), $hook, [$key, $post_type, $args, $expiration]);
        }

        return [];
    }

    /**
     * Single CRON event action, to populate the CACHE for this large query.
     * @param string $key The hashed cache key.
     * @param string $post_type The Post Type.
     * @param array $args Incoming query args array.
     * @param int $expiration Expiration cache time.
     */
    protected function setWpQueryGetAllIdsCache(string $key, string $post_type, array $args, int $expiration): void
    {
        $post_ids = $this->wpQueryGetAllIds($post_type, $args);
        $this->setCache($key, $post_ids, self::class, $expiration);
    }

    /**
     * Does the current GET request have $field_name assigned as the admin filter query?
     * @param string $field_name
     * @param Request|null $request
     * @return bool
     * @throws \BadMethodCallException
     */
    protected function requestHas(string $field_name, ?Request $request = null): bool
    {
        if ($request === null && (!\method_exists($this, 'getRequest') || $this->getRequest() === null)) {
            throw new \BadMethodCallException(
                sprintf(
                    'Class missing `%s` implementation or passing `%s` as a param.',
                    HttpFoundationRequestInterface::class,
                    Request::class
                )
            );
        }
        $request ??= $this->getRequest();

        return ($request->query->has('s') ||
                $request->query->has(RestrictPostsInterface::ADMIN_SEARCH_FIELD_VALUE)) &&
            $request->query->has(RestrictPostsInterface::ADMIN_FILTER_FIELD_NAME) &&
            $request->query->get(RestrictPostsInterface::ADMIN_FILTER_FIELD_NAME) === $field_name;
    }
}
