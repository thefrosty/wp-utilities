<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

use Illuminate\Encryption\Encrypter;
use function add_site_option;
use function base64_decode;
use function base64_encode;
use function class_exists;
use function explode;
use function get_site_option;
use function hash;
use function openssl_cipher_iv_length;
use function openssl_decrypt;
use function openssl_encrypt;
use function openssl_random_pseudo_bytes;
use function wp_generate_password;
use const OPENSSL_RAW_DATA;

/**
 * Trait Hash
 * @package TheFrosty\WpUtilities\Api
 * @ref https://gist.github.com/einnar82/3ffbc6e0e3894faa97736c0448d4ed30#file-cipher-php
 */
trait Hash
{

    private string $key;
    public const string OPTION = '_wp_utilities_encryption_key';
    private const string CIPHER = 'AES-256-CBC';

    /**
     * Decrypt a string.
     * @param string $data The encrypted string value.
     * @return string
     */
    public function decrypt(string $data): string
    {
        $encryptor = self::getEncrypter();
        if ($encryptor) {
            return $encryptor->decryptString($data);
        }

        $parts = explode('::', $data, 2);
        $iv = base64_decode($parts[0]);
        $ciphertext = base64_decode($parts[1]);

        return openssl_decrypt($ciphertext, self::CIPHER, self::getEncryptionKey(), OPENSSL_RAW_DATA, $iv);
    }

    /**
     * Encrypt a string.
     * @param string $data The string value to encrypt
     * @return string
     */
    public function encrypt(string $data): string
    {
        $encryptor = self::getEncrypter();
        if ($encryptor) {
            return $encryptor->encryptString($data);
        }

        $iv_size = openssl_cipher_iv_length(self::CIPHER);
        $iv = openssl_random_pseudo_bytes($iv_size);
        $ciphertext = openssl_encrypt($data, self::CIPHER, self::getEncryptionKey(), OPENSSL_RAW_DATA, $iv);
        $ciphertext_hex = base64_encode($ciphertext);
        $iv_hex = base64_encode($iv);

        return "$iv_hex::$ciphertext_hex";
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

        $value = wp_generate_password(length: 32);
        add_site_option(self::OPTION, $value);
        return $value;
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
     * @return Encrypter|null
     */
    private static function getEncrypter(): ?Encrypter
    {
        static $encrypter;
        $key = self::getEncryptionKey();
        if (class_exists(Encryter::class)) {
            $encrypter ??= new Encrypter($key, self::CIPHER);
        }
        return $encrypter;
    }
}
