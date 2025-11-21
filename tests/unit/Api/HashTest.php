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
        $expected = hash('sha256', $data);
        $actual = $this->reflection->getMethod('getHashedKey')->invoke($this->hash, $data);
        $this->assertTrue(method_exists($this->hash, 'getHashedKey'));
        $this->assertEquals($expected, $actual);
    }

    public function testDecrypt(): void
    {
        $data = 'test data';

        // Encrypt the data first to get a valid encrypted string with delimiter
        $encrypted_data = $this->encrypt($data);
        $this->assertNotEquals($data, $encrypted_data);

        // Decrypt the data and assert that it matches the original data
        $decrypted_data = $this->decrypt($encrypted_data);
        $this->assertEquals($data, $decrypted_data);
    }

    public function testEncrypt(): void
    {
        $data = 'test data';

        // Encrypt the data and assert that it is not equal to the original data
        $encrypted_data = $this->encrypt($data);
        $this->assertNotEquals($data, $encrypted_data);

        // Decrypt the encrypted data and assert that it matches the original data
        $decrypted_data = $this->decrypt($encrypted_data);
        $this->assertEquals($data, $decrypted_data);
    }

    private function decrypt(string $data): string
    {
        return $this->reflection->getMethod('decrypt')->invoke($this->hash, $data);
    }

    private function encrypt(string $data): string
    {
        return $this->reflection->getMethod('encrypt')->invoke($this->hash, $data);
    }
}
