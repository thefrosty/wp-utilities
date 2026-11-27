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
use WP_Http;

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

    public function testNon2xxResponsesAreNotReturnedOrCached(): void
    {
        $url = 'https://example.com/wp-json/wp/v2/missing-' . wp_generate_password(6, false);
        $this->fakeHttp('{"code":"rest_no_route","data":{"status":404}}', WP_Http::NOT_FOUND);

        $this->assertFalse($this->remote->retrieveBody($url));
        $this->assertFalse($this->remote->retrieveBodyCached($url, MINUTE_IN_SECONDS));
        $this->assertFalse(get_transient($this->remote->getTransientKey($url)), 'Error bodies are not cached.');

        // A redirect / server error is treated the same way.
        $this->fakeHttp('{"ok":true}', WP_Http::MULTIPLE_CHOICES);
        $this->assertFalse($this->remote->retrieveBody($url));
        $this->fakeHttp('{"ok":true}', WP_Http::INTERNAL_SERVER_ERROR);
        $this->assertFalse($this->remote->retrieveBody($url));

        // An empty 2xx body.
        $this->fakeHttp('', WP_Http::OK);
        $this->assertFalse($this->remote->retrieveBody($url));
    }

    public function testRetrieveBodyReturnsFalseForATransportError(): void
    {
        remove_all_filters('pre_http_request');
        add_filter('pre_http_request', static fn(): WP_Error => new WP_Error('http_request_failed', 'timeout'));

        $this->assertFalse($this->remote->retrieveBody('https://example.com/timeout'));
    }

    private function fakeHttp(string $body, int $code = WP_Http::OK): void
    {
        remove_all_filters('pre_http_request');
        add_filter('pre_http_request', static function () use ($body, $code): array|WP_Error {
            return [
                'headers' => [],
                'body' => $body,
                'response' => ['code' => $code, 'message' => ''],
                'cookies' => [],
                'filename' => null,
            ];
        });
    }
}
