<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Utils;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use TheFrosty\WpUtilities\Utils\Anonymizer;
use function get_option;
use function get_site_option;
use function strlen;

/**
 * Class AnonymizerTest
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
        $id = $this->anonymizer->uuid();
        $this->assertSame(32, strlen($id));
        $this->assertSame($id, get_site_option('_wp_utilities_telemetry_uuid'));
    }
}
