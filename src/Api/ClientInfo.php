<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

use Symfony\Component\HttpFoundation\Request;
use function filter_var;
use function sanitize_text_field;
use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_VALIDATE_IP;

/**
 * Class ClientInfo
 * @package TheFrosty\WpUtilities\Api
 */
class ClientInfo
{

    /**
     * Get the clients IP.
     * @ref https://dev.to/rogeriotaques/an-easy-way-to-get-the-real-client-ip-in-php-4pii
     * @param Request|null $request
     * @return string|null
     */
    public function getIpAddress(?Request $request = null): ?string
    {
        $request ??= Request::createFromGlobals();

        $ip = $request->server->get(
            'HTTP_CLIENT_IP',
            $request->server->get(
                'HTTP_CF_CONNECTING_IP',
                $request->server->get(
                    'HTTP_X_FORWARDED',
                    $request->server->get(
                        'HTTP_X_FORWARDED_FOR',
                        $request->server->get(
                            'HTTP_FORWARDED',
                            $request->server->get(
                                'HTTP_FORWARDED_FOR',
                                $request->server->get('REMOTE_ADDR')
                            )
                        )
                    )
                )
            )
        );

        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6)) {
            return null;
        }

        return sanitize_text_field($ip);
    }
}
