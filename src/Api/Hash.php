<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

use Illuminate\Encryption\Encrypter;
use function add_site_option;
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
     * Decrypt a string.
     * @param string $data The encrypted string value.
     * @return string
     */
    public function decrypt(string $data): string
    {
        return self::getEncrypter()->decryptString($data);
    }

    /**
     * Encrypt a string.
     * @param string $data The string value to encrypt
     * @return string
     */
    public function encrypt(string $data): string
    {
        return self::getEncrypter()->encryptString($data);
    }

    /**
     * Get an encryption key.
     * @return string
     */
    protected static function getEncryptionKey(): string
    {
        $key = get_site_option(self::OPTION);
        if ($key !== false) {
            return (string)$key;
        }

        $default = wp_generate_password(length: 32, extra_special_chars: true);
        add_site_option(self::OPTION, $default);
        return $default;
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
     * Create an instance of Encrypter.
     * @return Encrypter
     */
    private static function getEncrypter(): Encrypter
    {
        static $encrypter;
        $key = self::getEncryptionKey();
        $encrypter ??= new Encrypter($key, self::CIPHER);
        return $encrypter;
    }
}
