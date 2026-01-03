<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Utils;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\Utils\Anonymizer;
use function get_option;
use function get_site_option;
use function hash;
use function method_exists;
use function strlen;
use const TheFrosty\WpUtilities\ENCRYPTION_KEY_OPTION;

/**
 * Trait HashTest
 * @package TheFrosty\WpUtilities\Tests\Utils
 */
#[CoversTrait(Anonymizer::class)]
#[Group('utils')]
class AnonymizerTest extends TestCase
{
    private $anonymizer;

    protected function setUp(): void
    {
        $this->anonymizer = new class() {
            use Anonymizer;
        };
        $this->reflection = $this->getReflection($this->anonymizer);
    }

    public function testAnonymize(): void
    {
        $email = 'test@exammle.com';
        $anonymized = $this->anonymizer->anonymize($email);
        $this->assertNotEquals($email, $anonymized);

        $url = 'https://example.com/';
        $anonymized = $this->anonymizer->anonymize($url);
        $this->assertNotEquals($url, $anonymized);

        $value = 'test';
        $anonymized = $this->anonymizer->anonymize($value);
        $this->assertEquals($value, $anonymized);
    }

    public function testUuid(): void
    {
        $this->assertFalse(get_option('_wp_utilities_telemetry_uuid'));
        $uuid = $this->reflection->getMethod('uuid')->invoke($this->anonymizer);
        $this->assertSame(32, strlen($uuid));
        $this->assertSame($uuid, get_site_option('_wp_utilities_telemetry_uuid'));
    }
}
