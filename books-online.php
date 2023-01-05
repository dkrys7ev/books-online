<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://localhost
 * @since             1.0.0
 * @package           Books_Online
 *
 * @wordpress-plugin
 * Plugin Name:       Books Online
 * Plugin URI:        https://github.com/dkrys7ev/books-online
 * Description:       REST API BASIC CRUD WordPress Plugin for working with Books
 * Version:           1.0.0
 * Author:            Danislav Krastev
 * Author URI:        https://localhost
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       books-online
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The core plugin class
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-books-online.php';
