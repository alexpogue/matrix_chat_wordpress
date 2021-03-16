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

function matrix_get_matrix_host() {
    return "matrix.alexpogue.com:8008";
}

function matrix_send_message() {
    $data = $_POST;

    $curl = curl_init();

    $matrix_host = matrix_get_matrix_host();

    $room_id = urlencode($data["room_id"]);
    $url = "http://" . $matrix_host . "/_matrix/client/r0/rooms/" . $room_id . "/send/m.room.message";

    $query_params = array('access_token' => $data['access_token']);
    $url = sprintf("%s?%s", $url, http_build_query($query_params));

    $post_data_array = [
        "msgtype" => "m.text",
        "body" => $data["body"]
    ];
    $post_data_json = json_encode($post_data_array);

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data_json);

    $result = curl_exec($curl);
    curl_close($curl);

    wp_send_json_success($result);
}

function matrix_register_user() {
    $data = $_POST;

    $curl = curl_init();

    $matrix_host = matrix_get_matrix_host();

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
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data_json);

    $result = curl_exec($curl);
    curl_close($curl);

    wp_send_json_success($result);
}

function matrix_login_user() {
    $data = $_POST;

    $curl = curl_init();

    $matrix_host = matrix_get_matrix_host();

    $url = "http://" . $matrix_host . "/_matrix/client/r0/login";

    $post_data_array = [
        "user" => $data["user"],
        "password" => $data["password"],
        "type" => "m.login.password"
    ];
    $post_data_json = json_encode($post_data_array);

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data_json);

    $result = curl_exec($curl);
    curl_close($curl);

    wp_send_json_success($result);
}

function matrix_create_room() {
    $data = $_POST;

    $curl = curl_init();

    $matrix_host = matrix_get_matrix_host();

    $url = "http://" . $matrix_host . "/_matrix/client/r0/createRoom";

    $query_params = array('access_token' => $data['access_token']);
    $url = sprintf("%s?%s", $url, http_build_query($query_params));

    $post_data_array = [
        "room_alias_name" => $data['room_alias_name']
    ];
    $post_data_json = json_encode($post_data_array);

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data_json);

    $result = curl_exec($curl);
    curl_close($curl);

    wp_send_json_success($result);
}

function matrix_join_room() {
    $data = $_POST;

    $curl = curl_init();

    $matrix_host = matrix_get_matrix_host();

    $room_id_or_alias = urlencode($data["room_id_or_alias"]);
    $url = "http://" . $matrix_host . "/_matrix/client/r0/rooms/" . $room_id_or_alias . "/join";

    $query_params = array('access_token' => $data['access_token']);

    $url = sprintf("%s?%s", $url, http_build_query($query_params));

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data_json);

    $result = curl_exec($curl);
    curl_close($curl);

    wp_send_json_success($result);
}

function matrix_initial_get_messages() {
    $data = $_GET;

    $curl = curl_init();

    $matrix_host = matrix_get_matrix_host();

    $room_id = $data["room_id"];
    $url = "http://" . $matrix_host . "/_matrix/client/r0/sync";

    $filter = array(
        'room' => array(
            'timeline' => array(
                'limit' => 0
            ),
            'rooms' => array($room_id)
        )
    );

    $query_params = array(
        'filter' => json_encode($filter),
        'access_token' => $data["access_token"] 
    );

    $url = sprintf("%s?%s", $url, http_build_query($query_params));

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
    curl_close($curl);

    $result_obj = json_decode($result, true);

    # TODO: add checking if these keys exist, and return error if not

    $rooms_we_are_in = $result_obj["rooms"]["join"];
    $target_room = $rooms_we_are_in[$room_id];

    $target_room_events = $target_room["timeline"]["events"];

    $result_event_array = array();

    array_push($result_event_array, ...$target_room_events);

    $should_continue_polling = $target_room["timeline"]["limited"];
    $token_to_poll = $target_room["timeline"]["prev_batch"];
    while ($should_continue_polling) {
        $url = "http://" . $matrix_host . "/_matrix/client/r0/rooms/" . $room_id . "/messages";
        $query_params = array(
            'from' => $token_to_poll,
            'dir' => "b",
            'limit' => 10,
            'access_token' => $data["access_token"]
        );

        $url = sprintf("%s?%s", $url, http_build_query($query_params));

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);
        curl_close($curl);

        $result_obj = json_decode($result, true);

        $target_room_events = $result_obj["chunk"];
        array_push($result_event_array, ...$target_room_events);

        $should_continue_polling = $result_obj["start"] != $result_obj["end"];
        $token_to_poll = $result_obj["end"];
    }

    $result_event_array = array_reverse($result_event_array);
    $result_obj = array(
        "messages" => $result_event_array,
        "next_token" => $token_to_poll
    );
    $result = json_encode($result_obj);
    wp_send_json_success($result);
/*
    $curl = curl_init();

    $matrix_host = matrix_get_matrix_host();

    $room_id = $data["room_id"];
    $url = "http://" . $matrix_host . "/_matrix/client/r0/sync";

    $query_params = array(
        'filter' => array(
            'room' => array(
                'timeline' => array(
                    'limit' => 1
                )
            )
        ),
        'access_token' => $data['access_token']
    );

    $url = sprintf("%s?%s", $url, http_build_query($query_params));

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
    curl_close($curl);

    wp_send_json_success($result);
*/
    /*
    $result_obj = json_decode($result, true);

    # TODO: add checking if these keys exist, and return error if not
    $rooms_we_are_in = $result_obj["rooms"]["join"];
    $target_room = $rooms_we_are_in[$room_id];
     */

/*
    $target_room_events = $target_room["timeline"]["events"];

    $result_event_array = array();

    foreach ($target_room_events as $event) {
        array_push($result_event_array, $event["type"]);
    }

    $prev_batch_token = $target_room["timeline"]["prev_batch"];


    $url = "http://" . $matrix_host . "/_matrix/client/r0/rooms/" . $room_id . "/messages";

    $query_params = array(
        'from' => $prev_batch_token,
        'dir' => "f",
        'limit' => 10,
        'access_token' => $data['access_token']
    );

    $url = sprintf("%s?%s", $url, http_build_query($query_params));

    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
    curl_close($curl);

    $result_obj = json_decode($result);

    wp_send_json_success($result);
*/
}

add_shortcode('matrix-chat-plugin', 'matrix_chat_plugin');
add_action('wp_enqueue_scripts', 'matrix_enqueue_scripts');
add_action('admin_enqueue_scripts', 'matrix_enqueue_scripts');

add_action('wp_ajax_send_message', 'matrix_send_message' );
add_action('wp_ajax_nopriv_send_message', 'matrix_send_message' );

add_action('wp_ajax_matrix_login', 'matrix_login_user');
add_action('wp_ajax_nopriv_matrix_login', 'matrix_login_user');

add_action('wp_ajax_matrix_register', 'matrix_register_user');
add_action('wp_ajax_nopriv_matrix_register', 'matrix_register_user');

add_action('wp_ajax_matrix_create_room', 'matrix_create_room');
add_action('wp_ajax_nopriv_matrix_create_room', 'matrix_create_room');

add_action('wp_ajax_matrix_join_room', 'matrix_join_room');
add_action('wp_ajax_nopriv_matrix_join_room', 'matrix_join_room');

add_action('wp_ajax_matrix_initial_get_messages', 'matrix_initial_get_messages');
add_action('wp_ajax_nopriv_matrix_initial_get_messages', 'matrix_initial_get_messages');
