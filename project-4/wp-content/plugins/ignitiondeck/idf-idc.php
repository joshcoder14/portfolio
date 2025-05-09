<?php

/**
 * Check if the IDC key is valid.
 *
 * This function checks if the provided data contains a valid IDC key. It examines
 * the 'response' and 'download' values in the data array to determine the validity
 * of the key. If the 'response' is set and true, and the 'download' value is '29',
 * the key is considered valid and the function returns 1. Otherwise, it returns 0.
 *
 * @param array $data The data containing the 'response' and 'download' values.
 * @return int Returns 1 if the key is valid, 0 otherwise.
 */
function is_idc_key_valid($data) {
	$valid = 0;
	if (isset($data['response'])) {
		if ($data['response']) {
			if (isset($data['download'])) {
				if ($data['download'] == '29') {
					$valid = 1;
				}
			}
		}
	}
	return $valid;
}

/**
 * Determine the IDC license type based on validity.
 *
 * This function determines the IDC license type based on the validity status.
 * If the license is valid, it returns 2. Otherwise, it returns 0.
 *
 * @param int $valid The validity status of the IDC license.
 * @return int Returns the IDC license type: 2 if valid, 0 if invalid.
 */
function idf_idc_license_type($valid) {
	switch ($valid) {
		case 1:
			return 2;
			break;

		default:
			return 0;
			break;
	}
}

/**
 * Validate IDC license key.
 *
 * This function validates the IDC license key by making a request to the IgnitionDeck API.
 *
 * @param string $key The IDC license key to validate.
 * @return mixed Returns the download ID if the license is valid, false otherwise.
 */
function idf_idc_validate_key($key) {
    $id_account = get_option('id_account');
    $download_list = array(
        '30' => '83885', //Enterprise Annual
        '29' => '83887', //Echelon Annual
        '1' => '1'
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

    $api_url = 'https://members.ignitiondeck.com/';
    $query = array(
        'edd_action' => 'verify_license',
        'url' => $_SERVER['HTTP_HOST'],
        'license' => $key
    );
    $querystring = http_build_query($query);
    $url = urldecode($api_url . '?' . $querystring);

    // Use wp_remote_get() for HTTP requests
    $response = wp_remote_get($url, array(
        'timeout'   => 30, // Optional: specify a timeout in seconds
        'sslverify' => false, // Optional: verify SSL certificates (set to true for production)
    ));

    if (is_wp_error($response)) {
        echo 'HTTP request failed: ' . esc_html($response->get_error_message());
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $response_array = json_decode($body, true);

    if (!$response_array) {
        // If response is empty or not valid JSON
        echo 'Invalid response from API.';
        return false;
    }

    if (!$response_array['success'] && in_array($response_array['error'], $declined_license_error_codes, true) && in_array($response_array['license'], $declined_license_statuses, true)) {
        // License declined, check legacy
        $api_url = 'https://ignitiondeck.com/id/';
        $query = array(
            'action' => 'md_validate_license',
            'key' => $key,
            'id_account' => $id_account
        );
        $querystring = http_build_query($query);
        $url = $api_url . '?' . $querystring;

        $response = wp_remote_get($url, array(
            'timeout'   => 30,
            'sslverify' => false,
        ));

        if (is_wp_error($response)) {
            echo 'HTTP request failed: ' . esc_html($response->get_error_message());
            return false;
        }

        $body = wp_remote_retrieve_body($response);
        $response_array = json_decode($body, true);

        if (!$response_array) {
            echo 'Invalid response from API.';
            return false;
        }

        return idf_process_validation($body)['download'];
    } elseif ($response_array['success'] == 1 && $response_array['license'] == 'valid') {
        $response_array = apply_filters('edd_product_ids', $response_array);
        update_option('license_expiry', $response_array['expires']);
        echo wp_kses_post(edd_api_notice('valid'));
        return array_search($response_array['item_id'], $download_list);
    } else {
        if (isset($response_array['error'])) {
            echo wp_kses_post(edd_api_notice($response_array['error'], 'error'));
        }
        return false;
    }
}

add_action('idc_license_update', 'idc_license_update');

/**
 * Update the IDC license key and set related options.
 *
 * This function updates the IDC license key in the 'md_receipt_settings' option and sets
 * related options based on the validation result of the key. It also sets transients for
 * 'is_id_pro', 'is_idc_licensed', and 'is_id_basic' based on the validation result.
 *
 * @param string $idc_license_key The IDC license key to update.
 * @return void
 */
function idc_license_update($idc_license_key) {
	$valid = 0;
	$general = get_option('md_receipt_settings');
	$general = maybe_unserialize($general);
	$general['license_key'] = $idc_license_key;
	update_option('md_receipt_settings', $general);
	$validate = idf_idc_validate_key($idc_license_key);

	if ( ! is_bool( $validate ) ) {
		switch ($validate) {
			case '30':
				$is_pro = 1;
				$is_idc_licensed = 1;
				$is_basic = 0;
				break;
			case '29':
				$is_idc_licensed = $is_basic = 1;
				$is_pro = 0;
				break;
			default:
				$is_pro = $is_idc_licensed = $is_basic = 0;
				break;
		}
		#devnote we can set transients from the option? Can we push these to idcf/idc php?
		update_option('idcf_updated', true);

		update_option('is_id_pro', $is_pro);
		update_option('is_idc_licensed', $is_idc_licensed);
		update_option('is_id_basic', $is_basic);
		set_transient('is_id_pro', $is_pro);
		set_transient('is_idc_licensed', $is_idc_licensed);
		set_transient('is_id_basic', $is_basic);
	}
}

add_action('schedule_twicedaily_idf_cron', 'idf_schedule_twicedaily_idc_cron');

/**
 * Schedule the IDC cron job to run twice daily.
 *
 * This function checks the 'idf_license_entry_options' option and if it's empty or set to 'keys',
 * it retrieves the IDC license key from the options and updates the IDC license settings using the
 * idc_license_update function.
 *
 * @todo Are we doing this in 3 places?
 * 
 * @return void
 */
function idf_schedule_twicedaily_idc_cron() {
	$license_option = get_option('idf_license_entry_options');
	if (empty($license_option) || $license_option == 'keys') {
		$general = get_option('md_receipt_settings');
		$general = maybe_unserialize($general);
		$idc_license_key = (!empty($general['license_key']) ? $general['license_key'] : get_option( 'id_license_key' ));
		idc_license_update($idc_license_key);
	}
}

