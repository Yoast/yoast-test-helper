<?php
/**
 * Yoast Version Controller plugin.
 *
 * @package Yoast_Version_Controller\Main
 *
 * Plugin Name: Yoast tools: Version controller
 * Version: 1.0
 * Plugin URI: https://github.com/yoast/yoast-version-controller
 * Description: Manager Yoast Database Version to test upgrade routines.
 * Author: Team Yoast
 * Author URI: https://yoast.com/
 * Text Domain: yoast-version-controller
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

require __DIR__ . '/vendor/autoload.php';

$yoast_version_controller = new Yoast\Version_Controller\Plugin();
$yoast_version_controller->add_hooks();
