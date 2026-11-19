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
        $this->assertNotEquals($value, $anonymized);

        $value = 'A';
        $anonymized = $this->anonymizer->anonymize($value);
        $this->assertEquals($value, $anonymized);
    }

    public function testUuid(): void
    {
        $this->assertFalse(get_option('_wp_utilities_telemetry_uuid'));
        $id = $this->anonymizer->uuid();
        $this->assertSame(32, strlen($id));
        $this->assertSame($id, get_site_option('_wp_utilities_telemetry_uuid'));
        // The stored ID is reused.
        $this->assertSame($id, $this->anonymizer->uuid());
    }

    public function testAnonymizeMasksEmailsAndMultiPartDomains(): void
    {
        $this->assertSame('j**n@m**l.e*****e.com', $this->anonymizer->anonymize('john@mail.example.com'));
        $this->assertSame('j**n@e*****e.com', $this->anonymizer->anonymize('john@example.com'));
        $this->assertSame('a*', $this->anonymizer->anonymize('ab'));
    }

    public function testMaskDomainKeepsShortSecondLevelSuffixes(): void
    {
        $mask = $this->reflection->getMethod('maskDomain');

        $this->assertSame('m**l.e*****e.co.uk', $mask->invoke($this->anonymizer, 'mail.example.co.uk'));
        $this->assertSame('w*w.e*****e.com', $mask->invoke($this->anonymizer, 'www.example.com'));
        $this->assertSame('e*****e.org', $mask->invoke($this->anonymizer, 'example.org'));
        $this->assertSame('', $mask->invoke($this->anonymizer, ''));
    }

    public function testMaskEmailAndMaskStringEdgeCases(): void
    {
        $this->assertSame('not-an-email', $this->reflection->getMethod('maskEmail')->invoke($this->anonymizer, 'not-an-email'));
        $this->assertSame('', $this->reflection->getMethod('maskString')->invoke($this->anonymizer, ''));
        $this->assertSame('x', $this->reflection->getMethod('maskString')->invoke($this->anonymizer, 'x'));
    }
}
