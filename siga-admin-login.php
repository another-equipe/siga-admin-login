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

require 'core.php';

add_action("rest_api_init", function(){
    register_rest_route( 'SAL' , '/'.SAL_SECRET.'/reset/(?P<user_id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'reset_user_pass'
    ), true);

    register_rest_route( 'SAL' , '/'.SAL_SECRET.'/recovery/(?P<user_id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'recovery_pass'
    ), true);

    register_rest_route( 'SAL' , '/'.SAL_SECRET.'/users', array(
        'methods' => 'GET',
        'callback' => 'get_passwordless_users'
    ), true);
});
