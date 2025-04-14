<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Plugin;

use Symfony\Component\HttpFoundation\Request;

/**
 * Symfony HttpFoundation Request trait.
 *
 * @link https://github.com/symfony/http-foundation
 * @package TheFrosty\WpUtilities\Plugin
 */
trait HttpFoundationRequestTrait
{
    /**
     * Symfony HttpFoundation Request object.
     * @var Request|null $request
     */
    private static ?Request $request = null;

    /**
     * Get the Request instance.
     * @return Request|null
     */
    public function getRequest(): ?Request
    {
        return self::$request;
    }

    /**
     * Set the Request.
     * @param Request|null $request
     */
    public function setRequest(?Request $request = null): void
    {
        if (!(self::$request instanceof Request)) {
            self::$request = $request ?? Request::createFromGlobals();
        }
    }
}
