<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

/**
 * Interface RegistrarInterface
 * @package TheFrosty\WpUtilities\Api
 */
interface RegistrarInterface
{

    /**
     * Return an array of objects to register.
     * @return array
     */
    public function getObjectClasses(): array;
}
