<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\RestApi\Api;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use TheFrosty\WpUtilities\RestApi\Api\SunsetsEndpoints;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use WP_REST_Response;

/**
 * Class SunsetsEndpointsTest
 * @package TheFrosty\WpUtilities\Tests\RestApi\Api
 */
#[CoversTrait(SunsetsEndpoints::class)]
#[Group('rest-api')]
class SunsetsEndpointsTest extends TestCase
{
    public function testSunsetResponseAddsSunsetAndDeprecationHeaders(): void
    {
        $response = new WP_REST_Response(['ok' => true]);
        $host = new class {
            use SunsetsEndpoints;

            public function expose(WP_REST_Response $response, string $date, ?string $link = null): void
            {
                $this->sunsetResponse($response, $date, $link);
            }
        };

        $host->expose($response, '2030-01-01 00:00:00', 'https://example.com/sunset');
        $headers = $response->get_headers();

        $this->assertArrayHasKey('Sunset', $headers);
        $this->assertNotSame('', $headers['Sunset']);
        $this->assertArrayHasKey('Link', $headers);
        $this->assertStringContainsString('rel="sunset"', (string)$headers['Link']);
    }
}
