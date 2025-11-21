<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

use Illuminate\Encryption\Encrypter;
use function get_site_option;
use function hash;
use function wp_generate_password;

/**
 * Trait Hash
 * @package TheFrosty\WpUtilities\Api
 */
trait Hash
{

    public const string OPTION = '_wp_utilities_encryption_key';
    private const string CIPHER = 'AES-256-CBC';

    /**
     * Get an encryption key.
     *  To create a custom KEY, use `add_site_option(TheFrosty\WpUtilities\Api\Hash::OPTION, 'YOURKey')`.
     * @return string
     */
    protected function getEncryptionKey(): string
    {
        static $encryption_key;
        $encryption_key ??= wp_generate_password(32);
        return get_site_option(self::OPTION, $encryption_key);
    }

    /**
     * Get a sha256 hash key.
     * @param string $data
     * @return string
     */
    protected function getHashedKey(string $data): string
    {
        return hash('sha256', $data);
    }

    /**
     * Decrypt a string.
     * @param string $data The encrypted string value.
     * @param string|null $encryption_key The encryption key.
     * @return string
     */
    protected function decrypt(string $data, ?string $encryption_key = null): string
    {
        $encryption_key ??= $this->getEncryptionKey();
        return $this->getEncrypter($encryption_key)->decryptString($data);
    }

    /**
     * Encrypt a string.
     * @param string $data The string value to encrypt
     * @param string|null $encryption_key The encryption key.
     * @return string
     */
    protected function encrypt(string $data, ?string $encryption_key = null): string
    {
        $encryption_key ??= $this->getEncryptionKey();
        return $this->getEncrypter($encryption_key)->encryptString($data);
    }

    /**
     * Create an instance of Encrypter.
     * @param string $key
     * @return Encrypter
     */
    private function getEncrypter(string $key): Encrypter
    {
        static $encrypter;
        $encrypter ??= new Encrypter($key, self::CIPHER);
        return $encrypter;
    }
}
