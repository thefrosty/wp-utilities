<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

use Illuminate\Encryption\Encrypter;
use RuntimeException;
use SensitiveParameter;
use function add_site_option;
use function apply_filters;
use function base64_decode;
use function base64_encode;
use function class_exists;
use function explode;
use function get_site_option;
use function hash;
use function openssl_cipher_iv_length;
use function openssl_decrypt;
use function openssl_encrypt;
use function random_bytes;
use function sprintf;
use function substr;
use function wp_generate_password;
use const OPENSSL_RAW_DATA;
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
     * @param string|null $encryption_key
     * @return string
     * @throws RuntimeException
     */
    public function decrypt(string $data, ?string $encryption_key = null): string
    {
        $encryptor = self::getEncrypter($encryption_key);
        if ($encryptor && self::useEncrypter()) {
            return $encryptor->decryptString($data);
        }

        if ($encryption_key === null) {
            $parts = explode('::', $data);
            if (empty($parts[0]) || empty($parts[1])) {
                throw new RuntimeException('Could not decrypt the data.');
            }
            $iv = base64_decode($parts[0]);
            $ciphertext = base64_decode($parts[1]);
            if ($iv === false || $ciphertext === false) {
                throw new RuntimeException('The data is invalid.');
            }

            return openssl_decrypt($ciphertext, CIPHER, self::getEncryptionKey(), OPENSSL_RAW_DATA, $iv);
        }

        $key = $this->getHashedKey($encryption_key);
        $vector = substr($this->getHashedKey(sprintf('%s_iv', $encryption_key)), 0, 16);

        return openssl_decrypt(base64_decode($data), CIPHER, $key, 0, $vector);
    }

    /**
     * Encrypt a string.
     * @param mixed $data The string value to encrypt
     * @param string|null $encryption_key
     * @return string
     * @throws \Random\RandomException
     */
    public function encrypt(#[SensitiveParameter] mixed $data, ?string $encryption_key = null): string
    {
        $encryptor = self::getEncrypter($encryption_key);
        if ($encryptor && self::useEncrypter()) {
            return $encryptor->encryptString($data);
        }

        if ($encryption_key === null) {
            $iv = random_bytes(openssl_cipher_iv_length(CIPHER));

            return sprintf(
                '%1$s":%2$s',
                base64_encode($iv),
                base64_encode(openssl_encrypt($data, CIPHER, self::getEncryptionKey(), OPENSSL_RAW_DATA, $iv))
            );
        }

        $key = $this->getHashedKey($encryption_key);
        $vector = substr($this->getHashedKey(sprintf('%s_iv', $encryption_key)), 0, 16);

        return base64_encode(openssl_encrypt($data, CIPHER, $key, 0, $vector));
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
     * @param string|null $data
     * @return string
     */
    protected function getHashedKey(?string $data = null): string
    {
        $data ??= static::class;
        return hash('sha256', $data);
    }

    /**
     * Create an instance of Encrypter.
     * @param string|null $key
     * @return Encrypter|null
     */
    private static function getEncrypter(?string $key = null): ?Encrypter
    {
        static $encrypter;
        $key ??= self::getEncryptionKey();
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
        return apply_filters('wp_utilities_use_encrypter_package', false, static::class) === true;
    }
}
