<?php
add_action( 'idf_id_update_account', 'idf_id_update_account' );

/**
 * Set the validation type for IDF license entry options.
 *
 * This function updates the option 'idf_license_entry_options' with the specified
 * validation type. The validation type can be 'email' or any other custom type.
 *
 * @param string $type The validation type to be set. Default is 'email'.
 * @return void
 */
function idf_id_set_validation_type($type = 'email') {
	update_option('idf_license_entry_options', $type);
}

/**
 * Update the account information and set license level options.
 *
 * This function updates the account information with the specified ID and sets
 * the license level options based on the validation result.
 *
 * @param string $id_account The ID of the account to be updated.
 * @return void
 */
function idf_id_update_account($id_account) {
	update_option('id_account', $id_account);
	$license_level = idf_id_validate_account($id_account);

	if ( ! is_bool( $license_level ) ) {
		switch ($license_level) {
			case 'ide':
			case 'imcp_monthly':
			case 'imcp_annual':
				$is_pro = 1;
				$is_idc_licensed = 1;
				$is_basic = 0;
				break;
			case 'idc':
				$is_idc_licensed = $is_basic = 1;
				$is_pro = 0;
				break;
			default:
				$is_pro = $is_idc_licensed = $is_basic = 0;
				break;
		}
		#devnote we can set transients from the option? Can we push these to idcf/idc php?
		update_option('is_id_pro', $is_pro);
		update_option('is_idc_licensed', $is_idc_licensed);
		update_option('is_id_basic', $is_basic);
		set_transient('is_id_pro', $is_pro);
		set_transient('is_idc_licensed', $is_idc_licensed);
		set_transient('is_id_basic', $is_basic);
	}
}

/**
 * Validate the ID account and activate the license.
 *
 * This function validates the ID account and activates the license by sending a request to the IgnitionDeck API.
 *
 * @param string $id_account The ID account to be validated and activated.
 * @return mixed Returns the item ID associated with the validated license, or false if validation fails.
 */
function idf_id_validate_account($id_account) {
    $download_list = array(
        'ide'          => '83885', // Enterprise Annual
        'idc'          => '83887', // Echelon Annual
        'imcp_monthly' => '196344',
        'imcp_annual'  => '196335',
        'free'         => '1'
    );

    $declined_license_statuses = array(
        'invalid',
        'disabled',
        'expired',
    );

    $declined_license_error_codes = array(
        'expired',
        'disabled',
        'missing',
        'missing_url',
        'no_activations_left',
        'license_not_activable',
        'invalid_item_id',
        'key_mismatch',
        'item_name_mismatch',
        'blank',
    );

    // Activate License
    $api_url = 'https://members.ignitiondeck.com/';
    $query = array(
        'edd_action' => 'verify_license_by_email',
        'url'        => $_SERVER['HTTP_HOST'],
        'email'      => $id_account
    );
    $querystring = http_build_query($query);
    $url = urldecode($api_url . '?' . $querystring);

    $response = wp_remote_get($url, array(
        'timeout'   => 30,
        'sslverify' => false, // Optional: verify SSL certificates (set to true for production)
    ));

    if (is_wp_error($response)) {
        echo 'HTTP request failed: ' . esc_html($response->get_error_message());
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $return = json_decode($body, true);

    if (!$return) {
        echo 'Invalid response from API.';
        return false;
    }

    if (!$return['success'] && 
        in_array($return['error'], $declined_license_error_codes, true) && 
        in_array($return['license'], $declined_license_statuses, true)
    ) {
        // If license missing on EDD, check Legacy
        $download_list = array(
            'ide'   => '30',
            'idc'   => '29',
            'free'  => '1'
        );

        $api_url = 'https://ignitiondeck.com/id/';
        $query = array(
            'action'         => 'md_validate_account',
            'id_account'     => $id_account,
            'download_list'  => $download_list
        );
        $querystring = http_build_query($query);
        $url = $api_url . '?' . $querystring;

        $response = wp_remote_get($url, array(
            'timeout'   => 30,
            'sslverify' => false, // Optional: verify SSL certificates (set to true for production)
        ));

        if (is_wp_error($response)) {
            echo 'HTTP request failed: ' . esc_html($response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $license_level = idf_process_account_validation($body);
        return array_search($license_level, $download_list);
    } elseif ($return['success'] == 1 && $return['license'] == 'valid') {
        $return = apply_filters('edd_product_ids', $return);
        update_option('license_expiry', $return['expires']);
        echo wp_kses_post(edd_api_notice('valid'));
        return array_search($return['item_id'], $download_list);
    } else {
        if (isset($return['error'])) {
            echo wp_kses_post(edd_api_notice($return['error'], 'error'));
        }
        return false;
    }
}

/**
 * Schedule the ID account cron job to run twice daily.
 *
 * This function schedules the ID account cron job to run twice daily. It retrieves the ID account and license entry options,
 * and updates the account if the license entry option is set to 'email'.
 *
 * @return void
 */
function idf_schedule_twicedaily_id_account_cron() {
	$id_account = get_option('id_account');
	$license_option = get_option('idf_license_entry_options');
	if ($license_option == 'email') {
		idf_id_update_account($id_account);
	}
}

add_action('schedule_twicedaily_idf_cron', 'idf_schedule_twicedaily_id_account_cron');

/**
 * Parse the license key data and update the corresponding option.
 *
 * This function parses the license key data and updates the corresponding option based on the scale of the key data.
 *
 * @param array $key_data The array containing the license key data.
 * @return int The result of the option update operation.
 */
function idf_parse_license($key_data) {
	$scale = max($key_data['types']);
	$return = 0;
	switch ($scale) {
		case '1':
			$return = update_option('idf_key', $key_data['keys']['idcf_key']);
			break;
		case '2':
			$return = update_option('idf_key', $key_data['keys']['idc_key']);
			break;
		case '3':
			$return = update_option('idf_key', $key_data['keys']['idcf_key']);
		default:
			$return = update_option('idf_key', '');
			break;
	}
	return $return;
}

/**
 * Display an API notice message based on the return value.
 *
 * This function displays an API notice message based on the return value. It 
 * handles various cases such as missing license, disabled license key, expired 
 * license, etc.
 *
 * @param string $ret The return value indicating the status of the license.
 * @param string $class The CSS class for the notice message. Default is 'success'.
 * @return string The HTML notice message to be displayed.
 */
function edd_api_notice($ret, $class='success') {
	$msg = '';
	switch($ret) {
		case 'missing' : $msg = __('License doesn\'t exist', 'ignitiondeck' ); break;
		case 'missing_url' : $msg = __('URL not provided', 'ignitiondeck' ); break;
		case 'license_not_activable' : $msg = __('Attempting to activate a bundle\'s parent license', 'ignitiondeck' ); break;
		case 'disabled' : $msg = __('License key revoked', 'ignitiondeck' ); break;
		case 'no_activations_left' : $msg = __('No activations left', 'ignitiondeck' ); break;		
		case 'expired':
			// Translators: %s: URL for renewal
			$message = __('License has expired, <a href="%s" target="_blank">renew it now</a>', 'ignitiondeck');
			$url = 'https://members.ignitiondeck.com/welcome/';
			$message = sprintf($message, esc_url($url));
		case 'key_mismatch' : $msg = __('License is not valid for this product', 'ignitiondeck' ); break;
		case 'invalid_item_id' : $msg = __('Invalid Item ID', 'ignitiondeck' ); break;
		case 'item_name_mismatch' : $msg = __('License is not valid for this product', 'ignitiondeck' ); break;
		case 'blank' : $msg = __('Please enter a valid license key', 'ignitiondeck' ); break;
		case 'valid' : $msg = __('License has been validated successfully', 'ignitiondeck' ); break;
	}
	$message = $msg;
	$notice = '<div class="notice notice-'.$class.' is-dismissible"><p>'.$message.'.</p></div>';
	return $notice;
}

add_filter( 'edd_product_ids', 'process_edd_product_ids' );
/**
 * Process EDD product IDs.
 *
 * This function processes the Easy Digital Downloads (EDD) product IDs and performs
 * necessary modifications based on specific conditions. It includes a switch statement
 * to handle different item IDs and modify them accordingly.
 *
 * @param array $return The array containing the item ID to be processed.
 * @return array The modified array after processing the item ID.
 */
function process_edd_product_ids( $return ) {
	if(isset($return['item_id'])) {
		switch($return['item_id']) {
			case 84018: //Enterprise Lifetime
				$return['item_id'] = 83885;
				break;
			default:
				break;
		}
	}
	return $return;
}

