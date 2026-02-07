<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestInterface;

/**
 * Class HttpFoundationRequestInterfaceTest
 */
#[CoversNothing]
class HttpFoundationRequestInterfaceTest extends TestCase
{
    public function testHttpFoundationRequestInterface(): void
    {
        $this->assertTrue(interface_exists(HttpFoundationRequestInterface::class));
    }

    public function testInterfaceMethods(): void
    {
        $methods = [
            'setRequest',
            'getRequest',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(HttpFoundationRequestInterface::class, $method),
                "HttpFoundationRequestInterface should have method {$method}"
            );
        }
    }
}
