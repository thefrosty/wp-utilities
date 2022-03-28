<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Utils;

use Psr\Container\ContainerInterface;
use RuntimeException;
use TheFrosty\WpUtilities\Plugin\ContainerAwareTrait;
use Throwable;
use function get_class;
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
     * @return View|null View object, null if
     */
    public function getView(string $id): ?View
    {
        if (!method_exists($this, 'getContainer') || !$this->getContainer() instanceof ContainerInterface) {
            throw new RuntimeException(
                sprintf('%s must use %s', get_class($this), ContainerAwareTrait::class)
            );
        }

        if (!$this->view instanceof View) {
            try {
                $this->view = $this->getContainer()->get($id);
            } catch (Throwable $e) {
                return null;
            }
        }

        return $this->view;
    }
}
