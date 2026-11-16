<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

use TheFrosty\WpUtilities\Models\PageTemplate;
use TheFrosty\WpUtilities\Plugin\AbstractHookProvider;
use WP_Post;
use function array_merge;
use function file_exists;
use function get_post_meta;
use function is_null;
use function is_search;
use function is_string;
use function sprintf;
use function str_replace;
use function untrailingslashit;

/**
 * Class PageTemplates
 * @package TheFrosty\WpUtilities\Api
 */
class PageTemplates extends AbstractHookProvider
{

    /**
     * Holds the template file and full path.
     * @var array|null $pageTemplates
     */
    private ?array $pageTemplates = null;

    /**
     * Holds the template file and description for WordPress' UI.
     * @var array|null $wpTemplates
     */
    private ?array $wpTemplates = null;

    private const string PREFIX = 'PageTemplates/';

    /**
     * PageTemplates constructor.
     * @param PageTemplate[]|null $templates
     */
    public function __construct(?array $templates = null)
    {
        if (is_null($templates)) {
            return;
        }

        foreach ($templates as $template) {
            if (!$template instanceof PageTemplate) {
                continue;
            }

            $this->pageTemplates[$template->getFile()] = $template->getPath();
            $this->wpTemplates[$this->getPrefix($template->getFile())] = $template->getDescription();
        }
    }

    /**
     * Add class hooks.
     */
    public function addHooks(): void
    {
        $this->addFilter('theme_page_templates', [$this, 'addNewTemplate']);
        $this->addFilter('template_include', [$this, 'templateInclude']);
    }

    /**
     * Adds our template(s) to the page dropdown.
     * @param string[] $posts_templates
     * @return array
     */
    protected function addNewTemplate(array $posts_templates): array
    {
        if ($this->wpTemplates) {
            return array_merge($posts_templates, $this->wpTemplates);
        }

        return $posts_templates;
    }

    /**
     * Checks if the template is assigned to the page
     * @param string $template
     * @return string
     */
    protected function templateInclude(string $template): string
    {
        global $post;

        // Return the search template if we're searching (instead of the template for the first result)
        if (is_search()) {
            return $template;
        }

        if (!$post instanceof WP_Post) {
            return $template;
        }

        $page_template = get_post_meta($post->ID, '_wp_page_template', true);
        if (
            (!is_string($page_template) || empty($page_template)) ||
            !isset($this->wpTemplates[$page_template])
        ) {
            return $template;
        }

        $page_template = str_replace(self::PREFIX, '', $page_template);
        $filepath = str_replace($page_template, '', $this->pageTemplates[$page_template]);

        $file = sprintf('%s/%s', untrailingslashit($filepath), $page_template);

        // Just to be safe, we check if the file exist first
        if (file_exists($file)) {
            return $file;
        }

        // Return template
        return $template;
    }

    /**
     * Return a prefixed file.
     * @param string $file
     * @return string
     */
    private function getPrefix(string $file): string
    {
        return sprintf('%s%s', self::PREFIX, $file);
    }
}
