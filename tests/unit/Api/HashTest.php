<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Api;

use PHPUnit\Framework\Attributes\CoversClass;
use TheFrosty\WpUtilities\Api\Hash;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;

/**
 * Trait HashTest
 * @package TheFrosty\WpUtilities\Tests\Api
 */
#[CoversClass(Hash::class)]
class HashTest extends TestCase
{
    private $hash;

    protected function setUp(): void
    {
        $this->hash = new class() {
            use Hash;
        };
        $this->reflection = $this->getReflection($this->hash);
    }

    public function testGetHashedKey(): void
    {
        $data = 'example_input';
        $expected = hash('sha256', $data, true);
        $actual = $this->hash->getHashedKey($data);
        $this->assertEquals($expected, $actual);
    }

    public function testDecryptWithDelimiter(): void
    {
        $original_data = 'test data';
        $encryption_key = 'SomeKeyWith4Delimiter|';

        // Encrypt the data first to get a valid encrypted string with delimiter
        $encrypted_data = $this->hash->encrypt($original_data, $encryption_key);

        // Decrypt the data and assert that it matches the original data
        $decrypted_data = $this->hash->decrypt($encrypted_data, $encryption_key);
        $this->assertEquals($original_data, $decrypted_data);
    }

    public function testDecryptWithoutDelimiter(): void
    {
        $base64_encoded_data = base64_encode('test data');
        $encryption_key = 'SomeKeyWith4Delimiter|';

        // Decrypt the data without delimiter and assert that it matches the original data
        $decrypted_data = $this->hash->decrypt($base64_encoded_data, $encryption_key);
        $this->assertEquals('test data', $decrypted_data);
    }

    public function testDecrypt(): void
    {
    }

    public function testEncrypt(): void
    {
    }
}
