<?php

/*
// TEMP: Enable update check on every request. Normally you don't need this! This is for testing only!
// NOTE: The 
//	if (empty($checked_data->checked))
//		return $checked_data; 
// lines will need to be commented in the check_for_plugin_update function as well.
*/
/*set_site_transient('update_plugins', null);

// TEMP: Show which variables are being requested when query plugin API
add_filter('plugins_api_result', 'aaa_result', 10, 3);
function aaa_result($res, $action, $args) {
	print_r($res);
	return $res;
}
// NOTE: All variables and functions will need to be prefixed properly to allow multiple plugins to be updated
*/

global $api_url, $idc_plugin_slug, $api_key, $idc_license_type;
$api_url = 'https://ignitiondeck.com/id/pluginserv/';
$idc_license_type = idc_license_type();
$idc_plugin_slug = basename(dirname(__FILE__));
$api_key = '';
$general = get_option('md_receipt_settings');
if (!empty($general)) {
	$general = maybe_unserialize($general);
	$api_key = (isset($general['license_key']) ? $general['license_key'] : '');
}

// Take over the update check
//add_filter('pre_set_site_transient_update_plugins', 'check_for_idc_update', 20);

function check_for_idc_update($checked_data) {
	global $api_url, $idc_plugin_slug, $wp_version;

	$plugin_file = $idc_plugin_slug .'/'. $idc_plugin_slug .'.php';

	//Comment out these two lines during testing.
	if (empty($checked_data->checked)) {
		return $checked_data;
	}

	$args = array(
		'slug' => $idc_plugin_slug,
		'version' => idc_current_version(),
	);

	// Start checking for an update
	$raw_response = idc_update_info('basic_check', $args);

	if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200)) {
		$response = unserialize($raw_response['body']);
	}

	if (is_object($response) && !empty($response)) // Feed the update data into WP updater
		$checked_data->response[$plugin_file] = $response;

	return $checked_data;
}

// Take over the Plugin info screen
//add_filter('plugins_api', 'idc_api_call', 10, 3);

function idc_api_call($def, $action, $args) {
	global $idc_plugin_slug, $api_url, $wp_version, $api_key;

	$plugin_file = $idc_plugin_slug .'/'. $idc_plugin_slug .'.php';

	if (!isset($args->slug) || ($args->slug !== $idc_plugin_slug)) {
		return $def;
	}

	$args->version = idc_current_version();

	$request = idc_update_info($action, $args);

	if (is_wp_error($request)) {
		$res = new WP_Error('plugins_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>'), $request->get_error_message());
	} else {
		$res = unserialize($request['body']);

		if ($res === false)
			$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $request['body']);
	}

	return $res;
}

function idc_update_info($action, $args) {
	global $api_key, $api_url, $wp_version, $idc_license_type;
	$request_string = array(
		'body' => array(
			'action' => $action, 
			'request' => serialize($args),
			'api-key' => $api_key,
			'license_type' => $idc_license_type
		),
		'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
	);

	$request = wp_remote_post($api_url, $request_string);
	return $request;
}

function idc_current_version() {
	global $idc_plugin_slug;
	$plugin_file = $idc_plugin_slug .'/'. $idc_plugin_slug .'.php';
	$plugin_info = get_plugin_data(WP_PLUGIN_DIR.'/'.$plugin_file);
	return $plugin_info['Version'];
}

function is_idc_free() {
	if (is_idc_licensed()) {
		return false;
	}
	return true;
}

function is_idc_licensed() {
	$is_licensed = get_transient('is_idc_licensed');
	if (!$is_licensed) {
		$is_licensed = get_option('is_idc_licensed');
	}
	return $is_licensed;
}

function was_idc_licensed() {
	return get_option('was_idc_licensed');
}

function idc_license_type() {
	$is_licensed = is_idc_licensed();
	if ($is_licensed) {
		return 'active';
	}
	return 'free';
}

/**
 * Display a non-dismissible admin notice about deprecated CRM settings.
 */
function deprecated_crm_notice() {
	echo '<div class="notice notice-error is-dismissible">
            <p><strong>IgnitionDeck Commerce: ACTION REQUIRED - Your emails might be at risk.</strong></p>
            <p>We\'ve retired native integration with Sendgrid and Mandrill. Please set up emails on your hosting server or use a WP Mail plugin such as <a href="' . esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=wp-mail-smtp' ) ) . '" class="thickbox">WP Mail SMTP</a> to continue delivery of emails from your website.</p>
        </div>';
}

add_action( 'plugins_loaded', 'handle_deprecated_crm_settings' );
/**
 * Check for deprecated CRM settings and display an admin notice if needed.
 */
function handle_deprecated_crm_settings() {
	$crm_settings = get_option( 'crm_settings' );
	// Check if CRM settings exist.
	if ( ! $crm_settings ) {
		return false;
	}

	// Check if Sendgrid or Mandrill is enabled.
	if ( ! empty( $crm_settings['enable_sendgrid'] ) || ! empty( $crm_settings['enable_mandrill'] ) ) {
		// Set deprecated value for SendGrid & mandrill CRM settings.
		unset( $crm_settings['enable_sendgrid'] );
		unset( $crm_settings['sendgrid_api_key'] );
		unset( $crm_settings['enable_mandrill'] );
		unset( $crm_settings['mandrill_key'] );
		$crm_settings['deprecated_sendgrid_mandrill_notice'] = true;
		update_option( 'crm_settings', $crm_settings );

		// Sends an email to the admin email address from the WordPress installation, informing them of the sunset of these services and recommending the installation and setup of WP Mail SMTP.
		$to      = get_option( 'admin_email' );
		$subject = 'ACTION REQUIRED - Sending of your IgnitionDeck transactional emails may be halted';
		$message = 'We\'ve retired native integration with Sendgrid and Mandrill. Please set up emails on your hosting server or use a WP Mail plugin such as <a href="' . esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=wp-mail-smtp' ) ) . '" target="_blank">WP Mail SMTP</a> to continue delivery of emails from your website.';
		$headers = array(
			'From: ' . get_bloginfo( 'name' ) . '| IgnitionDeck Commerce <' . get_option( 'admin_email' ) . '>',
			'Content-Type: text/html; charset=UTF-8',
		);
		wp_mail( $to, $subject, $message, $headers );
	} else {
		if ( ! empty( $crm_settings['deprecated_sendgrid_mandrill_notice'] ) ) {
			// Check if WP Mail SMTP or its pro version is active.
			if ( function_exists('is_plugin_active') && ! is_plugin_active( 'wp-mail-smtp/wp_mail_smtp.php' ) && ! is_plugin_active( 'wp-mail-smtp-pro/wp_mail_smtp.php' ) ) {
				// Output an admin notice.
				add_action( 'admin_notices', 'deprecated_crm_notice' );
			} else {
				$crm_settings['deprecated_sendgrid_mandrill_notice'] = false;
				update_option( 'crm_settings', $crm_settings );
			}
		}
	}
	return true;
}


