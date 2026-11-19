<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities;

use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\Request;
use TheFrosty\WpUtilities\Exceptions\TerminationException;
use function apply_filters;
use function array_filter;
use function array_map;
use function array_reverse;
use function explode;
use function filter_var;
use function get_bloginfo;
use function is_array;
use function is_callable;
use function is_string;
use function sanitize_text_field;
use function version_compare;
use function wp_enqueue_script;
use function wp_register_script;
use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_VALIDATE_BOOLEAN;
use const FILTER_VALIDATE_IP;
use const PHP_SAPI;

const CIPHER = 'AES-256-CBC';
const ENCRYPTION_KEY_OPTION = '_wp_utilities_encryption_key';

/**
 * Exit or throw an exception.
 * @param bool|callable|null $throw Should an exception be thrown?
 * @param string $message The Throwable message.
 * @param string|int $status The exit status.
 * @param array|null $args callable args
 * @return never
 * @throws TerminationException
 */
function exitOrThrow(
    bool|callable|null $throw = null,
    string $message = '',
    string|int $status = 0,
    ?array $args = null
): never {
    $throw ??= isPhpunit();
    if ($throw === true || (is_callable($throw) && filter_var($throw(...$args), FILTER_VALIDATE_BOOLEAN))) {
        throw new TerminationException($message);
    }

    exit($status);
}

/**
 * Get the client's IP.
 * Uses `REMOTE_ADDR`, which can't be spoofed by the client. Proxy headers (`X-Forwarded-For` by default) are only
 * read when `REMOTE_ADDR` is a trusted proxy, configured via the `thefrosty/wp_utilities/trusted_proxies` filter
 * (IPs or CIDR ranges, e.g. your load balancer or Cloudflare's ranges). The header can be changed with the
 * `thefrosty/wp_utilities/trusted_proxy_header` filter (e.g. `HTTP_CF_CONNECTING_IP`).
 * @param Request|null $request
 * @param string[]|null $trusted_proxies Optional trusted proxies, overrides the filter.
 * @return string|null
 */
function getIpAddress(?Request $request = null, ?array $trusted_proxies = null): ?string
{
    $request ??= Request::createFromGlobals();

    $ip = $request->server->get('REMOTE_ADDR');
    $trusted_proxies ??= (array)apply_filters('thefrosty/wp_utilities/trusted_proxies', []);
    if (is_string($ip) && $trusted_proxies !== [] && IpUtils::checkIp($ip, $trusted_proxies)) {
        $header = apply_filters('thefrosty/wp_utilities/trusted_proxy_header', 'HTTP_X_FORWARDED_FOR');
        $forwarded = $request->server->get(is_string($header) ? $header : 'HTTP_X_FORWARDED_FOR', '');
        $hops = array_reverse(array_filter(array_map('trim', explode(',', (string)$forwarded))));
        // Walk from the closest hop: the client is the first address that isn't one of our proxies.
        foreach ($hops as $hop) {
            if (!filter_var($hop, FILTER_VALIDATE_IP)) {
                break; // Malformed chain, keep the last trusted address.
            }

            $ip = $hop;
            if (!IpUtils::checkIp($hop, $trusted_proxies)) {
                break;
            }
        }
    }

    if (!is_string($ip) || !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
        return null;
    }

    return sanitize_text_field($ip);
}

/**
 * Get the current HTTP Request.
 * @param Request|null $request
 * @return Request
 */
function getRequest(?Request $request = null): Request
{
    return $request ?? Request::createFromGlobals();
}

/**
 * Is the current request CLI and PHPunit?
 * @param Request|null $request
 * @return bool
 * @access private
 */
function isPhpunit(?Request $request = null): bool
{
    $request ??= Request::createFromGlobals();
    if (PHP_SAPI !== 'cli' || !$request->server->has('argv')) {
        return false;
    }

    $argv = $request->server->get('argv');
    return isset($argv[0]) && (str_contains($argv[0], 'phpunit') || $argv[0] === '/usr/bin/phpunit');
}

/**
 * 6.3.0 Stub for PHP 8.0+.
 * Registers a new script.
 * Registers a script to be enqueued later using the wp_enqueue_script() function.
 * @param string $handle Name of the script. Should be unique.
 * @param string|false $src Full URL of the script, or path of the script relative to the WordPress root directory.
 *                                    If source is set to false, script is an alias of other scripts it depends on.
 * @param string[] $deps Optional. An array of registered script handles this script depends on. Default empty array.
 * @param string|bool|int|null $ver Optional. String specifying script version number, if it has one, which is added to
 *     the URL as a query string for cache busting purposes. If version is set to false, a version number is
 *     automatically added equal to current installed WordPress version. If set to null, no version is added.
 * @param array|bool $args {
 *      Optional. An array of additional script loading strategies. Default empty array.
 *      Otherwise, it may be a boolean in which case it determines whether the script is printed in the footer. Default
 *     false.
 * @type string $strategy Optional. If provided, may be either 'defer' or 'async'.
 * @type bool $in_footer Optional. Whether to print the script in the footer. Default 'false'.
 * }
 * @return bool Whether the script has been registered. True on success, false on failure.
 * @see WP_Dependencies::add()
 * @see WP_Dependencies::add_data()
 * @since 2.1.0
 * @since 4.3.0 A return value was added.
 * @since 6.3.0 The $in_footer parameter of type boolean was overloaded to be an $args parameter of type array.
 */
function wpRegisterScript(
    string $handle,
    string|false $src,
    array $deps = [],
    mixed $ver = false,
    bool|array $args = []
): bool {
    if (!is_array($args)) {
        $args = [
            'in_footer' => $args,
        ];
    }

    if (version_compare(get_bloginfo('version'), '6.3') >= 0) {
        return wp_register_script($handle, $src, $deps, $ver, $args);
    }

    return wp_register_script($handle, $src, $deps, $ver, $args['in_footer']);
}

/**
 * 6.3.0 Stub for PHP 8.0+.
 * Enqueues a script.
 * Registers the script if $src provided (does NOT overwrite), and enqueues it.
 * @param string $handle Name of the script. Should be unique.
 * @param string|false $src Full URL of the script, or path of the script relative to the WordPress root directory.
 *                                    Default empty.
 * @param string[] $deps Optional. An array of registered script handles this script depends on. Default empty array.
 * @param string|bool|int|null $ver Optional. String specifying script version number, if it has one, which is added to
 *     the URL as a query string for cache busting purposes. If version is set to false, a version number is
 *     automatically added equal to current installed WordPress version. If set to null, no version is added.
 * @param array|bool $args {
 *      Optional. An array of additional script loading strategies. Default empty array.
 *      Otherwise, it may be a boolean in which case it determines whether the script is printed in the footer. Default
 *     false.
 * @see WP_Dependencies::add()
 * @see WP_Dependencies::add_data()
 * @see WP_Dependencies::enqueue()
 * @since 2.1.0
 * @since 6.3.0 The $in_footer parameter of type boolean was overloaded to be an $args parameter of type array.
 */
function wpEnqueueScript(
    string $handle,
    string|false $src = '',
    array $deps = [],
    mixed $ver = false,
    bool|array $args = []
): void {
    if (!is_array($args)) {
        $args = [
            'in_footer' => $args,
        ];
    }

    if (version_compare(get_bloginfo('version'), '6.3') >= 0) {
        wp_enqueue_script($handle, $src, $deps, $ver, $args);

        return;
    }

    wp_enqueue_script($handle, $src, $deps, $ver, $args['in_footer']);
}
