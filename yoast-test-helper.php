<?php
/**
 * Yoast Test Helper plugin.
 *
 * @package Yoast\Test_Helper
 *
 * Plugin Name: Yoast Development: Test Helper
 * Version: 1.0
 * Plugin URI: https://github.com/yoast/yoast-version-controller
 * Description: Utility to provide testing features for Yoast plugins.
 * Author: Team Yoast
 * Author URI: https://yoast.com/
 * Text Domain: yoast-test-helper
 * Domain Path: /languages/
 * License: GPL v3
 *
 * Copyright (C) 2018, Yoast BV - support@yoast.com
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

require __DIR__ . '/vendor/autoload.php';

$yoast_version_controller = new Yoast\Test_Helper\Plugin();
$yoast_version_controller->add_hooks();
