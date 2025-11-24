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
    if ( 'toplevel_page_daily-devhabit' !== $hook && 'daily-devhabit-log_page_daily-devhabit-settings' !== $hook ) {
        return;
    }

    wp_enqueue_script(
        'ddh-admin-js',
        DDH_PLUGIN_URL . 'assets/admin.js', 
        ['jquery'], 
        DDH_VERSION,
        true 
    );

    wp_enqueue_style(
        'ddh-admin-css',
        DDH_PLUGIN_URL . 'assets/admin.css', 
        [], 
        DDH_VERSION
    );

    $options = get_option( 'ddh_integration_options' );
    $mode = isset( $options['connection_mode'] ) ? $options['connection_mode'] : 'github';
    $raw_questions = isset( $options['custom_questions'] ) ? $options['custom_questions'] : '';
    $questions_array = [];

    if ( ! empty( $raw_questions ) ) {
        // Split by new line and filter empty lines
        $lines = preg_split('/\r\n|\r|\n/', $raw_questions);
        foreach ($lines as $line) {
            if ( ! empty( trim( $line ) ) ) {
                $questions_array[] = array(
                    'prompt' => trim( $line ),
                    'placeholder' => 'Type your answer here...'
                );
            }
        }
    }

    // Pass configuration to JS
    wp_localize_script('ddh-admin-js', 'ddh_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('ddh_save_log_nonce'),
        'mode'     => $mode,
        'questions' => $questions_array 
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