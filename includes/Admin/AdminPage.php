<?php
// Prevent direct access
if ( ! defined( 'WPINC' ) ) { die; }

/**
 * Renders the Main App Page (The Journal Form)
 * This is the callback used by add_menu_page() in the main plugin file.
 */
function ddh_render_main_app_page() {
    ?>
    <div class="wrap daily-devhabit-page-wrapper">
        <h1 class="wp-heading-inline">Dev Journal</h1>
        <hr class="wp-header-end">
        
        <div id="appContainer" class="devhabit-app-container">
            <p style="text-align: center; color: #64748b; margin-top: 2em;">
                <span class="dashicons dashicons-update" style="animation: spin 2s linear infinite;"></span>
                Loading Journal Tool...
            </p>
        </div>
    </div>
    <?php
}