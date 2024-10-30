<?php

/*
Plugin Name:    SmokeDrop
Plugin URI:     https://thesmokedrop.com
Description:    Dropship Marketplace - Import & dropship products in your woocommerce store.
Version:        1.0.1
Requires PHP:   7.4
Author:         SmokeDrop
Author URI:     https://thesmokedrop.com
License:        GPL v2 or later
License URI:    http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain:    smokedrop
Domain Path:    /languages
*/

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version and defined constants.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'SMOKEDROP_SLUG', plugin_basename( __DIR__ ) );
define( 'SMOKEDROP_BASEFILE', plugin_basename( __FILE__ ) );
define( 'SMOKEDROP_VERSION', '1.0.1' );
define( 'SMOKEDROP_CACHE_KEY', 'smokedrop_updater' );
define( 'SMOKEDROP_CACHE_ALLOWED', false );

// Include the main plugin class
require_once plugin_dir_path( __FILE__ ) . 'includes/class-smokedrop.php';

// Initialize the plugin
function smokedrop_execute() {
    $plugin_instance = new SmokeDrop();
    $plugin_instance->smokedrop_run();
}

smokedrop_execute();
