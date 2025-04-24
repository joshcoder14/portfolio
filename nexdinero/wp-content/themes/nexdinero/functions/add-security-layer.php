<?php

/**
 * Auto log out after 2 hours of inactivity
 */
    add_filter('auth_cookie_expiration', 'wp_auth_cookie_expiration_filter', 10, 2);
    function wp_auth_cookie_expiration_filter($expiration, $user_id) {
        
        $expiration = 2 * 60 * 60; 
        
        return $expiration;
    }


/**
 * Show less login info
 */
function show_less_login_info($errors_str) { 
   
    $pattern = "/<strong>ERROR<\/strong>: You have reached authentication limit, you will be able to try again in [a-zA-Z0-9- ]*minute[s]?./";
    $pattern_2 = "/CAPTCHA should not be empty/i";
    $pattern_3 = "/CAPTCHA response was incorrect/i";
    if( preg_match($pattern, $errors_str) ) { //skip pattern, since it's helpful to the login retry.

        return $errors_str;

    }elseif(preg_match( $pattern_2, $errors_str)){
       
        return $errors_str;

    }elseif(preg_match( $pattern_3, $errors_str)){

        return $errors_str;

    }else {
        return "<strong>ERROR</strong>: Stop guessing!"; 
    }
     
}
add_filter( 'login_errors', 'show_less_login_info' );

/**
 * Prevent Mass WordPress Login Attacks by setting locking the system when login fail.
 */
function login_attempt_vars( ){
    return [
        "failed_login_limit" => 5, // Number of authentification accepted
        "lockout_duration" => 300, // Stop authentification process for 5 minutes: 60*5 = 600
        "transient_name" => 'attempted_login' //Transient used
    ];
}

function check_attempted_login($user, $username, $password)
{
    $vars = login_attempt_vars();

    if (get_transient($vars['transient_name'])) {
        $datas = get_transient($vars['transient_name']);

        if ($datas['tried'] >= $vars['failed_login_limit']) {
            $until = get_option('_transient_timeout_' . $vars['transient_name']);
            $time = time_to_go($until);

            return new WP_Error('too_many_tried',  sprintf(__('<strong>ERROR</strong>: You have reached authentication limit, you will be able to try again in %1$s.'), $time));
        }
    }

    return $user;
}
add_filter('authenticate', 'check_attempted_login', 30, 3);
function login_failed($username)
{
    $vars = login_attempt_vars();

    if (get_transient($vars['transient_name'])) {
        $datas = get_transient($vars['transient_name']);
        $datas['tried']++;

        if ($datas['tried'] <= $vars['failed_login_limit'])
            set_transient($vars['transient_name'], $datas, $vars['lockout_duration']);
    } else {
        $datas = array(
            'tried' => 1
        );
        set_transient($vars['transient_name'], $datas, $vars['lockout_duration']);
    }
}
add_action('wp_login_failed', 'login_failed', 10, 1);

function time_to_go($timestamp)
{
    // converting the mysql timestamp to php time
    $periods = array(
        "second",
        "minute",
        "hour",
        "day",
        "week",
        "month",
        "year"
    );
    $lengths = array(
        "60",
        "60",
        "24",
        "7",
        "4.35",
        "12"
    );
    $current_timestamp = time();
    $difference = abs($current_timestamp - $timestamp);
    for ($i = 0; $difference >= $lengths[$i] && $i < count($lengths) - 1; $i++) {
        $difference /= $lengths[$i];
    }
    $difference = round($difference);
    if (isset($difference)) {
        if ($difference != 1)
            $periods[$i] .= "s";
        $output = "$difference $periods[$i]";
        return $output;
    }
}

/**
 * Disable Disable XML-RPC-API
 */
add_filter( 'xmlrpc_enabled', '__return_false' );


/**
 * Disallow file edits
 */
define( 'DISALLOW_FILE_EDIT', true );

/**
 * Hide WordPress Version Number
 */
remove_action('wp_head', 'wp_generator');

/**
 * Disable media comments
 */
function filter_media_comment_status( $open, $post_id ) {
    $post = get_post( $post_id );
    if( $post->post_type == 'attachment' ) {
        return false;
    }
    return $open;
}
add_filter( 'comments_open', 'filter_media_comment_status', 10 , 2 );