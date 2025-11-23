<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Api;

use Illuminate\Encryption\Encrypter;
use PHPUnit\Framework\Attributes\CoversTrait;
use TheFrosty\WpUtilities\Api\Hash;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use function get_site_option;
use function hash;
use function method_exists;
use const TheFrosty\WpUtilities\ENCRYPTION_KEY_OPTION;

/**
 * Trait HashTest
 * @package TheFrosty\WpUtilities\Tests\Api
 */
#[CoversTrait(Hash::class)]
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

    public function testDecrypt(): void
    {
        $data = 'test data';

        // Encrypt the data first to get a valid encrypted string with delimiter
        $encrypted_data = $this->hash->encrypt($data);
        $this->assertNotEquals($data, $encrypted_data);

        // Decrypt the data and assert that it matches the original data
        $decrypted_data = $this->hash->decrypt($encrypted_data);
        $this->assertEquals($data, $decrypted_data);
    }

    public function testEncrypt(): void
    {
        $data = 'test data';

        // Encrypt the data and assert that it is not equal to the original data
        $encrypted_data = $this->hash->encrypt($data);
        $this->assertNotEquals($data, $encrypted_data);

        // Decrypt the encrypted data and assert that it matches the original data
        $decrypted_data = $this->hash->decrypt($encrypted_data);
        $this->assertEquals($data, $decrypted_data);
    }

    public function testGetEncryptionKey(): void
    {
        $this->assertTrue(method_exists($this->hash, 'getEncryptionKey'));
        $actual = $this->reflection->getMethod('getEncryptionKey')->invoke($this->hash);
        $this->assertIsString($actual);
        $this->assertSame(get_site_option(ENCRYPTION_KEY_OPTION), $actual);
    }

    public function testGetHashedKey(): void
    {
        $this->assertTrue(method_exists($this->hash, 'getHashedKey'));
        $data = 'example_input';
        $expected = hash('sha256', $data);
        $actual = $this->reflection->getMethod('getHashedKey')->invoke($this->hash, $data);
        $this->assertEquals($expected, $actual);
    }

    public function testGetEncrypter(): void
    {
        $this->assertTrue(method_exists($this->hash, 'getEncrypter'));
        $this->assertNull($this->reflection->getMethod('getEncrypter')->invoke($this->hash));
    }
}
