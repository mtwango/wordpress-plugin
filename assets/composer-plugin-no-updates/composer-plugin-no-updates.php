<?php
/**
 * Plugin Name: Composer Plugin No Updates.
 * Description: Helper for Composer WordPress plugin to disable automatic updates.
 * Version:     1.0
 * Author:      Mtwango
 * Author URI:  https://github.com/mtwango/wordpress-plugin
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
