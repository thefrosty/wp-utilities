<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

use Symfony\Component\HttpFoundation\Request;

/**
 * Interface HttpFoundationRequestInterface
 * @package TheFrosty\WpUtilities\Plugin
 */
interface HttpFoundationRequestInterface
{
    /**
     * Get the Request object.
     * @return Request|null
     */
    public function getRequest(): ?Request;

    /**
     * Set the Request.
     * @param Request|null $request Symfony HttpFoundation Request object
     */
    public function setRequest(?Request $request = null): void;
}
