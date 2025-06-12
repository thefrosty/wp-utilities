<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;
use function base64_decode;
use function base64_encode;
use function hash;
use function openssl_decrypt;
use function openssl_encrypt;
use function sprintf;
use function substr;

/**
 * Trait Hash
 * @package TheFrosty\WpUtilities\Api
 */
trait Hash
{

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
     * @param string $encryption_key The encryption key.
     * @return string
     */
    protected function decrypt(string $data, string $encryption_key): string
    {
        $key = $this->getHashedKey($encryption_key);
        $vector = substr($this->getHashedKey(sprintf('%s_iv', $encryption_key)), 0, 16);

        return openssl_decrypt(base64_decode($data), 'AES-256-CBC', $key, 0, $vector);
    }

    /**
     * Encrypt a string.
     * @param string $data The string value to encrypt
     * @param string $encryption_key The encryption key. Example `SomeKeyWith4Delimiter|` _maybe_.
     * @return string
     */
    protected function encrypt(string $data, string $encryption_key): string
    {
        $key = $this->getHashedKey($encryption_key);
        $vector = substr($this->getHashedKey(sprintf('%s_iv', $encryption_key)), 0, 16);

        return base64_encode(openssl_encrypt($data, 'AES-256-CBC', $key, 0, $vector));
    }
}
