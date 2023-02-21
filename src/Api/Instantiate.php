<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

use TheFrosty\WpUtilities\Plugin\{AbstractContainerProvider, WpHooksInterface};
use function class_exists;
use function get_called_class;
use function is_subclass_of;
use function sprintf;
use function str_replace;
use function ucwords;

/**
 * Trait Instantiate
 * @package TheFrosty\WpUtilities\Api
 */
trait Instantiate
{

    private array $instantiated_order = [];

    /**
     * Instantiate all registered objects if a class exists.
     * @param string $namespace The called namespace.
     * @param string $class_name The WP_(Post|Taxonomy) object slug.
     */
    protected function instantiateClasses(string $namespace, string $class_name): void
    {
        $namespace = str_replace('\\Api', '', $namespace);
        $class = $this->buildClass($namespace, $class_name);
        if (!class_exists($class)) {
            return;
        }
        if (is_subclass_of($class, AbstractContainerProvider::class)) {
            $instance = new $class();
            $instance->setContainer($this->getPlugin()->getContainer());
        }
        $instance ??= new $class();
        if ($instance instanceof WpHooksInterface) {
            $this->getPlugin()->add($instance);
        }
        $this->instantiated_order[get_called_class()][] = $instance;
        $this->addAction('after_setup_theme', function (): void {
            $this->getPlugin()->initialize(); // @phpstan-ignore-line
        });
    }

    /**
     * Build the fully qualified class name.
     * @param string $namespace
     * @param string $class_name
     * @return string
     */
    protected function buildClass(string $namespace, string $class_name): string
    {
        return sprintf('%s\\%s', $namespace, $this->getClassName($class_name));
    }

    /**
     * Return the array of instantiated hooks in their respected instantiated order.
     * @param string $called_class The "Late Static Binding" class name
     * @return WpHooksInterface[]
     */
    protected function getInstantiatedOrder(string $called_class): array
    {
        return $this->instantiated_order[$called_class] ?? [];
    }

    /**
     * Helper to get the PSR4 class name.
     * @param string $class_name
     * @return string
     */
    private function getClassName(string $class_name): string
    {
        return str_replace(['_', '-'], '', ucwords($class_name, '_-'));
    }
}
