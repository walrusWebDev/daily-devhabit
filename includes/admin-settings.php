<?php
// Prevent direct access
if ( ! defined( 'WPINC' ) ) { die; }

/**
 * 1. Register Menus
 */
function ddh_add_admin_menu() {
    add_menu_page(
        __( 'Daily DevHabit', 'daily-devhabit' ),
        __( 'Daily DevHabit Log', 'daily-devhabit' ),
        'manage_options',
        'daily-devhabit', 
        'ddh_render_main_app_page', // Renamed for consistency
        'dashicons-edit-page',
        80 
    );

    add_submenu_page(
        'daily-devhabit',
        __( 'Settings', 'daily-devhabit' ),
        __( 'Settings', 'daily-devhabit' ),
        'manage_options',
        'daily-devhabit-settings',
        'ddh_render_settings_page'
    );
}
add_action( 'admin_menu', 'ddh_add_admin_menu' );

/**
 * 2. Initialize Settings (The New Data Structure)
 */
function ddh_settings_init() {
    register_setting( 'ddh_settings_group', 'ddh_integration_options', 'ddh_sanitize_options' );

    add_settings_section(
        'ddh_integration_section',
        __( 'Integration Configuration', 'daily-devhabit' ),
        'ddh_integration_section_callback',
        'daily-devhabit-settings'
    );

    // --- FIELD 1: THE SWITCH ---
    add_settings_field(
        'connection_mode',
        __( 'Connection Mode', 'daily-devhabit' ),
        'ddh_render_mode_field',
        'daily-devhabit-settings',
        'ddh_integration_section'
    );

    add_settings_field(
        'custom_questions',
        __( 'Journal Prompts', 'daily-devhabit' ),
        'ddh_render_questions_field',
        'daily-devhabit-settings',
        'ddh_integration_section'
    );

    // --- FIELD GROUP: GITHUB ---
    add_settings_field( 'github_username', __( 'GitHub Username', 'daily-devhabit' ), 'ddh_render_gh_user_field', 'daily-devhabit-settings', 'ddh_integration_section' );
    add_settings_field( 'github_repo', __( 'Repository Name', 'daily-devhabit' ), 'ddh_render_gh_repo_field', 'daily-devhabit-settings', 'ddh_integration_section' );
    add_settings_field( 'github_pat', __( 'GitHub PAT', 'daily-devhabit' ), 'ddh_render_gh_pat_field', 'daily-devhabit-settings', 'ddh_integration_section' );
    add_settings_field( 'github_path', __( 'File Path', 'daily-devhabit' ), 'ddh_render_gh_path_field', 'daily-devhabit-settings', 'ddh_integration_section' );

    // --- FIELD GROUP: CLOUD (API) ---
    add_settings_field( 'cloud_jwt', __( 'Cloud API Key (JWT)', 'daily-devhabit' ), 'ddh_render_cloud_jwt_field', 'daily-devhabit-settings', 'ddh_integration_section' );
}
add_action( 'admin_init', 'ddh_settings_init' );


/**
 * 3. Render Callbacks (The Inputs)
 */
function ddh_render_settings_page() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Daily Dev Habit Settings', 'daily-devhabit' ); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'ddh_settings_group' );
            do_settings_sections( 'daily-devhabit-settings' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function ddh_integration_section_callback() {
    echo '<p>Choose where you want to save your daily logs.</p>';
}

// --- Field Renderers ---

function ddh_render_mode_field() {
    $options = get_option( 'ddh_integration_options' );
    $mode = isset( $options['connection_mode'] ) ? $options['connection_mode'] : 'github';
    ?>
    <select name="ddh_integration_options[connection_mode]" id="ddh_connection_mode">
        <option value="github" <?php selected( $mode, 'github' ); ?>>GitHub (Self-Hosted)</option>
        <option value="cloud" <?php selected( $mode, 'cloud' ); ?>>DDH Cloud (API)</option>
    </select>
    <p class="description">Select "DDH Cloud" to use the analytics dashboard.</p>
    <?php
}

// Helpers for the other fields (Wrapped in TRs by WordPress, but we give them Classes via JS or CSS ideally)
// For simplicity, we keep standard rendering, we will handle visibility in JS.

function ddh_render_gh_user_field() {
    $options = get_option( 'ddh_integration_options' );
    ?>
    <div class="ddh-github-field">
        <input type="text" name="ddh_integration_options[github_username]" value="<?php echo esc_attr( $options['github_username'] ?? '' ); ?>" class="regular-text">
    </div>
    <?php
}
function ddh_render_gh_repo_field() {
    $options = get_option( 'ddh_integration_options' );
    ?>
    <div class="ddh-github-field">
        <input type="text" name="ddh_integration_options[github_repo]" value="<?php echo esc_attr( $options['github_repo'] ?? '' ); ?>" class="regular-text">
    </div>
    <?php
}
function ddh_render_gh_pat_field() {
    $options = get_option( 'ddh_integration_options' );
    ?>
    <div class="ddh-github-field">
        <input type="password" name="ddh_integration_options[github_pat]" value="<?php echo esc_attr( $options['github_pat'] ?? '' ); ?>" class="regular-text">
        <p class="description">Personal Access Token (Repo Scope)</p>
    </div>
    <?php
}
function ddh_render_gh_path_field() {
    $options = get_option( 'ddh_integration_options' );
    ?>
    <div class="ddh-github-field">
        <input type="text" name="ddh_integration_options[github_path]" value="<?php echo esc_attr( $options['github_path'] ?? '_logs/' ); ?>" class="regular-text">
    </div>
    <?php
}

function ddh_render_cloud_jwt_field() {
    $options = get_option( 'ddh_integration_options' );
    ?>
    <div class="ddh-cloud-field">
        <input type="password" name="ddh_integration_options[cloud_jwt]" value="<?php echo esc_attr( $options['cloud_jwt'] ?? '' ); ?>" class="regular-text">
        <p class="description">Your DDH Cloud Access Token.</p>
    </div>
    <?php
}

function ddh_render_questions_field() {
    $options = get_option( 'ddh_integration_options' );
    // Default questions if none exist
    $defaults = "What was the main theme or project today?\nHow many hours did you code?\nWhat technologies did you use (comma separated)?\nWhat was the biggest challenge?\nHow did you solve it?\nWhat did you learn today?";
    
    $value = isset( $options['custom_questions'] ) ? $options['custom_questions'] : $defaults;
    ?>
    <textarea name="ddh_integration_options[custom_questions]" rows="8" cols="50" class="large-text code"><?php echo esc_textarea( $value ); ?></textarea>
    <p class="description">Enter one question per line. These will appear in the journaling app.</p>
    <?php
}

/**
 * 4. Sanitization
 */
function ddh_sanitize_options( $input ) {
    $sanitized = [];
    $sanitized['connection_mode'] = sanitize_text_field( $input['connection_mode'] );
    $sanitized['github_username'] = sanitize_text_field( $input['github_username'] );
    $sanitized['github_repo']     = sanitize_text_field( $input['github_repo'] );
    $sanitized['github_pat']      = sanitize_text_field( $input['github_pat'] ); // Basic sanitization for tokens
    $sanitized['github_path']     = sanitize_text_field( $input['github_path'] );
    $sanitized['cloud_jwt']       = sanitize_text_field( $input['cloud_jwt'] );
    if ( isset( $input['custom_questions'] ) ) {
        $sanitized['custom_questions'] = implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $input['custom_questions'] ) ) );
    }
    return $sanitized;
}

/**
 * 5. Main App Page Renderer (Moved from main file)
 */
function ddh_render_main_app_page() {
    ?>
    <div class="wrap daily-devhabit-page-wrapper">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <div id="appContainer" class="devhabit-app-container">
            <p style="text-align: center; color: #64748b; margin-top: 2em;">Loading Journal Tool...</p>
        </div>
    </div>
    <?php
}