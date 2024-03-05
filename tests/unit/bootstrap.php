<?php

require_once dirname( dirname( __DIR__ ) ) . '/vendor/autoload.php';
require_once __DIR__ . '/Plugin/Framework/Mock/HookProvider.php';
require_once __DIR__ . '/Plugin/Framework/TestCase.php';

$_tests_dir = getenv('WP_TESTS_DIR') ?: getenv('WP_PHPUNIT__DIR');

if (!$_tests_dir) {
    $_tests_dir = rtrim(sys_get_temp_dir(), '/\\') . '/wordpress-tests-lib';
}

if (!file_exists($_tests_dir . '/includes/functions.php')) {
    echo "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL;
    exit(1);
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';
// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
