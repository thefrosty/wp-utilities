<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

/**
 * Class TemplateLoader
 *
 * @package TheFrosty\WpUtilities\Plugin
 */
class TemplateLoader implements TemplateLoaderInterface
{

    public const VAR = '_thefrosty_data';

    /**
     * Plugin object.
     *
     * @var Plugin $plugin
     */
    private Plugin $plugin;

    /**
     * Store variable names used for template data.
     * Means unsetTemplateData() can remove all custom references from $wp_query.
     *
     * @var array $template_data_var_names
     */
    private array $template_data_var_names = [self::VAR];

    /**
     * Store located template paths.
     *
     * @var array $template_path_cache
     */
    private array $template_path_cache = [];

    /**
     * TemplateLoader constructor.
     *
     * @param Plugin $plugin
     */
    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Clean up template data.
     */
    public function __destruct()
    {
        $this->unsetTemplateData();
    }

    /**
     * Helper to get the data
     * @param string|null $var
     *
     * @return mixed
     */
    public static function getData(?string $var = null)
    {
        return \get_query_var($var ?? self::VAR, []);
    }

    /**
     * Make custom data available to template.
     *
     * Data is available to the template as properties under the variable passed to '$var_name'.
     *
     * @param array $data Custom data for the template.
     * @param string|null $var The default var name.
     *
     * @return $this
     */
    public function setTemplateData(array $data = [], ?string $var = null): self
    {
        if (!empty($data)) {
            \set_query_var($var ?? self::VAR, $data);
        }

        // Add $var_name to custom variable store if not default value
        if (!\is_null($var) && $var !== self::VAR) {
            $this->template_data_var_names[] = $var;
        }

        return $this;
    }

    /**
     * Return a template part.
     *
     * @param string $slug Template slug.
     * @param string|null $name Optional. Template variation name. Default null.
     *
     * @return string URI string to the template path file.
     * @throws \Exception
     */
    public function getTemplatePart(string $slug, ?string $name = null): string
    {
        \do_action(Plugin::TAG . 'template_loader/get_template_part_' . $slug, $slug, $name);
        $templates = $this->getTemplateFileNames($slug, $name);

        return $this->getTemplate($templates);
    }

    /**
     * Retrieve a template part.
     *
     * @param string $slug Template slug.
     * @param string|null $name Optional. Template variation name. Default null.
     *
     * @throws \Exception
     */
    public function loadTemplatePart(string $slug, ?string $name = null): void
    {
        \do_action(Plugin::TAG . 'template_loader/get_template_part_' . $slug, $slug, $name);
        $templates = $this->getTemplateFileNames($slug, $name);
        $this->loadTemplate($templates);
    }

    /**
     * Retrieve the name of the highest priority template file that exists and returns it.
     *
     * @param array $template_names Template file(s) to search for, in order.
     *
     * @return string
     * @throws \Exception
     */
    protected function getTemplate(array $template_names): string
    {
        try {
            return $this->getLocatedFile($template_names);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Retrieve the name of the highest priority template file that exists and loads it.
     *
     * @param array $template_names Template file(s) to search for, in order.
     *
     * @throws \Exception
     */
    protected function loadTemplate(array $template_names): void
    {
        try {
            $located = $this->getLocatedFile($template_names);
            \load_template($located, false);
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    /**
     * Return a list of paths to check for template locations.
     *
     * Since we do not expect to support templates in theme overriding the plugin's
     * templates, we only check for templates in the plugin. It is possible to
     * add template directories through
     *
     * @return array
     */
    protected function getTemplatePaths(): array
    {
        $file_paths = [
            100 => $this->plugin->getPath('/views'),
        ];

        /**
         * Allow ordered list of template paths to be amended.
         *
         * @param array $file_paths Default is directory in child theme at index 1,
         *                          parent theme at 10, and plugin at 100.
         */
        $file_paths = \apply_filters(Plugin::TAG . 'template_loader/template_paths', $file_paths);

        // Sort the file paths based on priority.
        \ksort($file_paths, \SORT_NUMERIC);

        return \array_map('\trailingslashit', $file_paths);
    }

    /**
     * Remove access to custom data in template.
     * Good to use once the final template part has been requested.
     */
    protected function unsetTemplateData(): void
    {
        global $wp_query;

        foreach (\array_unique($this->template_data_var_names) as $var) {
            unset($wp_query->query_vars[$var]);
        }
    }

    /**
     * Retrieve the name of the highest priority template file that exists.
     *
     * @param array $template_names Template file(s) to search for, in order.
     *
     * @return string
     * @throws \Exception
     * @SuppressWarnings(PHPMD.MissingImport)
     */
    private function getLocatedFile(array $template_names): string
    {
        $cache_key = $this->getCacheKey($template_names);

        // If the key is in the cache array, we've already located this file.
        if ($cache_key && !empty($this->template_path_cache[$cache_key])) {
            return $this->template_path_cache[$cache_key];
        }
        // No file found yet.
        $located = false;
        // Remove empty entries.
        $template_names = \array_filter($template_names);
        $template_paths = $this->getTemplatePaths();

        // Try to find a template file.
        foreach ($template_names as $template_name) {
            // Trim off any slashes from the template name.
            $template_name = \ltrim($template_name, '/');

            // Try locating this template file by looping through the template paths.
            foreach ($template_paths as $template_path) {
                if (\file_exists($template_path . $template_name)) {
                    $located = $template_path . $template_name;
                    $this->template_path_cache[$cache_key] = $located;
                    break 2;
                }
            }
        }

        if (!is_string($located)) {
            throw new \Exception('Template not found');
        }

        return $located;
    }

    /**
     * Given a slug and optional name, create the file names of templates.
     *
     * @param string $slug Template slug.
     * @param string|null $name Template variation name.
     *
     * @return array
     */
    private function getTemplateFileNames(string $slug, ?string $name): array
    {
        $templates = [];
        if (\is_string($name)) {
            $templates[] = $slug . '-' . $name . '.php';
        }
        $templates[] = $slug . '.php';

        /**
         * Allow template choices to be filtered.
         *
         * The resulting array should be in the order of most specific first, to least specific last.
         * e.g. 0 => recipe-instructions.php, 1 => recipe.php
         *
         * @param array $templates Names of template files that should be looked for, for given slug and name.
         * @param string $slug Template slug.
         * @param string $name Template variation name.
         */
        return \apply_filters(Plugin::TAG . 'template_loader/get_template_part', $templates, $slug, $name);
    }

    /**
     * Use the template names as a cache key.
     *
     * @param array $names
     * @return string|null
     */
    private function getCacheKey(array $names): ?string
    {
        $values = \array_values($names);

        return \array_shift($values);
    }
}
