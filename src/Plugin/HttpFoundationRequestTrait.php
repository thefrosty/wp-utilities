<?php declare(strict_types=1);

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
     *
     * @var Request $request
     */
    private static $request;

    /**
     * {@inheritdoc}
     */
    public function getRequest() : Request
    {
        return self::$request;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequest(Request $request = null)
    {
        if (! (self::$request instanceof Request)) {
            self::$request = $request ?? Request::createFromGlobals();
        }
    }
}
