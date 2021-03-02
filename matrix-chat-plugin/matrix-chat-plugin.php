<?php
/**
 * Plugin Name: Matrix.org Chat Plugin
 * Plugin URI: https://www.alexpogue.com
 * Description: Display the Matrix.org chat interface
 * Version: 0.1
 * Text Domain: matrix-org-plugin
 * Author: Alex Pogue
 * Author URI: https://www.alexpogue.com
 */

function matrix_chat_plugin($atts) {
    $Content = '<div class="matrix-chat-output">
                    <ul id="matrix-chat-output-list"></ul>
                </div>
                <div class="matrix-chat-login">
                    Username: <input type="text" id="matrix-chat-username-textbox"></input>
                    Password: <input type="text" id="matrix-chat-password-textbox"></input>
                    <button class="matrix-chat-login">Login</button><button class="matrix-chat-register">Register</button>
                </div>
                <div class="matrix-chat-input">
                    Your chat message: <input type="text" id="matrix-chat-textbox"></input>
                    <button class="matrix-chat-submit">Send</button>
                    <span="matrix-chat-input-errors"></span>
                </div>';

    return $Content;
}

function matrix_enqueue_scripts() {
    wp_register_style( 'custom_wp_admin_css', plugin_dir_url( __FILE__ ) . 'style/admin-style.css', false, '1.0.0' );
    wp_enqueue_style( 'custom_wp_admin_css' );

    wp_enqueue_script( 'matrix-chat-plugin', plugin_dir_url( __FILE__ ) . 'js/scripts.js', array( 'jquery' ), null, true );
    wp_localize_script('matrix-chat-plugin', 'settings', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));

}

function matrix_submit_message() {
}

add_shortcode('matrix-chat-plugin', 'matrix_chat_plugin');
add_action('wp_enqueue_scripts', 'matrix_enqueue_scripts');
add_action('admin_enqueue_scripts', 'matrix_enqueue_scripts');
add_action( 'wp_ajax_submit_message', 'matrix_submit_message' );
