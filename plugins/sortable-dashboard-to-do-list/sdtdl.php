<?php

/**
 * Plugin Name: Sortable Dashboard To-Do List
 * Description: Adds a to-do list to the WordPress dashboard.
 * Version:     1.0.4
 * Author:      JFG Media
 * Author URI:  https://jfgmedia.com
 * Text Domain: sortable-dashboard-to-do-list
 * Domain Path: /lang
 * License: GPLv2 or later
 *
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define('SDTDL_PLUGIN_FILE', __FILE__);

require_once __DIR__ .'/classes/sdtdl.php';

add_action('plugins_loaded', ['SDTDL\SDTDL', 'init']);
register_uninstall_hook(__FILE__, ['SDTDL\SDTDL','uninstall_plugin']);