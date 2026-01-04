<?php

declare(strict_types=1);

namespace TheFrosty\WpUtilities\Utils;

use SensitiveParameter;
use function array_slice;
use function count;
use function explode;
use function filter_var;
use function get_home_url;
use function get_option;
use function gmdate;
use function implode;
use function is_email;
use function is_string;
use function md5;
use function sprintf;
use function str_repeat;
use function strlen;
use function update_option;
use function wp_generate_uuid4;
use const FILTER_VALIDATE_URL;

/**
 * Trait Anonymizer
 * @package TheFrosty\WpUtilities\Utils
 */
trait Anonymizer
{

    /**
     * Attempts to anonymize a string.
     * @param string $value The string to anonymize.
     * @return string
     */
    public function anonymize(#[SensitiveParameter] string $value): string
    {
        if (is_email($value)) {
            return $this->maskEmail($value);
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $this->maskDomain($value);
        }

        return $this->maskString($value);
    }

    /**
     * Gets the unique site ID.
     * This is generated from the home URL and two random pieces of data
     * to create a hashed site ID that anonymizes the site data.
     * @return string
     */
    public function uuid(): string
    {
        $id = get_option('_wp_utilities_telemetry_uuid');
        if (is_string($id)) {
            return $id;
        }

        $id = md5(sprintf('%1$s:%2$s:%3$s', get_home_url(), wp_generate_uuid4(), gmdate('now')));

        update_option('_wp_utilities_telemetry_uuid', $id, false);

        return $id;
    }

    /**
     * Given a domain, mask it with the * character.
     * TLD parts will remain intact (.com, .co.uk). All subdomains will be masked.
     * @param string $domain
     * @return string
     */
    protected function maskDomain(string $domain = ''): string
    {
        if (empty($domain)) {
            return '';
        }

        $parts = explode('.', $domain);

        if (count($parts) === 2) {
            // We have a single entry tld like ".org" or ".com".
            $parts[0] = $this->maskString($parts[0]);
        } else {
            $count = count($parts);
            $strlen = strlen($parts[$count - 2]) <= 3;

            $mask_parts = $strlen
                ? array_slice($parts, 0, $count - 2)
                : array_slice($parts, 0, $count - 1);

            $mask_parts = count($mask_parts);

            $i = 0;
            while ($i < $mask_parts) {
                $parts[$i] = $this->maskString($parts[$i]);
                $i++;
            }
        }

        return implode('.', $parts);
    }

    /**
     * Given an email address, mask the name and domain according to domain and string masking functions.
     * Will result in an email address like a***n@e*****e.org for admin@example.org.
     * @param string $email
     * @return string
     */
    protected function maskEmail(string $email): string
    {
        if (!is_email($email)) {
            return $email;
        }

        $parts = explode('@', $email);
        if (!isset($parts[0], $parts[1])) {
            return $email;
        }

        return sprintf('%s@%s', $this->maskString($parts[0]), $this->maskDomain($parts[1]));
    }

    /**
     * Given a string, mask it with the * character.
     * First and last character will remain with the filling characters being changed to *. One Character will
     * be left intact as is. Two character strings will have the first character remain and the second be a *.
     * @param string $string
     * @return string
     */
    protected function maskString(string $string = ''): string
    {
        if (empty($string)) {
            return '';
        }

        $first_char = $string[0];
        $last_char = $string[strlen($string) - 1];

        $masked = $string;

        if (strlen($string) > 2) {
            $total_stars = strlen($string) - 2;
            $masked = $first_char . str_repeat('*', $total_stars) . $last_char;
        } elseif (strlen($string) === 2) {
            $masked = $first_char . '*';
        }

        return $masked;
    }
}
