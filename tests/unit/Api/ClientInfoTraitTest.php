<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use TheFrosty\WpUtilities\Api\ClientInfoTrait;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Trait ClientInfoTraitTest
 * @package TheFrosty\WpUtilities\Tests\Api
 */
#[CoversClass(ClientInfoTrait::class)]
class ClientInfoTraitTest extends TestCase
{
    private $clientInfoTrait;

    protected function setUp(): void
    {
        $this->clientInfoTrait = new class() {
            use ClientInfoTrait;
        };
        $this->reflection = $this->getReflection($this->clientInfoTrait);
    }

    public function testGetIpAddressSame(): void
    {
        $this->assertSame('127.0.0.1', $this->clientInfoTrait->getIpAddress());
    }

    public function testGetIpAddressNull(): void
    {
        $request = Request::createFromGlobals();
        $request->server->set('HTTP_CLIENT_IP', '1999.0991.200.89');
        $request->server->set('REMOTE_ADDR', '1999.0991.200.89');
        $this->assertNull($this->clientInfoTrait->getIpAddress($request));
    }
}
