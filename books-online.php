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
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BOOKS_ONLINE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-books-online-activator.php
 */
function activate_books_online() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-books-online-activator.php';
	Books_Online_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-books-online-deactivator.php
 */
function deactivate_books_online() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-books-online-deactivator.php';
	Books_Online_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_books_online' );
register_deactivation_hook( __FILE__, 'deactivate_books_online' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-books-online.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_books_online() {

	$plugin = new Books_Online();
	$plugin->run();

}
run_books_online();
