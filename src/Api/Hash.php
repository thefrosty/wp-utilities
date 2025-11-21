<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

use Random\RandomException;
use RuntimeException;
use function base64_decode;
use function base64_encode;
use function explode;
use function hash;
use function openssl_cipher_iv_length;
use function openssl_decrypt;
use function openssl_encrypt;
use function random_bytes;
use function str_contains;
use const OPENSSL_RAW_DATA;

/**
 * Trait Hash
 * @package TheFrosty\WpUtilities\Api
 */
trait Hash
{

    private const string CIPHER = 'AES-256-CBC';

    /**
     * Get a sha256 hash key.
     * @param string $data
     * @return string
     */
    protected function getHashedKey(string $data): string
    {
        return hash('sha256', $data, true);
    }

    /**
     * Decrypt a string.
     * @param string $data The encrypted string value.
     * @param string $encryption_key The encryption key.
     * @return string
     */
    protected function decrypt(string $data, string $encryption_key): string
    {
        $key = $this->getHashedKey($encryption_key);
        if (!str_contains($data, '::')) {
            return base64_decode($data);
        }
        [$encrypted_data_base64, $iv_base64] = explode('::', $data, 2);
        $encrypted_data = base64_decode($encrypted_data_base64);
        $iv = base64_decode($iv_base64);

        return openssl_decrypt($encrypted_data, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
    }

    /**
     * Encrypt a string.
     * @param string $data The string value to encrypt
     * @param string $encryption_key The encryption key. Example `SomeKeyWith4Delimiter|`.
     * @return string
     * @throws RuntimeException
     */
    protected function encrypt(string $data, string $encryption_key): string
    {
        $key = $this->getHashedKey($encryption_key);
        // Is the encryption method is cryptographically strong?
        $iv_length = openssl_cipher_iv_length(self::CIPHER);
        try {
            $iv = random_bytes($iv_length);
            $encrypted_data = openssl_encrypt($data, self::CIPHER, $key, OPENSSL_RAW_DATA, $iv);
            if ($encrypted_data === false) {
                throw new RuntimeException('Encryption failed');
            }
            return base64_encode($encrypted_data . '::' . base64_encode($iv)); // Append IV to encrypted data.
        } catch (RandomException) {
            return base64_encode($data); // Return unencrypted on error as base64 encode.
        }
    }
}
