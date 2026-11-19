<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Api;

use Illuminate\Encryption\Encrypter;
use RuntimeException;
use SensitiveParameter;
use Throwable;
use function add_site_option;
use function apply_filters;
use function base64_decode;
use function base64_encode;
use function class_exists;
use function constant;
use function defined;
use function explode;
use function get_site_option;
use function hash;
use function hash_hkdf;
use function is_string;
use function openssl_cipher_iv_length;
use function openssl_decrypt;
use function openssl_encrypt;
use function random_bytes;
use function sprintf;
use function str_starts_with;
use function strlen;
use function substr;
use function wp_generate_password;
use const OPENSSL_RAW_DATA;
use const TheFrosty\WpUtilities\CIPHER;
use const TheFrosty\WpUtilities\ENCRYPTION_KEY_OPTION;

/**
 * Trait Hash
 * New values are encrypted with AES-256-GCM (authenticated, random nonce) and prefixed with `v2::`. Values written
 * by previous versions (AES-256-CBC, `iv::ciphertext` or a deterministic IV) can still be decrypted.
 * @package TheFrosty\WpUtilities\Api
 * @ref https://gist.github.com/einnar82/3ffbc6e0e3894faa97736c0448d4ed30#file-cipher-php
 */
trait Hash
{

    /**
     * Optional wp-config.php constant holding the encryption key, so the key isn't stored next to the data.
     */
    private const string KEY_CONSTANT = 'WP_UTILITIES_ENCRYPTION_KEY';

    /**
     * Decrypt a string.
     * @param string $data The encrypted string value.
     * @param string|null $encryption_key
     * @return false|string
     * @throws RuntimeException
     */
    public function decrypt(string $data, #[SensitiveParameter] ?string $encryption_key = null): false|string
    {
        if (str_starts_with($data, self::getVersionPrefix())) {
            if ($encryption_key !== null) {
                return self::decryptAead($data, $encryption_key);
            }

            $decrypted = self::decryptAead($data, self::getEncryptionKey());
            // Values written before the wp-config constant was defined used the stored site option key.
            if ($decrypted === false && self::getEncryptionKey() !== self::getStoredEncryptionKey()) {
                $decrypted = self::decryptAead($data, self::getStoredEncryptionKey());
            }

            if ($decrypted === false) {
                throw new RuntimeException('Could not decrypt the data.');
            }

            return $decrypted;
        }

        $encryptor = self::getEncrypter($encryption_key);
        if ($encryptor && self::useEncrypter()) {
            try {
                return $encryptor->decryptString($data);
            } catch (Throwable) {
                // Not an Encrypter payload (e.g. written before the package was enabled), try the legacy formats.
            }
        }

        // Legacy (pre `v2::`) formats.
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

            return openssl_decrypt(
                $ciphertext,
                self::getCipher(),
                self::getStoredEncryptionKey(),
                OPENSSL_RAW_DATA,
                $iv
            );
        }

        $key = $this->getHashedKey($encryption_key);
        $vector = substr($this->getHashedKey(sprintf('%s_iv', $encryption_key)), 0, 16);

        return openssl_decrypt(base64_decode($data), self::getCipher(), $key, 0, $vector);
    }

    /**
     * Encrypt a string.
     * @param mixed $data The string value to encrypt
     * @param string|null $encryption_key
     * @return string
     * @throws RuntimeException
     */
    public function encrypt(
        #[SensitiveParameter] mixed $data,
        #[SensitiveParameter] ?string $encryption_key = null
    ): string {
        $encryptor = self::getEncrypter($encryption_key);
        if ($encryptor && self::useEncrypter()) {
            return $encryptor->encryptString($data);
        }

        $key = self::deriveKey($encryption_key ?? self::getEncryptionKey());
        $nonce = random_bytes(openssl_cipher_iv_length(self::getAeadCipher()));
        $tag = '';
        $ciphertext = openssl_encrypt((string)$data, self::getAeadCipher(), $key, OPENSSL_RAW_DATA, $nonce, $tag);
        if ($ciphertext === false) {
            throw new RuntimeException('Could not encrypt the data.');
        }

        return self::getVersionPrefix() . base64_encode($nonce . $tag . $ciphertext);
    }

    /**
     * Get an encryption key.
     * Prefers the `WP_UTILITIES_ENCRYPTION_KEY` constant (define it in wp-config.php), and falls back to a random
     * key stored in the site options.
     * @return string
     */
    protected static function getEncryptionKey(): string
    {
        if (defined(self::KEY_CONSTANT) && is_string(constant(self::KEY_CONSTANT)) && constant(self::KEY_CONSTANT)) {
            return constant(self::KEY_CONSTANT);
        }

        return self::getStoredEncryptionKey();
    }

    /**
     * Get (or create) the random encryption key stored in the site options.
     * @return string
     */
    protected static function getStoredEncryptionKey(): string
    {
        $option = defined('ENCRYPTION_KEY_OPTION') ? ENCRYPTION_KEY_OPTION : '_wp_utilities_encryption_key';
        $key = get_site_option($option);
        if ($key !== false) {
            return (string)$key;
        }

        add_site_option($option, wp_generate_password(32, true, true));

        // Re-read: if another request created the key first, add_site_option() is a no-op and theirs wins.
        return (string)get_site_option($option);
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
     * Get our defined CIPHER, with fallback for legacy installs.
     * Only used to decrypt values written before the `v2::` format.
     * @return string
     */
    private static function getCipher(): string
    {
        return defined('CIPHER') ? CIPHER : 'AES-256-CBC';
    }

    /**
     * The authenticated cipher used for new values.
     * @return string
     */
    private static function getAeadCipher(): string
    {
        return 'aes-256-gcm';
    }

    /**
     * The prefix marking the current (authenticated) format.
     * @return string
     */
    private static function getVersionPrefix(): string
    {
        return 'v2::';
    }

    /**
     * Derive a 256-bit binary key from the key material.
     * @param string $material
     * @return string
     */
    private static function deriveKey(#[SensitiveParameter] string $material): string
    {
        return hash_hkdf('sha256', $material, 32, 'wp-utilities/hash/v2');
    }

    /**
     * Decrypt a `v2::` value.
     * @param string $data
     * @param string $material The key material.
     * @return false|string
     */
    private static function decryptAead(string $data, #[SensitiveParameter] string $material): false|string
    {
        $raw = base64_decode(substr($data, strlen(self::getVersionPrefix())), true);
        $nonce_length = openssl_cipher_iv_length(self::getAeadCipher());
        if ($raw === false || strlen($raw) < $nonce_length + 16) {
            return false;
        }

        $nonce = substr($raw, 0, $nonce_length);
        $tag = substr($raw, $nonce_length, 16);
        $ciphertext = substr($raw, $nonce_length + 16);

        return openssl_decrypt(
            $ciphertext,
            self::getAeadCipher(),
            self::deriveKey($material),
            OPENSSL_RAW_DATA,
            $nonce,
            $tag
        );
    }

    /**
     * Create an instance of Encrypter.
     * @param string|null $key
     * @return Encrypter|null
     */
    private static function getEncrypter(#[SensitiveParameter] ?string $key = null): ?Encrypter
    {
        static $encrypters = [];
        if (!class_exists(Encrypter::class)) {
            return null;
        }

        $key ??= self::getEncryptionKey();
        $encrypters[hash('sha256', $key)] ??= new Encrypter($key, self::getCipher());

        return $encrypters[hash('sha256', $key)];
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
