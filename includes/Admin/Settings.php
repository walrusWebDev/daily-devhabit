<?php
namespace DailyDevHabit\Admin;

class Settings {

    public function __construct() {
        // Initialize settings when the admin area loads
        add_action( 'admin_init', [ $this, 'init_settings' ] );
    }

    /**
     * 1. Register Settings & Sections
     */
    public function init_settings() {
        register_setting( 'ddh_settings_group', 'ddh_integration_options', [ $this, 'sanitize_options' ] );

        add_settings_section(
            'ddh_integration_section',
            __( 'Integration Configuration', 'daily-devhabit' ),
            null, // No callback needed for section description
            'ddh-log-settings'
        );

        // --- FIELD 1: MODE ---
        add_settings_field(
            'connection_mode',
            __( 'Connection Mode', 'daily-devhabit' ),
            [ $this, 'render_mode_field' ],
            'ddh-log-settings',
            'ddh_integration_section'
        );

        // --- FIELD 2: QUESTIONS ---
        add_settings_field(
            'custom_questions',
            __( 'Standup Prompts', 'daily-devhabit' ),
            [ $this, 'render_questions_field' ],
            'ddh-log-settings',
            'ddh_integration_section'
        );

        // --- GITHUB FIELDS ---
        add_settings_field( 'github_username', __( 'GitHub Username', 'daily-devhabit' ), [ $this, 'render_gh_user_field' ], 'ddh-log-settings', 'ddh_integration_section' );
        add_settings_field( 'github_repo', __( 'Repository Name', 'daily-devhabit' ), [ $this, 'render_gh_repo_field' ], 'ddh-log-settings', 'ddh_integration_section' );
        add_settings_field( 'github_pat', __( 'GitHub PAT', 'daily-devhabit' ), [ $this, 'render_gh_pat_field' ], 'ddh-log-settings', 'ddh_integration_section' );
        add_settings_field( 'github_path', __( 'File Path', 'daily-devhabit' ), [ $this, 'render_gh_path_field' ], 'ddh-log-settings', 'ddh_integration_section' );

        // --- CLOUD FIELD ---
        add_settings_field( 'cloud_jwt', __( 'Cloud API Key (JWT)', 'daily-devhabit' ), [ $this, 'render_cloud_jwt_field' ], 'ddh-log-settings', 'ddh_integration_section' );
    }

    public static function render_page() {
        ?>
        <div class="wrap">
            <h1>Daily Dev Habit Settings</h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'ddh_settings_group' );
                do_settings_sections( 'ddh-log-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * 2. Sanitize Input
     */
    public function sanitize_options( $input ) {
        $sanitized = [];
        $sanitized['connection_mode'] = sanitize_text_field( $input['connection_mode'] ?? '' );
        $sanitized['github_username'] = sanitize_text_field( $input['github_username'] ?? '' );
        $sanitized['github_repo']     = sanitize_text_field( $input['github_repo'] ?? '' );
        $sanitized['github_pat']      = sanitize_text_field( $input['github_pat'] ?? '' );
        $sanitized['github_path']     = sanitize_text_field( $input['github_path'] ?? '' );
        $sanitized['cloud_jwt']       = sanitize_text_field( $input['cloud_jwt'] ?? '' );
        
        if ( isset( $input['custom_questions'] ) ) {
            $sanitized['custom_questions'] = implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $input['custom_questions'] ) ) );
        }
        return $sanitized;
    }

    /**
     * 3. Render Fields
     */
    public function render_mode_field() {
        $options = get_option( 'ddh_integration_options' );
        $mode = $options['connection_mode'] ?? 'github';
        ?>
        <select name="ddh_integration_options[connection_mode]" id="ddh_connection_mode"> <option value="github" <?php selected( $mode, 'github' ); ?>>GitHub (Self-Hosted)</option>
            <option value="cloud" <?php selected( $mode, 'cloud' ); ?>>DDH Cloud (API)</option>
        </select>
        <p class="description">Select "DDH Cloud" to use the analytics dashboard.</p>
        <?php
    }
    

    public function render_questions_field() {
        $options = get_option( 'ddh_integration_options' );
        $defaults = "What was the main theme or project today?\nHow many hours did you code?\nWhat technologies did you use?\nWhat was the biggest challenge?\nHow did you solve it?\nWhat did you learn today?";
        $value = $options['custom_questions'] ?? $defaults;
        ?>
        <textarea name="ddh_integration_options[custom_questions]" rows="8" cols="50" class="large-text code"><?php echo esc_textarea( $value ); ?></textarea>
        <p class="description">Enter one question per line.</p>
        <?php
    }

    public function render_gh_user_field() {
        $val = get_option( 'ddh_integration_options' )['github_username'] ?? '';
        echo '<input type="text" name="ddh_integration_options[github_username]" value="' . esc_attr( $val ) . '" class="regular-text ddh-github-field">';
    }

    public function render_gh_repo_field() { 
        $val = get_option( 'ddh_integration_options' )['github_repo'] ?? '';
        echo '<input type="text" name="ddh_integration_options[github_repo]" value="' . esc_attr( $val ) . '" class="regular-text ddh-github-field">'; 
    }
    
    public function render_gh_pat_field() { 
        $val = get_option( 'ddh_integration_options' )['github_pat'] ?? '';
        echo '<input type="password" name="ddh_integration_options[github_pat]" value="' . esc_attr( $val ) . '" class="regular-text ddh-github-field"><p class="description">Repo Scope</p>'; 
    }
    
    public function render_gh_path_field() { 
        $val = get_option( 'ddh_integration_options' )['github_path'] ?? '_logs/';
        echo '<input type="text" name="ddh_integration_options[github_path]" value="' . esc_attr( $val ) . '" class="regular-text ddh-github-field">'; 
    }
    
    public function render_cloud_jwt_field() { 
        $val = get_option( 'ddh_integration_options' )['cloud_jwt'] ?? '';
        echo '<input type="password" name="ddh_integration_options[cloud_jwt]" value="' . esc_attr( $val ) . '" class="regular-text ddh-cloud-field">'; 
    }
}