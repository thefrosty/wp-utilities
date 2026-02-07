<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Plugin;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestInterface;
use TheFrosty\WpUtilities\Plugin\HttpFoundationRequestTrait;
use function trait_exists;

/**
 * Class HttpFoundationRequestTraitTest
 */
class HttpFoundationRequestTraitTest extends TestCase
{
    public function testHasTrait(): void
    {
        $class = new class implements HttpFoundationRequestInterface {
            use HttpFoundationRequestTrait;
        };

        $this->assertTrue(trait_exists(HttpFoundationRequestTrait::class));
        $this->assertInstanceOf(HttpFoundationRequestInterface::class, $class);
    }

    public function testSetRequest(): void
    {
        $class = new class implements HttpFoundationRequestInterface {
            use HttpFoundationRequestTrait;
        };

        $request = new Request();
        $class->setRequest($request);
        $this->assertSame($request, $class->getRequest());
    }

    public function testGetRequest(): void
    {
        $class = new class implements HttpFoundationRequestInterface {
            use HttpFoundationRequestTrait;
        };

        $this->assertNull($class->getRequest());
    }
}
