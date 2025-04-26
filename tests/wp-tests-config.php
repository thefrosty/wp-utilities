<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
/* Path to the WordPress codebase you'd like to test. Add a forward slash in the end. */
define('ABSPATH', dirname(__DIR__) . '/wordpress/');

/*
 * Path to the theme to test with.
 *
 * The 'default' theme is symlinked from test/phpunit/data/themedir1/default into
 * the themes directory of the WordPress installation defined above.
 */
define('WP_DEFAULT_THEME', 'default');

// Test with multisite enabled.
// Alternatively, use the tests/phpunit/multisite.xml configuration file.
// define( 'WP_TESTS_MULTISITE', true );

// Force known bugs to be run.
// Tests with an associated Trac ticket that is still open are normally skipped.
// define( 'WP_TESTS_FORCE_KNOWN_BUGS', true );

// Test with WordPress debug mode (default).
define('WP_DEBUG', true);

// ** MySQL settings ** //

// This configuration file will be used by the copy of WordPress being tested.
// wordpress/wp-config.php will be ignored.

// WARNING WARNING WARNING!
// These tests will DROP ALL TABLES in the database with the prefix named below.
// DO NOT use a production database or one that is shared with something else.

define('DB_NAME', getenv('WORDPRESS_DB_NAME') ?: 'wordpress_test');
define('DB_USER', getenv('WORDPRESS_DB_USER') ?: 'root');
define('DB_PASSWORD', getenv('WORDPRESS_DB_PASS') ?: 'root');
define('DB_HOST', getenv('WORDPRESS_DB_HOST') ?: '127.0.0.1');
define('DB_CHARSET', 'utf8mb4');
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 */
define('AUTH_KEY', '`sock93ZA-}nGce6]&dW`SM6_4xRds;FpHT%J|Ls%nQb/mb9PFNzU8{cLN6_]>mx');
define('SECURE_AUTH_KEY', 'zSUd-69$3tI$H?+38Q6^x[k-rQ/OVl^vFiwb8k8[D>6YD;(H:+^pxGZqP8P{(UxQ');
define('LOGGED_IN_KEY', 'dIJQ/7}`Yh.?jB0E;{WBJ:W ,?U#rR3^s^h5k.EVERdkCtM7B47I;,=c!jKCMcXP');
define('NONCE_KEY', 'De~~%;U|!&sZ`s7[:+;=iBnLq0n@++7++dPnOotUfba E+@2 qF8pBh`L)#`_yA_');
define('AUTH_SALT', 'O0m9wmHE9+BV.-|zH?Lqxp!mop4c7.9g,W|8R-vZr&fE!*IR^vk%jl&$!M/FX~e|');
define('SECURE_AUTH_SALT', 'sQ]%{hfjy!5#y3FY~#-b<x3n3%qf`Oq-@9?*J-{w )lRXvvIrz>Q:~T$cu`l_y,+');
define('LOGGED_IN_SALT', '^3#^E@:.ke>]z+}Lc<!e)+:?t5v>=<zV OOIZ~>f/[0wQ[9~!X3{5Mz$QYMP>55@');
define('NONCE_SALT', 'J,,eOFB-6oitNrlh[aH}`$Ad*RoL]v(+/Pfk+~FGsLctS(Ltwi|TRPpY5|BxBL@.');

$table_prefix = 'wptests_';

define('WP_TESTS_DOMAIN', 'example.org');
define('WP_TESTS_EMAIL', 'admin@example.org');
define('WP_TESTS_TITLE', 'Test Blog');

define('WP_PHP_BINARY', 'php');

define('WPLANG', '');
