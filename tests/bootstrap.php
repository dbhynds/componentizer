<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Componentizer
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// bash bin/install-wp-tests.sh componentizer_test root 'root' localhost latest

require_once 'setup.php';

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
