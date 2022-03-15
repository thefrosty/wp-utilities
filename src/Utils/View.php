<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Utils;

use function dirname;
use function extract;
use function file_exists;
use function sprintf;

/**
 * Class View
 * @package TheFrosty\WpUtilities\Utils
 */
final class View
{

    /**
     * Return a view file.
     * @param string $filename The view file to render from the `views` directory.
     * @return string
     */
    public function get(string $filename): string
    {
        return sprintf('%1$s/views/%2$s', dirname(__DIR__, 2), $filename);
    }

    /**
     * Render a view file.
     * @param string $filename The view file to render from the `views` directory.
     * @param array $args
     */
    public function render(string $filename, array $args = []): void
    {
        $filename = $this->get($filename);
        if (file_exists($filename)) {
            extract($args);
            include $filename;
        }
    }
}
