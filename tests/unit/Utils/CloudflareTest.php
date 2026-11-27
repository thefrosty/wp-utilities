<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Utils;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\Utils\Cloudflare;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Class CloudflareTest
 * @package TheFrosty\WpUtilities\Tests\Utils
 */
#[CoversClass(Cloudflare::class)]
#[Group('utils')]
class CloudflareTest extends TestCase
{
    protected function tearDown(): void
    {
        remove_all_filters(Cloudflare::FILTER_TRUST);
        remove_all_filters(Cloudflare::FILTER_IP_RANGES);
        parent::tearDown();
    }

    public function testIsTrustedByDefault(): void
    {
        $this->assertTrue(Cloudflare::isTrusted());
        add_filter(Cloudflare::FILTER_TRUST, '__return_false');
        $this->assertFalse(Cloudflare::isTrusted());
    }

    public function testIpRangesAreValidCidrs(): void
    {
        $this->assertSame(Cloudflare::IP_RANGES, Cloudflare::getIpRanges());
        foreach (Cloudflare::IP_RANGES as $range) {
            [$address, $bits] = explode('/', $range);
            $this->assertNotFalse(filter_var($address, FILTER_VALIDATE_IP), $range);
            $this->assertTrue(IpUtils::checkIp($address, $range), $range);
            $this->assertMatchesRegularExpression('/^\d{1,3}$/', $bits, $range);
        }
    }

    public function testIpRangesFilterDropsInvalidEntries(): void
    {
        add_filter(Cloudflare::FILTER_IP_RANGES, static fn(): array => ['192.0.2.0/24', '', 42, null]);
        $this->assertSame(['192.0.2.0/24'], Cloudflare::getIpRanges());
        $this->assertTrue(Cloudflare::isCloudflareIp('192.0.2.10'));
        $this->assertFalse(Cloudflare::isCloudflareIp('172.69.134.187'));

        add_filter(Cloudflare::FILTER_IP_RANGES, '__return_empty_array', 11);
        $this->assertFalse(Cloudflare::isCloudflareIp('192.0.2.10'));
    }

    #[DataProvider('ipProvider')]
    public function testIsCloudflareIp(string $ip, bool $expected): void
    {
        $this->assertSame($expected, Cloudflare::isCloudflareIp($ip));
    }

    public static function ipProvider(): array
    {
        return [
            'edge 172.64.0.0/13' => ['172.69.134.187', true],
            'edge 104.16.0.0/13' => ['104.16.1.1', true],
            'edge 162.158.0.0/15' => ['162.159.255.255', true],
            'edge IPv6' => ['2606:4700::6810:84e5', true],
            'documentation IPv4' => ['198.51.100.20', false],
            'private' => ['10.0.0.2', false],
            'loopback' => ['127.0.0.1', false],
            'documentation IPv6' => ['2001:db8::20', false],
        ];
    }
}
