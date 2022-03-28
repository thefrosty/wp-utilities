<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Utils;

use RuntimeException;
use function array_merge;
use function array_unshift;
use function count;
use function current_filter;
use function dirname;
use function extract;
use function file_exists;
use function function_exists;
use function get_object_vars;
use function is_array;
use function is_object;
use function ob_get_clean;
use function ob_start;
use function realpath;
use function sprintf;
use function str_replace;
use function trailingslashit;

/**
 * Class View
 * @package TheFrosty\WpUtilities\Utils
 */
final class View
{

    /**
     * List of paths to load views from.
     * Internal loader selects the first path with file that exists.
     * Paths that are loaded with addPath are prepended to the array.
     * @var array $viewPaths
     */
    protected array $viewPaths = [];

    /**
     * View data
     * @var array $viewData
     */
    private array $viewData = [];

    /**
     * View constructor.
     */
    public function __construct()
    {
        $this->setDefaultPaths();
    }

    /**
     * Return a view file.
     * @param string $view The view file to render from the `views` directory.
     * @return string|null
     */
    public function get(string $view): ?string
    {
        $file = $this->sanitizeFileExtension($view . '.php');

        return $this->getViewPath($file);
    }

    /**
     * Render a view.
     * @param string $filename The view file to render from the `views` directory.
     * @param array $viewData
     */
    public function render(string $filename, array $viewData = []): void
    {
        /*
         * Clear view data, so we can use the same object multiple times,
         * otherwise the view data will persist and may cause problems.
         */
        $this->viewData = [];
        $this->load([$filename, $viewData]);
    }

    /**
     * Return a view.
     * @param string $filename The view file to render from the `views` directory.
     * @param array $viewData
     * @return string
     */
    public function retrieve(string $filename, array $viewData = []): string
    {
        ob_start();
        $this->render($filename, $viewData);

        return ob_get_clean();
    }

    /**
     * Set variables to be available in any view
     * @param array|object $vars
     */
    public function setVars($vars = []): void
    {
        if (is_object($vars)) {
            $vars = get_object_vars($vars);
        }

        if (is_array($vars) and count($vars) > 0) {
            foreach ($vars as $key => $val) {
                $this->viewData[$key] = $val;
            }
        }
    }

    /**
     * Add View Path. Prepend the paths array with the new path
     * @param string $path
     */
    public function addPath(string $path): void
    {
        array_unshift($this->viewPaths, trailingslashit(realpath($path)));
    }

    /**
     * Get view data.
     * @return array
     */
    public function getViewData(): array
    {
        return $this->viewData;
    }

    /**
     * Internal view loader
     * @param array<string, array> $args
     * @throws RuntimeException
     */
    private function load(array $args): void
    {
        $this->setDefaultPaths();
        [$view, $data] = $args;

        // Add a file extension the view
        $file = $this->sanitizeFileExtension($view . '.php');

        // Get the view path
        $viewPath = $this->getViewPath($file);

        // Display error if view not found
        if ($viewPath === null) {
            $this->viewNotFoundError($file);
        }

        if (is_array($data) && !empty($data)) {
            $this->viewData = array_merge($this->viewData, $data);
        }

        extract($this->viewData);
        include $viewPath;
    }

    /**
     * Set the default paths.
     * The default view directories always need to be loaded first
     */
    private function setDefaultPaths(): void
    {
        if (empty($this->viewPaths)) {
            $this->viewPaths = [sprintf('%1$s/views/', dirname(__DIR__, 2))];
        }
    }

    /**
     * Sanitize the file extension.
     * @param string $file
     * @return string
     */
    private function sanitizeFileExtension(string $file): string
    {
        return str_replace('.php.php', '.php', $file);
    }

    /**
     * Get the view path.
     * @param string $file
     * @return string|null
     */
    private function getViewPath(string $file): ?string
    {
        $file = $this->sanitizeFileExtension($file);
        foreach ($this->viewPaths as $viewDir) {
            if (file_exists($viewDir . $file)) {
                return $viewDir . $file;
            }
        }

        return null;
    }

    /**
     * Display error when no view found.
     * @param string $file
     * @return mixed
     * @throws RuntimeException
     */
    private function viewNotFoundError(string $file): void
    {
        $errText = PHP_EOL .
            'View file "' . $file . '" not found.' . PHP_EOL .
            'Directories checked: ' . PHP_EOL .
            '[' . implode('],' . PHP_EOL . '[', $this->viewPaths) . ']' . PHP_EOL;

        throw new RuntimeException($errText);
    }
}
