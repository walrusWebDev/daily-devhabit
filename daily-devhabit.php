<?php
/**
 * Plugin Name:       Daily Dev Habit: Content Prompter
 * Plugin URI:        https://daily-devhabit.com
 * Description:       A simple, elegant tool that asks probing questions to help you generate content. Supports GitHub and DDH Cloud.
 * Version:           1.1.0
 * Author:            Lauren Bridges
 * Author URI:        https://oneoffboss.com/
 * License:           GPL v2 or later
 * Text Domain:       daily-devhabit
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// 1. Constants
define( 'DDH_VERSION', '1.1.0' );
define( 'DDH_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DDH_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DDH_API_URL', 'https://ddh-core-production.up.railway.app' );

// 2. Include Core Logic (The Journal)
require_once DDH_PLUGIN_DIR . 'includes/DailyLog.php';       // CPT Registration
require_once DDH_PLUGIN_DIR . 'includes/Admin/AdminPage.php'; // The Form UI
require_once DDH_PLUGIN_DIR . 'includes/Admin/Settings.php';  // Log Settings

// 3. Initialize CLI (Only if running via WP-CLI)
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    require_once DDH_PLUGIN_DIR . 'includes/CLI/Commands.php';
}

// 4. Initialize Settings
$ddh_settings = new DailyDevHabit\Admin\Settings();

// 5. Register Admin Menus
function ddh_add_admin_menu() {
    // Main Menu: The Journal Form
    add_menu_page(
        'Daily Dev Log', 
        'Dev Journal', 
        'manage_options', 
        'ddh-dev-log', 
        'ddh_render_main_app_page', 
        'dashicons-editor-ul', 
        6
    );

    // Submenu: Settings
    add_submenu_page(
        'ddh-dev-log',
        'Journal Settings',
        'Settings',
        'manage_options',
        'ddh-log-settings',
        [ 'DailyDevHabit\Admin\Settings', 'render_page' ]
    );
}
add_action( 'admin_menu', 'ddh_add_admin_menu' );

// 6. Enqueue Assets (Scripts & Styles)
function ddh_enqueue_admin_scripts( $hook ) {
    // Only load on our specific pages
    $allowed_pages = [
        'toplevel_page_ddh-dev-log',
        'dev-journal_page_ddh-log-settings'
    ];

    if ( ! in_array( $hook, $allowed_pages ) ) {
        return;
    }

    wp_enqueue_style( 'ddh-admin-css', DDH_PLUGIN_URL . 'assets/admin.css', [], DDH_VERSION );
    wp_enqueue_script( 'ddh-admin-js', DDH_PLUGIN_URL . 'assets/admin.js', ['jquery'], DDH_VERSION, true );

    // 1. GET OPTIONS FROM DB
    $options = get_option( 'ddh_integration_options' );
    
    // 2. EXTRACT THE MODE (Default to 'github' if missing)
    $mode = isset( $options['connection_mode'] ) ? $options['connection_mode'] : 'github';
    
    $raw_questions = isset( $options['custom_questions'] ) ? $options['custom_questions'] : '';
    
    // Default Questions Logic
    $questions_array = [];
    if ( ! empty( $raw_questions ) ) {
        $lines = preg_split('/\r\n|\r|\n/', $raw_questions);
        foreach ($lines as $line) {
            if ( ! empty( trim( $line ) ) ) {
                $questions_array[] = array( 'prompt' => trim( $line ), 'placeholder' => '...' );
            }
        }
    } else {
        $questions_array = [
            ['prompt' => 'What did you ship today?', 'placeholder' => '...'],
            ['prompt' => 'What blocked you?', 'placeholder' => '...']
        ];
    }

    // 3. PASS 'mode' TO JAVASCRIPT
    wp_localize_script('ddh-admin-js', 'ddh_ajax', array(
        'ajax_url'  => admin_url('admin-ajax.php'),
        'nonce'     => wp_create_nonce('ddh_save_log_nonce'),
        'questions' => $questions_array,
        'mode'      => $mode
    ));
}
add_action( 'admin_enqueue_scripts', 'ddh_enqueue_admin_scripts' );

// 7. Footer Branding
function ddh_add_footer_link( $text ) {
    $screen = get_current_screen();
    if ( $screen && strpos($screen->id, 'ddh-') !== false ) {
        $text .= ' | <a href="https://dailydevhabit.com" target="_blank">DailyDevHabit.com</a>';
    }
    return $text;
}
add_filter( 'admin_footer_text', 'ddh_add_footer_link' );

// 8. General Initialization
function ddh_init_plugin() {
    load_plugin_textdomain( 'daily-devhabit', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'ddh_init_plugin' );