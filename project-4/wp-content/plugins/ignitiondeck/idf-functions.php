<?php
/**
 * Get the development mode status.
 *
 * This function returns the development mode status by instantiating the ID_Dev_Tools
 * class and calling the dev_mode method.
 *
 * @return bool The development mode status.
 */
function idf_dev_mode() {
	$tools = New ID_Dev_Tools;
	return $tools->dev_mode();
}

/**
 * Get the current URL.
 *
 * This function returns the current URL by checking if SSL is enabled, then constructing
 * the URL using the server's HTTP_HOST and REQUEST_URI.
 *
 * @return string The current URL.
 */
function idf_current_url() {
	$prefix = 'http';
	if (is_ssl()) {
		$prefix .= 's';
	}
	$url = $prefix . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	return $url;
}

/**
 * Get the date format.
 *
 * This function returns the date format by applying the 'idf_date_format' filter to
 * the date format option, with a default format of 'm/d/Y'.
 *
 * @return string The date format.
 */
function idf_date_format() {
	$date_format = apply_filters('idf_date_format', get_option('date_format', 'm/d/Y'));
	return $date_format;
}

/**
 * Sanitize an array of values.
 *
 * This function sanitizes an array of values by applying the sanitize_text_field
 * function to each value in the array.
 *
 * @param array $array The array of values to be sanitized.
 * @return array The sanitized array.
 */
function idf_sanitize_array($array) {
	if (empty($array)) {
		return $array;
	}
	foreach ($array as $k=>$v) {
		$array[$k] = sanitize_text_field($v);
	}
	return $array;
}

/**
 * Get the platform option.
 *
 * This function retrieves the platform option from the database, with a default
 * value of 'idc'. The platform option is used to determine the active commerce
 * platform for the site.
 *
 * @return string The platform option.
 */
function idf_platform() {
	$platform = get_option('idf_commerce_platform', 'idc');
	return $platform;
}

/**
 * Check if the IDC plugin is active.
 *
 * This function checks if the IDC plugin is active by inspecting the list of active
 * plugins. It first checks if the $active_plugins array is empty, and if so, retrieves
 * the list of active plugins based on whether the site is a multisite installation or not.
 * If the $active_plugins array is not empty, it directly checks if the IDC plugin is
 * active based on the platform type. The function returns a boolean indicating whether
 * the IDC plugin is active.
 *
 * @return bool Whether the IDC plugin is active.
 */
function idf_has_idc() {
	global $active_plugins;
	$active = 0;
	if (empty($active_plugins)) {
		if (is_multisite()) {
			$active_plugins = get_site_option('active_sitewide_plugins');
			$active = array_key_exists('idcommerce/idcommerce.php', $active_plugins);
			if (!$active) {
				// check to see if multisite and single site active
				$active_plugins = get_option('active_plugins');
				$active = in_array('idcommerce/idcommerce.php', $active_plugins);
			}
		}
		else {
			$active_plugins = get_option('active_plugins');
			$active = in_array('idcommerce/idcommerce.php', $active_plugins);
		}
	}
	else {
		if (is_multisite()) {
			$active = array_key_exists('idcommerce/idcommerce.php', $active_plugins);
			if (!$active) {
				// check to see if multisite and single site active
				$active_plugins = get_option('active_plugins');
				$active = in_array('idcommerce/idcommerce.php', $active_plugins);
			}
		}
		else {
			$active = in_array('idcommerce/idcommerce.php', $active_plugins);
		}
	}
	return $active;
}

/**
 * Check if the IDC plugin is active.
 *
 * This function checks if the IDC plugin is active by inspecting the list of active
 * plugins. It first checks if the $active_plugins array is empty, and if so, retrieves
 * the list of active plugins based on whether the site is a multisite installation or not.
 * If the $active_plugins array is not empty, it directly checks if the IDC plugin is
 * active based on the platform type. The function returns a boolean indicating whether
 * the IDC plugin is active.
 *
 * @return bool Whether the IDC plugin is active.
 */
function idf_has_idcf() {
	global $active_plugins;
	$active = 0;
	if (empty($active_plugins)) {
		if (is_multisite()) {
			$active_plugins = get_site_option('active_sitewide_plugins');
			$active = array_key_exists('ignitiondeck-crowdfunding/ignitiondeck.php', $active_plugins);
			if (!$active) {
				// check to see if multisite and single site active
				$active_plugins = get_option('active_plugins');
				$active = in_array('ignitiondeck-crowdfunding/ignitiondeck.php', $active_plugins);
			}
		}
		else {
			$active_plugins = get_option('active_plugins');
			$active = in_array('ignitiondeck-crowdfunding/ignitiondeck.php', $active_plugins);
		}
	}
	else {
		if (is_multisite()) {
			$active = array_key_exists('ignitiondeck-crowdfunding/ignitiondeck.php', $active_plugins);
			if (!$active) {
				// check to see if multisite and single site active
				$active_plugins = get_option('active_plugins');
				$active = in_array('ignitiondeck-crowdfunding/ignitiondeck.php', $active_plugins);
			}
		}
		else {
			$active = in_array('ignitiondeck-crowdfunding/ignitiondeck.php', $active_plugins);
		}
	}
	return $active;
}

/**
 * Check if the IDC plugin has EDD platform.
 *
 * This function checks if the IDC plugin has the EDD platform by inspecting the
 * current platform. It returns a boolean indicating whether the IDC plugin has the
 * EDD platform.
 *
 * @return bool Whether the IDC plugin has the EDD platform.
 */
function idf_has_edd() {
	$platform = idf_platform();
	if ($platform == 'edd') {
		return true;
	}
	return false;
}

/**
 * Check if the site is using IgnitionDeck Crowdfunding platform.
 *
 * This function checks if the site is using IgnitionDeck Crowdfunding platform
 * and if the IDC plugin has the IDCF platform. It returns a boolean indicating
 * whether the site is using IgnitionDeck Crowdfunding platform.
 *
 * @return bool Whether the site is using IgnitionDeck Crowdfunding platform.
 */
function idf_crowdfunding() {
	if (idf_has_idcf() && idf_platform() == 'idc') {
		return true;
	}
	return false;
}

/**
 * Get the platforms used by the site.
 *
 * This function retrieves the platforms used by the site by checking for the existence
 * of specific classes and conditions. It returns an array of platforms used by the site.
 *
 * @return array The platforms used by the site.
 */
function idf_platforms() {
	$platforms = array();
	if (class_exists('ID_Member')) {
		$platforms[] = 'idc';
	}
	if (idf_has_idcf()) {
		if (is_id_basic() || is_id_pro()) {
			if (class_exists('EDD_API')) {
				$platforms[] = 'edd';
			}
			if (class_exists('WC_Install')) {
				$platforms[] = 'wc';
			}
		}
	}
	return $platforms;
}

/**
 * Check if the current theme is an IgnitionDeck theme.
 *
 * This function checks if the current theme is an IgnitionDeck theme by comparing
 * the theme author and template. It returns a boolean indicating whether the current
 * theme is an IgnitionDeck theme.
 *
 * @return bool Whether the current theme is an IgnitionDeck theme.
 */
function idf_is_id_theme() {
	$theme_info = wp_get_theme();
	$theme_author = strtolower($theme_info->get('Author'));
	if ($theme_author == 'ignitiondeck' || $theme_author == 'virtuousgiant') {
		return true;
	}
	if (is_child_theme()) {
		$parent_array = array(
			'fivehundred',
			'fundify',
			'crowdpress'
		);
		if (in_array($theme_info->template, $parent_array)) {
			return true;
		}
	}
	return false;
}

/**
 * Process account validation response.
 *
 * This function processes the account validation response and returns the license
 * level as a list of valid downloads by level id.
 *
 * @param string $response The account validation response.
 * @return array The list of valid downloads by level id.
 */
function idf_process_account_validation($response) {
	// this is where we would return a list of valid downloads by level id
	$license_level = json_decode($response);
	return $license_level;
}

/**
 * Process validation response.
 *
 * This function processes the validation response and returns the validity and
 * download ID as an array.
 *
 * @param string $response The validation response.
 * @return array The validity and download ID.
 */
function idf_process_validation($response) {
	$data = json_decode($response);
		if (isset($data->valid)) {
			$valid = $data->valid;
		}
		else {
			$valid = null;
		}
    if (isset($data->download_id)) {
    	$download = $data->download_id;
    }
    else {
    	$download = null;
    }
    return array('valid' => $valid, 'download' => $download);
}

/**
 * Deliver plugins.
 *
 * This function delivers the IDC, IDCF, and FH plugins. It triggers the delivery
 * process for each plugin, ensuring that the latest versions are obtained and
 * installed if necessary.
 */
function idf_deliver_plugins() {
	idf_idc_delivery();
	idf_idcf_delivery();
	idf_fh_delivery();
}

/**
 * Deliver IDC plugin.
 *
 * This function delivers the IDC plugin. It triggers the delivery process for the
 * plugin, ensuring that the latest version is obtained and installed if necessary.
 *
 * @todo Figure out what to do with the URL that's point to an old zip file that doesn't live there anymore!
 * 
 * @param bool $update Whether to force an update of the plugin.
 */
function idf_idc_delivery($update = false) {
    global $wp_filesystem;

    // Initialize the filesystem
    if (empty($wp_filesystem)) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $creds = request_filesystem_credentials(site_url() . '/wp-admin/', '', false, false, array());
        if (!WP_Filesystem($creds)) {
            //wp_die(__('ERROR: Unable to access the filesystem. Please check your file permissions.'));
        }
    }

    $plugins_path = plugin_dir_path(dirname(__FILE__));

    if (!file_exists($plugins_path . 'idcommerce') || $update) {
        $url = 'https://ignitiondeck.com/idf/idc_latest.zip';

        // Use wp_remote_get() to fetch the file
        $response = wp_remote_get($url, array(
            'timeout'   => 30, // Optional: specify a timeout in seconds
            'sslverify' => false, // Optional: verify SSL certificates (set to true for production)
        ));

        if (!is_wp_error($response)) {
            $idc = wp_remote_retrieve_body($response);

            if (!empty($idc)) {
                // Save the file to the specified path using WP_Filesystem
                $file_path = $plugins_path . 'idc_latest.zip';
                if ($wp_filesystem->put_contents($file_path, $idc, FS_CHMOD_FILE)) {
                    $idc_zip = new ZipArchive;
                    $idc_zip_res = $idc_zip->open($file_path);

                    if ($idc_zip_res === TRUE) {
                        $idc_zip->extractTo($plugins_path);
                        $idc_zip->close();
                        $wp_filesystem->delete($file_path); // Delete the zip file
                    }
                }
            }
        }
    }

    $path = $plugins_path . 'idcommerce/idcommerce.php';
    
    $current_time = wp_date('Y-m-d H:i:s'); // Get the current time in WordPress timezone
    wp_schedule_single_event(current_time(), 'idf_schedule_install', array($path));
}

/**
 * Deliver IDCF plugin.
 *
 * This function delivers the IDCF plugin. It triggers the delivery process for the
 * plugin, ensuring that the latest version is obtained and installed if necessary.
 * 
 * @todo Figure out what to do with the URL that's point to an old zip file that doesn't live there anymore!
 *
 * @param bool $update Whether to force an update of the plugin.
 */
function idf_idcf_delivery($update = false) {
    global $wp_filesystem;

    // Initialize the filesystem
    if (empty($wp_filesystem)) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $creds = request_filesystem_credentials(site_url() . '/wp-admin/', '', false, false, array());
        if (!WP_Filesystem($creds)) {
            //wp_die(__('ERROR: Unable to access the filesystem. Please check your file permissions.'));
        }
    }

    $plugins_path = plugin_dir_path(dirname(__FILE__));

    if (!file_exists($plugins_path . 'ignitiondeck-crowdfunding') || $update) {
        $url = 'https://ignitiondeck.com/idf/idcf_latest.zip';

        // Use wp_remote_get() to fetch the file
        $response = wp_remote_get($url, array(
            'timeout'   => 30, // Optional: specify a timeout in seconds
            'sslverify' => false, // Optional: verify SSL certificates (set to true for production)
        ));

        if (!is_wp_error($response)) {
            $idcf = wp_remote_retrieve_body($response);

            if (!empty($idcf)) {
                // Save the file to the specified path using WP_Filesystem
                $file_path = $plugins_path . 'idcf_latest.zip';
                if ($wp_filesystem->put_contents($file_path, $idcf, FS_CHMOD_FILE)) {
                    $idcf_zip = new ZipArchive;
                    $idcf_zip_res = $idcf_zip->open($file_path);

                    if ($idcf_zip_res === TRUE) {
                        $idcf_zip->extractTo($plugins_path);
                        $idcf_zip->close();
                        $wp_filesystem->delete($file_path); // Delete the zip file
                    }
                }
            }
        }
    }

    $path = $plugins_path . 'ignitiondeck-crowdfunding/ignitiondeck.php';

    wp_schedule_single_event(time() + 15, 'idf_schedule_install', array($path));
}

/**
 * Deliver FiveHundred theme.
 *
 * This function delivers the FiveHundred theme. It triggers the delivery process for the
 * theme, ensuring that the latest version is obtained and installed if necessary.
 * 
 * @todo Figure out what to do with the URL that's point to an old zip file that doesn't live there anymore!
 */
function idf_fh_delivery() {
    global $wp_filesystem;

    // Initialize the filesystem
    if (empty($wp_filesystem)) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $creds = request_filesystem_credentials(site_url() . '/wp-admin/', '', false, false, array());
        if (!WP_Filesystem($creds)) {
            //wp_die(__('ERROR: Unable to access the filesystem. Please check your file permissions.'));
        }
    }

    $themes_path = plugin_dir_path(dirname(dirname(__FILE__))) . 'themes/';

    // Check if the directory exists
    if (!file_exists($themes_path . 'fivehundred')) {
        $url = 'https://ignitiondeck.com/idf/fh_latest.zip';

        // Use wp_remote_get() to fetch the file
        $response = wp_remote_get($url, array(
            'timeout'   => 30, // Optional: specify a timeout in seconds
            'sslverify' => false, // Optional: verify SSL certificates (set to true for production)
        ));

        if (!is_wp_error($response)) {
            $fh = wp_remote_retrieve_body($response);

            if (!empty($fh)) {
                // Save the file to the specified path using WP_Filesystem
                $file_path = $themes_path . 'fh_latest.zip';
                if ($wp_filesystem->put_contents($file_path, $fh, FS_CHMOD_FILE)) {
                    $fh_zip = new ZipArchive;
                    $fh_zip_res = $fh_zip->open($file_path);

                    if ($fh_zip_res === TRUE) {
                        $fh_zip->extractTo($themes_path);
                        $fh_zip->close();
                        $wp_filesystem->delete($file_path); // Delete the zip file
                    }
                }
            }
        }
    }
}


/**
 * Get the list of IgnitionDeck extensions.
 *
 * This function retrieves the list of IgnitionDeck extensions from the IgnitionDeck
 * website. It uses cURL to fetch the data and applies filters to the result if
 * necessary.
 *
 * @todo Figure out what to do with link to old URL that doesn't exist anymore.
 * 
 * @param string|null $filter Optional. The filter to apply to the extension list.
 * @return array The list of IgnitionDeck extensions.
 */
function idf_extension_list($filter = null) {
    // Fetch the list of plugins (uncommented for completeness, if needed)
    // $plugins = get_plugins();
    // $plugin_array = array();
    // if (!empty($plugins)) {
    //     foreach ($plugins as $plugin) {
    //         $plugin_array[] = $plugin['basename'];
    //     }
    // }

    // Determine the protocol prefix based on SSL status
    $prefix = is_ssl() ? 'https' : 'http';
    $api = $prefix . '://ignitiondeck.com/id/?action=get_extensions';

    // Use wp_remote_get() to fetch the data
    $response = wp_remote_get($api, array(
        'timeout'   => 30, // Optional: specify a timeout in seconds
        'sslverify' => false, // Optional: verify SSL certificates (set to true for production)
    ));

    // if (is_wp_error($response)) {
    //     return array(); // Handle errors gracefully by returning an empty array or error message
    // }

    // Retrieve and decode the JSON response
    $json = wp_remote_retrieve_body($response);
    $data = json_decode($json);

	// if (!is_array($data) && !is_object($data)) {
    //     return array(); // Return an empty array if $data is not valid
    // }
    // Apply any filters
    $data = apply_filters('id_module_list', $data);

    // Filter the data based on provided criteria
    if (!empty($filter) && is_array($filter) && isset($filter['key']) && isset($filter['value'])) {
        $new_data = array();
        foreach ($data as $item) {
            if (isset($item->{$filter['key']}) && $item->{$filter['key']} == $filter['value']) {
                $new_data[] = $item;
            }
        }
        $data = $new_data;
    }

    return $data;
}

/**
 * Download a file from a given URL using allowed protocols.
 *
 * This function downloads a file from a given URL using either file_get_contents
 * or cURL, depending on the server configuration. If allow_url_fopen is enabled,
 * file_get_contents is used; otherwise, cURL is used.
 *
 * @param string $url The URL of the file to download.
 * @return string The contents of the downloaded file.
 */
function idf_get_file($url) {
    // Use wp_remote_get() to fetch the file content
    $response = wp_remote_get($url, array(
        'timeout'   => 30, // Optional: specify a timeout in seconds
        'sslverify' => false, // Optional: verify SSL certificates (set to true for production)
    ));

    // Check if the request was successful
    if (is_wp_error($response)) {
        return ''; // Handle errors gracefully by returning an empty string or error message
    }

    // Retrieve and return the body of the response
    return wp_remote_retrieve_body($response);
}

/**
 * Recursively remove a directory and its contents.
 *
 * This function removes the specified directory and all its contents, including
 * subdirectories and files. It iterates through the directory and its objects,
 * deleting files and recursively calling itself for subdirectories until the
 * entire directory structure is removed.
 *
 * @param string $dir The directory path to be removed.
 */
function rrmdir($dir) {
    global $wp_filesystem;

    // Ensure the WP_Filesystem class is loaded
    if (empty($wp_filesystem)) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();
    }

    if ($wp_filesystem->is_dir($dir)) {
        $objects = $wp_filesystem->dirlist($dir);
        foreach ($objects as $object) {
            $path = $dir . '/' . $object['name'];
            if ($object['type'] == 'dir') {
                rrmdir($path);
            } else {
                $wp_filesystem->delete($path);
            }
        }
        $wp_filesystem->rmdir($dir);
    }
}

/**
 * Generate a random password string.
 *
 * This function generates a random password string of the specified length using
 * alphanumeric characters.
 *
 * @param int $length The length of the password string to generate.
 * @return string The randomly generated password string.
 */
function idf_pw_gen($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    $charactersLength = strlen($characters);
    
    for ($i = 0; $i < $length; $i++) {
        $randomIndex = wp_rand(0, $charactersLength - 1);
        $randomString .= $characters[$randomIndex];
    }
    
    return $randomString;
}

/**
 * Retrieve sharing settings for the current project.
 *
 * This function retrieves the sharing settings for the current project if the
 * ID_Project class exists. It then returns the settings, or null if they are
 * empty.
 *
 * @return array|null The sharing settings for the current project, or null if
 *                   they are empty.
 */
function idf_sharing_settings() {
	if (class_exists('ID_Project')) {
		$settings = ID_Project::get_id_settings();
	}
	return (!empty($settings) ? $settings : null);
}

/**
 * Validate and format a URL string.
 *
 * This function validates a URL string and formats it properly. If the URL is
 * valid, it is returned. If it's just a domain name, the function appends 'http'
 * or 'https' based on the $http_secure parameter. If the URL is invalid, the
 * function returns false.
 *
 * @param string $url_string The string passed as URL to be formatted properly.
 * @param bool $http_secure Optional. If true, the return will be in 'https' format.
 * @return string|false The properly formatted URL or false if it's invalid.
 */
function id_validate_url($url_string, $http_secure = false) {
	// Using PHP 5+ version filter_var function if it exists
	if (function_exists('filter_var')) {
		$res = filter_var ($url_string, FILTER_VALIDATE_URL);
		// If it's a valid URL, return it
		if ($res) {
			if ($http_secure) {
				return preg_replace('/https?/', 'https', $res);
			} else {
				return $res;
			}
		} else {
			$match_res = preg_match('/((?:[\w]+\.)+)([a-zA-Z]{2,4})/', $url_string);
			// If we have a domain name coming, append http with it
			if ($match_res === 1) {
				// There are chances that there is a "//" already in the start of the $url_string, taking that into account
				$protocol = (($http_secure) ? 'https' : 'http');
				if (substr($url_string, 0, 2) == "//") {
					return $protocol.":".$url_string;
				} else {
					return $protocol."://".$url_string;
				}
			} else {
				// Not match as URL and domain, return false
				return false;
			}
		}
	} else {
		$match_res = preg_match('/((?:[\w]+\.)+)([a-zA-Z]{2,4})/', $url_string);
		// If we have a domain name coming, then check if it has http or doesn't have it
		if ($match_res === 1) {
			$match_http_str = preg_match('/https?:\/\//', $url_string);
			if ($match_http_str === 1) {
				// It has http/https in it, so simply return it, but checking argument if https is to be returned
				if ($http_secure) {
					return preg_replace('/https?/', 'https', $url_string);
				} else {
					return $url_string;
				}
			} else {
				// Doesn't have http/https in the URL, so append http
				$protocol = (($http_secure) ? 'https' : 'http');
				// There are chances that there is a "//" already in the start of the $url_string, taking that into account
				if (substr($url_string, 0, 2) == "//") {
					return $protocol.":".$url_string;
				} else {
					return $protocol."://".$url_string;
				}
			}
		} else {
			// Not match as URL and domain, return false
			return false;
		}
	}
}

/**
 * Handle video content.
 *
 * This function checks the provided video content and returns the appropriate
 * HTML representation. It first checks if the content contains an iframe, embed,
 * or object tag and returns the decoded and stripped content. If none of these
 * tags are found, it uses WordPress oEmbed to generate the HTML representation.
 *
 * @param string $video The video content to be handled.
 * @return string The HTML representation of the video content.
 */
function idf_handle_video($video) {
	if (empty($video)) {
		return;
	}
	$array = array('iframe', 'embed', 'object');
	foreach ($array as $accepted) {
		if (strpos($video, $accepted)) {
			return html_entity_decode(stripslashes($video));
		}
	}
	return wp_oembed_get($video);
}

/**
 * Get the client's IP address.
 *
 * This function retrieves the client's IP address from the server environment variables.
 *
 * @return string The client's IP address.
 */
function idf_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

/**
 * Get the query string prefix.
 *
 * This function retrieves the permalink structure and determines the appropriate
 * prefix to use for the query string. If the permalink structure is empty, the
 * function returns '&' as the prefix; otherwise, it returns '?'.
 *
 * @return string The query string prefix.
 */
function idf_get_querystring_prefix() {
	// Get permalink structure for '?' or '&'
	$prefix = '?';
	$permalink_structure = get_option('permalink_structure');
	if (empty($permalink_structure)) {
		$prefix = '&';
	}
	return $prefix;
}

/**
 * Determine the layout of an image based on its dimensions.
 *
 * This function takes the width and height of an image and determines its layout
 * based on these dimensions. If the width is greater than the height, the image
 * is considered to be in landscape orientation. If the width is less than the
 * height, the image is considered to be in portrait orientation. If the width and
 * height are equal, the image is considered to be square.
 *
 * @param int $width The width of the image.
 * @param int $height The height of the image.
 * @return string The layout of the image: 'landscape', 'portrait', or 'square'.
 */
function idf_image_layout_by_dimensions($width, $height) {
	if ($width > $height) {
		$image = "landscape";
	} else if ($width < $height) {
		$image = "portrait";
	} else {
		$image = "square";
	}
	return $image;
}

/**
 * Get the registration status.
 *
 * This function retrieves the registration status from the database and returns it.
 *
 * @return mixed The registration status, or null if not found.
 */
function idf_registered() {
	return get_option('idf_registered');
}

/**
 * Process registration data and update options.
 *
 * This function processes the registration data received via POST request and
 * updates the relevant options. It updates the 'idf_registered_post' option with
 * the received POST data, sets the 'idf_registered' option to 1, and updates the
 * 'id_account' option with the provided email. Additionally, it calls
 * idf_id_update_account() and idf_id_set_validation_type() functions. This function
 * is used as an AJAX callback for registration.
 * 
 * @todo Figure out if the idf_regsitered_post spelling mistake is causing a problem anywhere.
 *
 * @return void
 */
function idf_do_register() {
	// Check if the user has the required capability
	if (!current_user_can('manage_options')) {
		wp_send_json_error(__('You do not have sufficient permissions to perform this action.', 'memberdeck'));
		exit;
	}
	
	if (isset($_GET['wp_id_nonce'])) {
		check_admin_referer('wp_id_nonce', 'wp_id_nonce');
	}
	update_option('idf_regsitered_post', $_POST);
	//idf_deliver_plugins();
	update_option('idf_registered', 1);
	if (isset($_POST['Email'])) {
		$email = esc_attr($_POST['Email']);
		update_option('id_account', $email);
		idf_id_update_account($email);
		idf_id_set_validation_type();
	}
	exit;
}

add_action('wp_ajax_idf_do_register', 'idf_do_register');

/**
 * Reset account options.
 *
 * This function resets the 'idf_registered' and 'id_account' options by deleting
 * them from the database. It is used as an AJAX callback for account reset.
 */
function idf_reset_account() {
	$options_array = array();
	array_push($options_array, 'idf_registered', 'id_account');
	foreach ($options_array as $k=>$v) {
		delete_option($v);
	}
	exit;
}

add_action('wp_ajax_idf_reset_account', 'idf_reset_account');

/**
 * Activate a theme based on the provided slug.
 *
 * This function activates a theme based on the provided slug received via POST
 * request, if the current user has the capability to manage options. It replaces
 * '500' with 'fivehundred' in the slug, and then switches the theme using
 * switch_theme(). It is used as an AJAX callback for theme activation.
 *
 * @param string $slug The theme slug to be activated.
 * @return void
 */
function idf_activate_theme() {
	if (isset($_GET['wp_id_nonce'])) {
		check_admin_referer('wp_id_nonce', 'wp_id_nonce');
	}
	if (isset($_POST['theme']) && current_user_can('manage_options')) {
		$slug = esc_attr($_POST['theme']);
		$slug = str_replace('500', 'fivehundred', $slug);
		switch_theme($slug);
		echo 1;
	}
	exit;
}

add_action('wp_ajax_idf_activate_theme', 'idf_activate_theme');

/**
 * Activate a plugin based on the provided extension.
 *
 * This function activates a plugin based on the provided extension received via POST
 * request, if the current user has the capability to manage options. It activates
 * the plugin using the activate_plugin() function and echoes 1 upon successful
 * activation.
 *
 * @param string $extension The plugin extension to be activated.
 * @return void
 */
function idf_activate_extension() {
	if (isset($_GET['wp_id_nonce'])) {
		check_admin_referer('wp_id_nonce', 'wp_id_nonce');
	}
	if (isset($_POST['extension']) && current_user_can('manage_options')) {
		$extension = $_POST['extension'];
		if (!empty($extension)) {
			$plugin_path = dirname(IDF_PATH).'/'.$extension.'/'.$extension.'.php';
			activate_plugin($plugin_path);
			echo 1;
		}
	}
	exit;
}

add_action('wp_ajax_idf_activate_extension', 'idf_activate_extension');

/**
 * Check if the provided license key is in the correct format.
 *
 * This function checks if the provided license key is in the correct format. It first
 * checks if the key is in JSON format and if it contains a 'valid' property. Then it
 * verifies that the key has exactly two occurrences of double quotes, starts and ends
 * with a double quote, and contains a numeric license level after removing the quotes.
 *
 * @param string $key The license key to be checked.
 * @return bool Whether the license key is in the correct format.
 */
function is_license_format( $key ){

	$formatted = true;
	// var_dump($key);
	if( is_object( json_decode($key) ) ){ //check if key is in json format
		$jsnKy = json_decode($key);
		if( !isset( $jsnKy->valid ) ) $formatted = false;

		return $formatted;
	}

	if( !$formatted || substr_count( $key, '"' ) <> 2 ) $formatted = false; //check if it has 2 occurences of "
	if( !$formatted || substr( $key, 0, 1 ) <> '"' ) $formatted = false; //check if it starts with "
	if( !$formatted || substr( $key, strlen($key)-1, 1 ) <> '"' ) $formatted = false; //check if it ends with "

	$license_level = preg_replace('/"/', "", $key ); //remove " and check if it is numeric
	if( !$formatted || !is_numeric( $license_level ) ) $formatted = false;

	return $formatted;
}

?>