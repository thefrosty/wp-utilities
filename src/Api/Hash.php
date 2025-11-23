<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

use Illuminate\Encryption\Encrypter;
use function add_site_option;
use function apply_filters;
use function base64_decode;
use function base64_encode;
use function class_exists;
use function get_site_option;
use function hash;
use function openssl_decrypt;
use function openssl_encrypt;
use function sprintf;
use function substr;
use function wp_generate_password;
use const TheFrosty\WpUtilities\CIPHER;
use const TheFrosty\WpUtilities\ENCRYPTION_KEY_OPTION;

/**
 * Trait Hash
 * @package TheFrosty\WpUtilities\Api
 * @ref https://gist.github.com/einnar82/3ffbc6e0e3894faa97736c0448d4ed30#file-cipher-php
 */
trait Hash
{

    /**
     * Decrypt a string.
     * @param string $data The encrypted string value.
     * @param string $encryption_key
     * @return string
     */
    public function decrypt(string $data, string $encryption_key): string
    {
        $encryptor = self::getEncrypter();
        if ($encryptor && self::useEncrypter()) {
            return $encryptor->decryptString($data);
        }

        $key = $this->getHashedKey($encryption_key);
        $vector = substr($this->getHashedKey(sprintf('%s_iv', $encryption_key)), 0, 16);

        return openssl_decrypt(base64_decode($data), 'AES-256-CBC', $key, 0, $vector);
    }

    /**
     * Encrypt a string.
     * @param string $data The string value to encrypt
     * @param string $encryption_key
     * @return string
     */
    public function encrypt(string $data, string $encryption_key): string
    {
        $encryptor = self::getEncrypter();
        if ($encryptor && self::useEncrypter()) {
            return $encryptor->encryptString($data);
        }

        $key = $this->getHashedKey($encryption_key);
        $vector = substr($this->getHashedKey(sprintf('%s_iv', $encryption_key)), 0, 16);

        return base64_encode(openssl_encrypt($data, 'AES-256-CBC', $key, 0, $vector));
    }

    /**
     * Get an encryption key.
     * @return string
     */
    protected static function getEncryptionKey(): string
    {
        $key = get_site_option(ENCRYPTION_KEY_OPTION);
        if ($key !== false) {
            return (string)$key;
        }

        $value = wp_generate_password(32);
        add_site_option(ENCRYPTION_KEY_OPTION, $value);
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
            $encrypter ??= new Encrypter($key, CIPHER);
        }
        return $encrypter;
    }

    /**
     * Use the Illuminate Encrypter package if installed?
     * @return bool
     */
    protected static function useEncrypter(): bool
    {
        return apply_filters('wp_utilities_use_encrypter_package', false, self::class) === true;
    }
}
