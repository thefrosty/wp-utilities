<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin;

use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestInterface;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestTrait;
use WP_Query;
use function apply_filters;
use function array_shift;
use function defined;
use function esc_attr;
use function esc_url;
use function is_admin;
use function is_array;
use function is_numeric;
use function preg_match;
use function sanitize_text_field;
use function sprintf;
use function strcasecmp;
use function TheFrosty\WpUtilities\wp_register_script;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_register_style;
use const SCRIPT_DEBUG;

/**
 * Class RestrictManagePosts
 * @package TheFrosty\WpUtilities\WpAdmin
 */
class RestrictManagePosts extends AbstractHookProvider implements HttpFoundationRequestInterface, RestrictPostsInterface
{

    use FormElementsTrait, HttpFoundationRequestTrait;

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $this->addAction('admin_enqueue_scripts', [$this, 'enqueueScripts'], 19);
        $this->addFilter('script_loader_tag', [$this, 'modifyScriptType'], 10, 3);
        $this->addAction('restrict_manage_posts', [$this, 'addManagePostsHtml']);
        $this->addAction('pre_get_posts', [$this, 'preGetPostsFilterMetaQuery']);
    }

    /**
     * Get the applied filters for meta tag keys.
     * @param string $post_type
     * @return array
     */
    public static function getFilteredMetaKeys(string $post_type): array
    {
        $meta_keys = apply_filters(self::TAG_FILTER_META_KEYS, [], $post_type);

        return is_array($meta_keys) ? $meta_keys : [];
    }

    /**
     * Get the applied filters for meta tag values.
     * @param string $post_type
     * @return array
     */
    public static function getFilteredMetaValues(string $post_type): array
    {
        $meta_values = apply_filters(self::TAG_FILTER_META_VALUES, [], $post_type);

        return is_array($meta_values) ? $meta_values : [];
    }

    /**
     * Enqueue Scripts.
     * @param string $hook
     */
    protected function enqueueScripts(string $hook): void
    {
        if ($hook !== 'edit.php') {
            return;
        }

        $min = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';
        if (!wp_style_is('select2', 'registered')) {
            wp_register_style(
                'select2',
                sprintf('https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2%s.css', $min),
                ver: '4.0.13'
            );
        }
        if (!wp_script_is('select2', 'registered')) {
            wp_register_script(
                'select2',
                sprintf('https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2%s.js', $min),
                ['jquery'],
                '4.0.13',
                true
            );
        }
        wp_register_script(
            self::HANDLE_UTILITY_FUNCTIONS,
            sprintf('https://cdn.jsdelivr.net/gh/thefrosty/wp-utilities@3/assets/js/utilities/functions%s.js', $min),
            args: ['in_footer' => true]
        );
        wp_register_script(
            self::HANDLE,
            sprintf('https://cdn.jsdelivr.net/gh/thefrosty/wp-utilities@3/assets/js/%s%s.js', self::HANDLE, $min),
            ['select2', self::HANDLE_UTILITY_FUNCTIONS],
            args: ['in_footer' => true]
        );
        wp_enqueue_style('select2');
        wp_enqueue_script(self::HANDLE);
    }

    /**
     * Change the script `$tag` and use "module" for the type.
     * @param string $tag The `<script>` tag for the enqueued script.
     * @param string $handle The script's registered handle.
     * @param string $src The script's source URL.
     * @return string
     */
    protected function modifyScriptType(string $tag, string $handle, string $src): string
    {
        if ($handle !== self::HANDLE_UTILITY_FUNCTIONS) {
            return $tag;
        }

        return sprintf(
            "<script type='module' src='%s' id='%s-js'></script>\n",
            esc_url($src),
            esc_attr($handle)
        );
    }

    /**
     * Method to add select HTML elements to the `restrict_manage_posts` hook.
     * The first outputs "custom fields" set in the name `RestrictPostsInterface::ADMIN_FILTER_FIELD_NAME`.
     * The second outputs "#1's value" set in the name `RestrictPostsInterface::ADMIN_FILTER_FIELD_VALUE`.
     * The new input search text field is shown when there are more meta keys output than meta values. If this is the
     * case, you could search by any meta text value by selecting the meta key to search in.
     * @param string $post_type The current post type slug.
     */
    protected function addManagePostsHtml(string $post_type): void
    {
        $meta_keys = self::getFilteredMetaKeys($post_type);
        $this->selectHtml(
            self::ADMIN_FILTER_FIELD_NAME,
            \esc_html__('Meta Key', 'wp-utilities'),
            $meta_keys
        );
        $meta_values = self::getFilteredMetaValues($post_type);
        $this->selectHtml(
            self::ADMIN_FILTER_FIELD_VALUE,
            \esc_html__('Meta Value', 'wp-utilities'),
            $meta_values
        );
        if (!empty($meta_keys) && !empty($meta_values)) {
            $this->inputHtml(self::ADMIN_SEARCH_FIELD_VALUE);
        }
        unset($meta_keys, $meta_values);
    }

    /**
     * Filter the current query based on two GET parameters being present.
     * @param WP_Query $query Query.
     */
    protected function preGetPostsFilterMetaQuery(WP_Query $query): void
    {
        global $pagenow;

        if (
            !is_admin() ||
            ($pagenow !== 'edit.php') ||
            !$query->is_main_query() ||
            !$this->getRequest()->query->has(self::ADMIN_FILTER_FIELD_NAME) ||
            !$this->getRequest()->query->has(self::ADMIN_FILTER_FIELD_VALUE)
        ) {
            return;
        }

        if (
            !empty($this->getRequest()->query->get(self::ADMIN_FILTER_FIELD_NAME)) &&
            !empty($this->getRequest()->query->get(self::ADMIN_FILTER_FIELD_VALUE))
        ) {
            $query->set('meta_query', [
                [
                    'key' => $this->getRequest()->query->get(self::ADMIN_FILTER_FIELD_NAME),
                    'value' => $this->getRequest()->query->get(self::ADMIN_FILTER_FIELD_VALUE),
                ],
            ]);
        }

        // Allow "advanced search" before custom search field value.
        if ($this->advancedSearch($this->getRequest()->query->get(self::ADMIN_SEARCH_FIELD_VALUE), $query)) {
            return;
        }

        if (
            $this->getRequest()->query->has(self::ADMIN_SEARCH_FIELD_VALUE) &&
            !empty($this->getRequest()->query->get(self::ADMIN_SEARCH_FIELD_VALUE)) &&
            !empty($this->getRequest()->query->get(self::ADMIN_FILTER_FIELD_NAME))
        ) {
            $value = $this->getRequest()->query->get(self::ADMIN_SEARCH_FIELD_VALUE);
            if (strcasecmp($value, 'NULL') === 0 || strcasecmp($value, 'EMPTY') === 0) {
                $query->set('meta_query', [
                    [
                        'key' => $this->getRequest()->query->get(self::ADMIN_FILTER_FIELD_NAME),
                        'compare' => 'NOT EXISTS',
                    ],
                ]);

                return;
            }
            $query->set('meta_query', [
                [
                    'key' => $this->getRequest()->query->get(self::ADMIN_FILTER_FIELD_NAME),
                    'value' => sanitize_text_field($value),
                ],
            ]);
        }
    }

    /**
     * Maybe create a custom query for advanced searching based on passed key value.
     * @param mixed $value
     * @param WP_Query $query
     * @return bool|null
     */
    private function advancedSearch(mixed $value, WP_Query $query): ?bool
    {
        if (empty($value)) {
            return null;
        }

        preg_match(
            '/meta_key:\"(?P<meta_key>.*)\" post_title:\"(?P<post_title>.*)\" post_type:\"(?P<post_type>.*)\"/',
            sanitize_text_field($value),
            $matches
        );
        if (
            !empty($matches['meta_key']) &&
            !empty($matches['post_title']) &&
            !empty($matches['post_type'])
        ) {
            $search = new WP_Query([
                'fields' => 'ids',
                'post_type' => sanitize_text_field($matches['post_type']),
                's' => sanitize_text_field($matches['post_title']),
                'post_status' => 'publish',
                'orderby' => 'title',
                'order' => 'ASC',
            ]);
            if (!empty($search->posts) && is_numeric(array_shift($search->posts))) {
                $query->set('meta_query', [
                    [
                        'key' => sanitize_text_field($matches['meta_key']),
                        'value' => array_shift($search->posts),
                    ],
                ]);

                return true;
            }
        }

        return null;
    }
}
