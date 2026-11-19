<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Tests\Api;

use Illuminate\Encryption\Encrypter;
use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use TheFrosty\WpUtilities\Api\Hash;
use TheFrosty\WpUtilities\Tests\Plugin\Framework\TestCase;
use function get_site_option;
use function hash;
use function base64_decode;
use function base64_encode;
use function method_exists;
use function openssl_encrypt;
use function random_bytes;
use function sprintf;
use function str_starts_with;
use function substr;
use const OPENSSL_RAW_DATA;
use const TheFrosty\WpUtilities\ENCRYPTION_KEY_OPTION;

/**
 * Trait HashTest
 * @package TheFrosty\WpUtilities\Tests\Api
 */
#[CoversTrait(Hash::class)]
#[Group('api')]
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
        $key = 'aSecureKey|';

        // Encrypt the data first to get a valid encrypted string with delimiter
        $encrypted_data = $this->hash->encrypt($data, $key);
        $this->assertNotEquals($data, $encrypted_data);

        // Decrypt the data and assert that it matches the original data
        $decrypted_data = $this->hash->decrypt($encrypted_data, $key);
        $this->assertEquals($data, $decrypted_data);
    }

    public function testDecryptNullKey(): void
    {
        $data = 'test data';

        // Encrypt the data first to get a valid encrypted string with delimiter
        $encrypted_data = $this->hash->encrypt($data);
        $this->assertNotEquals($data, $encrypted_data);

        // Decrypt the data and assert that it matches the original data
        $decrypted_data = $this->hash->decrypt($encrypted_data);
        $this->assertEquals($data, $decrypted_data);
    }

    public function testDecryptException(): void
    {
        $data = 'test data';

        // Encrypt the data first to get a valid encrypted string with delimiter
        $encrypted_data = $this->hash->encrypt($data, 'SomeIncorrectKey');
        $this->assertNotEquals($data, $encrypted_data);

        $this->expectException(RuntimeException::class);
        $this->hash->decrypt($encrypted_data);
    }

    public function testDecryptFailed(): void
    {
        $data = 'test data';
        $key = 'aSecureKey|';

        // Encrypt the data first to get a valid encrypted string with delimiter
        $encrypted_data = $this->hash->encrypt($data, $key);
        $this->assertNotEquals($data, $encrypted_data);

        // Decrypt the data and assert that it matches the original data
        $decrypted_data = $this->hash->decrypt($encrypted_data, 'SomeNewChangedKey');
        $this->assertfalse($decrypted_data);
    }

    public function testEncrypt(): void
    {
        $data = 'test data';
        $key = 'aSecureKey|';

        // Encrypt the data and assert that it is not equal to the original data
        $encrypted_data = $this->hash->encrypt($data, $key);
        $this->assertNotEquals($data, $encrypted_data);

        // Decrypt the encrypted data and assert that it matches the original data
        $decrypted_data = $this->hash->decrypt($encrypted_data, $key);
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

    public function testGetCipher(): void
    {
        $this->assertTrue(method_exists($this->hash, 'getCipher'));
        $expected = 'AES-256-CBC';
        $actual = $this->reflection->getMethod('getCipher')->invoke($this->hash);
        $this->assertEquals($expected, $actual);
    }

    public function testGetEncrypter(): void
    {
        $this->assertTrue(method_exists($this->hash, 'getEncrypter'));
        $this->assertNull($this->reflection->getMethod('getEncrypter')->invoke($this->hash));
    }

    public function testUseEncrypter(): void
    {
        $this->assertTrue(method_exists($this->hash, 'useEncrypter'));
        $this->assertFalse($this->reflection->getMethod('useEncrypter')->invoke($this->hash));
    }

    public function testEncryptUsesTheAuthenticatedFormatWithARandomNonce(): void
    {
        $first = $this->hash->encrypt('same value', 'aSecureKey|');
        $second = $this->hash->encrypt('same value', 'aSecureKey|');

        $this->assertTrue(str_starts_with($first, 'v2::'));
        $this->assertNotSame($first, $second, 'Identical plaintexts must not produce identical ciphertexts.');
        $this->assertSame('same value', $this->hash->decrypt($first, 'aSecureKey|'));
        $this->assertSame('same value', $this->hash->decrypt($second, 'aSecureKey|'));
    }

    public function testTamperedCiphertextIsRejected(): void
    {
        $encrypted = $this->hash->encrypt('test data', 'aSecureKey|');
        $raw = base64_decode(substr($encrypted, 4));
        $raw[-1] = $raw[-1] === 'a' ? 'b' : 'a';
        $tampered = 'v2::' . base64_encode($raw);

        $this->assertFalse($this->hash->decrypt($tampered, 'aSecureKey|'));

        // With the default key a failed authentication throws, matching the legacy null-key behaviour.
        $raw = base64_decode(substr($this->hash->encrypt('test data'), 4));
        $raw[-1] = $raw[-1] === 'a' ? 'b' : 'a';
        $this->expectException(RuntimeException::class);
        $this->hash->decrypt('v2::' . base64_encode($raw));
    }

    public function testLegacyKeyedValuesStillDecrypt(): void
    {
        $key = 'aSecureKey|';
        $legacy = base64_encode(openssl_encrypt(
            'legacy data',
            'AES-256-CBC',
            hash('sha256', $key),
            0,
            substr(hash('sha256', sprintf('%s_iv', $key)), 0, 16)
        ));

        $this->assertSame('legacy data', $this->hash->decrypt($legacy, $key));
    }

    public function testLegacyDefaultKeyValuesStillDecrypt(): void
    {
        $key = $this->reflection->getMethod('getEncryptionKey')->invoke($this->hash);
        $iv = random_bytes(16);
        $legacy = sprintf(
            '%1$s::%2$s',
            base64_encode($iv),
            base64_encode(openssl_encrypt('legacy data', 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv))
        );

        $this->assertSame('legacy data', $this->hash->decrypt($legacy));
    }

    public function testGetEncryptionKeyIsStable(): void
    {
        $method = $this->reflection->getMethod('getEncryptionKey');
        $this->assertSame($method->invoke($this->hash), $method->invoke($this->hash));
    }
}
