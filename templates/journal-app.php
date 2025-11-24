<?php
/**
 * Plugin Name:       Daily Dev Habit: Content Prompter
 * Plugin URI:        https://daily-devhabit.com
 * Description:       A simple, elegant tool that asks probing questions to help you generate content. Supports GitHub and DDH Cloud.
 * Version:           0.3.0
 * Author:            Lauren Bridges
 * Author URI:        https://oneoffboss.com/
 * License:           GPL v2 or later
 * Text Domain:       daily-devhabit
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Define Constants for easy refactoring later
define( 'DDH_VERSION', '0.3.0' );
define( 'DDH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DDH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include Core Files
require_once DDH_PLUGIN_DIR . 'includes/admin-settings.php';
require_once DDH_PLUGIN_DIR . 'includes/api-handlers.php';

// Enqueue Scripts (Global Asset Loading)
function ddh_enqueue_admin_scripts( $hook ) {
    // Only load on our specific pages
    if ( 'toplevel_page_daily-devhabit' !== $hook && 'daily-devhabit_page_daily-devhabit-settings' !== $hook ) {
        return;
    }

    wp_enqueue_script(
        'ddh-admin-js',
        DDH_PLUGIN_URL . 'assets/admin.js', // Assuming you moved admin.js to assets/
        ['jquery'], 
        DDH_VERSION,
        true 
    );

    wp_enqueue_style(
        'ddh-admin-css',
        DDH_PLUGIN_URL . 'assets/admin.css', // Assuming you moved admin.css to assets/
        [], 
        DDH_VERSION
    );

    // Pass configuration to JS
    wp_localize_script('ddh-admin-js', 'ddh_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('ddh_save_log_nonce') 
    ));
}
add_action( 'admin_enqueue_scripts', 'ddh_enqueue_admin_scripts' );

// Add Footer Link (Support)
function ddh_add_admin_footer_link( $footer_text ) {
    $screen = get_current_screen();
    if ( $screen && ( $screen->id === 'toplevel_page_daily-devhabit' || $screen->id === 'daily-devhabit_page_daily-devhabit-settings' ) ) {
        $footer_text .= ' | <a href="https://daily-devhabit.com/support/" target="_blank">Daily Dev Habit Support</a>';
    }
    return $footer_text; 
}
add_filter( 'admin_footer_text', 'ddh_add_admin_footer_link' );