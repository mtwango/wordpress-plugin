<?php
/**
 * Plugin Name:       Composer Plugin No Updates
 * Plugin URI:        https://github.com/mtwango/wordpress-plugin
 * Description:       Helper for Composer WordPress plugin which disables automatic updates.
 * Version:           1.0.0
 * Requires at least: 6.2
 * Author:            Mtwango
 * Author URI:        https://github.com/mtwango
 * License:           MIT
 * License URI:       https://github.com/mtwango/wordpress-plugin/blob/main/LICENSE
 * Text Domain:       composer-plugin-no-updates
 */

// Disable WordPress Core updates.
add_filter( 'automatic_updater_disabled', '__return_true' );
add_filter( 'allow_dev_auto_core_updates', '__return_false' );
add_filter( 'allow_minor_auto_core_updates', '__return_false' );
add_filter( 'allow_major_auto_core_updates', '__return_false' );

// Disable plugin, themes and translation updates.
add_filter( 'auto_update_plugin', '__return_false' );
add_filter( 'auto_update_theme', '__return_false' );
add_filter( 'auto_update_translation', '__return_false' );
