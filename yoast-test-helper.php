<?php
/**
 * Yoast Test Helper plugin.
 *
 * @package   Yoast\WP\Test_Helper
 * @copyright Copyright (C) 2017-2020, Yoast BV - support@yoast.com
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 or higher
 *
 * @wordpress-plugin
 * Plugin Name: Yoast Test Helper
 * Version:     1.15
 * Plugin URI:  https://github.com/yoast/yoast-test-helper
 * Description: Utility to provide testing features for Yoast plugins.
 * Author:      Team Yoast
 * Author URI:  https://yoa.st/1uk
 * Text Domain: yoast-test-helper
 * Domain Path: /languages/
 * License:     GPL v3
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

define( 'YOAST_TEST_HELPER_FILE', __FILE__ );
define( 'YOAST_TEST_HELPER_DIR', dirname( YOAST_TEST_HELPER_FILE ) );
define( 'YOAST_TEST_HELPER_VERSION', '1.15' );

if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require __DIR__ . '/vendor/autoload.php';
}

$yoast_test_helper = new Yoast\WP\Test_Helper\Plugin();
$yoast_test_helper->add_hooks();
