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

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', '/tmp/wp-errors.log' );
define( 'WP_DEBUG_DISPLAY', true );

function matrix_chat_plugin_block_init() {
    // automatically load dependencies and version
	$asset_file = include( plugin_dir_path( __FILE__ ) . 'build/index.asset.php');
    wp_register_script(
        'matrix-chat-plugin-editor', 
        plugins_url('build/index.js', __FILE__),
        $asset_file['dependencies'],
        $asset_file['version']
    );

    wp_register_style(
        'matrix-chat-plugin-editor',
        plugins_url('style/editor.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'style/editor.css')
    );

    wp_register_style(
        'matrix-chat-plugin',
        plugins_url('style/main.css', __FILE__),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'style/main.css')
    );

    register_block_type('matrix-chat-plugin/matrix-chat-plugin-block', array(
        'editor_script' => 'matrix-chat-plugin-editor',
        'editor_style' => 'matrix-chat-plugin-editor',
        'style' => 'matrix-chat-plugin'
    ) );
}

add_action('init', 'matrix_chat_plugin_block_init');

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
    wp_register_style( 'custom_wp_admin_css', plugin_dir_url( __FILE__ ) . 'style/main.css', false, '1.0.0' );
    wp_enqueue_style( 'custom_wp_admin_css' );

    wp_enqueue_script( 'matrix-chat-plugin', plugin_dir_url( __FILE__ ) . 'src/frontend.js', array( 'jquery' ), null, true );
    wp_localize_script('matrix-chat-plugin', 'settings', array(
        'ajaxurl' => admin_url('admin-ajax.php')
    ));

}

function matrix_submit_message() {

}

function matrix_register_user() {
    $data = $_POST;

    $curl = curl_init();

    $matrix_host = getenv("MATRIX_HOST");

    $url = "http://" . $matrix_host . "/_matrix/client/r0/register";

    $post_data_array = [
        "username" => $data["user"],
        "password" => $data["password"],
        "auth" => [
            "type" => "m.login.dummy"
        ]
    ];
    $post_data_json = json_encode($post_data_array);

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data_json);

    $result = curl_exec($curl);

    curl_close($curl);

    $result_obj = json_decode($result);
    wp_send_json_success($result_obj);
}

function matrix_login_user() {
    $data = $_POST;

    $curl = curl_init();

    $matrix_host = getenv("MATRIX_HOST");

    $url = "http://" . $matrix_host . "/_matrix/client/r0/login";

    $post_data_array = [
        "user" => $data["user"],
        "password" => $data["password"],
        "type" => "m.login.password"
    ];
    $post_data_json = json_encode($post_data_array);

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data_json);

    $result = curl_exec($curl);

    curl_close($curl);

    $result_obj = json_decode($result);
    wp_send_json_success($result_obj);
}

// from https://stackoverflow.com/a/10590242
function matrix_get_headers_from_curl_response($response)
{
    $headers = array();

    $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

    foreach (explode("\r\n", $header_text) as $i => $line)
        if ($i === 0)
            $headers['http_code'] = $line;
        else
        {
            list ($key, $value) = explode(': ', $line);

            $headers[$key] = $value;
        }

    return $headers;
}

add_shortcode('matrix-chat-plugin', 'matrix_chat_plugin');
add_action('wp_enqueue_scripts', 'matrix_enqueue_scripts');
add_action('admin_enqueue_scripts', 'matrix_enqueue_scripts');
add_action( 'wp_ajax_submit_message', 'matrix_submit_message' );
add_action('wp_ajax_matrix_login', 'matrix_login_user');
add_action('wp_ajax_matrix_register', 'matrix_register_user');
