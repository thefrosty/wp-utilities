<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Utils;

use Psr\Container\ContainerInterface;
use Throwable;
use function method_exists;

/**
 * Trait Viewable
 * @package TheFrosty\WpUtilities\Utils
 */
trait Viewable
{

    /**
     * View instance.
     * @var View|null
     */
    private ?View $view = null;

    /**
     * Get an instance of View.
     * @param string $id Identifier of the entry to look for.
     * @return View|null
     */
    public function getView(string $id): ?View
    {
        if (
            !$this->view instanceof View &&
            method_exists($this, 'getContainer') &&
            $this->getContainer() instanceof ContainerInterface
        ) {
            try {
                $this->view = $this->getContainer()->get($id);
            } catch (Throwable $e) {
                return null;
            }
        }

        return $this->view;
    }
}
