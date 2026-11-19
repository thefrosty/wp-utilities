<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\WpAdmin;

use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestInterface;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestTrait;
use WP_Query;
use function apply_filters;
use function array_pad;
use function array_shift;
use function defined;
use function dirname;
use function esc_attr;
use function esc_html__;
use function esc_url;
use function explode;
use function file_exists;
use function filemtime;
use function is_admin;
use function is_array;
use function is_numeric;
use function is_string;
use function plugins_url;
use function preg_match;
use function sanitize_text_field;
use function sprintf;
use function str_contains;
use function str_replace;
use function strcasecmp;
use function TheFrosty\WpUtilities\wpRegisterScript;
use function trailingslashit;
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

    use FormElementsTrait;
    use HttpFoundationRequestTrait;

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $this->addAction('admin_enqueue_scripts', [$this, 'enqueueScripts'], 19);
        $this->addFilter('script_loader_tag', [$this, 'modifyScriptType'], 10, 3);
        $this->addFilter('style_loader_tag', [$this, 'modifyStyleTag'], 10, 2);
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
        $enabled = apply_filters(self::TAG_FILTER_ENABLE_SCRIPTS, true);

        if ($hook !== 'edit.php' || $enabled !== true) {
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
            wpRegisterScript(
                'select2',
                sprintf('https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2%s.js', $min),
                ['jquery'],
                '4.0.13',
                args: true
            );
        }

        wpRegisterScript(
            self::HANDLE_UTILITY_FUNCTIONS,
            $this->getAssetUrl('utilities/functions.js'),
            ver: $this->getAssetVersion('utilities/functions.js'),
            args: ['in_footer' => true]
        );
        wpRegisterScript(
            self::HANDLE,
            $this->getAssetUrl(self::HANDLE . '.js'),
            (array)apply_filters(self::TAG_FILTER_SCRIPT_DEPENDENCIES, ['select2', self::HANDLE_UTILITY_FUNCTIONS]),
            $this->getAssetVersion(self::HANDLE . '.js'),
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
        if ($handle === 'select2') {
            return $this->addSubresourceIntegrity($tag, $handle, '<script ');
        }

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
            esc_html__('Meta Key', 'wp-utilities'),
            $meta_keys
        );
        $meta_values = self::getFilteredMetaValues($post_type);
        $this->selectHtml(
            self::ADMIN_FILTER_FIELD_VALUE,
            esc_html__('Meta Value', 'wp-utilities'),
            $meta_values
        );
        $enabled = apply_filters(self::TAG_FILTER_ADVANCED_SEARCH, true, $post_type);
        if (!empty($meta_keys) && !empty($meta_values) && $enabled === true) {
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
            $value = $this->getRequest()->query->get(self::ADMIN_FILTER_FIELD_VALUE);
            if (str_contains($value, 'COMPARE:')) {
                [, $compare, $value] = array_pad(explode(':', $value), 3, '');
            }

            $query->set('meta_query', [
                [
                    'key' => $this->getRequest()->query->get(self::ADMIN_FILTER_FIELD_NAME),
                    'value' => sanitize_text_field($value),
                    'compare' => $compare ?? '=', // Default to `=`.
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
     * Add the SRI `integrity` & `crossorigin` attributes to the third-party (CDN) Select2 stylesheet.
     * @param string $tag The link tag for the enqueued style.
     * @param string $handle The style's registered handle.
     * @return string
     */
    protected function modifyStyleTag(string $tag, string $handle): string
    {
        if ($handle !== 'select2') {
            return $tag;
        }

        return $this->addSubresourceIntegrity($tag, $handle, '<link ');
    }

    /**
     * Get the URL to one of this library's bundled JS assets.
     * Assets are served from the installed package (not a CDN tracking a moving git tag) so the code running in
     * wp-admin is always the code that was installed. Filterable for installs outside the plugins directory.
     * @param string $file The file path relative to `assets/js/`.
     * @return string
     */
    protected function getAssetUrl(string $file): string
    {
        $base = apply_filters(
            self::TAG_FILTER_ASSETS_URL,
            plugins_url('assets/js/', dirname(__DIR__, 2) . '/composer.json')
        );

        return trailingslashit(is_string($base) ? $base : '') . $file;
    }

    /**
     * Get a cache busting version for a bundled JS asset.
     * @param string $file The file path relative to `assets/js/`.
     * @return string|null
     */
    protected function getAssetVersion(string $file): ?string
    {
        $path = dirname(__DIR__, 2) . '/assets/js/' . $file;

        return file_exists($path) ? (string)filemtime($path) : null;
    }

    /**
     * Add SRI attributes to a tag when a hash is provided for the handle.
     * @param string $tag The HTML tag.
     * @param string $handle The registered handle.
     * @param string $opening The opening of the tag to inject the attributes after.
     * @return string
     */
    private function addSubresourceIntegrity(string $tag, string $handle, string $opening): string
    {
        $hashes = apply_filters(self::TAG_FILTER_SRI_HASHES, []);
        $hash = is_array($hashes) ? ($hashes[$handle] ?? null) : null;
        if (!is_string($hash) || $hash === '' || str_contains($tag, 'integrity=')) {
            return $tag;
        }

        return str_replace(
            $opening,
            sprintf('%sintegrity="%s" crossorigin="anonymous" ', $opening, esc_attr($hash)),
            $tag
        );
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
