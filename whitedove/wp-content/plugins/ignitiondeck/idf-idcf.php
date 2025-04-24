<?php
/**
 * Validate the IDCF license key.
 *
 * This function validates the provided IDCF license key by making a request to the
 * IgnitionDeck API. It checks if the key is valid and retrieves the download
 * associated with the key if applicable.
 *
 * @param string $key The IDCF license key to validate.
 * @return mixed Returns the download associated with the key if the key is valid,
 *               otherwise returns false.
 */
function idf_idcf_validate_license($key) {
    $id_account = get_option('id_account');
    $download_list = array(
        '30' => '83885', // Enterprise Annual
        '29' => '83887', // Echelon Annual
        '1'  => '1'
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
        'url'        => $_SERVER['HTTP_HOST'],
        'license'    => $key
    );
    $querystring = http_build_query($query);
    $url = urldecode($api_url . '?' . $querystring);

    // Use wp_remote_get() for HTTP requests
    $response = wp_remote_get($url, array(
        'timeout'   => 30,
        'sslverify' => false, // Optional: verify SSL certificates (set to true for production)
    ));

    if (is_wp_error($response)) {
        echo 'HTTP request failed: ' . esc_html($response->get_error_message());
        return false;
    }

    $body = wp_remote_retrieve_body($response);
    $response_array = json_decode($body, true);

    // if (!$response_array) {
    //     echo 'Invalid response from API.';
    //     return false;
    // }

    if (!$response_array['success'] && 
        in_array($response_array['error'], $declined_license_error_codes, true) && 
        in_array($response_array['license'], $declined_license_statuses, true)
    ) {
        delete_option('is_idc_licensed');
        delete_option('is_id_pro');
        update_option('license_expiry', $response_array['error']);
        update_option('license_item_id', $response_array['item_id']);
        update_option('license_payment_id', $response_array['payment_id']);
        if (!empty($response_array['license_post_id'])) {
            update_option('license_post_id', $response_array['license_post_id']);
        }

        // If license missing on EDD, check Legacy
        $api_url = 'https://ignitiondeck.com/id/';
        $query = array(
            'action'     => 'md_validate_license',
            'key'        => $key,
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

        // if (!$response_array) {
        //     echo 'Invalid response from API.';
        //     return false;
        // }

        return idf_process_validation($body)['download'];
    } elseif ($response_array['success'] == 1 && $response_array['license'] == 'valid') {
        $response_array = apply_filters('edd_product_ids', $response_array);
        update_option('license_expiry', $response_array['expires']);
        update_option('license_item_id', $response_array['item_id']);
        if (!empty($response_array['license_post_id'])) {
            update_option('license_post_id', $response_array['license_post_id']);
        }
        echo wp_kses_post(edd_api_notice('valid'));
        return array_search($response_array['item_id'], $download_list);
    } else {
        if (isset($response_array['error'])) {
            echo wp_kses_post(edd_api_notice($response_array['error'], 'error'));
        }
        return false;
    }
}

/**
 * Check if the IDCF key is valid.
 *
 * This function checks if the provided data contains a valid IDCF key. It examines
 * the 'response' and 'download' values in the data array to determine the validity
 * of the key. If the 'response' is set and true, and the 'download' value is '30',
 * the key is considered valid and the function returns 1. Otherwise, it returns 0.
 *
 * @param array $data The data containing the 'response' and 'download' values.
 * @return int The validity of the IDCF key (1 for valid, 0 for invalid).
 */
function is_idcf_key_valid($data) {
	$valid = 0;
	if (isset($data['response'])) {
		if ($data['response']) {
			if (isset($data['download'])) {
				if ($data['download'] == '30') {
					$valid = 1;
				}
			}
		}
	}
	return $valid;
}

/**
 * Get the IDCF license type based on the data.
 *
 * This function determines the type of IDCF license based on the provided data. It
 * uses a switch statement to check the 'download' value in the data array and returns
 * the corresponding license type. If the 'download' value is '30', it returns 3. If it
 * is '1', it returns 1. Otherwise, it returns 0.
 *
 * @param array $data The data containing the 'download' value.
 * @return int The IDCF license type.
 */
function idf_idcf_license_type($data) {
	switch ($data['download']) {
		case '30':
			return 3;
			break;
		case '1':
			return 1;
			break;
		default:
			return 0;
			break;
	}
}

/**
 * Get the IDCF mode.
 *
 * This function retrieves the mode of the IDCF based on the platform. It checks if the
 * platform is 'idc' and if the function is_idc_free exists and returns true. If both
 * conditions are met, it sets the mode to 'idc_free'.
 *
 * @return string The mode of the IDCF.
 */
function idcf_mode() {
	$mode = idf_platform();
	if ($mode == 'idc') {
		if (function_exists('is_idc_free') && is_idc_free()) {
			$mode = 'idc_free';
		}
	}
	return $mode;
}

add_action('idcf_license_update', 'idcf_license_update');

/**
 * Update the IDCF license with the provided license key.
 *
 * This function updates the IDCF license with the provided license key. It checks
 * the validity of the license key and sets the appropriate flags based on the
 * license type. It then updates the options and sets transients accordingly.
 *
 * @param string $license_key The license key to update.
 * @return void
 */
function idcf_license_update($license_key) {
	$is_pro = 0;
	$is_basic = 0;
	update_option('id_license_key', $license_key);
	$validate = idf_idcf_validate_license($license_key);

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
		update_option('is_id_pro', $is_pro);
		update_option('is_idc_licensed', $is_idc_licensed);
		update_option('is_id_basic', $is_basic);
		set_transient('is_id_pro', $is_pro);
		set_transient('is_idc_licensed', $is_idc_licensed);
		set_transient('is_id_basic', $is_basic);
	}
}

/**
 * Schedule the IDCF cron job to run twice daily.
 *
 * This function checks the 'idf_license_entry_options' option and if it's empty or set to 'keys',
 * it retrieves the 'md_receipt_settings' option and the 'id_license_key' option to update the IDCF license.
 * 
 * @todo Isn't there another function doing this?
 *
 * @return void
 */
function idf_schedule_twicedaily_idcf_cron() {
	$license_option = get_option('idf_license_entry_options');
	if (empty($license_option) || $license_option == 'keys') {
		$general = get_option('md_receipt_settings');
		$general = maybe_unserialize($general);
		$idc_license_key = (!empty($general['license_key']) ? $general['license_key'] : get_option( 'id_license_key' ));
		idcf_license_update($idc_license_key);
	}
}

add_action('schedule_twicedaily_idf_cron', 'idf_schedule_twicedaily_idcf_cron');
