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
     *
     * @return Request
     */
    public function getRequest() : Request;

    /**
     * Set the container.
     *
     * @param Request|null $request Symfony HttpFoundation Request object
     * @return $this
     */
    public function setRequest(Request $request = null) : self;
}
