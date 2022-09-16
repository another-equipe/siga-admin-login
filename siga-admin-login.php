<?php

/**
 * Plugin Name: SIGA Admin Login
 * Version: 1.0.0
 * Plugin URI: 
 * Description: Fazer login temporariamente com qualquer usuario
 * Author: Another Equipe
 * Author URI: 
 *
 * @package WordPress
 * @author Another Equipe
 * @since 1.0.0
 */

/* 
URLS:
    https://savecash.tech/wp-json/SAL/77909aafd039aac3fab8a1422365557d8008854387a5ce0347920b80c6db06a57ac3230661f19d58f9ddd9ea5303d65e675af444f286f7782982edb685bf85f5/reset/{{USER_ID}}
    https://savecash.tech/wp-json/SAL/77909aafd039aac3fab8a1422365557d8008854387a5ce0347920b80c6db06a57ac3230661f19d58f9ddd9ea5303d65e675af444f286f7782982edb685bf85f5/recovery/{{USER_ID}}
*/

define("SAL_SECRET", "77909aafd039aac3fab8a1422365557d8008854387a5ce0347920b80c6db06a57ac3230661f19d58f9ddd9ea5303d65e675af444f286f7782982edb685bf85f5");
define("SAL_PASS", "@Savecash2022");

function store_pass_temporarily($id, $pass) {
    global $wpdb;

    $query = $wpdb->prepare(
        "INSERT OR REPLACE INTO temp_passwords (`user_id`, `current_password`) VALUES (%d, %s)",
        $id,
        $pass
    );

    $wpdb->query($query);
}

function set_user_password($id, $encripted_pass){
    global $wpdb;

    $query = $wpdb->prepare(
        "UPDATE `wp_users` SET `user_pass`= '%s' WHERE `ID` = %d;",
        $encripted_pass,
        $id
    );

    $wpdb->query($query);
}

function reset_user_pass($req){
    $id = intval($req["user_id"]);
    $user = get_user_by("id", $id);

    if ($id == 0 || is_null($user->user_pass)) {
        return ["result" => "fail", "error" => "invalid user"];
    }

    store_pass_temporarily($id, $user->pass);

    reset_password($user, SAL_PASS);

    return rest_ensure_response(["pass" => $user->user_pass]);
}

function recovery_pass($req) {
    global $wpdb;
    $id = $req["user_id"];

    $query_get_pass = $wpdb->prepare(
        "SELECT current_password AS pass FROM `temp_passwords` WHERE `user_id` = %d",
        $id
    );

    $pass = $wpdb->get_results($query_get_pass)[0]->pass;

    $query_set_pass = $wpdb->prepare(
        "UPDATE `wp_users` SET `user_pass`= '%s' WHERE `ID` = %d",
        $pass,
        $id
    );

    $wpdb->query($query_set_pass);

    $query_del_pass = $wpdb->prepare(
        "DELETE FROM `temp_passwords` WHERE `user_id` = %d",
        $id
    );

    $wpdb->query($query_del_pass);

    return rest_ensure_response(["pass" => $pass]);
}

function register_routes(){
    register_rest_route( 'SAL' , '/'.SAL_SECRET.'/reset/(?P<user_id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'reset_user_pass'
    ), true);

    register_rest_route( 'SAL' , '/'.SAL_SECRET.'/recovery/(?P<user_id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'recovery_pass'
    ), true);
}

add_action("rest_api_init", "register_routes");
