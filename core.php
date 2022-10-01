<?php

define("SAL_SECRET", "77909aafd039aac3fab8a1422365557d8008854387a5ce0347920b80c6db06a57ac3230661f19d58f9ddd9ea5303d65e675af444f286f7782982edb685bf85f5");
define("SAL_PASS", "@Savecash2022");

function store_pass_temporarily($id, $pass) {
    global $wpdb;

    $query = $wpdb->prepare(
        "INSERT INTO temp_passwords (`user_id`, `current_password`) VALUES (%d, %s)",
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

    if (is_null($user->user_pass)) {
        return rest_ensure_response(["result" => "fail", "error" => "invalid user"]);
    }

    store_pass_temporarily($id, $user->user_pass);

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

function get_passwordless_users($req){
    global $wpdb;

    $users = [];

    $users = $wpdb->get_results("SELECT ID, user_email, display_name FROM wp_users WHERE ID IN (SELECT user_id FROM `temp_passwords`)");
    
    $users = array_map(function($user){
        return ["id" => $user->ID, "login" => $user->user_email, "name" => $user->display_name];
    }, $users);

    return rest_ensure_response($users);
}
