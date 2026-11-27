<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Utils;

use Symfony\Component\HttpFoundation\IpUtils;
use function apply_filters;
use function array_filter;
use function array_values;
use function is_string;

/**
 * Class Cloudflare
 * Cloudflare's edge IP ranges, used to trust the `CF-Connecting-IP` header only on requests that came through
 * Cloudflare.
 * @link https://www.cloudflare.com/ips/
 * @package TheFrosty\WpUtilities\Utils
 */
final class Cloudflare
{

    /**
     * The `$_SERVER` key of the header Cloudflare sets to the visitor's IP.
     */
    public const string HEADER_CONNECTING_IP = 'HTTP_CF_CONNECTING_IP';

    /**
     * Filter: whether to trust `CF-Connecting-IP` from Cloudflare's edge. Default true.
     */
    public const string FILTER_TRUST = 'thefrosty/wp_utilities/trust_cloudflare';

    /**
     * Filter: the Cloudflare IP ranges (IPv4 & IPv6 CIDRs). Default `self::IP_RANGES`.
     */
    public const string FILTER_IP_RANGES = 'thefrosty/wp_utilities/cloudflare_ip_ranges';

    /**
     * Cloudflare's published edge ranges (last updated by Cloudflare: Sep 28, 2023).
     * @link https://www.cloudflare.com/ips-v4/
     * @link https://www.cloudflare.com/ips-v6/
     */
    public const array IP_RANGES = [
        // IPv4.
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/13',
        '104.24.0.0/14',
        '172.64.0.0/13',
        '131.0.72.0/22',
        // IPv6.
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2a06:98c0::/29',
        '2c0f:f248::/32',
    ];

    /**
     * Is trusting Cloudflare enabled?
     * @return bool
     */
    public static function isTrusted(): bool
    {
        return (bool)apply_filters(self::FILTER_TRUST, true);
    }

    /**
     * Get the (filtered) Cloudflare IP ranges.
     * @return string[]
     */
    public static function getIpRanges(): array
    {
        return array_values(array_filter(
            (array)apply_filters(self::FILTER_IP_RANGES, self::IP_RANGES),
            static fn(mixed $range): bool => is_string($range) && $range !== ''
        ));
    }

    /**
     * Is the IP one of Cloudflare's edge addresses?
     * @param string $ip
     * @return bool
     */
    public static function isCloudflareIp(string $ip): bool
    {
        $ranges = self::getIpRanges();

        return $ranges !== [] && IpUtils::checkIp($ip, $ranges);
    }
}
