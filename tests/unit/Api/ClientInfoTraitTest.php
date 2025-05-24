<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Api;

use TheFrosty\WpUtilities\Api\ClientInfoTrait;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Trait ClientInfoTraitTest
 * @package TheFrosty\WpUtilities\Tests\Api
 */
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

    public function testGetIpAddress(): void
    {
        $this->assertNull($this->clientInfoTrait->getIpAddress());
    }
}
