<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

use Illuminate\Encryption\Encrypter;

/**
 * Trait Hash
 * @package TheFrosty\WpUtilities\Api
 */
trait Hash
{

    private const string CIPHER = 'aes-256-cbc';

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
        return $this->getEncrypter($encryption_key)->decryptString($data);
    }

    /**
     * Encrypt a string.
     * @param string $data The string value to encrypt
     * @param string $encryption_key The encryption key. Example `SomeKeyWith4Delimiter|`.
     * @return string
     */
    protected function encrypt(string $data, string $encryption_key): string
    {
        return $this->getEncrypter($encryption_key)->encryptString($data);
    }

    /**
     * Create an instance of Encrypter.
     * @param string $key
     * @return Encrypter
     */
    private function getEncrypter(string $key): Encrypter
    {
        return new Encrypter($key, self::CIPHER);
    }
}
