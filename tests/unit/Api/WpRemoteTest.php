<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Api;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesTrait;
use TheFrosty\WpUtilities\Api\Hash;
use TheFrosty\WpUtilities\Api\TransientsTrait;
use TheFrosty\WpUtilities\Api\WpCacheTrait;
use TheFrosty\WpUtilities\Api\WpRemote;
use TheFrosty\WpUtilities\Plugin\Plugin;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use WP_Error;

/**
 * Class WpRemoteTest
 * @package TheFrosty\WpUtilities\Tests\Api
 */
#[CoversTrait(WpRemote::class)]
#[UsesTrait(Hash::class)]
#[UsesTrait(TransientsTrait::class)]
#[UsesTrait(WpCacheTrait::class)]
#[UsesClass(Plugin::class)]
#[Group('api')]
class WpRemoteTest extends TestCase
{
    private object $remote;

    protected function setUp(): void
    {
        parent::setUp();
        $this->remote = new class {
            use WpRemote;
        };
    }

    protected function tearDown(): void
    {
        remove_all_filters('pre_http_request');
        parent::tearDown();
    }

    public function testRetrieveBodyRejectsUnsupportedMethods(): void
    {
        $this->assertFalse($this->remote->retrieveBody('https://example.com', [], 'PUT'));
    }

    public function testRetrieveBodyDecodesJsonAndRejectsInvalidJson(): void
    {
        $this->fakeHttp('{"ok":true}');
        $body = $this->remote->retrieveBody('https://example.com/ok');
        $this->assertTrue($body->ok);

        $this->fakeHttp('not-json');
        $this->assertFalse($this->remote->retrieveBody('https://example.com/bad'));
    }

    public function testRetrieveBodyCachedStoresTheDecodedBody(): void
    {
        $this->fakeHttp('{"cached":true}');
        $body = $this->remote->retrieveBodyCached('https://example.com/cached', 60, 'WpUtilities', '1.0');

        $this->assertTrue($body->cached);
        $this->assertEquals($body, $this->remote->retrieveBodyCached('https://example.com/cached', 60));
    }

    public function testRemoteGetAndPostReturnTheHttpResponse(): void
    {
        $this->fakeHttp('{"method":"ok"}');

        $this->assertSame('{"method":"ok"}', $this->remote->wpRemoteGet('https://example.com/get')['body']);
        $this->assertSame('{"method":"ok"}', $this->remote->wpRemotePost('https://example.com/post')['body']);
    }

    private function fakeHttp(string $body): void
    {
        remove_all_filters('pre_http_request');
        add_filter('pre_http_request', static function () use ($body): array|WP_Error {
            return [
                'headers' => [],
                'body' => $body,
                'response' => ['code' => 200, 'message' => 'OK'],
                'cookies' => [],
                'filename' => null,
            ];
        });
    }
}
