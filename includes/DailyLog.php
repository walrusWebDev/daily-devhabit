<?php
// Prevent direct access
if ( ! defined( 'WPINC' ) ) { die; }

/**
 * Main AJAX Handler: Determines Destination
 */
function ddh_handle_save_log() {
    // 1. Security Check
    check_ajax_referer('ddh_save_log_nonce', 'nonce');
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Permission denied.', 403 );
    }

    // 2. Validate Input
    if ( empty( $_POST['log_content'] ) ) {
         wp_send_json_error( 'Log content is missing.', 400 );
    }
    $log_content = sanitize_textarea_field( wp_unslash( $_POST['log_content'] ) );

    // 3. Get Settings & Determine Mode
    $options = get_option( 'ddh_integration_options' );
    $mode = isset( $options['connection_mode'] ) ? $options['connection_mode'] : 'github';

    // 4. Route the Request (The "Strategy Pattern")
    if ( $mode === 'cloud' ) {
        ddh_send_to_cloud( $log_content, $options );
    } else {
        ddh_send_to_github( $log_content, $options );
    }
}
// Note: We updated the hook name to match the new prefix
add_action( 'wp_ajax_ddh_save_log', 'ddh_handle_save_log' );


/**
 * Strategy A: Send to Daily Dev Habit Cloud (Node API)
 */
function ddh_send_to_cloud( $content, $options ) {
    $jwt = isset( $options['cloud_jwt'] ) ? trim( $options['cloud_jwt'] ) : '';

    if ( empty( $jwt ) ) {
        wp_send_json_error( 'Configuration Error: Cloud JWT is missing.', 400 );
    }

    $api_url = defined('DDH_API_URL') ? DDH_API_URL . '/entries' : '';

    $body = json_encode( array(
        'content_html' => $content,
        'level'   => 'info',
        'origin'  => 'wordpress'
    ));

    $args = array(
        'method'  => 'POST',
        'headers' => array(
            'Authorization' => 'Bearer ' . $jwt,
            'Content-Type'  => 'application/json'
        ),
        'body'    => $body,
        'timeout' => 15
    );

    $response = wp_remote_post( $api_url, $args );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( 'Cloud Connection Failed: ' . $response->get_error_message(), 500 );
    }

    $code = wp_remote_retrieve_response_code( $response );
    if ( $code === 201 ) {
        wp_send_json_success( array( 'message' => 'Log saved to Cloud successfully!' ) );
    } else {
        wp_send_json_error( 'Cloud API Error (' . $code . ')', $code );
    }
}


/**
 * Strategy B: Send to GitHub (Legacy Support)
 */
function ddh_send_to_github( $content, $options ) {
    $pat      = isset( $options['github_pat'] ) ? $options['github_pat'] : '';
    $username = isset( $options['github_username'] ) ? $options['github_username'] : '';
    $repo     = isset( $options['github_repo'] ) ? $options['github_repo'] : '';
    $path     = isset( $options['github_path'] ) ? $options['github_path'] : '_logs/';

    if ( empty( $pat ) || empty( $username ) || empty( $repo ) ) {
        wp_send_json_error( 'GitHub settings are incomplete.', 400 );
    }

    // Format Data for GitHub
    $date_slug = date('Y-m-d');
    $file_name = $date_slug . '-' . date('H-i-s') . '-log.md';
    $full_path = trim( $path, '/' ) . '/' . $file_name;
    
    // API URL
    $api_url = sprintf( 'https://api.github.com/repos/%s/%s/contents/%s', $username, $repo, $full_path );

    $body = json_encode( array(
        'message' => 'Add dev log for ' . $date_slug,
        'content' => base64_encode( $content ), // GitHub requires Base64
        'branch'  => 'main' 
    ));

    $args = array(
        'method'  => 'PUT', // GitHub uses PUT for file creation
        'headers' => array(
            'Authorization' => 'Bearer ' . $pat,
            'Accept'        => 'application/vnd.github.v3+json',
            'Content-Type'  => 'application/json',
            'User-Agent'    => 'Daily Dev Habit Plugin'
        ),
        'body'    => $body,
        'timeout' => 15
    );

    $response = wp_remote_request( $api_url, $args );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( 'GitHub Connection Failed: ' . $response->get_error_message(), 500 );
    }

    $code = wp_remote_retrieve_response_code( $response );
    if ( $code === 201 || $code === 200 ) {
        wp_send_json_success( array( 'message' => 'Log saved to GitHub successfully!' ) );
    } else {
        wp_send_json_error( 'GitHub API Error (' . $code . ')', $code );
    }
}