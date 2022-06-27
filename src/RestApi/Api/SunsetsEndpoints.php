<?php declare(strict_types=1);

namespace TheFrosty\WpUtilities\RestApi\Api;

use DateTimeImmutable;
use DateTimeInterface;
use WP_REST_Response;
use function current_datetime;
use function sprintf;
use function wp_timezone;

/**
 * Trait SunsetsEndpoints
 * The Sunset header is an in-development HTTP response header that is aiming to standardize how URLs are marked
 * for deprecation. tl:dr; it looks a bit like this: `Sunset: Sat, 31 Dec 2018 23:59:59 GMT`.
 * @link https://datatracker.ietf.org/doc/html/draft-wilde-sunset-header-03
 * @package TheFrosty\WpUtilities\RestApi\Api
 */
trait SunsetsEndpoints
{

    /**
     * Adds the Sunset and optional Link headers to a response, marking the endpoint for deprecation.
     * @param WP_REST_Response $response
     * @param string $date
     * @param string|null $link
     */
    public function sunsetResponse(WP_REST_Response $response, string $date, ?string $link = null): void
    {
        $sunset = $this->normalizeDate($date);
        $response->header(RestRequest::HEADER_SUNSET, $sunset);
        if ($sunset > current_datetime()) {
            $response->header(RestRequest::HEADER_DEPRECATION, 'true');
        }

        if ($link !== null) {
            $response->header('Link', sprintf('<%s>; rel="sunset"', $link), false);
        }
    }

    /**
     * Format the string date into the `RFC7231` format.
     * @param string $date
     * @return string
     */
    private function normalizeDate(string $date): string
    {
        return (new DateTimeImmutable($date, wp_timezone()))->format(DateTimeInterface::RFC7231);
    }
}
