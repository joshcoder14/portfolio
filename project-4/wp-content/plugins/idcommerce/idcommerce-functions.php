<?php
global $crowdfunding;

function fd_customer_id() {
	return ID_Member::fd_customer_id();
}

function fd_customer_id_ajax($user_id) {
	return ID_Member::fd_customer_id_ajax($user_id);
}

function customer_id() {
	return ID_Member::customer_id();
}

function customer_id_ajax($user_id) {
	return ID_Member::customer_id_ajax($user_id);
}

function authnet_customer_id() {
	return ID_Member::authnet_customer_id();
}

function authorizenet_customer_id_ajax($user_id) {
	return ID_Member::authnet_customer_id_ajax($user_id);
}

function idc_stripe_sk($product_id = null) {
	$settings = get_option('memberdeck_gateways');
	if (!empty($settings)) {
		if (is_array($settings)) {
			$test = $settings['test'];
			if ($test) {
				$sk = $settings['tsk'];
			}
			else {
				$sk = $settings['sk'];
			}
		}
	}
	return (!empty($sk) ? $sk : null);
}

function idc_stripe_currency() {
	$gateways = get_option('memberdeck_gateways');
	$stripe_currency = 'USD';
	if (!empty($gateways) && is_array($gateways)) {
		$stripe_currency = $gateways['stripe_currency'];
	}
	return $stripe_currency;
}

function idc_stripe_recurring_types() {
	$recurring_types = array(
		"weekly" => "week",
		"monthly" => "month",
		"annually" => "year"
	);
	return $recurring_types;
}

function idc_stripe_standardize_plan_name($text) {
	return strtolower(preg_replace('/[\s:]+/', '-', str_replace(html_entity_decode("&nbsp;"), ' ', $text)));
}

function md_get_did() {
	$d_page = 0;
	$dash = get_option('md_dash_settings');
	$dash = maybe_unserialize($dash);
	if (!empty($dash)) {
		if (isset($dash['durl'])) {
			$d_page = absint($dash['durl']);
		}
	}
	return $d_page;
}

function md_get_durl($https = false) {
	global $permalink_structure;
	$durl = home_url('/dashboard/');
	$d_page = md_get_did();
	$durl = get_permalink($d_page);
	if (!$https) {
		$https = md_https();
	}
	if (!empty($permalink_structure)) {
		if (substr($durl, -1) !== '/') {
			$durl = $durl.'/';
		}
	}
	if ($https) {
		$durl = str_replace('http:', 'https:', $durl);
	}
	return $durl;
}

function is_idc_checkout() {
	global $post;
	if (!empty($post->post_content)) {
		$content = $post->post_content;
		if (has_shortcode($content, 'idc_checkout') || has_shortcode($content, 'memberdeck_checkout') || isset($_GET['mdid_checkout'])) {
			return true;
		}
	}
	return false;
}

function is_module_active($module) {
	return IDC_Modules::is_module_active($module);
}

add_action('init', 'idc_product_status_check');

function idc_product_status_check() {
	add_action('doing_idc_checkout', 'idc_checkout_status');
}

function idc_checkout_status($product_id) {
	$product = ID_Member_Level::get_level($product_id);
	if (!empty($product) && $product->product_status == 'disabled') {
		add_filter('idc_checkout_template', 'idc_return_404');
	}
}

function idc_return_404($path) {
	return get_404_template();
}

function md_https() {
	$https = 0;
	$settings = get_option('memberdeck_gateways');
	if (!empty($settings)) {
		if (is_array($settings) && !empty($settings['https'])) {
			$https = $settings['https'];
		}
	}
	return $https;
}

function idc_currency_to_symbol($currency_code) {
	// Converting shortcode to symbol and return, but first getting the array for code to symbol from file
	$currency_array = json_decode(file_get_contents(IDC_PATH . 'inc/currencies_global.json' ));
	// since currency array is for paypal only, we add some additional
	$btc = new stdClass();
	$btc->Currency_Code = 'BTC';
	$btc->Symbol = '&#3647;';
	$currency_array[] = $btc;
	// now that we have the array, we loop through it and compare the code and return the required symbol if code is matched
	foreach ($currency_array as $currency) {
		if ($currency->Currency_Code == $currency_code) {
			return $currency->Symbol;
		}
	}
	return '$';
}

add_action('wp', 'md_force_https', 1);

function md_force_https() {
	if (md_https()) {
		if (!isset($_GET['memberdeck_notify'])) {
			if (is_idc_checkout()) {
				$using_ssl = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' || $_SERVER['SERVER_PORT'] == 443;
				if (!$using_ssl) {
					header('Location: https://' . $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']);
				}
			}
		}
	}
}

add_action('init', 'idc_init_checks');

function idc_init_checks() {
	if (isset($_GET['idc_renew'])) {
		add_filter('the_content', 'idc_renew');
	}
	if (isset($_GET['idc_orders'])) {
		add_filter('the_content', 'idc_orders_list');
	}
}

function instant_checkout() {
	global $first_data;
	$instant_checkout = false;
	if (is_user_logged_in()) {
		if (is_multisite()) {
			require (ABSPATH . WPINC . '/pluggable.php');
		}
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
		$instant_checkout = false;
		$settings = get_option('memberdeck_gateways');
		if (!empty($settings)) {
			if (!is_array($settings)) {
				$settings = unserialize($settings);
			}
			if (isset($first_data) && $first_data) {
				$efd = $settings['efd'];
			}
			if (isset($settings['es']) && $settings['es'] == '1') {
				$customer_id = get_user_meta($user_id, 'stripe_customer_id', true);
				if (!empty($customer_id)) {
					$instant_checkout = get_user_meta($user_id, 'instant_checkout', true);
				}
			}
			else if (isset($efd) && $efd == '1') {
				$fd_card_details = fd_customer_id();
				if (!empty($fd_card_details)) {
					$fd_token = $fd_card_details['fd_token'];
					if (!empty($fd_token)) {
						$instant_checkout = get_user_meta($user_id, 'instant_checkout', true);
					}
				}
			}
			else if (isset($settings['eauthnet']) && $settings['eauthnet'] == '1') {
				$authnet_customer_ids = authnet_customer_id();
				if (!empty($authnet_customer_ids)) {
					$authorizenet_payment_profile_id = $authnet_customer_ids['authorizenet_payment_profile_id'];
					$authorizenet_profile_id = $authnet_customer_ids['authorizenet_profile_id'];
					if (!empty($authorizenet_profile_id) && !empty($authorizenet_payment_profile_id)) {
						$instant_checkout = get_user_meta($user_id, 'instant_checkout', true);
					}
				}
			}
			$instant_checkout = apply_filters('idc_instant_checkout', $instant_checkout, $user_id, $settings);
		}
	}
	return $instant_checkout;
}

function allow_instant_checkout() {
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;
		
	$settings = get_option('memberdeck_gateways', true);
	$es = (isset($settings['es']) ? $settings['es'] : 0);
	$efd = (isset($settings['efd']) ? $settings['efd'] : 0);
	$eauthnet = (isset($settings['eauthnet']) ? $settings['eauthnet'] : '0');
	
	if ($es == 1) {
		$customer_id = get_user_meta($user_id, 'stripe_customer_id', true);
		if (empty($customer_id)) {
			if (isset($user_id)) {
				$member = new ID_Member();
				$match = $member->match_user($user_id);
				if (isset($match->data)) {
					$data = unserialize($match->data);
					if (is_array($data)) {
						foreach ($data as $item) {
							if (is_array($item)) {
								foreach ($item as $k=>$v) {
									if ($k == 'customer_id') {
										$customer_id = $v;
										break 2;
									}
								}
							}	
						}
					}
				}
			}
		}
	}
	else if ($efd == 1) {
		if (isset($user_id)) {
			$customer_id = get_user_meta($user_id, 'fd_card_details', true);
		}
	}
	else if ($eauthnet == 1) {
		if (isset($user_id)) {
			$customer_id = authnet_customer_id();
		}
	}
	
	if (isset($customer_id) && !empty($customer_id)) {
		return true;
	} else {
		return false;
	}
}

function md_credits() {
	return ID_Member::md_credits();
}

function is_md_network_activated() {
	// check for network activation
	$active_plugins = get_site_option( 'active_sitewide_plugins');
	if (isset($active_plugins['memberdeck/memberdeck.php'])) {
		if (is_multisite()) {
			return true;
		}
	}
	return false;
}

function md_wpdb_prefix($blog_id = null) {
	global $wpdb;
	if (!empty($blog_id) && is_md_network_activated()) {
		// set prefix for each blog install on network activation
		if ($blog_id == 1) {
			// The first blog doesn't use a prefix of 1, so use base prefix instead
			$prefix = $wpdb->base_prefix;
		}
		else {
			$prefix = $wpdb->base_prefix.$blog_id.'_';
		}
	}
	else if (!empty($blog_id)) {
		// set prefix for each intall on standard ms activation
		if ($blog_id == 1) {
			$prefix = $wpdb->prefix;
		}
		else {
			$prefix = $wpdb->prefix.$blog_id.'_';
		}
	}
	else {
		// we aren't in ms, so use standard prefix
		$prefix = $wpdb->prefix;
	}
	return $prefix;
}

function md_user_prefix() {
	global $wpdb;
	if (is_multisite()) {
		$prefix = $wpdb->base_prefix;
	}
	else {
		$prefix = $wpdb->prefix;
	}
	return $prefix;
}

function memberdeck_pp_currency() {
	$settings = get_option('memberdeck_gateways');
	$currency = array('code' => 'USD', 'symbol' => '$');
	if (!empty($settings)) {
		if (is_array($settings)) {
			$pp_currency = (isset($settings['pp_currency']) ? $settings['pp_currency'] : 'USD');
			$pp_symbol = (isset($settings['pp_symbol']) ? $settings['pp_symbol'] : '$');
			$currency = array('code' => $pp_currency,
				'symbol' => $pp_symbol);
		}
	}
	return $currency;
}

function memberdeck_auto_page($level_id, $level_name) {
	$page = array(
    	'menu_order' => 100,
    	'comment_status' => 'closed',
    	'ping_status' => 'closed',
    	'post_name' => $level_name.'-checkout',
    	'post_status' => 'draft',
    	'post_title' => $level_name.' '.__('Checkout', 'memberdeck'),
    	'post_type' => 'page',
    	'post_content' => '[idc_checkout product="'.$level_id.'"]');
	$get_page = get_page_by_title($level_name.' '.__('Checkout', 'memberdeck'));
	if (empty($get_page)) {
    	$post_in = wp_insert_post($page);
	    if (isset($wp_error)) {
	    	echo $wp_error;
	    }
	    else {
	    	return $post_in;
	    }
    }
    else {
    	return $get_page->ID;
    }
}

function idmember_pw_gen($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

function idmember_e_date_format($e_date) {
	$etime = strtotime(date($e_date));
	if (empty($etime)) {
		// does not expire
		$days_left = null;
	}
	$now = strtotime('now');
	$dif = $etime - $now;
	if ($dif > 0) {
		$days_left = floor($dif / 60 / 60 / 24);
	}
	else {
		// expired
		$days_left = 0;
	}
	return $days_left;
}

add_action('posts_selection', 'move_to_protect');

function move_to_protect() {
	if (is_category()) {
		add_filter('the_content', 'idmember_protect_category');
	}
	else if (is_tax()) {
		add_filter('the_content', 'idmember_protect_category');
	}
	else if (is_archive()) {
		//echo 'archive';
	}
	else if (is_singular()) {
		$theme_name = wp_get_theme();
		$textdomain = $theme_name->get('Template');
		if ($textdomain == 'fivehundred' || ($theme_name->get('Author') == 'Virtuous Giant' || $theme_name->get('Author') == 'IgnitionDeck')) {
			md_fh_protection_check();
		}
		else {
			add_filter('the_content', 'idmember_protect_singular', 100);
		}
	}
	else {
		//echo 'else';
	}
}

function md_fh_protection_check() {
	global $post;
	if (is_user_logged_in()) {
		if (is_multisite()) {
			require (ABSPATH . WPINC . '/pluggable.php');
			$current_user = wp_get_current_user();
			$md_user_levels = null;
			if (!empty($current_user)) {
				$user_id = $current_user->ID;
				$md_user = ID_Member::user_levels($user_id);
				if (!empty($md_user)) {
					$md_user_levels = unserialize($md_user->access_level);
				}
			}
		}
		else {
			$md_user_levels = ID_Member::get_user_levels();
		}
	}

	// Check if the Post/Project is under a protected Category/tag, if yes, then we need to test if user have the products
	// that allows viewing of this Post/Projects
	if (isset($post->ID)) {
		if (idc_in_protected_category($post->ID)) {
			// See if user is allowed to view that category
			if ( ! idc_user_allowed_term_access($post->ID, '', 'category', (isset($md_user_levels) ? $md_user_levels : null)) ) {
				echo '<script>location.href="'.home_url().'";</script>';
			}
		}
		else if (idc_in_protected_tag($post->ID)) {
			// See if user is allowed to view that tag
			if ( ! idc_user_allowed_term_access($post->ID, '', 'tag', (isset($md_user_levels) ? $md_user_levels : null)) ) {
				echo '<script>location.href="'.home_url().'";</script>';
			}
		}
	}

	if (isset($post->ID)) {
		$post_id = $post->ID;
		$protected = get_post_meta($post_id, 'memberdeck_protected_posts', true);
		if (!current_user_can('manage_options')) {
			//echo 'not admin';
			if ($protected) {
				//echo 'protected';
				$login_url = site_url('/wp-login.php');
				if (!empty($md_user_levels)) {
					//echo 'they have levels';
					$access = unserialize($protected);
					$pass = false;
					foreach ($md_user_levels as $access_level) {
						if (in_array($access_level, $access)) {
							$pass = true;
							break;
						}
					}
					if (!$pass) {
						//echo 'does not match';
						echo '<script>location.href="'.home_url().'";</script>';
					}
					
				}
				else {
					//echo 'no levels';
					echo '<script>location.href="'.home_url().'";</script>';
				}
			}
			else {
				//echo 'not protected';
			}
		}
		else {
			//echo 'is admin';
		}
	}
	else {
		//echo 'no post id';
	}
}

/**
 * Function for returning the gateways that do allow subscriptions from the list of active gateways
 * Return: the gateways that are active and support subscriptions
 */
function idc_combined_purchase_allowed($gateway_settings = null) {
	// only active for Stripe atm #devnote can we better name vars here?
	if (empty($gateway_settings)) {
		$gateway_settings = get_option('memberdeck_gateways');
	}
	$gateways = array();
	// Now check which gateways are active that supports subscription
	// First credit card gateways
	if (isset($gateway_settings['es']) && $gateway_settings['es'] == '1') {
		$gateways['cc'] = 1;
	}
	else if (isset($gateway_settings['efd']) && $gateway_settings['efd'] == '1') {
		// $gateways['cc'] = 1;
	}
	else if (isset($gateway_settings['eauthnet']) && $gateway_settings['eauthnet'] == '1') {
		// $gateways['cc'] = 1;
	}
	// Coinbase supports subscriptions
	if (isset($gateway_settings['ecb']) && $gateway_settings['ecb'] == '1') {
		// $gateways['cb'] = 1;
	}
	// PayPal Adaptive supports subscriptions
	if (isset($gateway_settings['eppadap']) && $gateway_settings['eppadap'] == '1') {
		// $gateways['pp'] = 1;
	}

	return $gateways;
}

function memberdeck_profile_check() {
	if (is_user_logged_in()) {
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
		$nicename = $current_user->display_name;
		$user_firstname = $current_user->user_firstname;
		$user_lastname = $current_user->user_lastname;
		$email = $current_user->user_email;
		$customer_id = customer_id();
		$instant_checkout = instant_checkout();
		if (isset($_GET['edit-profile']) && $_GET['edit-profile'] == $user_id) {
			if (isset($_POST['edit-profile-submit'])) {
				$idc_avatar = sanitize_text_field($_POST['idc_avatar']);
				$user_firstname = sanitize_text_field($_POST['first-name']);
				$user_lastname = sanitize_text_field($_POST['last-name']);
				$posted_email = sanitize_email($_POST['email']);
				$nicename = sanitize_text_field($_POST['nicename']);
				$location = sanitize_text_field($_POST['location']);
				$url = sanitize_text_field($_POST['url']);
				$description = wp_kses_post($_POST['description']);
				$url = sanitize_text_field($_POST['url']);
				$twitter = sanitize_text_field($_POST['twitter']);
				$facebook = sanitize_text_field($_POST['facebook']);
				$google = sanitize_text_field($_POST['google']);
				if (isset($_POST['instant_checkout'])) {
					$instant_checkout = absint($_POST['instant_checkout']);
				}
				else {
					$instant_checkout = 0;
				}

				$pw = sanitize_text_field($_POST['pw']);
				$cpw = sanitize_text_field($_POST['cpw']);

				// Check that email doesn't already exists if it's modified
				if ($email != $posted_email) {
					// Email is changed, so check that it doesn't exists in db already
					$check_email = get_user_by('email', $posted_email);
					if (!empty($check_email)) {
						// That means user with this email already exists, give an error
						if (function_exists('idf_get_querystring_prefix')) {
							$prefix = idf_get_querystring_prefix();
						} else {
							$prefix = '?';
						}
						$dash_url = md_get_durl(is_ssl());
						echo '<script>location.href="'.$dash_url.$prefix.'edit-profile='.$user_id.'&error_msg='.__('User already exists with email '.urlencode($posted_email), 'memberdeck').'";</script>';
						$posted_email = $email;
						// exit();
					}
				}

				if ($pw == $cpw) {
					if (!empty($pw)) {
						wp_update_user(array(
						'ID' => $user_id,
						'user_email' => $posted_email,
						'user_pass' => $pw,
						'first_name' => $user_firstname,
						'last_name' => $user_lastname,
						'display_name' => $nicename,
						'description' => $description,
						'user_url' => $url));
					}
					else {
						wp_update_user(array(
						'ID' => $user_id,
						'user_email' => $posted_email,
						'first_name' => $user_firstname,
						'last_name' => $user_lastname,
						'display_name' => $nicename,
						'description' => $description,
						'user_url' => $url));
					}
				}
				update_user_meta($user_id, 'idc_avatar', $idc_avatar);
				update_user_meta($user_id, 'instant_checkout', $instant_checkout);
				update_user_meta($user_id, 'location', $location);
				update_user_meta($user_id, 'twitter', $twitter);
				update_user_meta($user_id, 'facebook', $facebook);
				update_user_meta($user_id, 'google', $google);
			}
			add_filter('the_content', 'memberdeck_profile_form');
		}
		else if (isset($_GET['edit-profile'])) {
			echo '<script>location.href="?edit-profile='.$user_id.'";</script>';
		}
	}
}

add_action('init', 'memberdeck_profile_check');




add_action('init', 'md_shipping_on_profile');
function md_shipping_on_profile() {
	$crm_settings = get_option('crm_settings');
	if (!empty($crm_settings)) {
		$shipping_info = $crm_settings['shipping_info'];
		if (isset($shipping_info) && $shipping_info == '1') {
			add_action('md_profile_extrafields', 'md_shipping_info');
		}
	}
}

function md_shipping_info() {
	$current_user = wp_get_current_user();
	$user_id = $current_user->ID;

	$shipping_info = get_user_meta($user_id, 'md_shipping_info', true);

	$countries = file_get_contents(IDC_PATH . '/inc/countries_list.json');
	if (!empty($countries)) {
		$countries = json_decode($countries);
	}

	if (isset($_POST['edit-profile-submit'])) {
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;

		$address = esc_attr($_POST['address']);
		$address_two = esc_attr($_POST['address_two']);
		$city = esc_attr($_POST['city']);
		$state = esc_attr($_POST['state']);
		$zip = esc_attr($_POST['zip']);
		$country = esc_attr($_POST['country']);

		$shipping_info = array(
			'address' => $address,
			'address_two' => $address_two,
			'city' => $city,
			'state' => $state,
			'zip' => $zip,
			'country' => $country
			);

		update_user_meta($user_id, 'md_shipping_info', $shipping_info);
	}
	include 'templates/_shippingInfo.php';
}

function idmember_login_redirect($user_login, $user) {
	// not needed yet - in wp login form
}

//add_action('wp_login', 'idmember_login_redirect', 10, 2);


function md_currency_symbol($currency) {
	if (empty($currency)) {
		$currency = 'USD';
	}

	// Loading a file for currency conversion to symbol
	$currency_json = json_decode(file_get_contents(IDC_PATH . "inc/currencies_global.json"));
	// Adding bitcoin as well to the array
	$btc = new stdClass();
	$btc->Currency_Code = 'BTC';
	$btc->Symbol = '&#3647;';
	$btc->Country_and_Currency = '';
	$currency_json[] = $btc;

	// now that we have the array, we loop through it and compare the code and return the required symbol if code is matched
	$ccode = '';
	foreach ($currency_json as $currency_data) {
		if ($currency_data->Currency_Code == $currency) {
			$ccode = $currency_data->Symbol;
		}
	}
	return $ccode;
}

/**
 * Function to create a plan for Stripe
 */
function idc_create_stripe_plan($product_id, $user_id, $args = null) {
	global $stripe_api_version;
	// Check if we have something in arguments, if not, get product parameters
	if (empty($args)) {
		// For translating IDC recurring type to Stripe
		$recurring_types = array(
			"weekly" => "week",
			"monthly" => "month",
			"annual" => "year"
		);
		// Getting memberdeck gateways for Stripe currency
		$gateways = get_option('memberdeck_gateways');
		$stripe_currency = 'USD';
		if (!empty($gateways)) {
			if (is_array($gateways)) {
				$stripe_currency = $gateways['stripe_currency'];
			}
		}

		$level = ID_Member_Level::get_level($product_id);
		// Setting default arguments
		$args['price'] = apply_filters('idc_stripe_plan_creation_price', $level->level_price, $product_id);
		$args['interval'] = apply_filters('idc_stripe_plan_creation_interval', $recurring_types[$level->recurring_type], $product_id);
		$args['name'] = apply_filters('idc_stripe_plan_creation_name', $level->level_name, $product_id);
		$args['currency'] = apply_filters('idc_stripe_plan_creation_currency', $stripe_currency, $product_id);
		$level->level_name = str_replace(html_entity_decode("&nbsp;"), '-', $level->level_name);
		$args['id'] = apply_filters('idc_stripe_plan_creation_id', strtolower(str_replace('/[\s:]+/', '-', $level->level_name)), $product_id);
	}
	$args = apply_filters('idc_stripe_plan_creation_args', $args);

	// validating Stripe connect ids
	$sc_validation = true;
	$settings = maybe_unserialize(get_option('memberdeck_gateways'));
	if (!empty($settings)) {
		$sk = $settings['sk'];
		if ($settings['test']) {
			$sk = $settings['tsk'];
		}
		if ($settings['esc']) {
			$sc_validation = validate_sc_params($user_id);
			if ($sc_validation) {
				// Getting Stripe credentials
				$params = get_sc_params($user_id);
				if (!empty($params)) {
					$sk = $params->access_token;
				}
			}
		}
		else {
			$sc_validation = true;
		}
	}
	
	if ($sc_validation && !empty($sk)) {
		if (!class_exists('Stripe')) {
			require_once 'lib/stripe-php-4.2.0/init.php';
		}
		$api_key = $sk;
		try {
			\Stripe\Stripe::setApiVersion($stripe_api_version);
			\Stripe\Stripe::setApiKey($api_key);
			$plan = \Stripe\Plan::create(array(
				"amount" => $args['price'] * 100,
				"interval" => $args['interval'],
				"name" => $args['name'],
				"currency" => $args['currency'],
				"id" => $args['id']
			));
			$response_array = array(
				"response" => "success",
				"plan" => $plan
			);
		}
		catch (Exception $e) {
			// plan not created
			$message = $e->jsonBody['error']['message'];
			$response_array = array(
				"response" => "error",
				"message" => $message
			);
		}
		return $response_array;
	}
	else {
		return array(
			"response" => "error",
			"message" => __("User not connected to Stripe", "memberdeck")
		);
	}
}

/**
 * Function to retrieve a Stripe plan according to ID
 */
function idc_retrieve_stripe_plan($plan_id, $user_id) {
	global $stripe_api_version;
	$gateways = maybe_unserialize(get_option('memberdeck_gateways'));
	if (empty($gateways)) {
		return;
	}
	if ($gateways['esc']) {
		$params = get_sc_params($user_id);
		$api_key = (isset($params->access_token) ? $params->access_token : null);
	}
	else {
		$api_key = ($gateways['test'] ? $gateways['tsk'] : $gateways['sk']);
	}
	if (!empty($api_key)) {
		if (!class_exists('Stripe')) {
			require_once 'lib/stripe-php-4.2.0/init.php';
		}
		try {
			\Stripe\Stripe::setApiKey($api_key);
			\Stripe\Stripe::setApiVersion($stripe_api_version);
			$plan = \Stripe\Plan::retrieve($plan_id);
			$response_array = array(
				"response" => "success",
				"message_code" => "plan_found",
				"plan" => $plan
			);
		}
		catch (Exception $e) {
			// plan not created
			$message = $e->jsonBody['error']['message'];
			$response_array = array(
				"response" => "error",
				"message_code" => "no_plan",
				"message" => $message
			);
		}
		return $response_array;
	}
	else {
		return array(
			"response" => "error",
			"message_code" => "user_not_connected",
			"message" => __("User not connected to Stripe", "memberdeck")
		);
	}
}

/**
 * Function to check if a post is allowed to a member
 */
function idc_user_allowed_post_access($post_id, $user_id, $md_user_levels = null) {
	if (empty($md_user_levels)) {
		$md_user = ID_Member::user_levels($user_id);
		if (!empty($md_user)) {
			// User has levels, so we need to check his access if Project is protected.
			$md_user_levels = unserialize($md_user->access_level);
		}
	}

	$allowed = true;
	if (isset($post_id)) {
		$protected = get_post_meta($post_id, 'memberdeck_protected_posts', true);
		if (!current_user_can('manage_options')) {
			// echo 'not admin<Br>';
			if ($protected) {
				//echo 'protected';
				$login_url = site_url('/wp-login.php');
				if (!empty($md_user_levels)) {
					// echo 'they have levels';
					$access = unserialize($protected);
					// echo "<pre>unserialize(protected): "; print_r($access); echo "</pre>";
					$pass = false;
					foreach ($md_user_levels as $access_level) {
						if (in_array($access_level, $access)) {
							$pass = true;
							break;
						}
					}
					// echo "<pre>pass: "; print_r($pass); echo "</pre>";
					if (!$pass) {
						$allowed = false;
						//echo 'does not match';
						$message_code = "no_member_level_match";
						// include_once 'templates/_protectedPage.php';
					}
					else {
						$message_code = "passed";
					}
					
				}
				else {
					//echo 'no levels';
					$allowed = false;
					$message_code = "no_member_level";
					// include_once 'templates/_protectedPage.php';
				}
			}
			else {
				//echo 'not protected';
				$message_code = "not_protected";
			}
		}
		else {
			//echo 'is admin';
			$message_code = "is_admin";
		}
	}
	else {
		//echo 'no post id';
		$message_code = "no_post_id";
	}

	return array($allowed, $message_code);
}

/**
 * To check whether the categories in a post if any protected allow the $user_id access to Post ($post_id)
 * @param int 		 $post_id 
 * @param int 		 $user_id 
 * @param string	 $tag_or_cat	 Will be either 'category' or 'tag' to check function for that
 * @param array|null $md_user_levels 
 * @return boolean	 true|false
 */
function idc_user_allowed_term_access($post_id, $user_id, $tag_or_cat, $md_user_levels = null) {
	if (!is_user_logged_in()) {
		return false;
	}

	if (empty($md_user_levels)) {
		$md_user = ID_Member::user_levels($user_id);
		if (!empty($md_user)) {
			// User has levels, so we need to check his access if Project is protected.
			$md_user_levels = unserialize($md_user->access_level);
		}
	}

	// Getting all the tags/categories of the current post
	if ($tag_or_cat == 'category') {
		$post_terms = wp_get_post_categories(!empty($post_id) ? $post_id : null);
		$project_cats = get_terms('project_category', array('fields' => 'ids'));
		if (! is_wp_error($project_cats)) {
			$post_terms = array_merge($post_terms, $project_cats);
		}
	} else if ($tag_or_cat == 'tag') {
		$post_terms = wp_get_post_tags(!empty($post_id) ? $post_id : null);
	}
	$access_allowed = true;
	$term_array = array();
	$i = 0;
	if (is_array($post_terms)) {
		// Looping the terms, check that user have those levels allowed by that category, if any one of the terms pass the case,
		// then user is allowed to view that Post
		foreach ($post_terms as $term_id) {
			// In case of Tag, the $term_id is an object
			if ($tag_or_cat == 'tag') {
				$tag = $term_id;
				$term_id = $tag->term_id;
			}
			$term_protected = get_option('protect_term_'.$term_id, false);

			// If term/category is protected and current post have that term/category
			if ($term_protected) {
				$term_array[$i]['term_id'] = $term_id;
				$allowed = get_option('term_'.$term_id.'_allowed_levels');

				if (isset($allowed)) {
					$array = unserialize($allowed);
                    if ($array == null) {
                        $array = array();
                    }
					// If we have an array of Product Ids attached with that Term/Category, loop through them, and if User have any assign to
					// him, then he can access the Post/Project
					if (!empty($array)) {
						$term_array[$i]['terms'] = $array;
						foreach ($term_array as $array) {
							foreach ($md_user_levels as $level) {
								if (in_array($level, $array['terms'])) {
									$pass = true;
									$access_allowed = true;
									break;
								}
								else {
									$fail = true;
								}
							}
							if (!isset($pass)) {
								continue;
							}
						}
						if (!isset($pass)) {
							// user doesn't own any required level
							$access_allowed = false;
						}
					}
					else {
						// no levels will grant access
						$access_allowed = false;
					}
				}
				else {
					// user doesn't own any levels
					$access_allowed = false;
				}
			}
			$i++;
		}
	}
	return $access_allowed;
}

/**
 * To check if the post is in Protected category
 * @param int $post_id 
 * @return boolean
 */
function idc_in_protected_category($post_id) {
	// Getting post categories
	$post_cats = idf_get_object('idc_in_protected_category-'.$post_id);
	if (!isset($post_cats)) {
		$post_cats = wp_get_post_categories(!empty($post_id) ? $post_id : null);
		do_action('idf_cache_object', 'idc_in_protected_category-'.$post_id, $post_cats);
	}
	$project_cats = idf_get_object('idc_in_protected_category-project_category-'.$post_id);
	if (!isset($project_cats)) {
		$project_cats = wp_get_post_terms(!empty($post_id) ? $post_id : null, 'project_category', array( 'fields' => 'ids' ) );
		do_action('idf_cache_object', 'idc_in_protected_category-project_category-'.$post_id, $project_cats);
	}
	if (! is_wp_error($project_cats)) {
		// #devnote simplify this
		if (empty($project_cats)) {
			$project_cats = array();
		}
		if (empty($post_cats)) {
			$post_cats = array();
		}
		$post_cats = array_merge($post_cats, $project_cats);
	}

	// Looping those and check if any of them is protected
	$protected_category = false;
	foreach ($post_cats as $cat_id) {
		$term_protected = get_option('protect_term_'.$cat_id, false);
		if ($term_protected) {
			$protected_category = true;
			break;
		}
	}

	return $protected_category;
}

/**
 * To check if the post is in Protected Tag
 * @param int $post_id 
 * @return boolean
 */
function idc_in_protected_tag($post_id) {
	// Getting post tags
	$post_tags = idf_get_object('idc_in_protected_tag-'.$post_id);
	if (!isset($post_tags)) {
		$post_tags = wp_get_post_tags(!empty($post_id) ? $post_id : null);
		do_action('idf_cache_object', 'idc_in_protected_tag-'.$post_id, $post_tags);
	}
	// Looping those and check if any of them is protected
	$protected_tag = false;
	if (!empty($post_tags)) {
		foreach ($post_tags as $tag) {
			$term_protected = get_option('protect_term_'.$tag->term_id, false);
			if ($term_protected) {
				$protected_tag = true;
				break;
			}
		}
	}

	return $protected_tag;
}

function idmember_purchase_receipt($user_id, $price, $level_id, $source, $new_order) {
	//error_reporting(0);
	$settings = get_option('md_receipt_settings');
	if (!empty($settings)) {
		$settings = maybe_unserialize($settings);
		$coname = apply_filters('idc_company_name', $settings['coname']);
		$coemail = $settings['coemail'];
	}
	else {
		$coname = apply_filters('idc_company_name', '');
		$coemail = get_option('admin_email', null);
	}
	$price = apply_filters('idc_order_price', $price, $new_order);
	$user = get_userdata($user_id);
	if (!empty($user)) {
		$email = $user->user_email;
		$fname = idc_text_format($user->first_name);
		$lname = idc_text_format($user->last_name);
	}
	else {
		$user = (object) ID_Member_Order::get_order_meta($new_order, 'guest_data', true);
		// #dev (should we re-map the email key so it matches WP default?)
		$email = $user->email;
		$fname = idc_text_format($user->first_name);
		$lname = idc_text_format($user->last_name);
	}


	$level = ID_Member_Level::get_level($level_id);
	$level_name = idc_text_format($level->level_name);

	$order = new ID_Member_Order($new_order);
	$the_order = $order->get_order();
	if (!empty($the_order)) {
		$txn_id = $the_order->transaction_id;
	}
	else {
		$txn_id = '';
	}

	/* 
	** Mail Function
	*/
	if (!empty($coemail)) {
		// Sending email to customer on the completion of order
		$subject = __('Payment Receipt', 'memberdeck');
		$headers = 'From: '.$coname.' <'.$coemail.'>' . "\n";
		$headers .= 'Reply-To: ' . $coemail ."\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\n";
		$message = '<html><body>';
		$text = idc_text_format(get_option('purchase_receipt'));
		if (empty($text)) {
			$text = idc_text_format(get_option('purchase_receipt_default'));
		}
		if (empty($text)) {
			$message .= '<div style="padding:10px;background-color:#f2f2f2;">
						<div style="padding:10px;border:1px solid #eee;background-color:#fff;">
						<h2>'.$coname.' '.__('Payment Receipt', 'memberdeck').'</h2>

							<div style="margin:10px;">

	 							'.__('Hello', 'memberdeck'). ' ' . $fname .' '. $lname .', <br /><br />
	  
	  							'.__('You have successfully made a payment of ', 'memberdeck').$price.'<br /><br />
	    
	    						'.__('This transaction should appear on your Credit Card statement as', 'memberdeck').' '.$coname.'<br /><br />
	    						<div style="border: 1px solid #333333; width: 600px;">
	    							<table width="600" border="0" cellspacing="0" cellpadding="5">
	          							<tr bgcolor="#333333" style="color: white">
					                        <td width="150">'.__('DATE', 'memberdeck').'</td>
					                        <td width="150">'.__('PRODUCT', 'memberdeck').'</td>
					                        <td width="150">'.__('AMOUNT', 'memberdeck').'</td>
					                        <td width="150">'.__('ORDER ID', 'memberdeck').'</td>
					                    </tr>
				                        <tr>
				                        	<td width="150">'.date("D, M j").'</td>
				                           	<td width="150">'.$level_name.'</td>
				                           	<td width="150">'.$price.'</td>
				                      		<td width="150">'.$txn_id.'</td>
				                      	</tr>
	    							</table>
	    						</div>
	    						<br /><br />
	    						'.__('Thank you for your support!', 'memberdeck').'<br />
	    						'.__('The', 'memberdeck').' '.$coname.' '.__('team', 'memberdeck').'
							</div>

							<table rules="all" style="border-color:#666;width:80%;margin:20px auto;" cellpadding="10">

	    					<!--table rows-->

							</table>

			               ---------------------------------<br />
			               '.$coname.'<br />
			               <a href="mailto:'.$coemail.'">'.$coemail.'</a>
			           

			            </div>
			        </div>';
		}
		else {
			$merge_swap = array(
				array(
					'tag' => '{{COMPANY_NAME}}',
					'swap' => $coname
					),
				array(
					'tag' => '{{NAME}}',
					'swap' => $fname.' '.$lname
					),
				array(
					'tag' => '{{AMOUNT}}',
					'swap' => $price
					),
				array(
					'tag' => '{{DATE}}',
					'swap' => date("D, M j")
					),
				array(
					'tag' => '{{PRODUCT_NAME}}',
					'swap' => $level_name
					),
				array(
					'tag' => '{{TXN_ID}}',
					'swap' => $txn_id
					),
				array(
					'tag' => '{{COMPANY_EMAIL}}',
					'swap' => $coemail
					),
				);
			foreach ($merge_swap as $swap) {
				$text = str_replace($swap['tag'], $swap['swap'], $text);
			}
			$message .= wpautop($text);
		}
		$message .= '</body></html>';

		wp_mail($email, $subject, $message, $headers);
	}
}

add_action('idmember_receipt', 'idmember_purchase_receipt', 1, 5);

function idmember_creator_receipt($user_id, $price, $level_id, $source, $new_order, $fields) {
	//error_reporting(0);
	global $crowdfunding;
	if ($crowdfunding) {
		if (isset($fields['project_id'])) {
			// when in php $_GET via IPN
			$project_id = $fields['project_id'];
		}
		else {
			// when in javascript
			foreach ($fields as $field) {
				if ($field['name'] == "project_id") {
					$project_id = $field['value'];
					break;
				}
			}
		}
		if (isset($project_id)) {
			$project = new ID_Project($project_id);
			$post_id = $project->get_project_postid();
			if ($post_id > 0) {
				$post = get_post($post_id);
				if (!empty($post)) {
					$author_id = $post->post_author;
					if ($author_id > 0) {
						$user = get_user_by('id', $author_id);
						$email = $user->user_email;
						$fname = idc_text_format($user->first_name);
						$lname = idc_text_format($user->last_name);
						if (!empty($user)) {
							$settings = get_option('md_receipt_settings');
							if (!empty($settings)) {
								$settings = maybe_unserialize($settings);
								$coname = apply_filters('idc_company_name', $settings['coname']);
								$coemail = $settings['coemail'];
							}
							else {
								$coname = apply_filters('idc_company_name', '');
								$coemail = get_option('admin_email', null);
							}
							$price = apply_filters('idc_order_price', $price, $new_order);

							$level = ID_Member_Level::get_level($level_id);
							$level_name = idc_text_format($level->level_name);

							$order = new ID_Member_Order($new_order);
							$the_order = $order->get_order();
							if (!empty($the_order)) {
								$txn_id = $the_order->transaction_id;
								$buyer_id = $the_order->user_id;
								if ($buyer_id > 0) {
									$buyer = get_user_by('id', $buyer_id);
									if (!empty($buyer)) {
										$buyer_email = $buyer->user_email;
									}
								}
							}
							else {
								$txn_id = '';
							}

							/* 
							** Mail Function
							*/
							if (!empty($coemail)) {
								// Notify project owner that level has been funded
								$subject = apply_filters('idc_creator_receipt_subject', sprintf(__('Payment of %s from %s', 'memberdeck'), $price, (isset($buyer_email) ? $buyer_email : __('Someone', 'memberdeck'))));
								$headers = 'From: '.$coname.' <'.$coemail.'>' . "\n";
								$headers .= 'Reply-To: ' . $coemail ."\n";
								$headers .= "MIME-Version: 1.0\n";
								$headers .= "Content-Type: text/html; charset=UTF-8\n";
								$message = '<html><body>';
								$text = idc_text_format(get_option('creator_receipt'));
								if (empty($text)) {
									$text = idc_text_format(get_option('creator_receipt_default'));
								}
								
								$merge_swap = array(
									array(
										'tag' => '{{COMPANY_NAME}}',
										'swap' => $coname
										),
									array(
										'tag' => '{{NAME}}',
										'swap' => $fname.' '.$lname
										),
									array(
										'tag' => '{{AMOUNT}}',
										'swap' => $price
										),
									array(
										'tag' => '{{DATE}}',
										'swap' => date("D, M j")
										),
									array(
										'tag' => '{{PRODUCT_NAME}}',
										'swap' => $level_name
										),
									array(
										'tag' => '{{TXN_ID}}',
										'swap' => $txn_id
										),
									array(
										'tag' => '{{COMPANY_EMAIL}}',
										'swap' => $coemail
										),
									);
								foreach ($merge_swap as $swap) {
									$text = str_replace($swap['tag'], $swap['swap'], $text);
								}
								$message .= wpautop($text);

								$message .= '</body></html>';

								wp_mail($email, $subject, $message, $headers);
							}
						}
					}
				}
			}
		}
	}
}

if (!is_idc_free()) {
	//add_action('idmember_receipt', 'idmember_creator_receipt', 1, 6);
}

function memberdeck_preauth_receipt($user_id, $price, $level_id, $source) {
	error_reporting(0);
	global $crowdfunding;
	$settings = get_option('md_receipt_settings');
	if (!empty($settings)) {
		if (!is_array($settings)) {
			$settings = unserialize($settings);
		}
		$coname = apply_filters('idc_company_name', $settings['coname']);
		$coemail = $settings['coemail'];
	}
	else {
		$coname = apply_filters('idc_company_name', '');
		$coemail = get_option('admin_email', null);
	}
	/*$currency = 'USD';
	$symbol = '$';
	if ($source == 'stripe') {
		$settings = get_option('memberdeck_gateways');
		if (!empty($settings)) {
			if (is_array($settings)) {
				$currency = $settings['stripe_currency'];
				$symbol = md_currency_symbol($stripe_currency);
			}
		}
	}*/
	$price = apply_filters('idc_order_price', $price, $new_order);
	$user = get_userdata($user_id);
	$email = $user->user_email;
	$fname = idc_text_format($user->first_name);
	$lname = idc_text_format($user->last_name);

	$level = ID_Member_Level::get_level($level_id);
	if (!is_idc_free()) {
		$credit_data = ID_Member_Credit::get_credit_by_level($level_id);
	}
	if (!empty($credit_data)) {
		$credit_value = $credit_data->credit_count;
	}
	$level_name = idc_text_format($level->level_name);

	$cf_level = false;
	if ($crowdfunding) {
		$cf_assignments = get_assignments_by_level($level_id);
		if (!empty($cf_assignments)) {
			$project_id = $cf_assignments[0]->project_id;
			$project = new ID_Project($project_id);
			$the_project = $project->the_project();
			$post_id = $project->get_project_postid();
			$end = date(get_option( 'date_format' ), get_post_meta( $post_id, 'ign_fund_end', true )); //conversion of timestamp to string so it doesn't changes any frontend view
			$cf_level = true;
		}
	}

	if (!empty($coemail)) {
		/* 
		** Mail Function
		*/

		// Sending email to customer on the completion of order
		$subject = $level_name.' '.__('Pre-Order Confirmation', 'memberdeck');
		$headers = 'From: '.$coname.' <'.$coemail.'>' . "\n";
		$headers .= 'Reply-To: ' . $coemail ."\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\n";
		$message = '<html><body>';
		$text = idc_text_format(get_option('preorder_receipt'));
		if (empty($text)) {
			$text = idc_text_format(get_option('preorder_receipt_default'));
		}
		if (empty($text)) {
			$message .= '<div style="padding:10px;background-color:#f2f2f2;">
							<div style="padding:10px;border:1px solid #eee;background-color:#fff;">
							<h2>'.$coname.' '.__('Pre-Order Confirmation', 'memberdeck').'</h2>

								<div style="margin:10px;">

		 							'.__('Hello', 'memberdeck'). ' ' . $fname .' '. $lname .', <br /><br />
		  
		  							'.__('This is a confirmation of your pre-order of ', 'memberdeck').$level_name.' for '.$price.'<br /><br />';
		  	if (isset($credit_value) && $credit_value > 0) {
		   		$message .=			__('You have earned a total of ', 'memberdeck').$credit_value.' '.($credit_value > 1 ? __('credits for this purchase', 'memberdeck') : 'credit for this purchase').'<br/><br/>';
		    }
		    if ($cf_level) {
		    	$message .=			__('If funding is successful, this charge will process on ', 'memberdeck').$end.'<br/><br/>';
		    }
		    $message .=				'<div style="border: 1px solid #333333; width: 500px;">
		    							<table width="500" border="0" cellspacing="0" cellpadding="5">
		          							<tr bgcolor="#333333" style="color: white">
						                        <td width="100">'.__('DATE', 'memberdeck').'</td>
						                        <td width="275">'.__('DESCRIPTION', 'memberdeck').'</td>
						                        <td width="125">'.__('AMOUNT', 'memberdeck').'</td>
						                    </tr>
					                         <tr>
					                           <td width="200">'.date("D, M j").'</td>
					                           <td width="275">'.$level_name.'</td>
					                           <td width="125">'.$price.'</td>
					                      	</tr>
		    							</table>
		    						</div>
		    						<br /><br />
		    						'.__('Thank you for your support!', 'memberdeck').'<br />
		    						'.__('The', 'memberdeck').' '.$coname.' '.__('team', 'memberdeck').'
								</div>

								<table rules="all" style="border-color:#666;width:80%;margin:20px auto;" cellpadding="10">

		    					<!--table rows-->

								</table>

				               ---------------------------------<br />
				               '.$coname.'<br />
				               <a href="mailto:'.$coemail.'">'.$coemail.'</a>
				           

				            </div>
				        </div>';
		}
		else {
			$merge_swap = array(
				array(
					'tag' => '{{COMPANY_NAME}}',
					'swap' => $coname
					),
				array(
					'tag' => '{{NAME}}',
					'swap' => $fname.' '.$lname
					),
				array(
					'tag' => '{{AMOUNT}}',
					'swap' => $price
					),
				array(
					'tag' => '{{DATE}}',
					'swap' => date("D, M j")
					),
				array(
					'tag' => '{{PRODUCT_NAME}}',
					'swap' => $level_name
					),
				array(
					'tag' => '{{TXN_ID}}',
					'swap' => $txn_id
					),
				array(
					'tag' => '{{COMPANY_EMAIL}}',
					'swap' => $coemail
					),
				array(
					'tag' => '{{END_DATE}}',
					'swap' => $end
					),
				);
			foreach ($merge_swap as $swap) {
				$text = str_replace($swap['tag'], $swap['swap'], $text);
			}
			$message .= wpautop($text);
		}
		$message .= '</body></html>';

		wp_mail($email, $subject, $message, $headers);
	}
}

add_action('memberdeck_preauth_receipt', 'memberdeck_preauth_receipt', 1, 4);

function idmember_registration_email($user_id, $reg_key, $order_id) {
	$settings = get_option('md_receipt_settings');
	if (!empty($settings)) {
		if (!is_array($settings)) {
			$settings = unserialize($settings);
		}
		$coname = apply_filters('idc_company_name', $settings['coname']);
		$coemail = $settings['coemail'];
	}
	else {
		$coname = apply_filters('idc_company_name', '');
		$coemail = get_option('admin_email', null);
	}
	$user = get_userdata($user_id);
	$email = $user->user_email;
	$fname = idc_text_format($user->first_name);
	$lname = idc_text_format($user->last_name);

	$order = new ID_Member_Order($order_id);
	$the_order = $order->get_order();
	if (isset($the_order)) {
		$level_id = $the_order->level_id;
		$level = ID_Member_Level::get_level($level_id);
	}
	
	if (!is_idc_free()) {
		$credit_data = ID_Member_Credit::get_credit_by_level($level_id);
	}
	if (!empty($credit_data)) {
		$credit_value = $credit_data->credit_count;
	}
	$level_name = idc_text_format(isset($level) ? $level->level_name : '');

	if (!empty($coemail)) {
		/* 
		** Mail Function
		*/

		// Sending email to customer on the completion of order
		$subject = __('Complete Your Registration', 'memberdeck');
		$headers = 'From: '.$coname.' <'.$coemail.'>' . "\n";
		$headers .= 'Reply-To: '.$coemail."\n";
		$headers .= "MIME-Version: 1.0\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\n";
		$message = '<html><body>';
		$text = idc_text_format(get_option('registration_email'));
		if (empty($text)) {
			$text = idc_text_format(get_option('registration_email_default'));
		}
		if (empty($text)) {
			$message .= '<div style="padding:10px;background-color:#f2f2f2;">
							<div style="padding:10px;border:1px solid #eee;background-color:#fff;">
							<h2>'.$coname.' '.__('Payment Receipt', 'memberdeck').'</h2>

								<div style="margin:10px;">

		 							'.__('Hello', 'memberdeck').' '. $fname .' '. $lname .', <br /><br />
		  
		  							'.__('Thank you for your purchase of ', 'memberdeck').' '.$level_name.'.<br /><br />
		    
		    						'.__('Your order is almost ready to go. We just need you to click the link below to complete your registration', 'memberdeck').':
		    						<br /><br />
		    						'.home_url("/").'?reg='.$reg_key.'
		    						<br /><br />';
		    if (isset($credit_value) && $credit_value > 0) {
		   	$message .=
		    						__('You have earned a total of ', 'memberdeck').$credit_value.' '.($credit_value > 1 ? __('credits for this purchase', 'memberdeck') : 'credit for this purchase');
		    }
		    $message .=				__('Thank you for your support', 'memberdeck').'!<br />
		    						'.__('The', 'memberdeck').' '.$coname.' '.__('team', 'memberdeck').'
								</div>

								<table rules="all" style="border-color:#666;width:80%;margin:20px auto;" cellpadding="10">

		    					<!--table rows-->

								</table>

				               ---------------------------------<br />
				               '.$coname.'<br />
				               <a href="mailto:'.$coemail.'">'.$coemail.'</a>
				           

				            </div>
				        </div>';
		}
		else {
			$merge_swap = array(
				array(
					'tag' => '{{COMPANY_NAME}}',
					'swap' => $coname
					),
				array(
					'tag' => '{{NAME}}',
					'swap' => $fname.' '.$lname
					),
				array(
					'tag' => '{{PRODUCT_NAME}}',
					'swap' => $level_name
					),
				array(
					'tag' => '{{REG_LINK}}',
					'swap' => home_url("/").'?reg='.$reg_key
					),
				array(
					'tag' => '{{COMPANY_EMAIL}}',
					'swap' => $coemail
					)
				);
			foreach ($merge_swap as $swap) {
				$text = str_replace($swap['tag'], $swap['swap'], $text);
			}
			$message .= wpautop($text);
		}
		$message .= '</body></html>';

		wp_mail($email, $subject, $message, $headers);
	}
}

add_action('idmember_registration_email', 'idmember_registration_email', 1, 3);

function md_send_mail($email, $headers, $subject, $message) {
	$settings = get_option('md_receipt_settings');
	if (!empty($settings)) {
		if (!is_array($settings)) {
			$settings = unserialize($settings);
		}
		$coname = apply_filters('idc_company_name', $settings['coname']);
		$coemail = $settings['coemail'];
	}
	else {
		$coname = apply_filters('idc_company_name', '');
		$coemail = get_option('admin_email', null);
	}

	$subject = apply_filters('idc_text_format', $subject);

	if (!empty($coemail)) {
		if (empty($headers)) {
			$headers = 'From: '.$coname.' <'.$coemail.'>' . "\n";
			$headers .= 'Reply-To: '.$coemail."\n";
			$headers .= "MIME-Version: 1.0\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\n";
		}
		wp_mail($email, $subject, $message, $headers);
	}
}

add_action('idc_register_success', 'idc_welcome_email', 10, 2);

function idc_welcome_email($user_id, $email) {
	$settings = get_option('md_receipt_settings');
	if (!empty($settings)) {
		if (!is_array($settings)) {
			$settings = unserialize($settings);
		}
		$coname = apply_filters('idc_company_name', $settings['coname']);
		$coemail = $settings['coemail'];
	}
	else {
		$coname = apply_filters('idc_company_name', '');
		$coemail = get_option('admin_email', null);
	}

	if (!empty($user_id)) {
		$user = get_user_by('id', $user_id);
		if (isset($user)) {
			$fname = idc_text_format($user->user_firstname);
			$lname = idc_text_format($user->user_lastname);
		}
	}

	$site_name = get_bloginfo('name');
	$subject = $site_name.' '.__('Registration Confirmation', 'memberdeck');
	$durl = md_get_durl();
	$message = '<html><body>';
	$text = idc_text_format(get_option('welcome_email'));
	if (empty($text)) {
		$text = idc_text_format(get_option('welcome_email_default'));
	}
	if (empty($text)) {
		$message .= '<div style="padding:10px;background-color:#f2f2f2;">
						<div style="padding:10px;border:1px solid #eee;background-color:#fff;">
						<h2>'.$subject.'</h2>

							<div style="margin:10px;">

	 							'.__('Hello', 'memberdeck').' '. (isset($fname) ? $fname : '') .' '. (isset($lname) ? $lname : '') .', <br /><br />
	  
	  							'.__('Congratulations, your registration for ', 'memberdeck').$site_name.__(' was successful', 'memberdeck').'.<br /><br />

	  							'.__('If you have already created a password, you can login at any time using the information below. Otherwise, please check your inbox for a second email with instructions for creating your password.', 'memberdeck').'<br/><br/>
							
	  							<div style="border: 1px solid #333333; width: 500px;">
	    							<table width="500" border="0" cellspacing="0" cellpadding="5">
	          							<tr bgcolor="#333333" style="color: white">
					                        <td width="200">'.__('Username', 'memberdeck').'</td>
					                        <td width="200">'.__('Login URL', 'memberdeck').'</td>
					                    </tr>
				                         <tr>
				                           <td width="200">'.$email.'</td>
				                           <td width="200">'.$durl.'</td>
				                      	</tr>
	    							</table>
	    						</div>
							</div>

							<table rules="all" style="border-color:#666;width:80%;margin:20px auto;" cellpadding="10">

	    					<!--table rows-->

							</table>

			               ---------------------------------<br />
			               '.$coname.'<br />
			               <a href="mailto:'.$coemail.'">'.$coemail.'</a>
			           

			            </div>
			        </div>';
	}
	else {
		$merge_swap = array(
			array(
				'tag' => '{{NAME}}',
				'swap' => $fname.' '.$lname
				),
			array(
				'tag' => '{{SITE_NAME}}',
				'swap' => $site_name
				),
			array(
				'tag' => '{{EMAIL}}',
				'swap' => $email
				),
			array(
				'tag' => '{{DURL}}',
				'swap' => $durl
				),
			array(
				'tag' => '{{COMPANY_NAME}}',
				'swap' => $coname
				),
			array(
				'tag' => '{{COMPANY_EMAIL}}',
				'swap' => $coemail
				)
			);
		foreach ($merge_swap as $swap) {
			$text = str_replace($swap['tag'], $swap['swap'], $text);
		}
		$message .= wpautop($text);
	}
	$message .= '</body></html>';
	$mail = new ID_Member_Email($email, $subject, $message, $user_id);
	$send_mail = $mail->send_mail();
}

add_action('memberdeck_recurring_success', 'idc_update_subscription', 10, 4);

function idc_update_subscription($source, $user_id, $order_id, $term_length = null) {
	global $stripe_api_version;
	//$log = fopen('update_sub_log.txt', 'w+');
	//fwrite($log, 'inside function');
	if (!empty($term_length) && $source == 'stripe') {
		//fwrite($log, 'have term length and stripe');
		$order = new ID_Member_Order($order_id);
		$the_order = $order->get_order();
		if (!empty($the_order)) {
			//fwrite($log, 'have an order');
			$level_id = $the_order->level_id;
			$user_id = $the_order->user_id;
			// now let's find the subscription
			$subscription = new ID_Member_Subscription(null, $user_id, $level_id);
			$filed_sub = $subscription->find_subscription();
			// should we check to make sure this count won't go over the limit?
			if (!empty($filed_sub)) {
				//fwrite($log, 'have filed sub');
				$id = $filed_sub->id;
				$subscription_id = $filed_sub->subscription_id;
				if (!empty($filed_sub->payments) && $filed_sub->payments >0) {
					$charge_count = $filed_sub->payments;
				}
				else {
					$charge_count = 0;
				}
				$new_count = $charge_count + 1;
				//fwrite($log, 'new count: '.$new_count."\n");
				$subscription = new ID_Member_Subscription($id);
				$order->set_subscription($id);
				if ($new_count >= $term_length) {
					//fwrite($log, 'update and cancel');
					// we have to update and cancel
					$update_subscription = $subscription->add_charge($new_count);
					// cancel
					$customer_id = customer_id_ajax($user_id);
					if (!empty($customer_id)) {
						//fwrite($log, 'have customer id');
						try {
							$settings = get_option('memberdeck_gateways');
							if (!empty($settings)) {
								if (is_array($settings)) {
									$test = $settings['test'];
									if ($test) {
										$sk = $settings['tsk'];
									}
									else {
										$sk = $settings['sk'];
									}
									$es = $settings['es'];
									$esc = $settings['esc'];
									if ($esc == '1') {
										$check_claim = get_option('md_level_'.$product_id.'_owner');
										if (!empty($check_claim)) {
											$md_sc_creds = get_sc_params($check_claim);
											if (!empty($md_sc_creds)) {
												//echo 'using sc';
												$sk = $md_sc_creds->access_token;
											}
										}
									}
								}
							}
							if ($es) {
								//fwrite($log, 'successful catch and stripe enabled'."\n");
								if (!class_exists('Stripe')) {
									require_once 'lib/stripe-php-4.2.0/init.php';
								}
								\Stripe\Stripe::setApiKey($sk);
								\Stripe\Stripe::setApiVersion($stripe_api_version);
								$c = \Stripe\Customer::retrieve($customer_id);
								if (!empty($c)) {
									//fwrite($log, 'not empty'."\n");
									try {
										$cancel = $c->subscriptions->retrieve($subscription_id)->cancel();
										$subscription->cancel_subscription();
										//fwrite($log, 'cancelled'."\n");
									}
									catch (Exception $e) {
										$jsonbody = $e->getJsonBody();
										$message = $jsonbody['error']['message'].' '.__LINE__;
										//fwrite($log, 'exception: '.$message."\n");
									}
								}
							}
						}
						catch (Exception $e) {
							$jsonbody = $e->getJsonBody();
							$message = $jsonbody['error']['message'].' '.__LINE__;
							//fwrite($log, 'exception: '.$message."\n");
							//print_r($e);
						}
					}
				}
				else {
					// update only
					//fwrite($log, 'update only');
					$update_subscription = $subscription->add_charge($new_count);
				}
			}
		}
	}
	else if (!empty($term_length) && $source == 'adaptive') {
		//$source, $user_id, $order_id, $term_length = null
		update_option('adaptive_1', 1);
		$order = new ID_Member_Order($order_id);
		$the_order = $order->get_order();
		if (!empty($the_order)) {
			update_option('adaptive_2', 2);
			//fwrite($log, 'have an order');
			$level_id = $the_order->level_id;
			$user_id = $the_order->user_id;
			// now let's find the subscription
			$subscription = new ID_Member_Subscription(null, $user_id, $level_id);
			$filed_sub = $subscription->find_subscription();
			// should we check to make sure this count won't go over the limit?
			if (!empty($filed_sub)) {
				update_option('adaptive_3', 3);
				//fwrite($log, 'have filed sub');
				$id = $filed_sub->id;
				$subscription_id = $filed_sub->subscription_id;
				if (!empty($filed_sub->payments) && $filed_sub->payments > 0) {
					$charge_count = $filed_sub->payments;
				}
				else {
					$charge_count = 0;
				}
				$new_count = $charge_count + 1;
				//fwrite($log, 'new count: '.$new_count."\n");
				$subscription = new ID_Member_Subscription($id);
				$order->set_subscription($id);
				if ($new_count >= $term_length) {
					update_option('adaptive_4', 4);
					//fwrite($log, 'update and cancel');
					// we have to update and cancel
					$update_subscription = $subscription->add_charge($new_count);
					// cancel subscription here
				}
			}
		}
	}
	//fclose($log);
}

add_action('widgets_init', 'memberdeck_dashboard_widgets', 20);

function memberdeck_dashboard_widgets() {

	if ( function_exists('register_sidebar') ) {
		register_sidebar(array(
			'name' => __('Dashboard Sidebar', 'memberdeck'),
			'id' => 'dashboard-sidebar',
			'description' => __('Appears on the Dashboard below the User Profile', 'memberdeck'),
			'before_widget' => '<li id="%1$s" class="widget %2$s">',
			'after_widget' => '</li>',
			'before_title' => '<h3 class="dashboard-widget">',
			'after_title' => '</h3>',
		));
	}
}

add_action('memberdeck_stripe_success', 'memberdeck_auto_login', 20, 2);
add_action('idc_register_success', 'memberdeck_auto_login', 20, 2);

function memberdeck_auto_login($user_id, $email) {
	$user = get_user_by('id', $user_id);
	if (!empty($user)) {
		$login = $user->user_login;
		wp_set_auth_cookie( $user_id, true, is_ssl() );
		do_action('wp_login', $login, $user);
	}
}

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
/**
 * Handles the download process for a downloadable file from AWS S3.
 *
 * This function is responsible for processing download requests, validating the user's credentials,
 * and delivering the downloadable file to the user. It supports downloads from AWS S3 when enabled.
 *
 */
function memberdeck_download_handler() {
	// Check for the required GET parameters.
	if ( ! isset( $_GET['md_download'], $_GET['key'] ) ) {
		return;
	}

	$download = sanitize_text_field( $_GET['md_download'] );
	$key      = sanitize_text_field( $_GET['key'] );
	$current_user = wp_get_current_user();

	// Validate key and user.
	if ( ! validate_key( $download, $key, $current_user->ID, $current_user->user_registered ) ) {
		error_log( 'Invalid file key' );
		return;
	}

	$new_dl = new ID_Member_Download( $download );
	$dl     = $new_dl->get_download();

	if ( null === $dl ) {
		error_log( 'Downloadable file doesn\'t exist' );
		return;
	}

	$link = $dl->download_link;

	if ( '1' === $dl->enable_s3 ) {
		$settings = get_option( 'md_s3_settings' );

		// Validate S3 settings.
		if ( ! is_array( $settings ) ) {
			$settings = maybe_unserialize( $settings );
		}

		if ( ! is_array( $settings ) || empty( $settings['access_key'] ) || empty( $settings['secret_key'] ) || empty( $settings['bucket'] ) || empty( $settings['region'] ) ) {
			// Handle missing or invalid S3 settings.
			error_log( 'Missing or invalid S3 settings' );
			return;
		}

		try {
			$client = new S3Client(
				array(
					'credentials' => array(
						'key'    => $settings['access_key'],
						'secret' => $settings['secret_key'],
					),
					'region' => $settings['region'],
					'version' => 'latest'
				)
			);

			$result = $client->getObject(
				array(
					'Bucket' => $settings['bucket'],
					'Key'    => $link,
				)
			);
			header( 'Content-Type: ' . $result['ContentType'] );
			header( 'Content-Disposition: attachment; filename=' . basename( $link ) );
			echo $result['Body']; // Output the file content.
		} catch ( AwsException $e ) {
			// Log the exception but don't output it.
			error_log($e->getMessage() . "\n");
		}
		exit();
	} else {
		wp_safe_redirect( $link ); // Redirect to the download link.
		exit();
	}
}
add_action('init', 'memberdeck_download_handler');

function validate_key($download, $key, $user_id, $user_registered) {
	$member = new ID_Member();
	$match = $member->match_user($user_id);
	if (!empty($match)) {
		if (md5($user_registered.$user_id) !== $key) {
			return false;
		}
		else {
			$access_levels = unserialize($match->access_level);
			$new_dl = new ID_Member_Download($download);
			$dl = $new_dl->get_download();
			foreach ($access_levels as $level) {
				if (in_array($level, unserialize($dl->download_levels))) {
					$pass = true;
				}
			}
			if ($pass) {
				return true;
			}
			else {
				return false;
			}
		}	
	}	
}

add_action('wp_login', 'memberdeck_exp_check_onlogin', 1, 2);

function memberdeck_exp_check_onlogin($user_login, $user) {
	$level_array = array();
	$userdata = $user->data;
	foreach ($userdata as $k=>$v) {
		if ($k == 'ID') {
			$user_id = $v;
			break;
		}
	}
	if (isset($user_id)) {
		$user_orders = ID_Member_Order::get_orders_by_user($user_id);
		if (count($user_orders) > 0) {
			foreach ($user_orders as $order) {
				if ($order->transaction_id !== 'free') {
					// a non-expiring level has a null value for e_date
					if (!empty($order->e_date) && $order->e_date !== '0000-00-00 00:00:00') {
						$e_date = $order->e_date;
						$datestring = strtotime($e_date);
						$now = time();
						if ($now > $datestring) {
							// expired
							ID_Member_Order::cancel_subscription($order->id);
						}
						if ($order->status == 'active') {
							$level_array[] = $order->level_id;
						}
					}
					else {
						if ($order->status == 'active') {
							$level_array[] = $order->level_id;
						}
					}
				}
				else {
					if ($order->status == 'active') {
						$level_array[] = $order->level_id;
					}
				}
			}
		}
	}
	if (!empty($level_array)) {
		ID_Member::expire_level($user_id, $level_array);
	}
	/*$user_levels = ID_Member::user_levels($user_id);
	if (!empty($user_levels)) {
		$level_array = unserialize($user_levels->access_level);
	}
	if (isset($level_array)) {
		//print_r($level_array)."\n";
		$i = 0;
		foreach ($level_array as $level) {
			$order = new ID_Member_Order(null, $user_id, $level);
			//print_r($order);
			$latest = $order->get_last_order();
				//print_r($latest)."\n";
			// make sure there is an order and it isn't for a free level
			if (isset($latest) && $latest->transaction_id !== 'free') {
				// a non-expiring level has a null value for e_date
				if (isset($latest->e_date) && $latest->e_date !== '0000-00-00 00:00:00') {
					$e_date = $latest->e_date;
					$datestring = strtotime($e_date);
					$now = time();
					if ($now > $datestring) {
						unset($level_array[$i]);
						ID_Member_Order::cancel_subscription($latest->id);
					}
				}
			}
			$i++;
		}
		//print_r($level_array)."\n";
		//exit();
		ID_Member::expire_level($user_id, $level_array);
	}*/
}

function memberdeck_exp_checkondash($user_id) {
	if (isset($user_id)) {
		$user_orders = ID_Member_Order::get_orders_by_user($user_id);
		if (count($user_orders) > 0) {
			foreach ($user_orders as $order) {
				if ($order->transaction_id !== 'free') {
					// a non-expiring level has a null value for e_date
					if (!empty($order->e_date) && $order->e_date !== '0000-00-00 00:00:00') {
						$e_date = $order->e_date;
						$datestring = strtotime($e_date);
						$now = time();
						if ($now > $datestring) {
							// expired
							ID_Member_Order::cancel_subscription($order->id);
						}
						else {
							if ($order->status == 'active') {
								$level_array[] = $order->level_id;
							}
						}
					}
					else {
						if ($order->status == 'active') {
							$level_array[] = $order->level_id;
						}
					}
				}
				else {
					if ($order->status == 'active') {
						$level_array[] = $order->level_id;
					}
				}
			}
		}
	}
	if (!empty($level_array)) {
		ID_Member::expire_level($user_id, $level_array);
	}
}

add_action('wp_login', 'memberdeck_license_gen_check', 1, 2);

function memberdeck_license_gen_check($user_login, $user) {
	$userdata = $user->data;
	foreach ($userdata as $k=>$v) {
		if ($k == 'ID') {
			$user_id = $v;
			break;
		}
	}
	$md_user = ID_Member::user_levels($user_id);
	if (!empty($md_user)) {
		$md_user_levels = unserialize($md_user->access_level);
	}

	if (!empty($md_user_levels)) {
		$downloads = ID_Member_Download::get_downloads();
		foreach ($md_user_levels as $level_id) {
			$level = ID_Member_Level::get_level($level_id);
			if (isset($level->license_count) && ($level->license_count > 0 || $level->license_count == -1)) {
				foreach ($downloads as $download) {
					$dl_id = $download->id;
					if (!empty($download->download_levels)) {
						$levels = maybe_unserialize($download->download_levels);
						if (is_array($levels) && in_array($level_id, $levels)) {
							if ($download->licensed == 1) {
								//echo $user_id;
								$key = MD_Keys::get_license($user_id, $dl_id);
								if (empty($key) || $key == '') {
									$keys = new MD_Keys();
									$license = $keys->generate_license($user_id, $dl_id);
									if (isset($license)) {
										$new_license = new MD_Keys($license, $level->license_count);
										$save_license = $new_license->store_license($user_id, $dl_id);
									}
								}
							}
						}
					}
				}
			}
		}
	}
	//exit;
}

add_action('init', 'md_validate_account');

function md_validate_account() {
	if (isset($_GET['action']) && $_GET['action'] == 'md_validate_account') {
		$valid_level = '1';
		do_action('md_validate_account', $_REQUEST, $_SERVER);
		if (empty($_GET['id_account']) || empty($_GET['download_list'])) {
			print_r(json_encode($valid_level));
			exit;
		}
		$id_account = sanitize_text_field($_GET['id_account']);
		$download_list = idf_sanitize_array($_GET['download_list']);
		$user = get_user_by('login', $id_account);
		if (empty($user)) {
			print_r(json_encode($valid_level));
			exit;
		}
		$member = new ID_Member($user->ID);
		$membership = $member->get_membership();
		if (empty($membership)) {
			exit;
		}
		foreach ($download_list as $download_id) {
			$download = new ID_Member_Download($download_id);
			$the_download = $download->get_download();
			$download_levels = maybe_unserialize($the_download->download_levels);
			if (empty($download_levels)) {
				continue;
			}
			foreach ($membership as $level) {
				if (in_array($level->level_id, $download_levels)) {
					$license_level = $download_id;
					break;
				}
			}
			if (isset($license_level)) {
				$valid_level = $license_level;
				break;
			}
		}
		print_r(json_encode($valid_level));
		exit;
	}
}

add_action('init', 'md_validate_license');

function md_validate_license() {
	if (isset($_GET['action']) && $_GET['action'] == 'md_validate_license') {
		do_action('md_validate_license', $_REQUEST, $_SERVER);
		$response = array('valid' => 0, 'download_id' => null);
		if (isset($_GET['key'])) {
			$key = $_GET['key'];
			$keys = new MD_Keys($key);
			$response = $keys->validate_license();
		}
		print_r(json_encode($response));
		exit;
	}
}

/**
 * Action of init to check button shortcode's data is submitted and then filters the content
 */
add_action('init', 'idc_shortcode_button_submit', 10, 1);
function idc_shortcode_button_submit($args) {
	if (isset($_GET['idc_button_submit'])) {
		add_filter('the_content', 'idc_shortcode_button_checkout', 12);
	}
}

/**
 * Action of init hook, for adding checkout form when user clicks the link on Product Renewal email
 */
add_action('init', 'idc_product_renewal_checkout_link', 10);
function idc_product_renewal_checkout_link() {
	if (isset($_GET['idc_renewal_checkout'])) {
		add_filter('the_content', 'idc_product_renewal_checkout', 14);
	}
}
if (!is_idc_free()) {
	add_action('memberdeck_free_success', 'md_sendto_mailchimp', 100, 2);
	add_action('memberdeck_payment_success', 'md_sendto_mailchimp', 100, 4);
}
function md_sendto_mailchimp($user_id, $order_id, $paykey = null, $fields = null) {
	if (!class_exists('MailChimp')) {
		require_once IDC_PATH.'lib/mailchimp-api-master/MailChimp.class.php';
	}
	$crm_settings = get_option('crm_settings');
	if (!empty($crm_settings)) {
		$mailchimp_key = apply_filters('idc_sendtomc_key', $crm_settings['mailchimp_key'], $order_id);
		if (!stripos($mailchimp_key, '-')) {
			// a bad API key will result in a printed error that breaks parsing
			return;
		}
		$mailchimp_list = apply_filters('idc_sendtomc_list', $crm_settings['mailchimp_list'], $order_id);
		$enable_mailchimp = $crm_settings['enable_mailchimp'];
		if ($enable_mailchimp == 1) {
			$current_user = get_userdata($user_id);
			$email = $current_user->user_email;
			$fname = $current_user->first_name;
			$lname = $current_user->last_name;
			$level_name = '';
			$order = new ID_Member_Order($order_id);
			$the_order = $order->get_order();

			if (!empty($the_order)) {
				$level_id = $the_order->level_id;
				if ($level_id > 0) {
					$level = ID_Member_Level::get_level($level_id);
					if (!empty($level)) {
						$level_price = $level->level_price;
						if ($level_price > 0) {
							$free = false;
						}
						else {
							$free = true;
						}
						$level_name = $level->level_name;
					}
				}
			}

			try {
				$mailchimp = new MailChimp($mailchimp_key);
			}
			catch (Exception $e) {
				// Log the exception in the error log
				error_log('Caught exception: ' . $e->getMessage());
				error_log('Stack trace: ' . $e->getTraceAsString());

				return;
			}

			$mailchimp->call('lists/' . $mailchimp_list . '/merge-fields', array(
				'tag' => 'LEVEL',
				'name' => (isset($level_name) ? $level_name : ' '),
				'type' => 'text',
				'required' => false,
				'public' => false,
			));

			$mailchimp->call('lists/' . $mailchimp_list . '/merge-fields', array(
				'tag' => 'IDC_FREE',
				'name' => urlencode('IDC Free Only'),
				'type' => 'text',
				'required' => false,
				'public' => false,
				));

			//echo 'after call 1';
			$merge_vars = array(
				'FNAME' => !empty($fname) ? $fname : '',
				'LNAME' => !empty($lname) ? $lname : '',
				'LEVEL' => $level_name,
			);

			if (isset($free)) {
				$member = new ID_Member($user_id);
				$get_member = $member->match_user($user_id);
				if (!empty($get_member)) {
					$levels = $get_member->access_level;
					if (!empty($levels)) {
						$levels = unserialize($levels);
						if (count($levels) > 0) {
							foreach ($levels as $prior_level) {
								$this_level = ID_Member_Level::get_level($prior_level);
								if (!empty($this_level)) {
									$this_level_price = $this_level->level_price;
									if ($this_level_price > 0) {
										$free = false;
									}
								}
							}
						}
					}
				}
				if ($free) {
					$free_text = 'YES';
				}
				else {
					$free_text = 'NO';
				}
				$merge_vars['IDC_FREE'] = $free_text;
			}

			$mailchimp->call('lists/' . $mailchimp_list . '/members', array(
				'email_address' => $email,
				'status' => 'subscribed', // 'subscribed' for double opt-in, 'pending' for single opt-in
				'merge_fields' => $merge_vars,
				'update_existing' => true,
				'replace_interests' => false,
				));

		}
	}
}

add_action('idc_guest_checkout_order', 'idc_save_guest_data', 10, 2);

function idc_save_guest_data($order_id, $customer = array()) {
	if (!empty($customer['pw'])) {
		unset($customer['pw']);
	}
	if (!empty($customer['cpw'])) {
		unset($customer['cpw']);
	}
	ID_Member_Order::update_order_meta($order_id, "guest_data", $customer);
}

/**
 * Action called after order success, will store currency in order meta and Client's IP Address as well
 */
add_action('memberdeck_free_success', 'idc_save_order_meta', 100, 2);
add_action('memberdeck_payment_success', 'idc_save_order_meta', 100, 5);
add_action('memberdeck_preauth_success', 'idc_save_order_meta', 100, 5);
function idc_save_order_meta($user_id, $order_id, $paykey = '', $fields = null, $source = '') {
	// store extra fields
	ID_Member_Order::update_order_meta($order_id, "extra_fields", $fields);
	ID_Member_Order::update_order_meta($order_id, 'paykey', $paykey);

	// Getting symbol to store in meta data
	$currency_code = ID_Member_Order::get_order_currency($source);
	$gateway_options = array("gateway" => ((isset($source)) ? $source : ''), "currency_code" => $currency_code);

	// store gateway info
	ID_Member_Order::update_order_meta(
		$order_id,
		$meta_key = "gateway_info",
		$meta_value = $gateway_options,
		$prev_value = '',
		$unique_arg = true
	);

	// Saving user IP address and User Agent
	if (function_exists('idf_client_ip')) {
		$clients_data = array();
		$clients_data['ip_address'] = idf_client_ip();
		// #devnote need to pass $_SERVER or add to fields
		$clients_data['user_agent'] = (isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
		ID_Member_Order::update_order_meta($order_id, "user_ip_address", $clients_data);
	}
}

if (!is_idc_free()) {
	// complete upgrade by removing old product
	add_action('memberdeck_payment_success', 'idc_cancel_pathway_order', 100, 5);
}

function idc_cancel_pathway_order($user_id, $order_id, $paykey = '', $fields = null, $source = '') {
	// Cancelling the order based on upgrade_pathways, if exists for current order product
	$idc_pathway = new ID_Member_Pathways();
	$cancelled = $idc_pathway->cancel_order_on_upgrade_pathways($order_id, $user_id);
}

/**
 * Action for making a combined purchase if any
 */
if (!is_idc_free()) {
	add_action('memberdeck_payment_success', 'idc_make_combined_purchase_on_success', 100, 5);
}
function idc_make_combined_purchase_on_success($user_id, $order_id, $paykey = '', $fields = null, $source = '') {
	if (!empty($source) && $source == "stripe") {
		// Getting order details to get more information
		$order = new ID_Member_Order($order_id);
		$order_details = $order->get_order();
		// Getting stripe customer id
		$custid = ID_Member::get_customer_id($user_id);
		// Getting stripe plan from level details
		$level_data = ID_Member_Level::get_level($order_details->level_id);
		$plan = $level_data->plan;
		// Calling the action to make combined product purchase
		do_action('idc_make_combined_purchase', $level_data->id, $source, $custid, $user_id, $plan, $level_data);
	}
}
if (!is_idc_free()) {
	add_action('idc_register_success', 'idc_assign_product_on_register', 20, 2);
}
function idc_assign_product_on_register($user_id, $email) {
	// Checking if default product is enabled in Admin, then add a member with default product
	$general = maybe_unserialize(get_option('md_receipt_settings'));
	if (isset($general['enable_default_product'])) {
		if (apply_filters('idc_enable_default_product', $general['enable_default_product']) == "1") {
			$default_product = $general['default_product'];
			if (!empty($default_product)) {
				// First see if member exists in memberdeck_members table
				$member = new ID_Member($user_id);
				$current_member = $member->match_user($user_id);
				if (empty($current_member)) {
					// Add Member with this product to their own product list
					$access_levels = array($default_product);
					$user = array('user_id' => $user_id, 'level' => $access_levels, 'data' => array());
					$new = ID_Member::add_user($user);
				}
				else {
					// level is being overwritten by the udpate_user call in create_customer
					$access_levels = maybe_unserialize($current_member->access_level);
					$access_levels[] = $default_product;
					$current_member->level = $access_levels;
					$update = ID_Member::update_user((array) $current_member);
				}
			}
		}
	}
}

/****************************************************************************************************************
 * MemberDeck Ajax
 ****************************************************************************************************************/

function md_level_data() {
	if (isset($_POST['level_id'])) {
		$level_id = absint($_POST['level_id']);
		if (isset($_POST['get_instant_checkout'])) {
			$instant_checkout = instant_checkout();
			if ($level_id > 0) {
				$level = ID_Member_Level::get_level($level_id);
				print_r(json_encode(array(
					"level" => $level,
					"instant_checkout" => $instant_checkout
				)));
			}
		}
		else {
			if ($level_id > 0) {
				$level = ID_Member_Level::get_level($level_id);
				print_r(json_encode($level));
			}
		}
	}
	exit;
}

add_action('wp_ajax_md_level_data', 'md_level_data');
add_action('wp_ajax_nopriv_md_level_data', 'md_level_data');

function idmember_get_profile() {
	if ($_POST['ID'] > 0) {
		$user_id = absint($_POST['ID']);
		$userdata = get_userdata($user_id);
		if (!empty($userdata)) {
			$usermeta = get_user_meta($user_id);
			$shipping_info = get_user_meta($user_id, 'md_shipping_info', true);
			print_r(json_encode(array('shipping_info' => $shipping_info, 'userdata' => $userdata, 'usermeta' => $usermeta)));
		}
	}
	exit;
}

add_action('wp_ajax_idmember_get_profile', 'idmember_get_profile');

function idmember_get_levels() {
	// Used to list levels in admin, combined products, and in content protection metabox
	$filter = null;
	if (isset($_POST['filter'])) {
		$filter = idf_sanitize_array($_POST['filter']);
	}
	$levels = ID_Member_Level::get_levels($filter);	
	$level = array();
	foreach ($levels as $object) {
		foreach ($object as $k=>$v) {
			$object->$k = html_entity_decode(idc_text_format($v));
		}
		$level[$object->id] = $object;
	}
	print_r(json_encode($level));
	exit;
}

add_action('wp_ajax_idmember_get_levels', 'idmember_get_levels');

function idc_level_get_all_level_meta() {
	if (empty($_POST['id'])) {
		return;
	}
	// Global function for getting level meta
	$raw_meta = ID_Member_Level::get_all_level_meta(absint($_POST['id']));
	$meta = array();
	if (!empty($raw_meta)) {
		#devnote make recursive
		foreach ($raw_meta as $object) {
			foreach ($object as $key=>$val) {
				if (is_array($val)) {
					foreach ($val as $k=>$v) {
						$object->$k = html_entity_decode(idc_text_format($v));
					}
				}
				else {
					$object->$key = html_entity_decode(idc_text_format($val));
				}
			}
			$meta[$object->id] = $object;
		}
	}
	print_r(json_encode($meta));
	exit;
}

add_action('wp_ajax_idc_level_get_all_level_meta', 'idc_level_get_all_level_meta');

function idmember_get_credits() {
	if (!is_idc_free()) {
		$credits = ID_Member_Credit::get_all_credits();
		$credit = array();
		foreach ($credits as $object) {
			$credit[$object->id] = $object;
		}
		print_r(json_encode($credit));
	}
	else {
		print_r(json_encode(new stdClass()));
	}
	exit;
}

add_action('wp_ajax_idmember_get_credits', 'idmember_get_credits');

function idmember_get_downloads() {
	$res = ID_Member_Download::get_downloads();
	$downloads = array();
	foreach ($res as $object) {
		$levels = unserialize($object->download_levels);
		$object->levels = $levels;
		if (!empty($object->image_link) && stristr($object->image_link, "http") === false) {
			$download_thumb = wp_get_attachment_image_src($object->image_link, 'thumbnail');
			if (!empty($download_thumb)) {
				$object->download_image_url = $download_thumb[0];
			}
		}
		$downloads[$object->id] = $object;
	}
	print_r(json_encode($downloads));
	exit;
}

add_action('wp_ajax_idmember_get_downloads', 'idmember_get_downloads');
add_action('wp_ajax_nopriv_idmember_get_downloads', 'idmember_get_downloads');

function idmember_get_level() {
	if (isset($_POST['action']) && isset($_POST['Level'])) {
		$id = $_POST['Level'];
		$level = ID_Member_Level::get_level($id);
		print_r(json_encode($level));
	}
	else {
		echo 0;
	}
	exit();
}

add_action('wp_ajax_idmember_get_level', 'idmember_get_level');
add_action('wp_ajax_nopriv_idmember_get_level', 'idmember_get_level');

function idmember_get_pages($ajax = null) {
	$pages = get_pages(array('post_type' => 'page',
		'sort_order' => 'DESC',
		'sort_column' => 'post_title'
		)
	);
	if ($ajax) {
		print_r(json_encode($pages));
		exit;
	}
	else {
		return $pages;
	}
}

add_action('wp_ajax_idmember_get_pages', 'idmember_get_pages');
add_action('wp_ajax_nopriv_idmember_get_pages', 'idmember_get_pages');

//A function for pulling level-based creator permissions from the database
//Pass a parameter of 1 to have it return a standard array
//Pass 0/nothing for it to use json_encode instead
function idmember_get_cperms($_treturn = 0) {
	$general = get_option('md_receipt_settings');
	$allowed_creator_levels = array();
	if (!empty($general)) {
		if (!is_array($general)) {
			$general = unserialize($general);
		}
		//Load level-based creator permission values
		if (isset($general['allowed_creator_levels'])) {
			foreach ($general['allowed_creator_levels'] as $ac_assign) {
				$allowed_creator_levels[] = $ac_assign;
			}
		}
	}
	if ($_treturn){
		return $allowed_creator_levels;
	}
	else {
		print_r(json_encode($allowed_creator_levels));
		exit;
	}
}

add_action('wp_ajax_idmember_get_cperms', 'idmember_get_cperms');
add_action('wp_ajax_nopriv_idmember_get_cperms', 'idmember_get_cperms');

function idc_cancel_sub() {
	global $stripe_api_version;
	if (isset($_POST['plan'])) {
		$plan = esc_attr($_POST['plan']);
	}
	if (isset($_POST['plan_id'])) {
		$plan_id = esc_attr($_POST['plan_id']);
	}
	if (isset($_POST['user_id'])) {
		$user_id = absint($_POST['user_id']);
	}
	if (isset($_POST['payment_gateway'])) {
		$payment_gateway = sanitize_text_field($_POST['payment_gateway']);
	} else {
		$payment_gateway = 'stripe';
	}
	$response = array('status' => 'error', 'message' => __('Could not process request.', 'memberdeck'));
	if (!empty($plan) && !empty($plan_id) && isset($user_id)) {
		if ($payment_gateway == 'authorize.net') {
			// Gateway settings
			$settings = get_option('memberdeck_gateways');

			// Requiring the library of Authorize.Net
			require("lib/AuthorizeNet/vendor/authorizenet/authorizenet/AuthorizeNet.php");
			define("AUTHORIZENET_API_LOGIN_ID", $settings['auth_login_id']);
			define("AUTHORIZENET_TRANSACTION_KEY", $settings['auth_transaction_key']);
			if ($settings['test'] == '1') {
				define("AUTHORIZENET_SANDBOX", true);
			} else {
				define("AUTHORIZENET_SANDBOX", false);
			}

			// Cancelling the subscription using subscription_id
			$subscription = new AuthorizeNetARB;
			$response_cancel = $subscription->cancelSubscription($plan_id);
			if ($response_cancel->isOk()) {
				$response['status'] = 'success';
				$response['message'] = __('Subscription successfully cancelled', 'memberdeck');

				// Setting subscription as inactive
				ID_Member_Subscription::cancel_subscription_id($plan_id);
			}
			else {
				$response['message'] = $response_cancel->getMessageText().' '.__LINE__;
			}
		}
		else {
			$sk = idc_stripe_sk();
			if (!class_exists('Stripe')) {
				require_once 'lib/stripe-php-4.2.0/init.php';
			}
			\Stripe\Stripe::setApiKey($sk);
			\Stripe\Stripe::setApiVersion($stripe_api_version);
			$customer_id = customer_id_ajax($user_id);
			if (!empty($customer_id)) {
				try {
					$cu = \Stripe\Customer::retrieve($customer_id);
					if (!empty($cu)) {
						try {
							$subscription = $cu->subscriptions->retrieve($plan_id)->cancel();
							$response['status'] = 'success';
							$response['message'] = __('Subscription successfully cancelled', 'memberdeck');
						}
						catch (\Stripe\Error\InvalidRequest $e) {
							$response['message'] = __('Could not retrieve subscription', 'memberdeck');
						}
						catch (Exception $e) {
							$response['message'] = __('Could not retrieve subscription', 'memberdeck');
						}
					}
				}
				catch (\Stripe\Error\InvalidRequest $e) {
					$response['message'] = __('Could not retrieve customer', 'memberdeck');
				}
				catch (Exception $e) {
					$response['message'] = __('Could not retrieve customer', 'memberdeck');
				}
			}
		}
	}
	print_r(json_encode($response));
	exit;
}

add_action('wp_ajax_idc_cancel_sub', 'idc_cancel_sub');
add_action('wp_ajax_nopriv_iidc_cancel_sub', 'idc_cancel_sub');

function idmember_edit_user() {
	if (isset($_POST['action']) && $_POST['action'] == 'idmember_edit_user') {
		$id = $_POST['ID'];
		$user = new ID_Member();
		$levels = $user->user_levels($id);
		if (!empty($levels)) {
			$levels = maybe_unserialize($levels->access_level);
			$lasts = array();
			if (is_array($levels)) {
				$i = 0;
				$ordered_levels = array();
				foreach ($levels as $level) {
					$ordered_levels[] = $level;
					$order = new ID_Member_Order(null, $id, $level);
					$last = $order->get_last_order();
					if (!empty($last)) {
						$lasts[$i]['e_date'] = $last->e_date;
						$lasts[$i]['order_date'] = $last->order_date;
						$lasts[$i]['id'] = $last->id;
					}
					$i++;
				}
				$levels = $ordered_levels;
			}
			if ($levels == null) {
				$levels = 0;
			}
		}
		else {
			$levels = 0;
			$lasts = array();
			//echo 0;
		}
		print_r(json_encode(array('levels' => $levels, 'lasts' => $lasts)));
	}
	else {
		echo 0;
	}
	exit();
}

add_action('wp_ajax_idmember_edit_user', 'idmember_edit_user');
add_action('wp_ajax_nopriv_idmember_edit_user', 'idmember_edit_user');

function idmember_edit_profile() {
	if (isset($_POST['Userdata'])) {
		$userdata_array = $_POST['Userdata'];
		do_action('idc_edit_profile_before', $userdata_array);
		// need to get user ID
		$user_id = $userdata_array['id'];
		$user = array('ID' => $user_id, 
				'user_email' => $userdata_array['user_email'], 
				'first_name' => (isset($userdata_array['first_name']) ? $userdata_array['first_name'] : ''), 
				'last_name' => (isset($userdata_array['last_name']) ? $userdata_array['last_name'] : ''),
				'display_name' => $userdata_array['display_name'],
				'description' => (isset($userdata_array['description']) ? $userdata_array['description'] : ''),
				'user_url' => (isset($userdata_array['user_url']) ? $userdata_array['user_url'] : '')
				);
		$update_user = wp_update_user($user);
		update_user_meta($user_id, 'twitter', $userdata_array['twitter']);
		update_user_meta($user_id, 'facebook', $userdata_array['facebook']);
		update_user_meta($user_id, 'google', $userdata_array['google']);
		if (isset($userdata_array['block_purchasing'])) {
			$block_purchasing = absint($userdata_array['block_purchasing']);
		}
		else {
			$block_purchasing = 0;
		}
		update_user_meta($user_id, 'block_purchasing', $block_purchasing);
		do_action('idc_edit_profile_after', $userdata_array);
	}
	exit;
}

add_action('wp_ajax_idmember_edit_profile', 'idmember_edit_profile');
add_action('wp_ajax_nopriv_idmember_edit_profile', 'idmember_edit_profile');

function idmember_save_user() {
	if (isset($_POST['action']) && $_POST['action'] == 'idmember_save_user') {
		$id = $_POST['ID'];
		$levels = $_POST['Levels'];
		$date = date('Y-m-d H:i:s');
		if (isset($_POST['Dates'])) {
			$dates = $_POST['Dates'];
		}
		$level_array = array();
		$user = new ID_Member();
		$match = $user->match_user($id);
		$have_levels = false;
		if (!empty($match)) {
			$have_match = true;
			$current_levels = $match->access_level;
			if (isset($current_levels)) {
				$old_levels = unserialize($current_levels);
				if (is_array($old_levels)) {
					$have_levels = true;
				}
			}
		}
		else {
			// add empty user first so we can ensure credits post
			$user_vars = array('user_id' => $id,
				'level' => array(),
				'data' => array());
			$new_user = $user->add_user($user_vars);
		}
		foreach ($levels as $level) {
			if (isset($level['level']) && isset($level['value'])) {
				if($level['value']=='true') { //Check if value is set to true
					$level_array[] = $level['level'];
					$order = new ID_Member_Order(null, $id, $level['level']);
					$check_order = $order->get_last_order();

					if ($have_levels) {
						if (!in_array($level['level'], $old_levels)) {
							$add_order = $order->add_order();
						}
					}

					// old function - led to duplicate orders
					/*
					if (empty($check_order)) {
						$add_order = $order->add_order();
					}
				
					// I don't think this is a possible outcome, need to examine
					else if ($check_order->status == 'active') {
						// order is still active so we should update
						if (isset($have_levels)) {
							// old levels existed
							if (!in_array($level['level'], $old_levels)) {
								// this level wasn't in the old levels, we need to reactivate
								$update = new ID_Member_Order($check_order->id, $id, $level, null, $check_order->transaction_id);
								$update_order = $update->update_order();
							}
						}
					}
					*/

					else {
						// order is cancelled add new
						$add_order = $order->add_order();
					}
				}
			}
		}
		if (isset($have_match) && isset($have_levels)) {
			$dif = array_diff($old_levels, $level_array);
			if (!empty($dif)) {
				foreach ($dif as $dropped) {
					$order = new ID_Member_Order(null, $id, $dropped);
					$last = $order->get_last_order();
					$order = new ID_Member_Order($last->id);
					$order->cancel_status();
				}
			}
			$update = $user->save_user($id, $level_array);
		}
		else {
			$update = $user->save_user($id, $level_array);
		}
		
		if (isset($dates)) {
			foreach ($dates as $date) {
				$e_date = $date['date'];
				$oid = $date['id'];
				$update_dates = ID_Member_Order::update_order_date($oid, $e_date);
			}
		}
	}
	else {
		echo 0;
	}
	exit();
}

add_action('wp_ajax_idmember_save_user', 'idmember_save_user');
add_action('wp_ajax_nopriv_idmember_save_user', 'idmember_save_user');

function admin_edit_subscription() {
	global $stripe_api_version;
	// subscription management
	if (isset($_POST['user_id'])) {
		$user_id = absint($_POST['user_id']);
		$show_subscriptions = false;
		$settings = get_option('memberdeck_gateways');
		if (isset($settings)) {
			$es = $settings['es'];
			if ($es == 1) {
				$customer_id = customer_id_ajax($user_id);
				if (!empty($customer_id)) {
					$show_subscriptions = true;
				}
			}
		}

		if ($show_subscriptions) {
			$sk = idc_stripe_sk();
			if (!class_exists('Stripe')) {
				require_once 'lib/stripe-php-4.2.0/init.php';
			}
			\Stripe\Stripe::setApiKey($sk);
			\Stripe\Stripe::setApiVersion($stripe_api_version);
			try {
				$subscriptions = \Stripe\Customer::retrieve($customer_id)->subscriptions->all();
			}
			catch (\Stripe\Error\InvalidRequest $e) {
				//
				print_r($e);
			}
			catch (Exception $e) {
				//
				print_r($e);
			}
			if (!empty($subscriptions)) {
				$plans = array();
				foreach ($subscriptions->data as $sub) {
					if ($sub->status == 'active') {
						$plan = array();
						$plan_id = $sub->plan->id;
						$plan['id'] = $sub->id;
						$plan['plan_id'] = $plan_id;
						$plans[] = $plan;
					}
				}
				print_r(json_encode($plans));
			}
		}
	}
	exit;
}

add_action('wp_ajax_admin_edit_subscription', 'admin_edit_subscription');
add_action('wp_ajax_nopriv_admin_edit_subscription', 'admin_edit_subscription');

function idmember_credit_total() {
	if (isset($_POST['ID'])) {
		$user_id = absint($_POST['ID']);
		$user = new ID_Member($user_id);
		$credits = $user->get_user_credits();
		echo $credits;
	}
	exit;
}

add_action('wp_ajax_idmember_credit_total', 'idmember_credit_total');
add_action('wp_ajax_nopriv_idmember_credit_total', 'idmember_credit_total');

function idmember_save_credits() {
	if (isset($_POST['ID'])) {
		$user_id = absint($_POST['ID']);
	}
	if (isset($_POST['Credits'])) {
		$credits = absint($_POST['Credits']);
	}
	if (isset($user_id) && isset($credits)) {
		$member = new ID_Member($user_id);
		$set = $member->set_credits($user_id, $credits);
	}
	exit;
}

add_action('wp_ajax_idmember_save_credits', 'idmember_save_credits');

function idmember_create_customer() {
	// this manages payments via the purchase form and instant checkout
	if (isset($_POST['Token'])) {
		do_action('idc_create_customer', $_POST);
		global $crowdfunding;
		global $stripe_api_version;
		$token = $_POST['Token'];
		$customer = $_POST['Customer'];
		$txn_type = $_POST['txnType'];
		$renewable = ((isset($_POST['Renewable'])) ? $_POST['Renewable'] : '');
		$pwyw_price = ((isset($_POST['PWYW'])) ? sanitize_text_field($_POST['PWYW']) : '');
		$product_id = absint(sanitize_text_field($customer['product_id']));
		$settings = get_option('memberdeck_gateways');
		$stripe_currency = 'USD';
		if (isset($_POST['Card'])) {
			$cc_number = esc_attr($_POST['Card']);
		}
		if (isset($_POST['Expiry'])) {
			$cc_expiry = esc_attr($_POST['Expiry']);
		}
		if (isset($_POST['CCode'])) {
			$cc_code = esc_attr($_POST['CCode']);
		}
		if (!empty($settings)) {
			if (is_array($settings)) {
				$mc = (isset($settings['manual_checkout']) ? $settings['manual_checkout'] : 0);
				$test = (isset($settings['test']) ? $settings['test'] : 0);
				$sk = (isset($settings['sk']) ? $settings['sk'] : '');
				$tsk = (isset($settings['tsk']) ? $settings['tsk'] : '');
				$es = (isset($settings['es']) ? $settings['es'] : 0);
				$esc = (isset($settings['esc']) ? $settings['esc'] : 0);
				// $ecb = (isset($settings['ecb']) ? $settings['ecb'] : 0); hard-setting it to 0 for already activated installs
				$ecb = 0;
				$eauthnet = (isset($settings['eauthnet']) ? $settings['eauthnet'] : 0);
				if (!empty($settings['stripe_currency'])) {
					$stripe_currency = $settings['stripe_currency'];
				}
				if (isset($settings['efd']) && $settings['efd']) {
					$gateway_id = $settings['gateway_id'];
					$fd_pw = $settings['fd_pw'];
					$key_id = $settings['key_id'];
					$hmac = $settings['hmac'];
					$efd = $settings['efd'];
				}
			}
		}
		if (function_exists('is_id_pro') && is_id_pro()) {
			$settings = get_option('memberdeck_gateways');
			if (!empty($settings)) {
				if (is_array($settings)) {
					$esc = $settings['esc'];
					if ($esc == '1') {
						$check_claim = get_option('md_level_'.$product_id.'_owner');
						if (!empty($check_claim)) {
							$md_sc_creds = get_sc_params($check_claim);
							if (!empty($md_sc_creds)) {
								//echo 'using sc';
								$sc_accesstoken = $md_sc_creds->access_token;
								$sc_account = $md_sc_creds->stripe_user_id;
							}
						}
					}
				}
			}
		}
		$source = $_POST['Source'];

		if (empty($source)) {
			if ($efd == 1) {
				$source = 'fd';
				$endpoint = 'https://api.globalgatewaye4.firstdata.com/transaction/v12';
				$wsdl = 'https://api.globalgatewaye4.firstdata.com/transaction/v12/wsdl';
			}
			else if ($eauthnet == 1) {
				$source = 'authorize.net';
			}
			else {
				$source = 'stripe';
				// this won't work because we are ajax
				$customer_id = customer_id();
			}
		}
		else {
			// source isn't set - this shouldn't happen
			if ($source == 'stripe') {
				// this won't work because we are ajax
				$customer_id = customer_id();
			}
			else if ($source == 'fd') {
				// we don't charge fees for fd
			}
			else if ($source == 'mc') {
				// 
			}
			$customer_id = apply_filters('idc_customer_id_checkout', (isset($customer_id) ? $customer_id : ''), $source, null, $_POST['Fields']);
		}

		if ($source == 'stripe') {
			if (!class_exists('Stripe')) {
				require_once 'lib/stripe-php-4.2.0/init.php';
			}
			if ($test == '1') {
				$apikey = $tsk;
				\Stripe\Stripe::setApiKey($tsk);
				\Stripe\Stripe::setApiVersion($stripe_api_version);
			}
			else {
				$apikey = $sk;
				\Stripe\Stripe::setApiKey($sk);
				\Stripe\Stripe::setApiVersion($stripe_api_version);
			}
		}
		else if ($source == 'fd') {
			// we can pass a reference number as description
			// transarmor token is what we want for tokenizing cards
			// Authorization_Num is used for preauth
			// Need API 13 for Customer Info
			if ($test == 1) {
				$endpoint = 'https://api.demo.globalgatewaye4.firstdata.com/transaction/v13';
				$wsdl = 'https://api.demo.globalgatewaye4.firstdata.com/transaction/v12/wsdl';
			} else {
				$endpoint = 'https://api.globalgatewaye4.firstdata.com/transaction/v12';
				$wsdl = 'https://api.globalgatewaye4.firstdata.com/transaction/v12/wsdl';
			}
		}
		else if ($source == "authorize.net") {
			// Requiring the library of Authorize.Net
			require('lib/AuthorizeNet/sdk-php-master/vendor/autoload.php');
			define("AUTHORIZENET_API_LOGIN_ID", $settings['auth_login_id']);
			define("AUTHORIZENET_TRANSACTION_KEY", $settings['auth_transaction_key']);
			if ($test == '1') {
				define("AUTHORIZENET_SANDBOX", true);
			} else {
				define("AUTHORIZENET_SANDBOX", false);
			}
			$ID_Authorize_Net = new ID_Authorize_Net($settings['auth_login_id'], $settings['auth_transaction_key'], $test);
		}
		else {
			// 
		}
		
		$access_levels = array($product_id);
		$level_data = ID_Member_Level::get_level($product_id);
		// If product is renewable, then we need to use renewable price, and avoid any pwyw we might have
		if ($renewable) {
			$level_data->level_price = $level_data->renewal_price;
			$ignore_upgrade = true;
		}
		else {
			if (isset($pwyw_price) && $pwyw_price > 0) {
				if ($level_data->product_type == 'purchase') {
					if ($pwyw_price > $level_data->level_price) {
						$level_data->level_price = $pwyw_price;
						// Setting variable so that it's known level_price is pwyw price
						$ignore_upgrade = true;
					}
				}
				else {
					$level_data->level_price = $pwyw_price;
					$ignore_upgrade = true;
				}
			}
		}

		$fname = sanitize_text_field($customer['first_name']);
		$lname = sanitize_text_field($customer['last_name']);
		if (isset($customer['email'])) {
			$email = sanitize_email($customer['email']);
		}
		else {
			// they have used 1cc or some other mechanism and we don't have their email
			if (is_user_logged_in()) {
				$current_user = wp_get_current_user();
				$email = $current_user->user_email;
			}
		}
		$pw = null;
		if (!empty($customer['pw'])) {
			$pw = sanitize_text_field($customer['pw']);
		}
		$member = new ID_Member();
		$check_user = $member->check_user($email);

		$level_data->level_price = apply_filters( 'idc_checkout_level_price', $level_data->level_price, $product_id, ((!empty($check_user)) ? $check_user->ID : ''), ((isset($ignore_upgrade)) ? $ignore_upgrade : false) );
		$level_data = apply_filters('idc_level_data', $level_data, 'checkout');
		$recurring_type = $level_data->recurring_type;
		if ($level_data->level_type == 'recurring') {
			$plan = $level_data->plan;
			if ($recurring_type == 'weekly') {
				// weekly
				$exp = strtotime('+1 week');
			}
			else if ($recurring_type == 'monthly') {
				// monthly
				$exp = strtotime('+1 month');
			}
			else {
				// annually
				$exp = strtotime('+1 years');
				
			}
			$e_date = date('Y-m-d H:i:s', $exp);
			$recurring = true;
			$interval = $level_data->recurring_type;
			// check for limits
			if ($level_data->limit_term) {
				$term_length = $level_data->term_length;
			}
		}
		else if ($level_data->level_type == 'lifetime') {
			$e_date = null;
			$recurring = false;
		}
		else {
			$e_date = idc_set_order_edate($level_data);
			$recurring = false;
		}
		if (!empty($check_user)) {
			// echo 'check user is set'."\n";
			// We have a match so we need to add this level to the array of access levels
			// I also need to re-use our Stripe customer somehow
			$user_id = $check_user->ID;
			if ($source == 'stripe') {
				// this 2nd attempt should work
				$customer_id = customer_id_ajax($user_id);
			}
			else if ($source == 'fd') {
				// this 2nd attempt should work
				$fd_card_details = fd_customer_id_ajax($user_id);
				if (!empty($fd_card_details)) {
					$fd_token = $fd_card_details['fd_token'];
					$customer_id = $fd_token;
					$cc_expiry = $fd_card_details['cc_expiry'];
					$credit_card_type = $fd_card_details['credit_card_type'];
					$cardholder_name = $fd_card_details['cardholder_name'];
				}
			}
			else if ($source == 'authorize.net') {
				// echo "getting customer id from meta\n";
				$authorize_customer_id = authorizenet_customer_id_ajax($user_id);
				if (!empty($authorize_customer_id)) {
					$customer_id = $authorize_customer_id['authorizenet_payment_profile_id'];
					$customerProfileId = $authorize_customer_id['authorizenet_profile_id'];
				} else {
					$customer_id = '';
					$customerProfileId = '';
				}
				$ID_Authorize_Net->set_profile_ids($customerProfileId, $customer_id);
				// echo "from meta; customer_id: ". $customer_id. ", customerProfileId: ".$customerProfileId."\n";
			}
			// Same filter called for 2nd attempt, when $user_id is there
			$customer_id = apply_filters('idc_customer_id_checkout', $customer_id, $source, $user_id, $_POST['Fields']);
			$match_user = $member->match_user($user_id);
			if (!isset($match_user->data) && empty($customer_id)) {
				// no customer ID exists
				if ($source == 'stripe') {
					// this means we need to create a customer id with stripe
					// echo 'is new customer'."\n";
					try {
						$newcust = \Stripe\Customer::create(array(
						'description' => $fname . ' ' . $lname,
						'email' => $email,
						'source' => $token));
						//print_r($newcust);
						$custid = $newcust->id;
						$insert = true;
					}
					catch (\Stripe\Error\Card $e) {
						// Card was declined
						$jsonbody = $e->getJsonBody();
						$message = $jsonbody['error']['message'].' '.__LINE__;
						print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
						exit;
					}
					catch (\Stripe\Error\InvalidRequest $e) {
						$jsonbody = $e->getJsonBody();
						$message = $jsonbody['error']['message'].' '.__LINE__;
						print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
						exit;
					}
				}
				else if ($source == 'fd') {
					// Create a Transarmor Multi-Use Token
					$data = array('gateway_id' => $gateway_id,
						'password' => $fd_pw,
						'transaction_type' => '01',
						'amount' => 0,
						'cardholder_name' => $fname.' '.$lname,
						'cc_number' => $cc_number,
						'cc_expiry' => $cc_expiry);
					$data_string = json_encode($data);

					$gge4Date = strftime("%Y-%m-%dT%H:%M:%S", time() - (int) substr(date('O'), 0, 3)*60*60) . 'Z';
					$digest = sha1($data_string);
					$size = sizeof($data_string);

					$method = 'POST';
					$content_type = 'application/json';

					$hashstr = $method."\n".$content_type."\n".$digest."\n".$gge4Date."\n".'/transaction/v13';

					$authstr = 'GGE4_API ' . $key_id . ':' . base64_encode(hash_hmac("sha1", $hashstr, $hmac, true));

					$headers = array('Content-Type: '.$content_type,
						'X-GGe4-Content-SHA1: '.$digest,
						'Authorization: '.$authstr,
						'X-GGe4-Date: '.$gge4Date,
						'charset=UTF-8',
						'Accept: '.$content_type
					);

					$ch = curl_init($endpoint);
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					if ($test == 1) {
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
					}
					curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

					$res = curl_exec($ch);

					if (curl_errno($ch)) {
						//echo 'error:' . curl_error($c);
					}
					else {
						//print_r($res);
						$res_string = json_decode($res);
						//print_r($res_string);
						if ($res_string->transaction_approved == 1) {
							// it is approved
							$txn_id = $res_string->authorization_num;
							$fd_token = $res_string->transarmor_token;
							$card_id = $fd_token;
							$custid = $fd_token;
							$cc_expiry = $res_string->cc_expiry;
							$credit_card_type = $res_string->credit_card_type;
							$fd_card_details = array('cc_expiry' => $cc_expiry, 'credit_card_type' => $credit_card_type, 'cardholder_name' => $fname.' '.$lname);
							$insert = true;
						}
						else{
							print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $res)));
							exit;
						}
					}
				}
				else if ($source == 'authorize.net') {
					// echo "no customer ID exists, so creating a new one\n";
					$request = new AuthorizeNetCIM;
					// Create new customer profile
					$customerProfile = new AuthorizeNetCustomer;
					$customerProfile->merchantCustomerId = time();
					$customerProfile->email = $email;
					// $customerProfile->paymentProfiles = array($paymentProfile);
					$response = $request->createCustomerProfile($customerProfile);
					if ($response->isOk()) {
						$customerProfileId = $response->getCustomerProfileId();

						// Now creating payment profile
						$customerPaymentProfile = new AuthorizeNetPaymentProfile;
						$customerPaymentProfile->billTo->firstName = $fname;
						$customerPaymentProfile->billTo->lastName = $lname;
						$customerPaymentProfile->payment->creditCard->cardNumber = $cc_number;
						$customerPaymentProfile->payment->creditCard->expirationDate = $cc_expiry;
						$customerPaymentProfile->payment->creditCard->cardCode = $cc_code;
						// Action for AVS information addition
						$customerPaymentProfile = apply_filters('idc_authnet_avs_info_add', $customerPaymentProfile, $_POST['Fields'], "3645");
						
						$responsePayment = $request->createCustomerPaymentProfile($customerProfileId, $customerPaymentProfile, apply_filters('idc_authnet_validationmode_select', 'none'));
						if ($responsePayment->isOk()) {
							$customerPaymentProfileId = $responsePayment->getPaymentProfileId();
							$custid = $customerPaymentProfileId;
							$ID_Authorize_Net->set_profile_ids($customerProfileId, $custid);
						}
						else {
							print_r(json_encode(array('response' => $responsePayment->getResultCode(), 'message' => $responsePayment->getMessageText().' '.__LINE__, 'line' => __LINE__)));
							exit();
						}
						$insert = true;
					}
					else {
						print_r(json_encode(array('response' => $response->getResultCode(), 'message' => $response->getMessageText().' '.__LINE__, 'line' => __LINE__)));
						exit();
					}
				}
				else if ($source == 'mc') {
					$insert = true;
				}
				// No customer exists, create a new one
				$custid = apply_filters('idc_create_customer_checkout', (isset($custid) ? $custid : ''), $user_id, array(
					"fname" => $fname,
					"lname" => $lname,
					"email" => $email,
					"cc_number" => ((isset($cc_number)) ? $cc_number : ''),
					"cc_expiry" => ((isset($cc_expiry)) ? $cc_expiry : ''),
					"cc_code" => ((isset($cc_code)) ? $cc_code : ''),
					"settings" => $settings,
					"source" => $source,
					"extra_fields" => $_POST['Fields'],
					"insert" => false
				));
			}
			else {
				// we have a customer ID
				// this is the point at which we check for add card vs re-use
				if (!empty($customer_id)) {
					// echo 'cust id not empty and equal to '.$customer_id."\n";
					$custid = $customer_id;
					// there is a customer id saved, so we have the option to use it
					if (!empty($token) && $token == 'customer') {
						// they used 1cc
						//echo 'option 1';
						// echo "token is 'customer'\n";
					}
					else {
						// they entered new details, let's add this card to their account
						// need to make sure this card doesn't already exist
						// echo 'option 2'."\n";
						$use_token = true;
						$in_acct = false;
						// Check if card exists, if not add into customer's account
						$custid = apply_filters('idc_new_customer_card_check_checkout', $custid, $user_id, array(
							"fname" => $fname,
							"lname" => $lname,
							"email" => $email,
							"cc_number" => (isset($cc_number) ? $cc_number : ''),
							"cc_expiry" => (isset($cc_expiry) ? $cc_expiry : ''),
							"cc_code" => (isset($cc_code) ? $cc_code : ''),
							"settings" => $settings,
							"source" => $source,
							"extra_fields" => $_POST['Fields']
						));
						if ($source == 'stripe') {
							//echo 'source is stripe';
							try {
								$token_obj = \Stripe\Token::retrieve($token);
							}
							catch (\Stripe\Error\InvalidRequest $e) {
								//print_r($e);
								$jsonbody = $e->getJsonBody();
								$message = $jsonbody['error']['message'].' '.__LINE__;
								//$message = 1;
								print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
								exit;
							}
							try {
								//$cards = \Stripe\Customer::retrieve($custid)->cards->all();
								$cards = \Stripe\Customer::retrieve($custid)->sources->all(array('object' => 'card'));
							}
							catch (Exception $e) {
								// could not retrieve a customer, so we need to create one
								//$message = $e->json_body['error']['message'];
								//print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
								//exit;
								$new_customer = true;
								$use_token = false;
							}
							if (isset($cards) && isset($token_obj)) {
								$list = $cards['data'];
								$last4 = $token_obj->card->last4;
								$exp_year = $token_obj->card->exp_year;
								foreach ($list as $card) {
									if ($last4 == $card->last4 && $exp_year == $card->exp_year) {
										// card exists, we don't need to create it
										$in_acct = true;
										$card_id = $card->id;
										break;
									}
								}
							}
							else {
								$card_id = $token;
							}
							if ($in_acct == false) {
								//echo 'no match';
								try {
									$cu = \Stripe\Customer::retrieve($customer_id);
								}
								catch (Exception $e) {
									//$message = $e->json_body['error']['message'];
									//print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
									//exit;
									$new_customer = true;
									$use_token = false;
								}
								if (isset($cu)) {
									try {
										//$card_object = $cu->cards->create(array('card' => $token));
										$card_object = $cu->sources->create(array('source' => $token));
										$card_id = $card_object->id;
									}
									catch (\Stripe\Error\Card $e) {
										$new_customer = true;
										// Card was declined
										//$message = $e->jsonBody['error']['message'].' '.__LINE__;
										//print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
										//exit;
									}
									catch (\Stripe\Error\InvalidRequest $e) {
										$new_customer = true;
										// Card was declined
										//$message = $e->jsonBody['error']['message'].' '.__LINE__;
										//print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
										//exit;
									}
								}
							}
						}
						else if ($source == 'fd') {
							// we create a new token and overwrite old one
							// Create a Transarmor Multi-Use Token
							$data = array('gateway_id' => $gateway_id,
								'password' => $fd_pw,
								'transaction_type' => '01',
								'amount' => 0,
								'cardholder_name' => $fname.' '.$lname,
								'cc_number' => $cc_number,
								'cc_expiry' => $cc_expiry);
							$data_string = json_encode($data);

							$gge4Date = strftime("%Y-%m-%dT%H:%M:%S", time() - (int) substr(date('O'), 0, 3)*60*60) . 'Z';
							$digest = sha1($data_string);
							$size = sizeof($data_string);

							$method = 'POST';
							$content_type = 'application/json';

							$hashstr = $method."\n".$content_type."\n".$digest."\n".$gge4Date."\n".'/transaction/v13';

							$authstr = 'GGE4_API ' . $key_id . ':' . base64_encode(hash_hmac("sha1", $hashstr, $hmac, true));

							$headers = array('Content-Type: '.$content_type,
								'X-GGe4-Content-SHA1: '.$digest,
								'Authorization: '.$authstr,
								'X-GGe4-Date: '.$gge4Date,
								'charset=UTF-8',
								'Accept: '.$content_type
							);

							$ch = curl_init($endpoint);
							curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
							curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							if ($test == 1) {
								curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
							}
							curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

							$res = curl_exec($ch);

							if (curl_errno($ch)) {
								//echo 'error:' . curl_error($c);
							}
							else {
								//print_r($res);
								$res_string = json_decode($res);
								//print_r($res_string);
								if ($res_string->transaction_approved == 1) {
									// it is approved
									$txn_id = $res_string->authorization_num;
									$fd_token = $res_string->transarmor_token;
									$card_id = $fd_token;
									$custid = $fd_token;
									$cc_expiry = $res_string->cc_expiry;
									$credit_card_type = $res_string->credit_card_type;
									$fd_card_details = array('cc_expiry' => $cc_expiry, 'credit_card_type' => $credit_card_type, 'cardholder_name' => $fname.' '.$lname);
								}
								else{
									print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $res)));
									exit;
								}
							}
						}
						else if ($source == 'authorize.net') {
							// Check if card already exists, get its payment profile id, if not, make a new payment profile
							$ID_Authorize_Net->check_payment_profile_exists($fname, $lname, $email, $cc_number, $cc_expiry, $cc_code, $_POST['Fields']);
							$custid = $ID_Authorize_Net->get_payment_profile_id();
							$customerProfileId = $ID_Authorize_Net->get_profile_id();
						}
					}
				}
				else {
					// echo __line__.'. new cust'."\n";
					$new_customer = true;
				}
				if (isset($new_customer)) {
					// we didn't find a custid so we have to make one
					// echo __line__.". new customer\n";
					if ($source == 'stripe') {
						try {
							$newcust = \Stripe\Customer::create(array(
								'description' => $fname . ' ' . $lname,
								'email' => $email,
								'source' => $token));
								$custid = $newcust->id;
								//print_r($newcust);
						}
						catch (\Stripe\Error\Card $e) {
							// Card was declined
							$jsonbody = $e->getJsonBody();
							$message = $jsonbody['error']['message'].' '.__LINE__;
							print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
							exit;
						}
						catch (\Stripe\Error\InvalidRequest $e) {
							$jsonbody = $e->getJsonBody();
							$message = $jsonbody['error']['message'].' '.__LINE__;
							print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
							exit;
						}
					}
					else if ($source == 'fd') {
						// we create a new token and overwrite old one
						// Create a Transarmor Multi-Use Token
						$data = array('gateway_id' => $gateway_id,
							'password' => $fd_pw,
							'transaction_type' => '01',
							'amount' => 0,
							'cardholder_name' => $fname.' '.$lname,
							'cc_number' => $cc_number,
							'cc_expiry' => $cc_expiry);
						$data_string = json_encode($data);

						$gge4Date = strftime("%Y-%m-%dT%H:%M:%S", time() - (int) substr(date('O'), 0, 3)*60*60) . 'Z';
						$digest = sha1($data_string);
						$size = sizeof($data_string);

						$method = 'POST';
						$content_type = 'application/json';

						$hashstr = $method."\n".$content_type."\n".$digest."\n".$gge4Date."\n".'/transaction/v13';

						$authstr = 'GGE4_API ' . $key_id . ':' . base64_encode(hash_hmac("sha1", $hashstr, $hmac, true));

						$headers = array('Content-Type: '.$content_type,
							'X-GGe4-Content-SHA1: '.$digest,
							'Authorization: '.$authstr,
							'X-GGe4-Date: '.$gge4Date,
							'charset=UTF-8',
							'Accept: '.$content_type
						);

						$ch = curl_init($endpoint);
						curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						if ($test == 1) {
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
						}
						curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

						$res = curl_exec($ch);

						if (curl_errno($ch)) {
							//echo 'error:' . curl_error($c);
						}
						else {
							//print_r($res);
							$res_string = json_decode($res);
							//print_r($res_string);
							if ($res_string->transaction_approved == 1) {
								// it is approved
								$txn_id = $res_string->authorization_num;
								$fd_token = $res_string->transarmor_token;
								$custid = $fd_token;
								$cc_expiry = $res_string->cc_expiry;
								$credit_card_type = $res_string->credit_card_type;
								$fd_card_details = array('cc_expiry' => $cc_expiry, 'credit_card_type' => $credit_card_type, 'cardholder_name' => $fname.' '.$lname);
							}
							else{
								print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $res)));
								exit;
							}
						}
					}
					else if ($source == 'authorize.net') {
						// echo __LINE__.": we didn't find a custid so we have to make one\n";
						$request = new AuthorizeNetCIM;
						// Create new customer profile
						$customerProfile = new AuthorizeNetCustomer;
						$customerProfile->merchantCustomerId = time();
						$customerProfile->email = $email;
						// $customerProfile->paymentProfiles = array($paymentProfile);
						$response = $request->createCustomerProfile($customerProfile);
						if ($response->isOk()) {
							$customerProfileId = $response->getCustomerProfileId();

							// Now creating payment profile
							$customerPaymentProfile = new AuthorizeNetPaymentProfile;
							$customerPaymentProfile->billTo->firstName = $fname;
							$customerPaymentProfile->billTo->lastName = $lname;
							$customerPaymentProfile->payment->creditCard->cardNumber = $cc_number;
							$customerPaymentProfile->payment->creditCard->expirationDate = $cc_expiry;
							$customerPaymentProfile->payment->creditCard->cardCode = $cc_code;
							// Action for AVS information addition
							$customerPaymentProfile = apply_filters('idc_authnet_avs_info_add', $customerPaymentProfile, $_POST['Fields'], "4062");

							$responsePayment = $request->createCustomerPaymentProfile($customerProfileId, $customerPaymentProfile, apply_filters('idc_authnet_validationmode_select', 'none'));
							// echo "responsePayment: "; print_r($responsePayment);
							if ($responsePayment->isOk()) {
								$customerPaymentProfileId = $responsePayment->getPaymentProfileId();
								$custid = $customerPaymentProfileId;
								$ID_Authorize_Net->set_profile_ids($customerProfileId, $custid);
							}
							else {
								print_r(json_encode(array('response' => $response->getResultCode(), 'message' => $response->getMessageText().' '.__LINE__, "line" => __LINE__)));
								exit;
							}
						}
						else {
							print_r(json_encode(array('response' => $response->getResultCode(), 'message' => $response->getMessageText().' '.__LINE__, "line" => __LINE__)));
							exit;
						}
					}
					// No customer exists, create a new one
					$custid = apply_filters('idc_create_customer_checkout', (isset($custid) ? $custid : ''), $user_id, array(
						"fname" => $fname,  
						"lname" => $lname, 
						"email" => $email, 
						"cc_number" => ((isset($cc_number)) ? $cc_number : ''), 
						"cc_expiry" => ((isset($cc_expiry)) ? $cc_expiry : ''), 
						"cc_code" => ((isset($cc_code)) ? $cc_code : ''), 
						"settings" => $settings, 
						"source" => $source,
						"extra_fields" => $_POST['Fields'],
						"called_line" => __line__
					));
				}
			}	
		}
		else {
			// this is a new user
			$newuser = true;
			if ($source == 'stripe') {
				// brand new user so we can insert with just this level
				// after we create a new Stripe customer
				try {
					$newcust = \Stripe\Customer::create(array(
						'description' => $fname . ' ' . $lname,
						'email' => $email,
						'source' => $token));
					//print_r($newcust);
					$custid = $newcust->id;
					$newuser = true;
				}
				catch (\Stripe\Error\Card $e) {
					// Card was declined
					$jsonbody = $e->getJsonBody();
					$message = $jsonbody['error']['message'].' '.__LINE__;
					print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
					exit;
				}
				catch (\Stripe\Error\InvalidRequest $e) {
					$jsonbody = $e->getJsonBody();
					$message = $jsonbody['error']['message'].' '.__LINE__;
					print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
					exit;
				}
			}
			else if ($source == 'fd') {
				// we create a new token
				// Create a Transarmor Multi-Use Token
				$data = array('gateway_id' => $gateway_id,
					'password' => $fd_pw,
					'transaction_type' => '01',
					'amount' => 0,
					'cardholder_name' => $fname.' '.$lname,
					'cc_number' => $cc_number,
					'cc_expiry' => $cc_expiry);
				$data_string = json_encode($data);

				$gge4Date = strftime("%Y-%m-%dT%H:%M:%S", time() - (int) substr(date('O'), 0, 3)*60*60) . 'Z';
				$digest = sha1($data_string);
				$size = sizeof($data_string);

				$method = 'POST';
				$content_type = 'application/json';

				$hashstr = $method."\n".$content_type."\n".$digest."\n".$gge4Date."\n".'/transaction/v13';

				$authstr = 'GGE4_API ' . $key_id . ':' . base64_encode(hash_hmac("sha1", $hashstr, $hmac, true));

				$headers = array('Content-Type: '.$content_type,
					'X-GGe4-Content-SHA1: '.$digest,
					'Authorization: '.$authstr,
					'X-GGe4-Date: '.$gge4Date,
					'charset=UTF-8',
					'Accept: '.$content_type
				);

				$ch = curl_init($endpoint);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				if ($test == 1) {
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				}
				curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

				$res = curl_exec($ch);

				if (curl_errno($ch)) {
					//echo 'error:' . curl_error($c);
				}
				else {
					//print_r($res);
					$res_string = json_decode($res);
					//print_r($res_string);
					if (!empty($res_string) && $res_string->transaction_approved == 1) {
						// it is approved
						$txn_id = $res_string->authorization_num;
						$fd_token = $res_string->transarmor_token;
						$newcust = $fd_token;
						$custid = $fd_token;
						$cc_expiry = $res_string->cc_expiry;
						$credit_card_type = $res_string->credit_card_type;
						$fd_card_details = array('cc_expiry' => $cc_expiry, 'credit_card_type' => $credit_card_type, 'cardholder_name' => $fname.' '.$lname);
						$newuser = true;
					}
					else{
						print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $res)));
						exit;
					}
				}
			}
			else if ($source == 'authorize.net') {
				// echo "this is a new user, creating profile\n";
				$request = new AuthorizeNetCIM;
				// Create new customer profile
				$customerProfile = new AuthorizeNetCustomer;
				$customerProfile->merchantCustomerId = time();
				$customerProfile->email = $email;
				// $customerProfile->paymentProfiles = array($paymentProfile);
				$response = $request->createCustomerProfile($customerProfile);
				if ($response->isOk()) {
					$customerProfileId = $response->getCustomerProfileId();

					// Now creating payment profile
					$customerPaymentProfile = new AuthorizeNetPaymentProfile;
					$customerPaymentProfile->billTo->firstName = $fname;
					$customerPaymentProfile->billTo->lastName = $lname;
					$customerPaymentProfile->payment->creditCard->cardNumber = $cc_number;
					$customerPaymentProfile->payment->creditCard->expirationDate = $cc_expiry;
					$customerPaymentProfile->payment->creditCard->cardCode = $cc_code;
					// Action for AVS information addition
					$customerPaymentProfile = apply_filters('idc_authnet_avs_info_add', $customerPaymentProfile, $_POST['Fields'], __LINE__);
					
					$responsePayment = $request->createCustomerPaymentProfile($customerProfileId, $customerPaymentProfile, apply_filters('idc_authnet_validationmode_select', 'none'));
					if ($responsePayment->isOk()) {
						$customerPaymentProfileId = $responsePayment->getPaymentProfileId();
						$custid = $customerPaymentProfileId;
						$ID_Authorize_Net->set_profile_ids($customerProfileId, $custid);
						$newuser = true;
					}
					else {
						print_r(json_encode(array('response' => $responsePayment->getResultCode(), 'message' => $responsePayment->getMessageText().' '.__LINE__, "line" => __LINE__)));
						exit();
					}
				}
				else {
					print_r(json_encode(array('response' => $response->getResultCode(), 'message' => $response->getMessageText().' '.__LINE__, "line" => __LINE__)));
					exit();
				}
			}
			// New User, create a new Customer
			$custid = apply_filters('idc_create_customer_checkout', (isset($custid) ? $custid : ''), $user_id='', array(
				"fname" => $fname, 
				"lname" => $lname, 
				"email" => $email, 
				"cc_number" => ((isset($cc_number)) ? $cc_number : ''), 
				"cc_expiry" => ((isset($cc_expiry)) ? $cc_expiry : ''), 
				"cc_code" => ((isset($cc_code)) ? $cc_code : ''), 
				"settings" => $settings, 
				"source" => $source,
				"extra_fields" => $_POST['Fields'],
				"called_line" => __line__
			));
		}
		if ((isset($custid) && !empty($custid)) || $source == 'mc') {
			// echo 'custid is set to '.$custid.''."\n";
			// now we need to charge the customer
			if (!isset($recurring) || $recurring == false) {
				// echo 'not recurring';
				if (empty($txn_type)) {
					if (!empty($level_data->txn_type)) {
						$txn_type = $level_data->txn_type;
					}
					else {
						$txn_type = 'capture';
					}
				}
				if ($txn_type == 'capture') {
					if (isset($use_token) && $use_token) {
						// echo "use token\n";
						if ($source == 'stripe') {
							//try {
								$price = str_replace(',', '', $level_data->level_price) * 100;
								if (!empty($sc_accesstoken)) {
									$fee = 0;
									$sc_settings = get_option('md_sc_settings');
									if (!empty($sc_settings)) {
										if (!is_array($sc_settings)) {
											$sc_settings = maybe_unserialize($sc_settings);
										}
										if (is_array($sc_settings)) {
											$app_fee = apply_filters('idc_app_fee', $sc_settings['app_fee'], $level_data);
											$fee_type = apply_filters('idc_fee_type', $sc_settings['fee_type'], $level_data);
											$fee = apply_filters('idc_fee_amount', $app_fee, $price, $fee_type, $source);											
										}
									}
									try {
										// generate a new token using shared customer 
										$card_id = \Stripe\Token::create(array(
											"customer" => $custid,
											"card" => $card_id),
											$sc_accesstoken);
									}
									catch (\Stripe\Error\Card $e) {
										// Card was declined
										$jsonbody = $e->getJsonBody();
										$message = $jsonbody['error']['message'].' '.__LINE__;
										print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
										exit;
									}
									catch (\Stripe\Error\InvalidRequest $e) {
										$jsonbody = $e->getJsonBody();
										$message = $jsonbody['error']['message'].' '.__LINE__;
										print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
										exit;
									}
									try {
										$newcharge = \Stripe\Charge::create(array(
										//'customer' => $custid, does not work when using shared customers
										'amount' => $price,
										'source' => $card_id->id,
										'description' => $fname . ' ' . $lname . ' - ' . $level_data->level_name,
										'receipt_email' => $email,
										'currency' => $stripe_currency,
										'application_fee' => $fee),
										$sc_accesstoken);
									}
									catch (\Stripe\Error\Card $e) {
										// Card was declined
										$jsonbody = $e->getJsonBody();
										$message = $jsonbody['error']['message'].' '.__LINE__;
										print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
										exit;
									}
									catch (\Stripe\Error\InvalidRequest $e) {
										$jsonbody = $e->getJsonBody();
										$message = $jsonbody['error']['message'].' '.__LINE__;
										print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
										exit;
									}
								}
								else {
									try {
										$newcharge = \Stripe\Charge::create(array(
										'customer' => $custid,
										'amount' => $price,
										'customer' => $custid,
										'source' => $card_id,
										'description' => $fname . ' ' . $lname . ' - ' . $level_data->level_name,
										'receipt_email' => $email,
										'currency' => $stripe_currency));
									}
									catch (\Stripe\Error\Card $e) {
										// Card was declined
										$jsonbody = $e->getJsonBody();
										$message = $jsonbody['error']['message'].' '.__LINE__;
										print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
										exit;
									}
									catch (\Stripe\Error\InvalidRequest $e) {
										$jsonbody = $e->getJsonBody();
										$message = $jsonbody['error']['message'].' '.__LINE__;
										print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
										exit;
									}
								}
							/*}
							catch (\Stripe\Error\InvalidRequest $e) {
								$message = $e->json_body['error']['message'].' '.__LINE__;
								print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
								exit;
							}*/
						}
						else if ($source == 'fd') {
							// here we would use token to charge the customer
							$data = array('gateway_id' => $gateway_id,
								'password' => $fd_pw,
								'transaction_type' => '00',
								'amount' => $level_data->level_price,
								'cardholder_name' => $fname.' '.$lname,
								'transarmor_token' => $fd_token,
								'credit_card_type' => $credit_card_type,
								'cc_expiry' => $cc_expiry);
							$data_string = json_encode($data);

							$gge4Date = strftime("%Y-%m-%dT%H:%M:%S", time() - (int) substr(date('O'), 0, 3)*60*60) . 'Z';
							$digest = sha1($data_string);
							$size = sizeof($data_string);

							$method = 'POST';
							$content_type = 'application/json';

							$hashstr = $method."\n".$content_type."\n".$digest."\n".$gge4Date."\n".'/transaction/v13';

							$authstr = 'GGE4_API ' . $key_id . ':' . base64_encode(hash_hmac("sha1", $hashstr, $hmac, true));

							$headers = array('Content-Type: '.$content_type,
								'X-GGe4-Content-SHA1: '.$digest,
								'Authorization: '.$authstr,
								'X-GGe4-Date: '.$gge4Date,
								'charset=UTF-8',
								'Accept: '.$content_type
							);
							try {
								$ch = curl_init($endpoint);
								curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
								curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
								if ($test == 1) {
									curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
								}
								curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

								$res = curl_exec($ch);

								if (curl_errno($ch)) {
									//echo 'error:' . curl_error($c);
									print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => curl_error($c), "line" => __LINE__)));
									exit;
								}
								else {
									//print_r($res);
									$res_string = json_decode($res);
									//print_r($res_string);
									if ($res_string->transaction_approved == 1) {
										// it is approved
										$txn_id = $res_string->authorization_num;
										$success = true;
										$fd_card_details['fd_token'] = $fd_token;
										if (isset($user_id)) {
											update_user_meta($user_id, 'fd_card_details', $fd_card_details);
										}
									}
									else{
										print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $res)));
										exit;
									}
								}
							}
							catch (Exception $e) {
								//print_r($e);
								print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $e, "line" => __LINE__)));
								exit;
							}
						}
						else if ($source == 'authorize.net') {
							// Updating the profile address in case of AVS
							$ID_Authorize_Net->update_payment_profile_address($_POST['Fields']);

							$price = str_replace(',', '', $level_data->level_price);
							$transaction = new AuthorizeNetTransaction;
							$transaction->amount = $price;
							$transaction->customerPaymentProfileId = $custid;
							$transaction->customerProfileId = $customerProfileId;
							$transaction->order->invoiceNumber = time();

							$request = new AuthorizeNetCIM;
							$response = $request->createCustomerProfileTransaction('AuthCapture', $transaction);
							if ($response->isOk()) {
								$transactionResponse = $response->getTransactionResponse();
								$txn_id = $transactionResponse->transaction_id;
								$success = true;
								// echo "transaction done\n";
							} else if ($response->isError()) {
								$success = false;
								print_r(json_encode(array('response' => $response->getResultCode(), 'message' => $response->getMessageText().': '.__LINE__)));
								exit;
								// echo "transaction error: ".$response->getMessageText()."\n";
							}
						}
						// Charging using token we have and getting transaction id
						$txn_id = apply_filters('idc_charge_using_token_checkout', ((isset($txn_id)) ? $txn_id : ''), (isset($success) ? $success : false), array(
							"txn_type" => $txn_type, 
							"custid" => $custid, 
							"email" => $email, 
							"card_id" => ((isset($card_id)) ? $card_id : ''), 
							"amount" => $level_data->level_price, 
							"settings" => $settings, 
							"source" => $source,
							"extra_fields" => $_POST['Fields']
						));
					}
					else {
						// echo "do not use token\n";
						if ($source == 'stripe') {
							try {
								$price = str_replace(',', '', $level_data->level_price) * 100;
								if (!empty($sc_accesstoken)) {
									$fee = 0;
									$sc_settings = get_option('md_sc_settings');
									if (!empty($sc_settings)) {
										$sc_settings = maybe_unserialize($sc_settings);
										if (is_array($sc_settings)) {
											$app_fee = apply_filters('idc_app_fee', $sc_settings['app_fee'], $level_data);
											$fee_type = apply_filters('idc_fee_type', $sc_settings['fee_type']);
											$fee = apply_filters('idc_fee_amount', $app_fee, $price, $fee_type, $source);											
										}
									}
									try {
										$card_id = \Stripe\Token::create(array(
											"customer" => $custid),
											$sc_accesstoken);
									}
									catch (\Stripe\Error\Card $e) {
										// Card was declined
										$jsonbody = $e->getJsonBody();
										$message = $jsonbody['error']['message'].' '.__LINE__;
										print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
										exit;
									}
									catch (\Stripe\Error\InvalidRequest $e) {
										$jsonbody = $e->getJsonBody();
										$message = $jsonbody['error']['message'].' '.__LINE__;
										print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
										exit;
									}
									try {
										$newcharge = \Stripe\Charge::create(array(
										//'customer' => $custid,
										'amount' => $price,
										'source' => $card_id->id,
										'description' => $fname . ' ' . $lname . ' - ' . $level_data->level_name,
										'currency' => $stripe_currency,
										'application_fee' => $fee),
										$sc_accesstoken);
									}
									catch (\Stripe\Error\Card $e) {
										// Card was declined
										$jsonbody = $e->getJsonBody();
										$message = $jsonbody['error']['message'].' '.__LINE__;
										print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
										exit;
									}
									catch (\Stripe\Error\InvalidRequest $e) {
										$jsonbody = $e->getJsonBody();
										$message = $jsonbody['error']['message'].' '.__LINE__;
										print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
										exit;
									}
								}
								else {
									try {
										//echo 'use customer';
										$newcharge = \Stripe\Charge::create(array(
										'customer' => $custid,
										'amount' => $price,
										'description' => $fname . ' ' . $lname . ' - ' . $level_data->level_name,
										'receipt_email' => $email,
										'currency' => $stripe_currency));
									}
									catch (\Stripe\Error\Card $e) {
										// Card was declined
										$jsonbody = $e->getJsonBody();
										$message = $jsonbody['error']['message'].' '.__LINE__;
										print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
										exit;
									}
									catch (\Stripe\Error\InvalidRequest $e) {
										$jsonbody = $e->getJsonBody();
										$message = $jsonbody['error']['message'].' '.__LINE__;
										print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
										exit;
									}
								}
							}
							catch (\Stripe\Error\InvalidRequest $e) {
								$jsonbody = $e->getJsonBody();
								$message = $jsonbody['error']['message'].' '.__LINE__;
								print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
								exit;
							}
						}
						else if ($source == 'fd') {
							// here we would use token to charge the customer
							$data = array('gateway_id' => $gateway_id,
								'password' => $fd_pw,
								'transaction_type' => '00',
								'amount' => $level_data->level_price,
								'cardholder_name' => $fname.' '.$lname,
								'transarmor_token' => $fd_token,
								'credit_card_type' => $credit_card_type,
								'cc_expiry' => $cc_expiry);
							$data_string = json_encode($data);

							$gge4Date = strftime("%Y-%m-%dT%H:%M:%S", time() - (int) substr(date('O'), 0, 3)*60*60) . 'Z';
							$digest = sha1($data_string);
							$size = sizeof($data_string);

							$method = 'POST';
							$content_type = 'application/json';

							$hashstr = $method."\n".$content_type."\n".$digest."\n".$gge4Date."\n".'/transaction/v13';

							$authstr = 'GGE4_API ' . $key_id . ':' . base64_encode(hash_hmac("sha1", $hashstr, $hmac, true));

							$headers = array('Content-Type: '.$content_type,
								'X-GGe4-Content-SHA1: '.$digest,
								'Authorization: '.$authstr,
								'X-GGe4-Date: '.$gge4Date,
								'charset=UTF-8',
								'Accept: '.$content_type
							);
							try {
								$ch = curl_init($endpoint);
								curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
								curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
								if ($test == 1) {
									curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
								}
								curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

								$res = curl_exec($ch);

								if (curl_errno($ch)) {
									//echo 'error:' . curl_error($c);
									print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => curl_error($c), "line" => __LINE__)));
									exit;
								}
								else {
									//print_r($res);
									$res_string = json_decode($res);
									//print_r($res_string);
									if ($res_string->transaction_approved == 1) {
										// it is approved
										$txn_id = $res_string->authorization_num;
										$success = true;
										$fd_card_details['fd_token'] = $fd_token;
										if (isset($user_id)) {
											update_user_meta($user_id, 'fd_card_details', $fd_card_details);
										}
									}
									else{
										print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $res)));
										exit;
									}
								}
							}
							catch (Exception $e) {
								//print_r($e);
								print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $e, "line" => __LINE__)));
								exit;
							}
						}
						else if ($source == 'authorize.net') {
							// Updating the profile address in case of AVS
							$ID_Authorize_Net->update_payment_profile_address($_POST['Fields']);

							$price = str_replace(',', '', $level_data->level_price);
							$transaction = new AuthorizeNetTransaction;
							$transaction->amount = $price;
							$transaction->customerPaymentProfileId = $custid;
							$transaction->customerProfileId = $customerProfileId;
							$transaction->order->invoiceNumber = time();

							$request = new AuthorizeNetCIM;
							$response = $request->createCustomerProfileTransaction('AuthCapture', $transaction);
							if ($response->isOk()) {
								// echo "transaction done\n";
								$transactionResponse = $response->getTransactionResponse();
								$txn_id = $transactionResponse->transaction_id;
								$success = true;
							} else if ($response->isError()) {
								$success = false;
								// echo "transaction error: ".$response->getMessageText()."\n";
							}
						}
						else if ($source == 'mc') {
							$txn_id = 'mc_'.time();
							$type = 'order';
							$success = true;
						}
						// Charging using token we have and getting transaction id
						$txn_id = apply_filters('idc_charge_without_token_checkout', ((isset($txn_id)) ? $txn_id : ''), (isset($success) ? $success : false), array(
							"txn_type" => $txn_type, 
							"custid" => $custid, 
							"email" => $email, 
							"card_id" => ((isset($card_id)) ? $card_id : ''), 
							"amount" => $level_data->level_price, 
							"settings" => $settings, 
							"source" => $source,
							"extra_fields" => $_POST['Fields']
						));
					}

					if (isset($newcharge)) {
						$success = true;
						$type = 'order';
						$txn_id = $newcharge->id;
					}
				}
				else if ($txn_type == 'preauth') {
					// just store customer so we can process later
					// echo "txn_type: ".$txn_type."\n";
					$preauth = true;
					if ($source == 'mc') {
						$txn_id = 'mc_'.time();
					}
					else {
						$txn_id = 'pre';
					}
					$txn_id = apply_filters('idc_preauth_charge', $txn_id);
					$type = 'preauth';
				}
			}
			else {
				//echo 'recurring';
				// We use Stripe if active
				// Authorize.Net though supports recurring payments, so if it's selected
				if ($source == "authorize.net") {
					$price = str_replace(',', '', $level_data->level_price);
					$recurring_type_units = array(
						'weekly' => 7,
						'monthly' => 1,
						'annual' => 365
					);

					// Add 1 interval for ARB as 1st payment is done by CIM/AIM
					if ($level_data->recurring_type == 'weekly') {
						$timestamp_start = strtotime('+1 week');
					} else if ($level_data->recurring_type == 'monthly') {
						$timestamp_start = strtotime('+1 month');
					} else if ($level_data->recurring_type == 'annual') {
						$timestamp_start = strtotime('+1 year');
					}

					$subscription = new AuthorizeNet_Subscription;
					$subscription->name = $level_data->level_name;
					$subscription->intervalLength = $recurring_type_units[$level_data->recurring_type];
					$subscription->intervalUnit = ($level_data->recurring_type == 'monthly' ? 'months' : "days");
					// Start date will be 2nd interval of the interval length as 1st payment will be made by CIM so adding it to start date
					$subscription->startDate = date('Y-m-d', $timestamp_start);
					$subscription->amount = $price;
					$subscription->totalOccurrences = (($level_data->term_length > 0) ? $level_data->term_length - 1 : '9999');
					$subscription->creditCardCardNumber = $cc_number;
					$subscription->creditCardExpirationDate = $cc_expiry;
					$subscription->creditCardCardCode = $cc_code;
					$subscription->billToFirstName = $fname;
					$subscription->billToLastName = $lname;

					// Create the subscription.
					$request = new AuthorizeNetARB;
					$response = $request->createSubscription($subscription);
					if ($response->isOk()) {
						// echo "subscription is new\n";
						$subscription_id = $response->getSubscriptionId();
						$txn_id = $subscription_id;
						$success = true;
						$type = 'recurring';
						$new_sub = true;

						// Making the 1st transaction using CIM
						$price = str_replace(',', '', $level_data->level_price);
						$transaction = new AuthorizeNetTransaction;
						$transaction->amount = $price;
						$transaction->customerPaymentProfileId = $custid;
						$transaction->customerProfileId = $customerProfileId;
						$transaction->order->invoiceNumber = time();

						$requestFirstPayment = new AuthorizeNetCIM;
						$responseFirstPayment = $requestFirstPayment->createCustomerProfileTransaction('AuthCapture', $transaction);
						if ($responseFirstPayment->isOk()) {
							// 1st transaction is successful
							$transactionResponse = $responseFirstPayment->getTransactionResponse();
							$txn_id = $transactionResponse->transaction_id;
						} else if ($responseFirstPayment->isError()) {
							// There is some error, print that error
							print_r(json_encode(array('response' => $responseFirstPayment->getResultCode(), 'message' => "First transaction could not be made<br>".$responseFirstPayment->getMessageText(), "line" => __LINE__)));
							exit();
						}
					} else {
						// If such subscription already created. Then get it's id
						if ($response->getMessageCode() == "E00012") {
							// echo "payment needs updation. already exists\n";
							$message = $response->getMessageText();
							$txn_id = filter_var($message, FILTER_SANITIZE_NUMBER_INT);
							$subscription_id = $txn_id;
							// Unsetting some variables that can't be updated
							$subscription->intervalLength = '';
							$subscription->intervalUnit = '';
							// Sending request to update subscription
							$request = new AuthorizeNetARB;
							$responseUpdate = $request->updateSubscription($txn_id, $subscription);
							// If success
							if ($responseUpdate->isOk()) {
								$type = 'recurring';
								$success = true;
								$new_sub = false;

								// Making the 1st transaction using CIM for this subscription
								$price = str_replace(',', '', $level_data->level_price);
								$transaction = new AuthorizeNetTransaction;
								$transaction->amount = $price;
								$transaction->customerPaymentProfileId = $custid;
								$transaction->customerProfileId = $customerProfileId;
								$transaction->order->invoiceNumber = time();

								$requestFirstPayment = new AuthorizeNetCIM;
								$responseFirstPayment = $requestFirstPayment->createCustomerProfileTransaction('AuthCapture', $transaction);
								if ($responseFirstPayment->isOk()) {
									// 1st transaction is successful
									$transactionResponse = $responseFirstPayment->getTransactionResponse();
									$txn_id = $transactionResponse->transaction_id;
								} else if ($responseFirstPayment->isError()) {
									// There is some error, print that error
									print_r(json_encode(array('response' => $responseFirstPayment->getResultCode(), 'message' => "First transaction could not be made<br>".$responseFirstPayment->getMessageText().' '.__LINE__, "line" => __LINE__)));
									exit();
								}
							} else {
								// There is some error, print that error
								print_r(json_encode(array('response' => $responseUpdate->getResultCode(), 'message' => $responseUpdate->getMessageText().' '.__LINE__, "line" => __LINE__)));
								exit();
							}
						} else {
							print_r(json_encode(array('response' => $response->getResultCode(), 'message' => $response->getMessageText().' '.__LINE__, "line" => __LINE__)));
							exit();
						}
					}

					if (isset($user_id) && $new_sub) {
						// echo 'it\'s here and creating a new subscription';
						$new_sub = new ID_Member_Subscription(null, $user_id, $level_data->id, $subscription_id, $source);
						$filed_sub = $new_sub->add_subscription();
					}
					
					$start = time();
					$new_order = '';
				}
				else {
					try {
						$c = \Stripe\Customer::retrieve($custid);
					}
					catch (Exception $e) {
						$jsonbody = $e->getJsonBody();
						$message = $jsonbody['error']['message'].' '.__LINE__;
						print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
						exit;
					}
					try {
						// we now have a customer and need to see if we are using Stripe Connect
						// if using Stripe Connect, we have to store the customer on connected user's account also
						if (!empty($sc_accesstoken)) {
							\Stripe\Stripe::setApiKey($sc_accesstoken);
							// see if customer exists already
							$customer_idcopy = get_user_meta($user_id, 'customer_id_'.$check_claim, true);
							if (!empty($customer_idcopy)) {
								try {
									$ccopy = \Stripe\Customer::retrieve($customer_idcopy);
									if (empty($ccopy->default_source)) {
										// add original token as default source for new shared customer
										$ccopy->source = $token;
										$ccopy->save();
									}
								}
								catch (Exception $e) {
									$jsonbody = $e->getJsonBody();
									$message = $jsonbody['error']['message'].' '.__LINE__;
									print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
									exit;
								}
							}
							if (empty($ccopy)) {
								// customer does not exist. create
								try {
									// create a token for use in creating a customer
									$tokencopy = \Stripe\Token::create(array('customer' => $custid));
								}
								catch (Exception $e) {
									// Card was declined
									$jsonbody = $e->getJsonBody();
									$message = $jsonbody['error']['message'].' '.__LINE__;
									print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
									exit;
								}
								try {
									$ccopy = \Stripe\Customer::create(array(
									'description' => $fname . ' ' . $lname,
									'email' => $email,
									'source' => $tokencopy->id));
									// $ccopy = $newcust;
									//print_r($custcopy);
									if (!empty($ccopy)) {
										$customer_idcopy = $ccopy->id;
										update_user_meta($user_id, 'customer_id_'.$check_claim, $customer_idcopy);
									}
								}
								catch (Exception $e) {
									// Card was declined
									$jsonbody = $e->getJsonBody();
									$message = $jsonbody['error']['message'].' '.__LINE__;
									print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
									exit;
								}
							}
							// now we set the key back to original account
							if ($test) {
								\Stripe\Stripe::setApiKey($tsk);
							}
							else {
								\Stripe\Stripe::setApiKey($sk);
							}
						}
					}
					catch (Exception $e) {
						$jsonbody = $e->getJsonBody();
						$message = $jsonbody['error']['message'].' '.__LINE__;
						print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
						exit;
					}
					//echo $custid;
					//print_r($c);
					// varchange
					if (!empty($customer_idcopy)) {
						\Stripe\Stripe::setApiKey($sc_accesstoken);
						$subscriptions = $ccopy->subscriptions->data;
					}
					else {
						$subscriptions = $c->subscriptions->data;
					}
					$new_sub = true;
					if (!empty($subscriptions)) {
						// #devnote convert this into a function for grabbing subscriptions from customer objects
						foreach ($subscriptions as $sub_object) {
							try {
								// we can pass recursive = true to avoid __toArray() further down the line
								$sub_values = $sub_object->__toArray();
							}
							catch (Exception $e) {
								$jsonbody = $e->getJsonBody();
								$message = $jsonbody['error']['message'].' '.__LINE__;
								print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
								exit;
							}
							if (!empty($sub_values)) {
								$sub_plan = $sub_values['plan'];
								if (!empty($sub_plan)) {
									try {
										$sub_data = $sub_plan->__toArray();
									}
									catch (Exception $e) {
										$jsonbody = $e->getJsonBody();
										$message = $jsonbody['error']['message'].' '.__LINE__;
										print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
										exit;
									}
									if (!empty($sub_data)) {
										$sub_id = $sub_data['id'];
										if ($sub_id == $plan) {
											try {
												$sub_actual = $sub_values['id'];
												$new_sub = false;
												break;
											}
											catch (Exception $e) {
												$jsonbody = $e->getJsonBody();
												$message = $jsonbody['error']['message'].' '.__LINE__;
												print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
												exit;
											}
										}
									}
								}
							}
						}
					}
					try {
						$sub_args = array(
							'customer' => (isset($customer_idcopy) ? $customer_idcopy : $custid),
							'plan' => $plan,
						);
						if (!empty($customer_idcopy)) {
							$sc_settings = maybe_unserialize(get_option('md_sc_settings'));
							$fee_type = apply_filters('idc_fee_type', $sc_settings['fee_type']);
							$app_fee = apply_filters('idc_app_fee', $sc_settings['app_fee'], $level_data);
							$sub_args['application_fee_percent'] = $app_fee;
							if (!empty($fee_type) && $fee_type == 'percentage') {
								if ($new_sub) {
									$sub = \Stripe\Subscription::create($sub_args);
								}
								else {
									$sub = \Stripe\Subscription::update($sub_actual, array('plan' => $plan, 'application_fee_percent' => $app_fee));
								}
							}
							else {
								// #devnote combine with next conditional
								if ($new_sub) {
									$sub = \Stripe\Subscription::create($sub_args);
								}
								else {
									$sub = \Stripe\Subscription::update($sub_actual, array('plan' => $plan));
								}
							}
						}
						else {
							// #devnote combine with above conditional
							if ($new_sub) {
								$sub = \Stripe\Subscription::create($sub_args);
							}
							else {
								$sub = \Stripe\Subscription::update($sub_actual, array('plan' => $plan));
							}
						}
					}
					catch (\Stripe\Error\Card $e) {
						//print_r($e);
						$jsonbody = $e->getJsonBody();
						$message = $jsonbody['error']['message'].' '.__LINE__;
						print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
						exit;
					}
					catch (\Stripe\Error\InvalidRequest $e) {
						$jsonbody = $e->getJsonBody();
						$message = $jsonbody['error']['message'].' '.__LINE__;
						print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
						exit;
					}
					//print_r($sub);
					if ($sub->status == 'active' || $sub->status == 'trialing') {
						if ($sub->status == 'trialing') {
							$trial = true;
						}
						$txn_id = $sub->plan->id;
						//echo $txn_id;
						$success = true;
						if (isset($user_id) && $new_sub) {
							$new_sub = new ID_Member_Subscription(null, $user_id, $level_data->id, $sub->id, $source);
							$filed_sub = $new_sub->add_subscription();
	
						}
					}
					$start = $sub->start;
					//echo $start;
					$new_order = '';
					$type = 'recurring';
					//print_r($sub);
				}
			}
			$success = apply_filters('idc_checkout_success', (isset($success) ? $success : false), $txn_id, $source);
			if ((isset($success) && $success == true) || (isset($preauth) && $preauth == true)) {
				// this handles our custom post fields, if any
				if (isset($_POST['Fields'])) {
					$fields = $_POST['Fields'];
				}
				else {
					$fields = array();
				}
				//echo 'success';
				$paykey = md5($email.time());
				if (isset($newuser)) {
					//echo 'new user';
					// user doesn't exist at all, so we create and insert in both
					if (!empty($pw)) {
						// only create user if we have a password and intend to create an account
						$user_id = wp_insert_user(array('user_email' => $email, 'user_login' => $email, 'user_pass' => $pw, 'first_name' => $fname, 'last_name' => $lname, 'display_name' => $fname));
					}
					if (!empty($user_id)) {
						if ($source == 'fd') {
							$fd_card_details['fd_token'] = $fd_token;
							update_user_meta($user_id, 'fd_card_details', $fd_card_details);
							$user = array('user_id' => $user_id, 'level' => $access_levels, 'data' => array());
						}
						else if ($source == 'authorize.net') {
							$authorizenet_customer_ids['authorizenet_payment_profile_id'] = $custid;
							$authorizenet_customer_ids['authorizenet_profile_id'] = $customerProfileId;
							update_user_meta($user_id, 'authorizenet_profile_id', $authorizenet_customer_ids);
							$user = array('user_id' => $user_id, 'level' => $access_levels, 'data' => array());
						}
						else {
							$user = array('user_id' => $user_id, 'level' => $access_levels, 'data' => (isset($custid) ? array('customer_id' => $custid) : array()));
						}
						// #dev
						$user = apply_filters('idc_user_update_checkout', $user, $source);

						$new = ID_Member::add_user($user);
						// it's important this happens after member is added to prevent duplicate memberships
						do_action('idc_register_success', $user_id, $email);
					}
					
					if (!$recurring) {
						$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, '', 'active', $e_date, $level_data->level_price);
						if ($renewable) {
							// cancel last order #devnote move to hook / price doesn't account for pwyw or donations
							$order->price = $level_data->renewal_price;
							$last_order = new ID_Member_Order(null, $user_id, $level_data->id);
							$get_last_order = $last_order->get_last_order();
							if (isset($get_last_order)) {
								$lo_time = strtotime($get_last_order->e_date);
								$order->e_date = idc_set_order_edate($level_data, $lo_time);
							}
						}
						$new_order = $order->add_order();
						if (empty($user_id)) {
							do_action('idc_guest_checkout_order', $new_order, $customer);
						}
						if (is_multisite() && !empty($user_id)) {
							// #devnote add to webhook handler?
							$blog_id = get_current_blog_id();
							//echo $blog_id;
							add_user_to_blog($blog_id, $user_id, 'subscriber');
							MD_Keys::set_licenses($user_id, $product_id);
						}
						if (isset($preauth) && $preauth == true) {
							if (isset($use_token) && $use_token == true) {
								$charge_token = apply_filters('idc_card_id_checkout', $card_id, $use_token, $preauth = true, $source);
							}
							else {
								if ($source == 'fd') {
									$charge_token = $token;
								}
								else {
									$charge_token = $custid;
								}
							}
							$charge_token = apply_filters('idc_preorder_charge_token', $charge_token, $txn_id, array(
								"txn_type" => 'preauth',
								"custid" => $custid,
								"email" => $email,
								"card_id" => (isset($card_id) ? $card_id : ''),
								"amount" => $level_data->level_price,
								"settings" => $settings,
								"source" => $source,
								"extra_fields" => $_POST['Fields']
							));

							// If Auth.Net we don't depend on $charge_token, make Auth Only transaction and send the authorization_code as charge token
							if ($source == 'authorize.net') {
								// Updating the profile address in case of AVS
								$ID_Authorize_Net->update_payment_profile_address($_POST['Fields']);
								$charge_token = $ID_Authorize_Net->create_charge_token($level_data->level_price);
							}
							//echo 'sending a preorder';
							$preorder_entry = ID_Member_Order::add_preorder($new_order, $charge_token, $source);
							do_action('memberdeck_preauth_success', (isset($user_id) ? $user_id : null), $new_order, $paykey, $fields, $source);
							do_action('memberdeck_preauth_receipt', (isset($user_id) ? $user_id : null), $level_data->level_price, $product_id, $source, $new_order);
						}
						else {
							do_action('memberdeck_payment_success', (isset($user_id) ? $user_id : null), $new_order, $paykey, $fields, $source);
							do_action('idmember_receipt', (isset($user_id) ? $user_id : null), $level_data->level_price, $product_id, $source, $new_order, $fields);
						}
					}
					else {
						if ($new_sub) {
							$new_sub = new ID_Member_Subscription(null, $user_id, $level_data->id, $sub->id, $source);
							$filed_sub = $new_sub->add_subscription();
						}
						/*if ($trial) {
							$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, '', 'active', idc_set_order_edate($level_data), '0.00');
							#devnote renewable options omitted
							$new_order = $order->add_order();
							if (empty($user_id)) {
								do_action('idc_guest_checkout_order', $new_order, $customer);
							}
							if (is_multisite() && !empty($user_id)) {
								// #devnote add to webhook handler?
								$blog_id = get_current_blog_id();
								//echo $blog_id;
								add_user_to_blog($blog_id, $user_id, 'subscriber');
								MD_Keys::set_licenses($user_id, $product_id);
							}
							do_action('memberdeck_payment_success', (isset($user_id) ? $user_id : null), $new_order, $paykey, $fields, $source);
							do_action('idmember_receipt', (isset($user_id) ? $user_id : null), '0.00', $product_id, $source, $new_order, $fields);
						}*/

						// If payment gateway is Authorize.Net, do work here as IPN not available in Auth.Net
						if ($source == "authorize.net") {
							if (isset($user_id)) {
								$txn_check = ID_Member_Order::check_order_exists($txn_id);
								if (empty($txn_check)) {
									$level = $level_data;
									$recurring_type = $level->recurring_type;
									if ($recurring_type == 'weekly') {
										// weekly
										$exp = strtotime('+1 week');
									}
									else if ($recurring_type == 'monthly') {
										// monthly
										$exp = strtotime('+1 month');
									}
									else {
										// annually
										$exp = strtotime('+1 years');
									}
									$e_date = date('Y-m-d H:i:s', $exp);
									//fwrite($log, $e_date);
									if ($level->limit_term == 1) {
										$term_length = $level->term_length;
									}
									$paykey = md5($user_email.time());
									$order = new ID_Member_Order(null, $user_id, $level->id, null, $txn_id, $subscription_id, 'active', $e_date, $level->level_price);
									$new_order = $order->add_order();
									
									do_action('memberdeck_payment_success', $user_id, $new_order, $paykey, $fields, 'authorize.net');
									do_action('memberdeck_recurring_success', 'authorize.net', $user_id, $new_order, (isset($term_length) ? $term_length : null));
									do_action('idmember_receipt', $user_id, $level->level_price, $product_id, 'authorize.net', $new_order, $fields);
								}
							}

						}
					}
				}
				else {
					// echo 'not new user'."\n";
					if (isset($match_user->access_level)) {
						// echo 'is set 1'."\n";
						$old_levels = maybe_unserialize($match_user->access_level);
						if (is_array($old_levels)) {
							foreach ($old_levels as $key['val']) {
								$access_levels[] = $key['val'];
							}
						}	
					}
					if (!empty($match_user->data)) {
						// echo 'is set 2'."\n";
						$old_data = unserialize($match_user->data);
						//print_r($old_data);
						if ($source == 'stripe') {
							$new_data = array('customer_id' => $custid);
							if (!is_array($old_data)) {
								$old_data = unserialize($old_data);
							}
							if (is_array($old_data)) {
								$old_data[] = $new_data;
							}
							//$old_data[] = array('customer_id' => $custid, 'txn_id' => $txn_id);
						}
						else if ($source == 'fd') {
							$new_data = array('customer_id' => $custid);
							if (!is_array($old_data)) {
								$old_data = unserialize($old_data);
							}
							$old_data[] = $new_data;
							$fd_card_details['fd_token'] = $fd_token;
							update_user_meta($user_id, 'fd_card_details', $fd_card_details);
						}
						else if ($source == 'authorize.net') {
							$authorizenet_customer_ids['authorizenet_payment_profile_id'] = $custid;
							$authorizenet_customer_ids['authorizenet_profile_id'] = $customerProfileId;
							update_user_meta($user_id, 'authorizenet_profile_id', $authorizenet_customer_ids);
						}
					}
					else {
						if ($source == 'stripe') {
							$old_data = array('customer_id' => $custid);
						}
						else if ($source == 'fd') {
							$old_data = array('customer_id' => $custid);
							$fd_card_details['fd_token'] = $fd_token;
							update_user_meta($user_id, 'fd_card_details', $fd_card_details);
						}
						else if ($source == 'authorize.net') {
							$old_data = array('customer_id' => $custid);
							$authorizenet_customer_ids['authorizenet_payment_profile_id'] = $custid;
							$authorizenet_customer_ids['authorizenet_profile_id'] = $customerProfileId;
							update_user_meta($user_id, 'authorizenet_profile_id', $authorizenet_customer_ids);
						}
						else {
							$old_data = array();
						}
					}
					$user = apply_filters('idc_user_update_checkout', array('user_id' => $user_id, 'level' => $access_levels, 'data' => $old_data), $source);
					//print_r($user);
					$insert = apply_filters('idc_member_insert_checkout', (isset($insert) ? $insert : false), $custid, $source);
					if (isset($insert) && $insert == true) {
						//echo 'insert';
						// user exists only in wp_users so we insert
						$new = ID_Member::add_user($user);
					}
					else {
						//echo 'update';
						// user exists in both tables, so we update
						$new = ID_Member::update_user($user);
					}
					if (!isset($recurring) || $recurring == false) {
						$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, '', 'active', $e_date, $level_data->level_price);
						if ($renewable) {
							// cancel last order
							$order->price = $level_data->renewal_price;
							$last_order = new ID_Member_Order(null, $user_id, $level_data->id);
							$get_last_order = $last_order->get_last_order();
							if (isset($get_last_order)) {
								$lo_time = strtotime($get_last_order->e_date);
								$order->e_date = idc_set_order_edate($level_data, $lo_time);

								// Adding meta to last order that it's renewed
								ID_Member_Order::update_order_meta($get_last_order->id, 'idc_order_renewed', '1');
							}
							/*$last_order = new ID_Member_Order(null, $user_id, $level_data->id);
							$get_it = $last_order->get_last_order();
							if (!empty($get_it)) {
								$canceled_order_id = $get_it->id;
								$canceled_order = new ID_Member_Order($canceled_order_id);
								$get_canceled_order = $canceled_order->get_order();
								$canceled_order_edate = $get_canceled_order->e_date;
								$cancel_it = $canceled_order->cancel_status($canceled_order_edate);
							}*/
						}
						$new_order = $order->add_order();

						if (is_multisite()) {
							$blog_id = get_current_blog_id();
							//echo $blog_id;
							add_user_to_blog($blog_id, $user_id, 'subscriber');
						}
						MD_Keys::set_licenses($user_id, $product_id);
						//echo 'order: '.$new_order;
						if (isset($preauth) && $preauth == true) {
							// echo 'sending a preorder'."\n";
							if (isset($use_token) && $use_token == true) {
								$charge_token = apply_filters('idc_card_id_checkout', ((isset($card_id)) ? $card_id : ''), $use_token, $preauth = true, $source);
							}
							else if ($source == 'fd') {
								$charge_token = $token;
							}
							else if ($source == 'mc') {
								$charge_token = 'manual';
							}
							else {
								$charge_token = $custid;
							}

							// If Auth.Net, make Auth Only transaction and send the id as charge token
							if ($source == 'authorize.net') {
								$ID_Authorize_Net->update_payment_profile_address($_POST['Fields']);
								$charge_token = $ID_Authorize_Net->create_charge_token($level_data->level_price);
							}
							$charge_token = apply_filters('idc_preorder_charge_token', $charge_token, $txn_id, array(
								"txn_type" => 'preauth',
								"custid" => $custid,
								"email" => $email,
								"card_id" => (isset($card_id) ? $card_id : ''),
								"amount" => $level_data->level_price,
								"settings" => $settings,
								"source" => $source,
								"extra_fields" => $_POST['Fields']
							));

							$preorder_entry = ID_Member_Order::add_preorder($new_order, $charge_token, $source);
							do_action('memberdeck_preauth_success', $user_id, $new_order, $paykey, $fields, $source);
							do_action('memberdeck_preauth_receipt', $user_id, $level_data->level_price, $product_id, $source, $new_order);
						}
						else {
							do_action('memberdeck_payment_success', $user_id, $new_order, $paykey, $fields, $source);
							do_action('idmember_receipt', $user_id, $level_data->level_price, $product_id, $source, $new_order, $fields);
						}
					}
					else {
						// If payment gateway is Authorize.Net, do work here as IPN not available in Auth.Net
						if ($source == "authorize.net") {
							if (isset($user_id)) {
								$txn_check = ID_Member_Order::check_order_exists($txn_id);
								if (empty($txn_check)) {
									$level = $level_data;
									$recurring_type = $level->recurring_type;
									if ($recurring_type == 'weekly') {
										// weekly
										$exp = strtotime('+1 week');
									}
									else if ($recurring_type == 'monthly') {
										// monthly
										$exp = strtotime('+1 month');
									}
									else {
										// annually
										$exp = strtotime('+1 years');
									}
									$e_date = date('Y-m-d H:i:s', $exp);
									//fwrite($log, $e_date);
									if ($level->limit_term == 1) {
										$term_length = $level->term_length;
									}
									$paykey = md5($email.time());
									$order = new ID_Member_Order(null, $user_id, $level->id, null, $txn_id, $subscription_id, 'active', $e_date, $level->level_price);
									$new_order = $order->add_order();
									
									do_action('memberdeck_payment_success', $user_id, $new_order, $paykey, $fields, 'authorize.net');
									do_action('memberdeck_recurring_success', 'authorize.net', $user_id, $new_order, (isset($term_length) ? $term_length : null));
									do_action('idmember_receipt', $user_id, $level->level_price, $product_id, 'authorize.net', $new_order, $fields);
								}
							}
						}
						else if ($source == 'stripe') {
							/*if ($trial) {
								$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, '', 'active', idc_set_order_edate($level_data), '0.00');
								#devnote renewable options omitted
								$new_order = $order->add_order();
								if (empty($user_id)) {
									do_action('idc_guest_checkout_order', $new_order, $customer);
								}
								if (is_multisite() && !empty($user_id)) {
									// #devnote add to webhook handler?
									$blog_id = get_current_blog_id();
									//echo $blog_id;
									add_user_to_blog($blog_id, $user_id, 'subscriber');
									MD_Keys::set_licenses($user_id, $product_id);
								}
								do_action('memberdeck_payment_success', (isset($user_id) ? $user_id : null), $new_order, $paykey, $fields, $source);
								do_action('idmember_receipt', (isset($user_id) ? $user_id : null), '0.00', $product_id, $source, $new_order, $fields);
							}*/
						}
					}
				}
				if ($crowdfunding) {
					//echo 'order: '.$new_order;
					if (isset($_POST['Fields'])) {
						//echo 'isset post fields';
						$fields = $_POST['Fields'];
						if (is_array($fields)) {
							foreach ($fields as $field) {
								if ($field['name'] == 'project_id') {
									$project_id = $field['value'];
								}
								else if ($field['name'] == 'project_level') {
									$proj_level = $field['value'];
								}
							}
						}
						if (isset($project_id) && $project_id > 0 && isset($proj_level)) {
							$price = $level_data->level_price;
							if (isset($new_order)) {
								//echo $new_order;
								$order = new ID_Member_Order($new_order);
								//print_r($order);
								//$the_order = $order->get_order();
								$created_at = $order->order_date;
							}
							else {
								$created_at = date('Y-m-d H:i:s');
							}
							if (isset($preauth) && $preauth == true) {
								$status = 'W';
							}
							else {
								$status = 'C';
							}
							$pay_id = mdid_insert_payinfo($fname, $lname, $email, $project_id, $txn_id, $proj_level, $price, $status, $created_at);
							// now need to insert mdid order
							if (isset($pay_id)) {
								if ($type == 'recurring') {
									$mdid_id = mdid_insert_order($custid, $pay_id, $start, $txn_id);
								}
								else {
									$mdid_id = mdid_insert_order($custid, $pay_id, $new_order, null);
								}
								do_action('id_payment_success', $pay_id);
							}
						}
					}
				}
				if ($source == 'stripe') {
					//echo 'inside stripe success';
					do_action('memberdeck_stripe_success', $user_id, $email);
					update_user_meta($user_id, 'stripe_customer_id', $custid);
				}
				else if ($source == 'fd') {
					do_action('memberdeck_fd_success', $user_id, $email);
				}
				//echo 'before response';
				// go ahead and send the response so we can redirect them
				print_r(json_encode(array('response' => 'success', 'product' => $product_id, 'paykey' => $paykey, 'customer_id' => $custid, 'user_id' => $user_id, 'order_id' => $new_order, 'type' => $type)));
			}
			else {
				print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => __('Could not authorize transaction', 'memberdeck').': '.__LINE__)));
			}
		}
		else {
			print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => __('Could not create customer token', 'memberdeck').': '.__LINE__)));
		}
	}
	else {
		print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => __('Could not create customer token', 'memberdeck').': '.__LINE__)));
	}
	exit();
}

add_action('wp_ajax_idmember_create_customer', 'idmember_create_customer');
add_action('wp_ajax_nopriv_idmember_create_customer', 'idmember_create_customer');

function md_export_customers() {
	$url = ID_Member::export_members();
	echo $url;
	exit;
}

add_action('wp_ajax_md_export_customers', 'md_export_customers');
add_action('wp_ajax_nopriv_md_export_customers', 'md_export_customers');

function md_delete_export() {
	if (isset($_POST['file'])) {
		$file = $_POST['file'];
		ID_Member::delete_export($filepath);
	}
	exit;
}

add_action('wp_ajax_md_delete_export', 'md_delete_export');
add_action('wp_ajax_nopriv_md_delete_export', 'md_delete_export');

function md_use_credit() {
	global $crowdfunding, $global_currency;
	$customer_id = customer_id();
	$md_credits = md_credits();
	$customer = $_POST['Customer'];
	$Fields = (isset($_POST['Fields']) ? $_POST['Fields'] : '');
	$product_id = absint(esc_attr($customer['product_id']));
	$access_levels = array($product_id);
	$level_data = ID_Member_Level::get_level($product_id);
	if ($level_data->level_type == 'recurring') {
		// we need to return false here
		$error = __('Cannot use '.strtolower(apply_filters('idc_credits_label', 'credits', true)).' to purchase recurring products', 'memberdeck');
		print_r(json_encode(array('response' => 'failure', 'message' => $error)));
		exit;
	}
	else if ($level_data->level_type == 'lifetime') {
		$e_date = null;
	}
	else {
		$e_date = idc_set_order_edate($level_data);
	}
	$fname = esc_attr($customer['first_name']);
	$lname = esc_attr($customer['last_name']);
	if (isset($customer['email'])) {
		$email = esc_attr($customer['email']);
	}
	else {
		// they have used 1cc or some other mechanism and we don't have their email
		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();
			$email = $current_user->user_email;
		}
	}
	$member = new ID_Member();
	$check_user = $member->check_user($email);
	if (!empty($check_user)) {
		if ($md_credits >= $level_data->credit_value) {
			$user_id = $check_user->ID;
			$match_user = $member->match_user($user_id);
			if (!empty($match_user)) {
				// this user already exists within MemberDeck
				$txn_id = 'credit';
				if (isset($match_user->access_level)) {
					// let's combine levels
					$old_levels = unserialize($match_user->access_level);
					if (is_array($old_levels)) {
						foreach ($old_levels as $key['val']) {
							$access_levels[] = $key['val'];
						}
					}
				}
				if (isset($match_user->data)) {
					// let's combine data
					$old_data = unserialize($match_user->data);
					// do we need any data for credit purchases?
					//$old_data[] =  array('customer_id' => $custid);
				}
				else {
					$old_data = array();
				}
				$paykey = md5($email.time());
				// price is 0 because they are using a credit
				/*if ($global_currency == 'credit') {
					$price = $level_data->credit_value;
				}*/
				$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, '', 'active', $e_date, '0');
				$new_order = $order->add_order();
				MD_Keys::set_licenses($user_id, $product_id);
				$user = array('user_id' => $user_id, 'level' => $access_levels, 'data' => $old_data);
				$new = ID_Member::update_user($user);
				if (isset($_POST['PWYW'])) {
					$pwyw_price = esc_attr($_POST['PWYW']);
					if ($pwyw_price > $level_data->credit_value) {
						$level_data->credit_value = $pwyw_price;
					}
					ID_Member_Order::update_order_meta($new_order, 'pwyw_price', $pwyw_price);
				}
				ID_Member_Credit::use_credits($user_id, $level_data->credit_value);

				// don't send receipt for now
				//do_action('memberdeck_credit_receipt', $user_id, $level_data->credit_value);
				do_action('memberdeck_payment_success', $user_id, $new_order, $paykey, $Fields, 'credit');

				if ($crowdfunding) {
					//echo 'order: '.$new_order;
					if (isset($_POST['Fields'])) {
						//echo 'isset post fields';
						$fields = $_POST['Fields'];
						if (is_array($fields)) {
							foreach ($fields as $field) {
								if ($field['name'] == 'project_id') {
									$project_id = $field['value'];
								}
								else if ($field['name'] == 'project_level') {
									$proj_level = $field['value'];
								}
							}
						}
						if (isset($project_id) && isset($proj_level)) {
							$price = apply_filters('id_idc_pwyw_price', $level_data->level_price, $level_data->credit_value, $pwyw_price);
							if (isset($new_order)) {
								//echo $new_order;
								$order = new ID_Member_Order($new_order);
								//print_r($order);
								//$the_order = $order->get_order();
								$created_at = $order->order_date;
							}
							else {
								$created_at = date('Y-m-d H:i:s');
							}
							$status = 'C';
							// Setting price/credits based on the global currency display
							// if (!empty($global_currency) && $global_currency == "credits") {
							// 	$pay_id = mdid_insert_payinfo($fname, $lname, $email, $project_id, $txn_id, $proj_level, $level_data->credit_value, $status, $created_at);
							// } else {
							$pay_id = mdid_insert_payinfo($fname, $lname, $email, $project_id, $txn_id, $proj_level, $price, $status, $created_at);
							// }
							// now need to insert mdid order
							if (isset($pay_id)) {
								$mdid_id = mdid_insert_order(null, $pay_id, $new_order, null);
								do_action('id_payment_success', $pay_id);
							}
						}
					}
				}
				print_r(json_encode(array('response' => 'success', 'product' => $product_id, 'paykey' => $paykey, 'customer_id' => null, 'user_id' => $user_id, 'order_id' => $new_order, 'type' => 'credit')));
			}
			else {
				$error = __('This user is not a memberdeck user', 'memberdeck').' '.__LINE__;
				print_r(json_encode(array('response' => 'failure', 'message' => $error)));
			}
		}
		else {
			$error = __('You do not have enough credits to complete this transaction', 'memberdeck').' '.__LINE__;
			print_r(json_encode(array('response' => 'failure', 'message' => $error)));
		}
	}
	else {
		$error = __('User was not found', 'memberdeck').' '.__LINE__;
		print_r(json_encode(array('response' => 'failure', 'message' => $error)));
	}
	exit;
}

add_action('wp_ajax_md_use_credit', 'md_use_credit');
add_action('wp_ajax_nopriv_md_use_credit', 'md_use_credit');

function idmember_free_product() {
	if (isset($_POST['action']) && $_POST['action'] == 'idmember_free_product') {
		$customer = $_POST['Customer'];
		$product_id = absint(esc_attr($customer['product_id']));
		$access_levels = array($product_id);
		$level_data = ID_Member_Level::get_level($product_id);
		$level = ID_Member_Level::get_level($product_id);
		$fname = esc_attr($customer['first_name']);
		$lname = esc_attr($customer['last_name']);
		$email = esc_attr($customer['email']);
		if (isset($customer['pw'])) {
			$pw = esc_attr($customer['pw']);
		}
		$user = new ID_Member();
		$check_user = $user->check_user($email);


			if (empty($check_user)) {
				//echo 'new user';
				// user doesn't exist at all, so we create and insert in both
				if (!empty($pw)) {
					// only create user if we have a password and intend to create an account
					$user_id = wp_insert_user(array('user_email' => $email, 'user_login' => $email, 'user_pass' => $pw, 'first_name' => $fname, 'last_name' => $lname, 'display_name' => $fname));
				}
				if (!empty($user_id)) {
					do_action('idc_register_success', $user_id, $email);
					$user = array('user_id' => $user_id, 'level' => $access_levels, 'data' => array());
					$new = ID_Member::add_user($user);
					if (is_multisite()) {
						$blog_id = get_current_blog_id();
						//echo $blog_id;
						add_user_to_blog($blog_id, $user_id, 'subscriber');
					}
				}
				// Price is zero because it is free
				$order = new ID_Member_Order(null, $user_id, $product_id, null, 'free', '', 'active', null, '0');
				$new_order = $order->add_order();
				if (!empty($user_id)) {
					MD_Keys::set_licenses($user_id, $product_id);
				}
				else {
					do_action('idc_guest_checkout_order', $new_order, $customer);
				}
			}
			else {
				//echo 'not new user';
				$user_id = $check_user->ID;
				$match_user = $user->match_user($user_id);
				if (isset($match_user->access_level)) {
					//echo 'is set 1';
					$old_levels = unserialize($match_user->access_level);
					foreach ($old_levels as $key['val']) {
						$access_levels[] = $key['val'];
					}	
				}
				$user = array('user_id' => $user_id, 'level' => $access_levels, 'data' => $match_user->data);
				//print_r($user);
				if (empty($match_user)) {
					//echo 'insert';
					// user exists only in wp_users so we insert
					$new = ID_Member::add_user($user);
				}
				else {
					//echo 'update';
					// user exists in both tables, so we update
					$new = ID_Member::update_user($user);
				}
				$order = new ID_Member_Order(null, $user_id, $product_id, null, 'free', '', 'active', null, '0');
				$new_order = $order->add_order();
				if (is_multisite()) {
					$blog_id = get_current_blog_id();
					//echo $blog_id;
					add_user_to_blog($blog_id, $user_id, 'subscriber');
				}
				MD_Keys::set_licenses($user_id, $product_id);
				//echo $new_order;
			}
			do_action('memberdeck_free_success', $user_id, $new_order);
		
			print_r(json_encode(array('response' => 'success', 'product' => $product_id)));
			exit;
	}
}
add_action('wp_ajax_idmember_free_product', 'idmember_free_product');
add_action('wp_ajax_nopriv_idmember_free_product', 'idmember_free_product');

function idmember_check_email() {
	if (isset($_POST['action']) && $_POST['action'] == 'idmember_check_email' && isset($_POST['Email'])) {
		$email = $_POST['Email'];
		$member = new ID_Member();
		$check_user = $member->check_user($email);
		$cuid = get_current_user_id();
		if (isset($check_user) && $check_user->ID != $cuid) {
			print_r(json_encode(array('response' => 'exists')));
		}
		else {
			print_r(json_encode(array('response' => 'available')));
		}
	}
	exit();
}

add_action('wp_ajax_idmember_check_email', 'idmember_check_email');
add_action('wp_ajax_nopriv_idmember_check_email', 'idmember_check_email');

function memberdeck_insert_user() {
	if (isset($_POST['action']) && $_POST['action'] == 'memberdeck_insert_user' && isset($_POST['User'])) {
		$user = $_POST['User'];
		$fname = esc_attr($user['first_name']);
		$lname = esc_attr($user['last_name']);
		$email = esc_attr($user['email']);
		$pw = esc_attr($user['pw']);
		$fields = (!empty($_POST['Fields']) ? $_POST['Fields'] : array());
		$user = array(
			'user_pass' => $pw, 
			'user_email' => $email, 
			'user_login' => $email, 
			'first_name' => $fname, 
			'last_name' => $lname,
			'display_name' => $fname
			);
		$insert = wp_insert_user($user);

		if (is_wp_error($insert)) {
			$message = '';
			foreach ($insert->errors as $error) {
				if (empty($message)) {
					$message .= $error[0];
				}
				else {
					$message .= ' '.$error[0];
				}
			}
			print_r(json_encode(array('response' => 'failure', 'message' => $message)));
		}
		else if ($insert > 0) {
			$fields_length = count($fields);
			$metakey = '';
			$metavalue = '';
			
			for ($i = 0; $i < $fields_length; $i++) {
			    foreach( $fields[$i] as $key => $value) {
			        if ( $key == 'name' ) {
			            $metakey = "_" . str_replace( '-', '_', $value );
			        } else if ( $key == 'value' ) {
			            $metavalue = $value;
			        }
			    }
			    add_user_meta( $insert, $metakey, $metavalue );
			}

			do_action('idc_before_register_success', $insert, $email, $fields);
			// deprecated action?
			do_action('idc_register_success', $insert, $email);
			do_action('idc_register_post_extra', $insert, $email, $fields);
			print_r(json_encode(array('response' => 'success')));
		}
		else {
			print_r(json_encode(array('response' => 'failure')));
		}
	}
	exit();
}

add_action('wp_ajax_memberdeck_insert_user', 'memberdeck_insert_user');
add_action('wp_ajax_nopriv_memberdeck_insert_user', 'memberdeck_insert_user');

function idmember_update_user() {
	if (isset($_POST['action']) && $_POST['action'] == 'idmember_update_user' && isset($_POST['User'])) {
		$user = $_POST['User'];
		$reg_key = $user['regkey'];
		$user_object = ID_Member::retrieve_user_key($reg_key);
		if (!empty($user_object)) {
			$user_id = $user_object->user_id;
		}
		if (isset($user_id)) {
			$fname = esc_attr($user['first_name']);
			$lname = esc_attr($user['last_name']);
			$email = esc_attr($user['email']);
			$pw = esc_attr($user['pw']);

			$user = array('ID' => $user_id, 
				'user_pass' => wp_hash_password($pw), 
				'user_email' => $email, 
				'user_login' => $email, 
				'first_name' => $fname, 
				'last_name' => $lname,
				'display_name' => $fname
				);
			$update = wp_insert_user($user);
			if ($update == $user_id) {
				ID_Member::delete_reg_key($user_id);
				do_action('memberdeck_stripe_success', $user_id, $email);
				print_r(json_encode(array('response' => 'success')));
			}
			else {
				//echo '2';
				print_r(json_encode(array('response' => 'failure')));
			}
		}
		else {
			//echo '3';
			print_r(json_encode(array('response' => 'failure')));
		}
	}
	exit();
}

add_action('wp_ajax_idmember_update_user', 'idmember_update_user');
add_action('wp_ajax_nopriv_idmember_update_user', 'idmember_update_user');

function idmember_get_ppadaptive_paykey() {
	// including libraries using autoloader
	require 'lib/PayPalAdaptive/lib/vendor/autoload.php';
	$tz = get_option('timezone_string');
    if (empty($tz)) {
        $tz = 'UTC';
    }
    date_default_timezone_set($tz);
	$permalink_structure = get_option('permalink_structure');
	$prefix = '?';
	if (empty($permalink_structure)) {
		$prefix = '&';
	}
	$https = false;
	if ((isset($_SERVER['https']) && $_SERVER['https'] == 'on') || $_SERVER['SERVER_PORT'] == 443) {
		$https = true;
	}
	$cur_url = $_SERVER['HTTP_REFERER'];

	// Getting gateways options stored in Settings
	$gateways = get_option('memberdeck_gateways');
	$ppada_currency = (isset($gateways['ppada_currency']) ? $gateways['ppada_currency'] : 'USD');
	// PayPal adaptive Classic API keys
	if (isset($gateways['test']) && $gateways['test'] == 1) {
		$ppadap_api_username = (isset($gateways['ppadap_api_username_test']) ? $gateways['ppadap_api_username_test'] : '');
		$ppadap_api_password = (isset($gateways['ppadap_api_password_test']) ? $gateways['ppadap_api_password_test'] : '');
		$ppadap_api_signature = (isset($gateways['ppadap_api_signature_test']) ? $gateways['ppadap_api_signature_test'] : '');
		$ppadap_app_id = (isset($gateways['ppadap_app_id_test']) ? $gateways['ppadap_app_id_test'] : '');
		$ppadap_receiver_email = (isset($gateways['ppadap_receiver_email_test']) ? $gateways['ppadap_receiver_email_test'] : '');
	} else {
		$ppadap_api_username = (isset($gateways['ppadap_api_username']) ? $gateways['ppadap_api_username'] : '');
		$ppadap_api_password = (isset($gateways['ppadap_api_password']) ? $gateways['ppadap_api_password'] : '');
		$ppadap_api_signature = (isset($gateways['ppadap_api_signature']) ? $gateways['ppadap_api_signature'] : '');
		$ppadap_app_id = (isset($gateways['ppadap_app_id']) ? $gateways['ppadap_app_id'] : '');
		$ppadap_receiver_email = (isset($gateways['ppadap_receiver_email']) ? $gateways['ppadap_receiver_email'] : '');
	}

	$gateway_settings = array(
		$ppadap_api_username,
		$ppadap_api_password,
		$ppadap_api_signature,
		$ppadap_app_id,
		$ppadap_receiver_email,
	);

	foreach ($gateway_settings as $gs) {
		if (empty($gs)) {
			echo json_encode(array("response" => "failure", 'message' => __('Gateway settings are incomplete', 'memberdeck'). ' ' . __LINE__, 'token' => null));
			exit();
		}
	}

	// Getting the level details
	$id = $_POST['product_id'];
	$level = ID_Member_Level::get_level($id);
	$user = $_POST['Customer'];
	$type = sanitize_text_field($_POST['Type']);
	$txnType = sanitize_text_field($_POST['txnType']);
	$recurring = $level->recurring_type;
	$pwywPrice = sanitize_text_field($_POST['PWYW']);
	$renewable = sanitize_text_field($_POST['Renewable']);
	$guest_checkout = sanitize_text_field($_POST['guestCheckout']);
	// For sending a GET vars to see if it's a preauth payment
	$preauth_check = '';
	$current_user = get_user_by('email', $user['email']);
	// Taking into account product renewable option
	if (isset($renewable) && $renewable) {
		$price = $level->renewal_price;
	}
	else {
		$price = $level->level_price;
		if (isset($pwywPrice) && $pwywPrice > $price) {
			$price = $pwywPrice;
		}
	}
	$price = apply_filters( 'idc_checkout_level_price', $price, $id, ((!empty($current_user)) ? $current_user->ID : ''), false );
	$query_string = $_POST['queryString'];
	$query_string = $query_string.'&price='.$price;
	// If the payment is preapproval or recurring
	if ($type == 'recurring' || $txnType == 'preauth') {
		$user_email = $user['email'];
		/*$max = $price;
		if (!empty($user)) {
			$preauths = ID_Member_Order::get_preorders_by_userid($current_user->ID);
			if (!empty($preauths)) {
				$max = 0;
				foreach ($preauths as $preauth) {
					$max = $max + floatval($preauth->price);
				}
				$max = $max + $price;
			}
		}*/
		try {
			$preapprovalRequest = new \PayPal\Types\AP\PreapprovalRequest(new \PayPal\Types\Common\RequestEnvelope("en_US"), $cur_url.'&ppadap_cancel=1', $ppada_currency, md_get_durl($https).$prefix.'ppadap_success=1&idc_product='.$id.'&paykey=' . $query_string, date('Y-m-d'));

			// If transaction is 'preauth' then there are some separate variables needed
			if ($txnType == 'preauth') {
				$preapprovalRequest->maxTotalAmountOfAllPayments = $price;
				//$preapprovalRequest->maxNumberOfPayments = 1;
				$preapprovalRequest->maxNumberOfPaymentsPerPeriod = 1;
				$preapprovalRequest->maxAmountPerPayment = $price;
				
				// Setting Start Date as today, and ending date after 30 days
				$preapprovalRequest->startingDate = date('Y-m-d');
				// From settings, if not empty, getting the end time of Preauth
				$ending_after_days = ((isset($gateways['ppadap_max_preauth_period']) && !empty($gateways['ppadap_max_preauth_period'])) ? $gateways['ppadap_max_preauth_period'] : '30');
				$preapprovalRequest->endingDate = date('Y-m-d', strtotime("+".$ending_after_days." days"));
				// $preapprovalRequest->memo = "PREAPPROVAL-Authorization";
				$preauth_check = "PREAPPROVAL-Authorization";
			}
			else {
				$preapprovalRequest->paymentPeriod = ($recurring == "annual" ? 'ANNUALLY' : strtoupper($recurring));
				$preapprovalRequest->maxAmountPerPayment = $price;
			}
			// Setting IPN url
			$preapprovalRequest->ipnNotificationUrl = home_url('/').$prefix.'memberdeck_notify=pp_adaptive&user_id=&user_email='.$user['email'].'&user_fname='.urlencode($user['first_name']).'&user_lname='.urlencode($user['last_name']).'&product_id='.$id.'&preauth_check='.$preauth_check . $query_string;

			$config = array(
				"mode" => (($gateways['test'] == 1) ? "sandbox" : "live"),
				// Signature Credential
				"acct1.UserName" => $ppadap_api_username,
				"acct1.Password" => $ppadap_api_password,
				"acct1.Signature" => $ppadap_api_signature,
				"acct1.AppId" => $ppadap_app_id,
			);

			try {
				$service = new \PayPal\Service\AdaptivePaymentsService($config);
				$response = $service->Preapproval($preapprovalRequest);
			}
			catch (Exception $e) {
				echo json_encode(array("response" => "failure", 'message' => $e->getMessage(). ' ' . __LINE__, 'token' => null));
				exit();
			}
		}
		catch (Exception $e) {
			echo json_encode(array("response" => "failure", 'message' => $e->getMessage(). ' ' . __LINE__, 'token' => null));
			exit();
		}
	}
	else {
		// If payment is normal
		$chained = false;
		if ($gateways['epp_fes'] && function_exists('is_id_pro') && is_id_pro()) {
			$check_claim = get_option('md_level_'.$id.'_owner');
			if (!empty($check_claim)) {
				$payment_settings = apply_filters('md_payment_settings', get_user_meta($check_claim, 'md_payment_settings', true));
				if (!empty($payment_settings)) {
					// we process this now, since it is during pay request
					$secondary_receiver = $payment_settings['paypal_email'];
					$enterprise_settings = get_option('idc_enterprise_settings');
					if (!empty($enterprise_settings) && !empty($secondary_receiver)) {
						$fee_type = (isset($enterprise_settings['fee_type']) ? $enterprise_settings['fee_type'] : 'flat');
						$enterprise_fee = (isset($enterprise_settings['enterprise_fee']) ? $enterprise_settings['enterprise_fee'] : null);
						if (!empty($enterprise_fee)) {
							$chained = true;
						}
					}
				}
			}
		}
		$receiver = array();
		if (!$chained) {
			try {
				$receiver[0] = new \PayPal\Types\AP\Receiver();
				$receiver[0]->email = $ppadap_receiver_email;
				$receiver[0]->amount = $price;
			}
			catch (Exception $e) {
				echo json_encode(array("response" => "failure", 'message' => $e->getMessage(). ' ' . __LINE__, 'token' => null));
				exit();
			}
		}
		else {
			//  add chained payment details and remove fee
			$enterprise_fee = apply_filters('idc_fee_amount', $enterprise_fee, $price, $fee_type, 'pp-adaptive');
			$primary_receiver = (isset($enterprise_settings['primary_receiver']) ? $enterprise_settings['primary_receiver']: 'site-admin');
			
			$receiver[0] = new \PayPal\Types\AP\Receiver();
			$receiver[0]->email = apply_filters('idc_ppadap_chained_primary_receiver', $ppadap_receiver_email, $secondary_receiver, $enterprise_settings);
			$receiver[0]->amount = $price;
			$receiver[0]->primary = true;
			$receiver[1] = new \PayPal\Types\AP\Receiver();
			$receiver[1]->email = apply_filters('idc_ppadap_chained_secondary_receiver', $secondary_receiver, $ppadap_receiver_email, $enterprise_settings);
			$receiver[1]->amount = apply_filters('idc_ppadap_secondary_receiver_amount', ($price - $enterprise_fee), $price, $enterprise_fee, $enterprise_settings);
		}
		try {
			$receiverList = new \PayPal\Types\AP\ReceiverList($receiver);
		}
		catch (Exception $e) {
			echo json_encode(array("response" => "failure", 'message' => $e->getMessage(). ' ' . __LINE__, 'token' => null));
			exit();
		}
		try {
			$payRequest = new \PayPal\Types\AP\PayRequest(new \PayPal\Types\Common\RequestEnvelope("en_US"), 'PAY', $cur_url.'&ppadap_cancel=1', $ppada_currency, $receiverList, md_get_durl($https).$prefix.'ppadap_success=1&idc_product='.$id.'&paykey=' . $query_string);
		}
		catch (Exception $e) {
			echo json_encode(array("response" => "failure", 'message' => $e->getMessage(). ' ' . __LINE__, 'token' => null));
			exit();
		}
		// (Optional) The URL to which you want all IPN messages for this payment to be sent. Maximum length: 1024 characters 
		$payRequest->ipnNotificationUrl = home_url('/').$prefix.'memberdeck_notify=pp_adaptive&user_id=&user_email='.$user['email'].'&user_fname='.urlencode($user['first_name']).'&user_lname='.urlencode($user['last_name']).'&product_id='.$id.'&guest_checkout='.$guest_checkout.$query_string;
		$payRequest->memo = (isset($level) ? $level->level_name : '');
		$payRequest->feesPayer = apply_filters('idc_ppadap_fee_payer', 'PRIMARYRECEIVER', $chained, (isset($enterprise_settings) ? $enterprise_settings : ''));
		// Filtering the payRequest object
		$payRequest = apply_filters('idc_ppadap_payrequest', $payRequest, (isset($enterprise_settings) ? $enterprise_settings : ''));

		$config = array(
			"mode" => (($gateways['test'] == 1) ? "sandbox" : "live"),
			// Signature Credential
			"acct1.UserName" => $ppadap_api_username,
			"acct1.Password" => $ppadap_api_password,
			"acct1.Signature" => $ppadap_api_signature,
			"acct1.AppId" => $ppadap_app_id
		);
		try {
			$service = new \PayPal\Service\AdaptivePaymentsService($config);
			$response = $service->Pay($payRequest);
		}
		catch (Exception $e) {
			echo json_encode(array("response" => "failure", 'message' => $e->getMessage(). ' ' . __LINE__, 'token' => null));
			exit();
		}
	}
	if (strtoupper($response->responseEnvelope->ack) == "SUCCESS") {
		if ($type == 'recurring' || $txnType == 'preauth') {
			$token = $response->preapprovalKey;
		} else {
			$token = $response->payKey;
		}
		echo json_encode(array("response" => "success", 'message' => '', "token" => $token, 'return_address' => md_get_durl($https).$prefix.'ppadap_success=1&idc_product='.$id.'&paykey=' . $query_string));
	} else {
		$message = $response->error[0]->message;
		echo json_encode(array('response' => $response->responseEnvelope->ack, 'message' => $message.' '.__LINE__, 'token' => null));
	}
	exit();
}

add_action('wp_ajax_idmember_get_ppadaptive_paykey', 'idmember_get_ppadaptive_paykey');
add_action('wp_ajax_nopriv_idmember_get_ppadaptive_paykey', 'idmember_get_ppadaptive_paykey');

function md_process_preauth() {
	if (isset($_POST['action']) && $_POST['action'] == 'md_process_preauth') {
		global $wpdb;
		global $first_data;
		global $crowdfunding;
		global $stripe_api_version;
		if (isset($_POST['Level'])) {
			$permalink_structure = get_option('permalink_structure');
			$prefix = '?';
			if (empty($permalink_structure)) {
				$prefix = '&';
			}
			$level_id = $_POST['Level'];
			$level_data = ID_Member_Level::get_level($level_id);
			/**
			*
			*/
			$charge_b_fee = false;
			$settings = get_option('memberdeck_gateways');
			$test = (isset($settings['test']) ? $settings['test'] : 0);
			if (function_exists('is_id_pro') && is_id_pro()) {
				if (!empty($settings)) {
					if (is_array($settings)) {
						$esc = $settings['esc'];
						if ($esc == '1') {
							$check_claim = get_option('md_level_'.$level_id.'_owner');
							if (!empty($check_claim)) {
								$md_sc_creds = get_sc_params($check_claim);
								if (!empty($md_sc_creds)) {
									$sc_accesstoken = $md_sc_creds->access_token;
								}
							}
						}
						else if ($settings['efd'] == '1') {
							$gateway_id = $settings['gateway_id'];
							$fd_pw = $settings['fd_pw'];
							$key_id = $settings['key_id'];
							$hmac = $settings['hmac'];
							$efd = $settings['efd'];
						}
					}
				}
			}
			if ($settings['es']) {
				if (!class_exists('Stripe')) {
					if (is_array($settings) && isset($settings['es']) && $settings['es']) {
						if (!class_exists('Stripe')) {
							require_once 'lib/stripe-php-4.2.0/init.php';
						}
					}
				}
				if (!empty($settings)) {
					if (is_array($settings)) {
						$sk = $settings['sk'];
						$tsk = $settings['tsk'];
						$stripe_currency = $settings['stripe_currency'];
					}
				}
			}
			if ($test == '1') {
				if ($settings['es']) {
					\Stripe\Stripe::setApiKey($tsk);
					\Stripe\Stripe::setApiVersion($stripe_api_version);
				}
				if ($settings['efd']) {
					$endpoint = 'https://api.demo.globalgatewaye4.firstdata.com/transaction/v13';
					$wsdl = 'https://api.demo.globalgatewaye4.firstdata.com/transaction/v12/wsdl';
				}
			}
			else {
				if ($settings['es']) {
					\Stripe\Stripe::setApiKey($sk);
					\Stripe\Stripe::setApiVersion($stripe_api_version);
				}
				if ($settings['efd']) {
					$endpoint = 'https://api.globalgatewaye4.firstdata.com/transaction/v12';
					$wsdl = 'https://api.globalgatewaye4.firstdata.com/transaction/v12/wsdl';
				}
			}
			if ($settings['eauthnet']) {
				// Requiring the library of Authorize.Net
				require('lib/AuthorizeNet/sdk-php-master/vendor/autoload.php');
				define("AUTHORIZENET_API_LOGIN_ID", $settings['auth_login_id']);
				define("AUTHORIZENET_TRANSACTION_KEY", $settings['auth_transaction_key']);
				if ($test == '1') {
					define("AUTHORIZENET_SANDBOX", true);
				} else {
					define("AUTHORIZENET_SANDBOX", false);
				}
			}

			// Hook before processing pre-orders
			do_action('idc_before_preauth_processing', $level_id);

			$preorders = ID_Member_Order::get_md_preorders($level_id);
			$success = array();
			$fail = array();
			$response = array();
			if (!empty($preorders)) {
				$level = ID_Member_Level::get_level($level_id);
				$price = $level->level_price;
			}
			foreach ($preorders as $capture) {
				// need to get customer id
				// need to update order from W to C and txn from pre to txn
				$user_id = $capture->user_id;
				$userdata = get_userdata($user_id);
				$email = (isset($userdata->user_email) ? $userdata->user_email : '');
				$pre_info = ID_Member_Order::get_preorder_by_orderid($capture->id);
				if (!empty($pre_info)) {
					$order_id = $pre_info->order_id;
					$order = new ID_Member_Order($order_id);
					$the_order = $order->get_order();
					if (!empty($the_order)) {
						if (!empty($the_order->price)) {
							$price = $the_order->price;
						}
					}
					$gateway = $pre_info->gateway;
					if (empty($gateway) || $gateway == 'stripe') {
						$customer_id = ID_Member::get_customer_id($user_id);
					}
					else if ($gateway == 'fd') {
						$fd_card_details = get_user_meta($user_id, 'fd_card_details', true);
						if (!empty($fd_card_details)) {
							$cc_expiry = $fd_card_details['cc_expiry'];
							$credit_card_type = $fd_card_details['credit_card_type'];
							$fd_token = $fd_card_details['fd_token'];
							$cardholder_name = $fd_card_details['cardholder_name'];
							$customer_id = $fd_token;
						}
					}
					else if ($gateway == 'authorize.net') {
						$authnet_customer_ids = authnet_customer_id();
						if (!empty($authnet_customer_ids)) {
							$authorizenet_payment_profile_id = $authnet_customer_ids['authorizenet_payment_profile_id'];
							$authorizenet_profile_id = $authnet_customer_ids['authorizenet_profile_id'];
							$customer_id = $authorizenet_payment_profile_id;
						}
					}
					else if ($gateway == 'pp-adaptive') {
						// Putting charge token (Pre approval key)
						$customer_id = $pre_info->charge_token;
					}
					// #devnote create general filter for this
					$customer_id = apply_filters('idc_preauth_customer_id', (isset($customer_id) ? $customer_id : ''), $pre_info->gateway, $user_id);
					try {
						if ($pre_info->gateway == 'fd') {
							if (!empty($fd_token)) {
								$paid = 0;
								$refunded = 0;
								$data = array('gateway_id' => $gateway_id,
								'password' => $fd_pw,
								'transaction_type' => '00',
								'amount' => $price,
								'cardholder_name' => $cardholder_name,
								'transarmor_token' => $fd_token,
								'credit_card_type' => $credit_card_type,
								'cc_expiry' => $cc_expiry);
								$data_string = json_encode($data);

								$gge4Date = strftime("%Y-%m-%dT%H:%M:%S", time() - (int) substr(date('O'), 0, 3)*60*60) . 'Z';
								$digest = sha1($data_string);
								$size = sizeof($data_string);

								$method = 'POST';
								$content_type = 'application/json';

								$hashstr = $method."\n".$content_type."\n".$digest."\n".$gge4Date."\n".'/transaction/v13';

								$authstr = 'GGE4_API ' . $key_id . ':' . base64_encode(hash_hmac("sha1", $hashstr, $hmac, true));

								$headers = array('Content-Type: '.$content_type,
									'X-GGe4-Content-SHA1: '.$digest,
									'Authorization: '.$authstr,
									'X-GGe4-Date: '.$gge4Date,
									'charset=UTF-8',
									'Accept: '.$content_type
								);
								try {
									$ch = curl_init($endpoint);
									curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
									curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
									if ($test == 1) {
										curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
									}
									curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

									$res = curl_exec($ch);

									if (curl_errno($ch)) {
										$error = __('First Data Error', 'memberdeck');
										$paid = 0;
										$refunded = 0;
										//echo 'error:' . curl_error($c);
									}
									else {
										//print_r($res);
										$res_string = json_decode($res);
										//print_r($res_string);
										if (!empty($res_string)) {
											if ($res_string->transaction_approved == 1) {
												// it is approved
												$txn_id = $res_string->authorization_num;
												$paid = 1;
												//$refunded = 0;
											}
											else{
												print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $res)));
												exit;
											}
										}
										else{
											print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $res." (order# ".$order_id.") ")));
											exit;
										}
									}
								}
								catch (Exception $e) {
									$error = $e->getMessage();
									$paid = 0;
									$refunded = 0;
									//print_r($e);
								}
							}
						}
						else if ($pre_info->gateway == 'authorize.net') {
							$transaction = new AuthorizeNetTransaction;
							$transaction->amount = $price;
							$transaction->customerPaymentProfileId = $customer_id;
							$transaction->customerProfileId = $authorizenet_profile_id;
							$transaction->approvalCode = $pre_info->charge_token;

							$request = new AuthorizeNetCIM;
							$response = $request->createCustomerProfileTransaction('CaptureOnly', $transaction);
							if ($response->isOk()) {
								$transactionResponse = $response->getTransactionResponse();
								$txn_id = $transactionResponse->transaction_id;
								$paid = 1;
								$refunded = 0;
								// echo "transaction done\n";
							} else if ($response->isError()) {
								$error = __('Could not process transaction', 'memberdeck').' '.__LINE__;
								$paid = 0;
								$refunded = 0;
								//print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => __('Could not create charge token', 'memberdeck').': '.__LINE__)));
								// echo "transaction error: ".$response->getMessageText()."\n";
							}
						}
						else if ($pre_info->gateway == 'pp-adaptive') {
							// Processing the pre-approval transaction
							require 'lib/PayPalAdaptive/lib/vendor/autoload.php';
							if ($test == '1') {
								$ppada_currency = $settings['ppada_currency'];
								$ppadap_api_username = $settings['ppadap_api_username_test'];
								$ppadap_api_password = $settings['ppadap_api_password_test'];
								$ppadap_api_signature = $settings['ppadap_api_signature_test'];
								$ppadap_app_id = $settings['ppadap_app_id_test'];
								$ppadap_receiver_email = $settings['ppadap_receiver_email_test'];
							}
							else {
								$ppada_currency = $settings['ppada_currency'];
								$ppadap_api_username = $settings['ppadap_api_username'];
								$ppadap_api_password = $settings['ppadap_api_password'];
								$ppadap_api_signature = $settings['ppadap_api_signature'];
								$ppadap_app_id = $settings['ppadap_app_id'];
								$ppadap_receiver_email = $settings['ppadap_receiver_email'];
							}
							$chained = false;
							if ($settings['epp_fes'] && function_exists('is_id_pro') && is_id_pro()) {
								$check_claim = get_option('md_level_'.$the_order->level_id.'_owner');
								if (!empty($check_claim)) {
									$payment_settings = apply_filters('md_payment_settings', get_user_meta($check_claim, 'md_payment_settings', true));
									if (!empty($payment_settings)) {
										// we process this now, since it is during pay request
										$secondary_receiver = $payment_settings['paypal_email'];
										$enterprise_settings = get_option('idc_enterprise_settings');
										if (!empty($enterprise_settings) && !empty($secondary_receiver)) {
											$chained = true;
											$fee_type = (isset($enterprise_settings['fee_type']) ? $enterprise_settings['fee_type'] : 'flat');
											$enterprise_fee = (isset($enterprise_settings['enterprise_fee']) ? $enterprise_settings['enterprise_fee'] : null);
										}
									}
								}
							}
							$receiver = array();
							if (!$chained) {
								$receiver[0] = new \PayPal\Types\AP\Receiver();
								$receiver[0]->email = $ppadap_receiver_email;
								$receiver[0]->amount = $price;
							}
							else {
								//  add chained payment details and remove fee
								$enterprise_fee = apply_filters('idc_fee_amount', $enterprise_fee, $price, $fee_type, $pre_info->gateway, $user_id, $the_order->level_id);
								
								$receiver[0] = new \PayPal\Types\AP\Receiver();
								$receiver[0]->email = apply_filters('idc_ppadap_chained_primary_receiver', $ppadap_receiver_email, $secondary_receiver, $enterprise_settings);
								$receiver[0]->amount = $price;
								$receiver[0]->primary = true;
								$receiver[1] = new \PayPal\Types\AP\Receiver();
								$receiver[1]->email = apply_filters('idc_ppadap_chained_secondary_receiver', $secondary_receiver, $ppadap_receiver_email, $enterprise_settings);
								$receiver[1]->amount = apply_filters('idc_ppadap_secondary_receiver_amount', ($price - $enterprise_fee), $price, $enterprise_fee, $enterprise_settings);
							}
							try {
								$receiverList = new \PayPal\Types\AP\ReceiverList($receiver);
								$payRequest = new \PayPal\Types\AP\PayRequest(new \PayPal\Types\Common\RequestEnvelope("en_US"), 'PAY', home_url('/').$prefix.'ppadap_cancel=1', $ppada_currency, $receiverList, home_url('/').$prefix.'ppadap_success=1');
							}
							catch (Exception $e) {
								$error = $e->getMessage();
								$paid = 0;
								$refunded = 0;
							}
							// (Optional) The URL to which you want all IPN messages for this payment to be sent. Maximum length: 1024 characters 
							// no need for query string here because we aren't posting any new order data, and it is being updated via our general handler
							$payRequest->ipnNotificationUrl = home_url('/').$prefix.'memberdeck_notify=pp_adaptive&user_id='.$user_id.'&user_email='.$email.'&user_fname='.$userdata->user_firstname.'&user_lname='.$userdata->user_lastname.'&product_id='.$the_order->level_id.'&price='.$price;
							$payRequest->preapprovalKey  = $pre_info->charge_token;
							$payRequest->feesPayer = apply_filters('idc_ppadap_fee_payer', "PRIMARYRECEIVER", $chained, (isset($enterprise_settings) ? $enterprise_settings : ''));
							$payRequest->memo = (isset($level) ? $level->level_name : '');

							$config = array(
								"mode" => (($settings['test'] == 1) ? "sandbox" : "live"),
								// Signature Credential
								"acct1.UserName" => $ppadap_api_username,
								"acct1.Password" => $ppadap_api_password,
								"acct1.Signature" => $ppadap_api_signature,
								"acct1.AppId" => $ppadap_app_id
							);
							
							try {
								$service = new \PayPal\Service\AdaptivePaymentsService($config);
								$response = $service->Pay($payRequest);
								if (strtoupper($response->responseEnvelope->ack) == "SUCCESS") {
									$paid = 1;
									$txn_id = $response->paymentInfoList->paymentInfo[0]->transactionId;
									$refunded = 0;
								} else {
									$error = $response->error[0]->message;
									$paid = 0;
									$refunded = 0;
								}
							}
							catch (Exception $e) {
								$error = $e->getMessage();
								$paid = 0;
								$refunded = 0;
							}
						}
						else if ($pre_info->gateway == 'stripe') {
							// we are using customer ID to charge
							$priceincents = str_replace(',', '', $price) * 100;
							if (!empty($sc_accesstoken)) {
								$fee = 0;
								$sc_settings = get_option('md_sc_settings');
								if (!empty($sc_settings)) {
									if (!is_array($sc_settings)) {
										$sc_settings = maybe_unserialize($sc_settings);
									}
									if (is_array($sc_settings)) {
										$app_fee = apply_filters('idc_app_fee', $sc_settings['app_fee'], $level_data);
										$fee_type = apply_filters('idc_fee_type', $sc_settings['fee_type']);
										$fee = apply_filters('idc_fee_amount', $app_fee, $priceincents, $fee_type, $pre_info->gateway, $user_id, $the_order->level_id);
									}
								}
								try {
									if (!empty($pre_info->charge_token) && $pre_info->charge_token !== $customer_id) {
							   			$card_id = \Stripe\Token::create(array(
							   			"customer" => $customer_id,
										"card" => $pre_info->charge_token),
										$sc_accesstoken);
									}
									else {
										$card_id = \Stripe\Token::create(array(
											"customer" => $customer_id),
											$sc_accesstoken);
									}
									$card_id = $card_id->id;
									$stripe_params = array(
										'receipt_email' => $email,
										'amount' => $priceincents,
										'source' => $card_id,
										'description' => $fname . ' ' . $lname . ' - ' . $level_data->level_name,
										'currency' => $stripe_currency,
										'application_fee' => $fee);
								}
								catch (\Stripe\Error\Card $e) {
									// Card was declined
									$jsonbody = $e->getJsonBody();
									$message = $jsonbody['error']['message'].' '.__LINE__;
									//print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
									//exit;
									$paid = 0;
									$refunded = 0;
								}
								catch (\Stripe\Error\InvalidRequest $e) {
									$jsonbody = $e->getJsonBody();
									$message = $jsonbody['error']['message'].' '.__LINE__;
									//print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
									//exit;
									$paid = 0;
									$refunded = 0;
								}
							}
							else {
								$stripe_params = array(
							    'customer' => $customer_id,
							    'receipt_email' => $email,
								"amount" => $priceincents,
							    'description' => $fname . ' ' . $lname . ' - ' . $level_data->level_name,
							    "currency" => $stripe_currency);

							    if (!empty($pre_info->charge_token) && $pre_info->charge_token !== $customer_id) {
								    $stripe_params["card"] = $pre_info->charge_token;
								}
							}
							try {
								if (isset($sc_accesstoken)) {
									$charge = \Stripe\Charge::create($stripe_params, $sc_accesstoken);
								}
								else {
									$charge = \Stripe\Charge::create($stripe_params);
								}
								$paid = $charge->paid;
								$refunded = $charge->refunded;
								$txn_id = $charge->id;
								$created = $charge->created;
							}
							catch (\Stripe\Error\Card $e) {
								// Card was declined
								$body = $e->getJsonBody();
									$err  = $body['error'];
								$error = $err['message'];
								//$fail[] = "failure";
								$paid = 0;
								$refunded = 0;
							}
							catch (\Stripe\Error\InvalidRequest $e) {
								$jsonbody = $e->getJsonBody();
								$error = $jsonbody['error']['message'].' '.__LINE__;
								//print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
								//exit;
								$paid = 0;
								$refunded = 0;
							}
						}
						else {
							// external use for modules and plugins
							$paid = 0;
							$refunded = 0;
							$preauth_data = apply_filters('idc_preauth_data_'.$pre_info->charge_token, new stdClass());

							if (!empty($preauth_data)) {
								$paid = $preauth_data->paid;
								$refunded = $preauth_data->refunded;
								$txn_id = $preauth_data->txn_id;
								$error = $preauth_data->error;
							}
						}

						// Filter for $paid
						$paid = apply_filters('idc_preauth_'.$pre_info->charge_token.'_paid', (isset($paid) ? $paid : 0), $price, $pre_info->gateway, $customer_id, $pre_info->charge_token, $settings);
						// Refunded filter
						$refunded = apply_filters('idc_preauth_'.$pre_info->charge_token.'_refund', (isset($refunded) ? $refunded : 0), $price, $pre_info->gateway, $settings);

						if ($paid == 1 && $refunded !== 1) {
							$txn_id = apply_filters('idc_preauth_paid_transaction', (isset($txn_id) ? $txn_id : ''), $pre_info->charge_token, $customer_id, $pre_info->gateway, $settings);
							$payment_variables = array(
								"txn_id" => $txn_id,
								"status" => "C",
								"id" => $capture->id
								);
					  		// Payment succeeded and was not refunded
					  		$mdid_order = mdid_by_orderid($capture->id);
							if (!empty($mdid_order)) {
								$customer_id = $mdid_order->customer_id;
								if (isset($mdid_order->pay_id) && $mdid_order->pay_id !== '') {
									$pay_id = $mdid_order->pay_info_id;
								}
							}
					  		if (isset($pay_id)) {
								$payment_variables['pay_id'] = $pay_id;
								do_action('id_payment_success', $capture->id);
							}
					  		mdid_set_approval($payment_variables);
					  		$user = get_userdata($user_id);
					  		$email = $user->user_email;
					  		$paykey = md5($email.time());
							$response = array('code' => 'success');
							$success[] = $txn_id;
							do_action('memberdeck_payment_success', $user_id, $capture->id, $paykey, null, $gateway);
							do_action('idmember_receipt', $user_id, $price, $level_id, $gateway, $capture->id, $fields = array());
						}
						else {
							//print_r($charge);
							$meta = array('date' => time(), 'error' => $error);
							ID_Member_Order::update_order_meta($pre_info->order_id, 'preauth_error', $meta);
							$response = array('code' => 'failure');
							$fail[] = "failure";
						}
					}
					catch(Exception $e) {
						$error = $e->getMessage();
						//echo $e;
						$fail[] = "failure";
						$meta = array('date' => time(), 'error' => $error);
						ID_Member_Order::update_order_meta($pre_info->order_id, 'preauth_error', serialize($meta));
					}
				}
			}

			// Hook after processing pre-orders
			do_action('idc_after_preauth_processing', $level_id, $preorders, $success, $fail);
		}
		$successes = count($success);
		$failures = count($fail);
		$response["counts"] = array("success" => $successes, "failures" => $failures);
		print_r(json_encode($response));
		/**
		*
		*/
	}
	exit();
}

add_action('wp_ajax_md_process_preauth', 'md_process_preauth');

/**
* MDID Core Functions
*/

if ($crowdfunding) {
	add_action('wp_head', 'mdid_replace_purchaseform');
	add_action('md_purchase_extrafields', 'mdid_project_fields', 1);
	if (is_id_pro()) {
		add_action('idcf_project_success', 'idc_success_notification', 10, 2);
		add_action('idcf_project_success', 'idc_success_notification_admin', 10, 2);
	}
	add_action('id_content_after', 'mdid_backers_list');
}

/**
 * Action to display list of backers along with content
 */
function mdid_backers_list($project_id, $post_id = null) {
	$content = '';
	// Getting crowdfunding project details
	$project = new ID_Project($project_id);
	if (empty($post_id)) {
		$post_id = $project->get_project_postid();
	}
	$the_project = $project->the_project();
	$project_orders = ID_Order::get_orders_by_project($project_id, 'ORDER BY id DESC LIMIT 10');
	$all_orders = ID_Order::get_total_orders_by_project($project_id);
	$order_count = $all_orders->count;
	if (!empty($project_orders)) {
		// We have the project orders, now search mdid_orders for pay ids we have and add them all into array
		$mdid_orders = array();
		foreach ($project_orders as $idcf_order) {
			$mdid_order = mdid_payid_check($idcf_order->id);
			if (!empty($mdid_order)) {
				array_push($mdid_orders, $mdid_order);
			}
		}
		// now looping mdid orders and getting unique users for those orders
		$content .= '<ul class="ign_backer_list" data-count="'.$order_count.'">';
		if (!empty($mdid_orders)) {
			foreach ($mdid_orders as $mdid_order) {
				$order = new ID_Member_Order($mdid_order->order_id);
				$idc_order = $order->get_order();
				if (!empty($idc_order)) {
					// Getting level/product info for price and name
					$level = ID_Member_Level::get_level($idc_order->level_id);
					if (!empty($level)) {
						// Getting the meta for currency
						$gateway_info = ID_Member_Order::get_order_meta($order->id, 'gateway_info', true);
						$price = $idc_order->price;
						if (!empty($gateway_info) && $gateway_info['gateway'] == 'credit') {
							$price = $level->credit_value;
						}
						// Getting user info
						$user_info = apply_filters('idc_backer_userdata', get_userdata($idc_order->user_id), $idc_order->id);
						// Writing into table
						$content .= '<li class="backer_list_item backers_tab content_tab">
										<div class="backer_list_avatar">'.(is_id_pro() && isset($user_info->ID) ? '<a href="'.md_get_durl().'?'.apply_filters('idc_backer_profile_slug', 'backer_profile').'='.$user_info->ID.'">' : '').get_avatar(apply_filters('idc_backers_avatar_id', $idc_order->user_id, $idc_order->id), 30).(is_id_pro() && isset($user_info->ID) ? '</a>' : '').'</div>
										<div class="backer_list_namedate">
											<div class="backer_list_name">'.(is_id_pro() && isset($user_info->ID) ? '<a href="'.md_get_durl().'?'.apply_filters('idc_backer_profile_slug', 'backer_profile').'='.$user_info->ID.'">' : '').apply_filters('idc_backers_listing_name', (isset($user_info->display_name) ? $user_info->display_name : __('Anonymous', 'memberdeck')), $idc_order->id, $idc_order->user_id).(is_id_pro() && isset($user_info->ID) ? '</a>' : '').'</div>
											<div class="backer_list_date">'.date('m/d/Y', strtotime($idc_order->order_date)).'</div>
										</div>
										<div class="backer_list_project"><a class="backer_list project_url" href="'.get_permalink($post_id).'">'.$the_project->product_name.'</a></div>
										<div class="backer_list_levelprice">
											<div class="backer_list_level">'.apply_filters('idc_backers_listing_level_name', $level->level_name, $idc_order->id, $level->id).'</div>
											<div class="backer_list_price">'.apply_filters('idc_order_price', $price, $idc_order->id, $gateway_info).'</div>
										</div>
									</li>';
					}
				}
			}
		}
	}
	$content .= '</ul>';
	if ($order_count > absint(10)) {
		$content .= '<div name="more" class="backer_list_more"><a class="" href="#more" data-first="0" data-last="9" data-total="'.$order_count.'" data-project="'.$project_id.'">'.__('show more', 'memberdeck').'</a></div>';
	}
	echo $content;
}

function mdid_show_more_backers() {
	$vars = $_POST['Vars'];
	if (!empty($vars)) {
		$project_id = $vars['Project'];
		$content = '';
		// Getting crowdfunding project details
		$project = new ID_Project($project_id);
		$post_id = $project->get_project_postid();
		$the_project = $project->the_project();
		$misc = 'ORDER BY id DESC LIMIT '.absint($vars['Last'] + 1).', 20';
		$project_orders = ID_Order::get_orders_by_project($project_id, $misc);
		if (!empty($project_orders)) {
			// We have the project orders, now search mdid_orders for pay ids we have and add them all into array
			$mdid_orders = array();
			foreach ($project_orders as $idcf_order) {
				$mdid_order = mdid_payid_check($idcf_order->id);
				if (!empty($mdid_order)) {
					array_push($mdid_orders, $mdid_order);
				}
			}
			// now looping mdid orders and getting unique users for those orders
			if (!empty($mdid_orders)) {
				foreach ($mdid_orders as $mdid_order) {
					$order = new ID_Member_Order($mdid_order->order_id);
					$idc_order = $order->get_order();
					if (!empty($idc_order)) {
						// Getting level/product info for price and name
						$level = ID_Member_Level::get_level($idc_order->level_id);
						if (!empty($level)) {
							// Getting the meta for currency
							$order_meta = ID_Member_Order::get_order_meta($order->id, 'gateway_info', true);
							$price = $idc_order->price;
							if (!empty($meta) && $meta['gateway'] == 'credit') {
								$price = $level->credit_value;
							}
							// Getting user info
							$user_info = apply_filters('idc_backer_userdata', get_userdata($idc_order->user_id), $idc_order->id);
							// Writing into table
							$content .= '<li class="backer_list_item backers_tab content_tab new_backer_item" style="display: none;">
											<div class="backer_list_avatar">'.(is_id_pro() && isset($user_info->ID) ? '<a href="'.md_get_durl().'/?'.apply_filters('idc_backer_profile_slug', 'backer_profile').'='.$user_info->ID.'">' : '').get_avatar(apply_filters('idc_backers_avatar_id', $idc_order->user_id, $idc_order->id), 30).(is_id_pro() && isset($user_info->ID) ? '</a>' : '').'</div>
											<div class="backer_list_namedate">
												<div class="backer_list_name">'.(is_id_pro() && isset($user_info->ID) ? '<a href="'.md_get_durl().'/?'.apply_filters('idc_backer_profile_slug', 'backer_profile').'='.$user_info->ID.'">' : '').apply_filters('idc_backers_listing_name', (isset($user_info->display_name) ? $user_info->display_name : __('Anonymous', 'memberdeck')), $idc_order->id, $idc_order->user_id).(is_id_pro() && isset($user_info->ID) ? '</a>' : '').'</div>
												<div class="backer_list_date">'.date('m/d/Y', strtotime($idc_order->order_date)).'</div>
											</div>
											<div class="backer_list_project"><a class="backer_list project_url" href="'.get_permalink($post_id).'">'.$the_project->product_name.'</a></div>
											<div class="backer_list_levelprice">
												<div class="backer_list_level">'.apply_filters('idc_backers_listing_level_name', $level->level_name, $idc_order->id, $level->id).'</div>
												<div class="backer_list_price">'.apply_filters('idc_order_price', $price, $idc_order->id, $gateway_info).'</div>
											</div>
										</li>';
						}
					}
				}
			}
		}
	}
	print_r(json_encode($content));
	exit;
}

add_action('wp_ajax_mdid_show_more_backers', 'mdid_show_more_backers');
add_action('wp_ajax_nopriv_mdid_show_more_backers', 'mdid_show_more_backers');

function mdid_replace_purchaseform() {
	if (isset($_GET['mdid_checkout'])) {
		add_filter('the_content', 'mdid_set_form', 1);
	}
}

function mdid_project_fields() {
	if (isset($_GET['mdid_checkout'])) {
		$project_id = absint($_GET['mdid_checkout']);
	}
	else {
		$project_id = null;
	}
	if (isset($_GET['level'])) {
		$level = $_GET['level'];
	}
	else {
		$level = null;
	}
	$fields = '<input type="hidden" name="mdid_checkout" value="1" />';
	$fields .= '<input type="hidden" name="project_id" value="'.$project_id.'" />';
	$fields .= '<input type="hidden" name="project_level" value="'.$level.'"/>';
	echo $fields;
	return;
}

function idc_success_notification($post_id, $project_id) {
	// create message
	$text = idc_text_format(get_option('success_notification'));
	if (empty($text)) {
		$text = idc_text_format(get_option('success_notification_default'));
	}
	if (!empty($text)) {
		// get project info
		$project = new ID_Project($project_id);
		$the_project = $project->the_project();
		$end = date(get_option( 'date_format' ), get_post_meta( $post_id, 'ign_fund_end', true )); //conversion of timestamp to string so it doesn't changes any frontend view
		$post = get_post($post_id);
		if (!empty($post)) {
			$project_name = $post->post_title;
			$project_url = get_permalink( $post_id );
		}
		else {
			$project_name = $the_project->product_name;
			$project_url = $the_project->product_url;
		}
		// company info
		$settings = get_option('md_receipt_settings');
		if (!empty($settings)) {
			if (!is_array($settings)) {
				$settings = unserialize($settings);
			}
			$coname = apply_filters('idc_company_name', $settings['coname']);
			$coemail = $settings['coemail'];
		}
		else {
			$coname = apply_filters('idc_company_name', '');
			$coemail = get_option('admin_email', null);
		}
		// filter merge tags
		$merge_swap = array(
			array(
				'tag' => '{{PROJECT_NAME}}',
				'swap' => $project_name
				),
			array(
				'tag' => '{{PROJECT_URL}}',
				'swap' => $project_url
				),	
			array(
				'tag' => '{{END_DATE}}',
				'swap' => $end
				),
			array(
				'tag' => '{{COMPANY_NAME}}',
				'swap' => $coname
				),
			array(
				'tag' => '{{COMPANY_EMAIL}}',
				'swap' => $coemail
				),
			);
		foreach ($merge_swap as $swap) {
			$text = str_replace($swap['tag'], $swap['swap'], $text);
		}
		// get all orders
		$idcf_orders = ID_Order::get_orders_by_project($project_id);
		if (!empty($idcf_orders)) {
			$email_array = array();
			foreach ($idcf_orders as $idcf_order) {
				$email = $idcf_order->email;
				$pay_id = $idcf_order->id;
				$mdid_order = mdid_payid_check($pay_id);
				if (!empty($mdid_order) && !in_array($email, $email_array)) {
					$idc_order = $mdid_order->order_id;
					$order = new ID_Member_Order($idc_order);
					$the_order = $order->get_order();
					if (!empty($the_order)) {
						$user_id = $the_order->user_id;
						$user = get_user_by('id', $user_id);
						if (!empty($user)) {
							$fname = $user->user_firstname;
							$lname = $user->user_lastname;
							$name_text = str_replace('{{NAME}}', $fname.' '.$lname, $text);
						}
						$amount = $the_order->price;
						$price = apply_filters('idc_order_price', $amount, $idc_order);
						$user_text = str_replace('{{AMOUNT}}', $price, $name_text);
						$message = '<html><body>';
						$message .= wpautop($user_text);
						$message .= '</body></html>';
						$subject = __('Successful Project Notification', 'memberdeck');
						$mail = new ID_Member_Email($email, $subject, $message, (isset($user_id) ? $user_id : ''));
						$send_mail = $mail->send_mail();
						$email_array[] = $email;
					}
				}
				
			}
		}
	}
}

function idc_success_notification_admin($post_id, $project_id) {
	// create message
	$text = idc_text_format(get_option('success_notification_admin'));
	if (empty($text)) {
		$text = idc_text_format(get_option('success_notification_admin_default'));
	}
	if (!empty($text)) {
		// get project info
		$project = new ID_Project($project_id);
		$the_project = $project->the_project();
		$end = date(get_option( 'date_format' ), get_post_meta( $post_id, 'ign_fund_end', true )); //conversion of timestamp to string so it doesn't changes any frontend view
		$post = get_post($post_id);
		if (!empty($post)) {
			$project_name = $post->post_title;
			$project_url = get_permalink( $post_id );
		}
		else {
			$project_name = $the_project->product_name;
			$project_url = $the_project->product_url;
		}
		// company info
		$settings = get_option('md_receipt_settings');
		if (!empty($settings)) {
			if (!is_array($settings)) {
				$settings = unserialize($settings);
			}
			$coname = apply_filters('idc_company_name', $settings['coname']);
			$coemail = $settings['coemail'];
		}
		else {
			$coname = '';
			$coemail = get_option('admin_email', null);
		}
		// filter merge tags
		$merge_swap = array(
			array(
				'tag' => '{{PROJECT_NAME}}',
				'swap' => $project_name
				),
			array(
				'tag' => '{{PROJECT_URL}}',
				'swap' => $project_url
				),	
			array(
				'tag' => '{{END_DATE}}',
				'swap' => $end
				),
			array(
				'tag' => '{{COMPANY_NAME}}',
				'swap' => $coname
				),
			array(
				'tag' => '{{COMPANY_EMAIL}}',
				'swap' => $coemail
				),
			);
		foreach ($merge_swap as $swap) {
			$text = str_replace($swap['tag'], $swap['swap'], $text);
		}
		$subject = __('Successful Project Notification', 'memberdeck');
		$message = '<html><body>';
		$message .= wpautop($text);
		$message .= '</body></html>';
		// fire to admin
		$mail = new ID_Member_Email($coemail, $subject, $message);
		$send_mail = $mail->send_mail();
		// fire to project owner
		$assignments = get_assignments_by_project($project_id);
		if (!empty($assignments)) {
			foreach ($assignments as $assignment) {
				$level_id = $assignment->level_id;
				if ($level_id > 0) {
					break;
				}
			}
			if (isset($level_id)) {
				$user_id = get_option('md_level_'.$level_id.'_owner');
				$user = get_user_by('id', $user_id);
				if (!empty($user)) {
					$fname = $user->user_firstname;
					$lname = $user->user_lastname;
					$message = str_replace('{{NAME}}', $fname.' '.$lname, $message);
				}
				$subject .= ' owner';
				$mail = new ID_Member_Email($user->user_email, $subject, $message, (isset($user_id) ? $user_id : ''));
				$send_mail = $mail->send_mail();
			}
		}
	}
}

function is_level_available($project_id, $level) {
	$assignments = get_assignments_by_project($project_id);
	foreach ($assignments as $assignment) {
		$project_levels = get_project_levels($assignment->assignment_id);
		if (!empty($project_levels)) {
			$data = unserialize($project_levels->levels);
			if (is_array($data)) {
				if (in_array($level, $data)) {
					return false;
				}
			}
		}
	}
	return true;
}

function mdid_get_owner($project_id, $level) {
	$assignments = get_assignments_by_project($project_id);
	foreach ($assignments as $assignment) {
		$project_levels = get_project_levels($assignment->assignment_id);
		if (!empty($project_levels)) {
			$data = unserialize($project_levels->levels);
			if (is_array($data)) {
				if (in_array($level, $data)) {
					return $assignment->level_id;
				}
			}
		}
	}
	return;
}

function mdid_get_child($level) {
	$assignments = get_assignments_by_level($level);
	foreach($assignments as $assignment) {

	}
}

function get_assignments_by_level($level) {
	global $wpdb;
	$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'mdid_assignments WHERE level_id = %s', $level);
	$res = $wpdb->get_results($sql);
	return $res;
}

function get_assignments_by_project($project_id, $misc = null) {
	global $wpdb;
	$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'mdid_assignments WHERE project_id = %s'.((!empty($misc)) ? ' '.$misc : ' ORDER BY assignment_id ASC'), $project_id);
	$res = $wpdb->get_results($sql);
	return $res;
}

function get_project_levels($assignment_id) {
	global $wpdb;
	$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'mdid_project_levels WHERE id = %d', $assignment_id);
	$res = $wpdb->get_row($sql);
	return $res;
}

function mdid_get_selected() {
	global $wpdb;
	$sql = 'SELECT * FROM '.$wpdb->prefix.'mdid_assignments';
	$res = $wpdb->get_results($sql);
	$active_projects = array();
	foreach ($res as $assignment) {
		$active_projects[] = $assignment->project_id;
	}
	return $active_projects;
}

function mdid_insert_payinfo($fname, $lname, $email, $project_id, $transaction_id, $proj_level, $price, $status = 'P', $created_at = null) {
	if (empty($created_at)) {
		$created_at = date('Y-m-d H:i:s');
	}
	global $wpdb;
	$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'ign_pay_info (first_name,
					last_name,
					email, 
					product_id, 
					transaction_id, 
					product_level, 
					prod_price,
					status,
					created_at
					) VALUES (
					%s,
					%s,
					%s,
					%d,
					%s,
					%d,
					%s,
					%s,
					%s
					)', $fname, $lname, $email, $project_id, $transaction_id, $proj_level, $price, $status, $created_at);
	$res = $wpdb->query($sql);
	$pay_id = $wpdb->insert_id;
	if (isset($pay_id)) {
		do_action('id_modify_order', $pay_id, 'insert');
		do_action('id_insert_order', $pay_id);
		return $pay_id;
	}
}

function mdid_insert_order($custid, $pay_info_id, $order_id = '', $sub_id = '') {
	global $wpdb;
	if (isset($sub_id)) {
		// subscription genius
		$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'mdid_orders (customer_id, order_id, pay_info_id, subscription_id) VALUES (%s, %s, %d, %s)', $custid, $order_id, $pay_info_id, $sub_id);
	}
	else {
		// this is a normal order
		$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'mdid_orders (customer_id, order_id, pay_info_id, subscription_id) VALUES (%s, %s, %d, %s)', $custid, $order_id, $pay_info_id, $sub_id);
	}
	$res = $wpdb->query($sql);
	$mdid_id = $wpdb->insert_id;
	if (isset($mdid_id)) {
		return $mdid_id;
	}
}

function mdid_remove_order($order_id) {
	global $wpdb;
	$sql = $wpdb->prepare('DELETE FROM '.$wpdb->prefix.'mdid_orders WHERE order_id = %d', $order_id);
	$res = $wpdb->query($sql);
}

function mdid_member_orders($user_id) {
	global $wpdb;
	//$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'memberdeck_orders LEFT JOIN '.$wpdb->prefix.'mdid_orders WHERE '.$wpdb->prefix.'memberdeck_orders.')
}

function mdid_transaction_to_order($id, $transaction_id) {
	global $wpdb;
	$order = new ID_Member_Order(null, null, null, null, $transaction_id);
	$transaction = $order->get_transaction();
	if (isset($transaction)) {
		$order_id = $transaction->id;
		if (isset($order_id)) {
			$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'mdid_orders SET order_id = %s WHERE id = %d', $order_id, $id);
			$res = $wpdb->query($sql);
		}
	}	
}

function mdid_plan_match($id, $sub_id) {
	global $wpdb;
	$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'mdid_orders SET subscription_id = %s WHERE id = %d', $sub_id, $id);
	$res = $wpdb->query($sql);
}

function mdid_orders_bycustid($custid) {
	global $wpdb;
	$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'mdid_orders WHERE customer_id = %s', $custid);
	$res = $wpdb->get_results($sql);
	return $res;
}

function mdid_order_by_sub($sub_id) {
	global $wpdb;
	$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'mdid_orders WHERE subscription_id = %s', $sub_id);
	$res = $wpdb->get_row($sql);
	return $res;
}

function mdid_order_by_customer_plan($customer_id, $plan) {
	global $wpdb;
	$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'mdid_orders WHERE customer_id = %s AND subscription_id = %s', $customer_id, $plan);
	$res = $wpdb->get_row($sql);
	return $res;
}

function mdid_transaction_check($txn_id) {
	global $wpdb;
	$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'ign_pay_info WHERE transaction_id = %s', $txn_id);
	$res = $wpdb->get_row($sql);
	return $res;
}

function mdid_start_check($start) {
	global $wpdb;
	$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'mdid_orders WHERE order_id = %s', $start);
	$res = $wpdb->get_row($sql);
	return $res;
}

function mdid_payid_check($pay_id) {
	global $wpdb;
	$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'mdid_orders WHERE pay_info_id = %d', $pay_id);
	$res = $wpdb->get_row($sql);
	return $res;
}

function mdid_payinfo_transaction($pay_id, $txn_id) {
	global $wpdb;
	$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'ign_pay_info SET transaction_id = %s WHERE id = %d', $txn_id, $pay_id);
	$res = $wpdb->query($sql);
}

function mdid_by_orderid($order_id) {
	global $wpdb;
	$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'mdid_orders WHERE order_id = %d', $order_id);
	$res = $wpdb->get_row($sql);
	return $res;
}

function mdid_pay_id_by_orderid($order_id) {
	$mdid_order = mdid_by_orderid($order_id);
	if (empty($mdid_order)) {
		return null;
	}
	return $mdid_order->pay_info_id;
}

function mdid_idcf_order_by_orderid($order_id) {
	$pay_id = mdid_pay_id_by_orderid($order_id);
	if (empty($pay_id)) {
		return null;
	}
	$id_order = new ID_Order($pay_id);
	return $id_order->get_order();
}

function mdid_set_collected($pay_id, $txn_id) {
	global $wpdb;
	$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'ign_pay_info SET transaction_id = %s, status = "C" WHERE id = %d', $txn_id, $pay_id);
	$res = $wpdb->query($sql);
}

function idc_id_add_level_associations($level_number, $level_id, $project_id, $user_id) {
	global $wpdb;
	// check existence of assignments
	$assignment_check = get_assignments_by_project($project_id, $misc = 'AND level_id = '.$level_id);
	// $level_check = get_assignments_by_level($level_id);
	if (empty($assignment_check)) {
		// assign cf levels
		$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'mdid_project_levels (levels) VALUES (%s)', serialize(array($level_number+1)));
		$res = $wpdb->query($sql);
		$assignment_id = $wpdb->insert_id;
		$sql = $wpdb->prepare('INSERT INTO '.$wpdb->prefix.'mdid_assignments (level_id, project_id, assignment_id) VALUES (%d, %d, %d)', $level_id, $project_id, $assignment_id);
		$res = $wpdb->query($sql);
		// attach user to this project/level
		$claim_level = update_option('md_level_'.$level_id.'_owner', apply_filters('md_level_owner', $user_id));
	}
}

function mdid_set_approval($args) {
	global $wpdb;
	if (!empty($args)) {
		if (isset($args['txn_id'])) {
			$txn_id = $args['txn_id'];
		}
		if (isset($args['id'])) {
			$order_id = $args['id'];
		}
		if (isset($args['pay_id'])) {
			$pay_id = $args['pay_id'];
		}
		if (isset($txn_id) && isset($order_id)) {
			$status = 'C';
			// things we need to do:
			// 1: Set MD order txn_id from pre to actual
			// 2: Set ID order txn_id from pre to actual
			// 3: Set ID order status to C
			$update_md_txn = ID_Member_Order::update_txn_id($order_id, $txn_id);
			if (isset($pay_id)) {
				mdid_set_collected($pay_id, $txn_id);
			}

		}
	}
}

function idc_id_project_levels_ascending($project_id) {
	// Get the attached IDC product
	$project_assignments = get_assignments_by_project($project_id);
	// Now adding into $level_data array, the product id
	$levels = array();
	foreach ($project_assignments as $assignment) {
		$assignment_data = get_project_levels($assignment->assignment_id);
		$assignment_detail = maybe_unserialize($assignment_data->levels);
		$project_level = array_shift($assignment_detail);
		// Getting product data for level_type
		$levels[$project_level - 1] = $assignment->level_id;
	}
	return $levels;
}

add_action('idc_delete_level', 'mdid_delete_associations');

function mdid_delete_associations($level_id) {
	global $wpdb;
	$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'mdid_assignments WHERE level_id = %d', $level_id);
	$res = $wpdb->get_results($sql);
	if (!empty($res)) {
		foreach ($res as $row) {
			$assignment_id = $row->assignment_id;
			$sql = 'DELETE FROM '.$wpdb->prefix.'mdid_assignments WHERE id = "'.$row->id.'"';
			$res = $wpdb->query($sql);
			$sql = 'DELETE FROM '.$wpdb->prefix.'mdid_project_levels WHERE id = "'.$assignment_id.'"';
			$res = $wpdb->query($sql);
		}
	}
}

function mdid_delete_project($post_id) {
	global $wpdb;
    $post = get_post($post_id);
    if ($post->post_type == 'ignition_product') {
        $project_id = get_post_meta($post_id, 'ign_project_id', true);
        if (isset($project_id) && $project_id > 0) {
        	global $wpdb;
        	$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'mdid_assignments WHERE project_id = %d', $project_id);
        	$res = $wpdb->get_results($sql);
        	if (!empty($res)) {
        		foreach ($res as $row) {
        			$assignment_id = $row->assignment_id;
        			$sql = 'DELETE FROM '.$wpdb->prefix.'mdid_assignments WHERE id = "'.$row->id.'"';
        			$res = $wpdb->query($sql);
        			$sql = 'DELETE FROM '.$wpdb->prefix.'mdid_project_levels WHERE id = "'.$assignment_id.'"';
        			$res = $wpdb->query($sql);
        		}
        	}
        }
    }
}
add_action('before_delete_post', 'mdid_delete_project');

/**
* MDID Bridge Ajax
*/

// Ajax listeners below

function mdid_project_list() {
	$project_set = mdid_get_selected();
	$active_projects = array();
	foreach ($project_set as $project_id) {
		if (class_exists('ID_Project')) {
			$project = new ID_Project($project_id);
			$post_id = $project->get_project_postid();
			//$active = get_post_meta($post_id, 'mdid_project_activate', true);
			$the_project = $project->the_project();
			$active_projects[] = $the_project;
		}
	}
	print_r(json_encode($active_projects));
	exit;
}

if ($crowdfunding) {
	add_action('wp_ajax_mdid_project_list', 'mdid_project_list');
	add_action('wp_ajax_nopriv_mdid_project_list', 'mdid_project_list');
}

function mdid_get_assignments() {
	if (isset($_POST['Level'])) {
		$level = $_POST['Level'];
		if (!empty($level)) {
			$assignment_array = array();
			global $wpdb;
			$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'mdid_assignments WHERE level_id = %s', $level);
			$res = $wpdb->get_results($sql);
			foreach ($res as $assignment) {
				$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'mdid_project_levels WHERE id = %d', $assignment->assignment_id);
				$res = $wpdb->get_row($sql);
				if (!empty($res)) {
					$data = unserialize($res->levels);
					if (is_array($data)) {
						$project = array('project' => $assignment->project_id, 'levels' => $data);
						$assignment_array[] = $project;
					}
				}
			}
			print_r(json_encode($assignment_array));
		}
	}
	exit;
}

if ($crowdfunding) {
	add_action('wp_ajax_mdid_get_assignments', 'mdid_get_assignments');
	add_action('wp_ajax_nopriv_mdid_get_assignments', 'mdid_get_assignments');
}

function mdid_save_assignments() {
	if (isset($_POST['Assignments'])) {
		$assignments = $_POST['Assignments'];
		if (!empty($assignments)) {
			global $wpdb;
			$level = $assignments['level'];
			if (isset($assignments['projects'])) {
				$sql = 'SELECT * FROM '.$wpdb->prefix.'mdid_assignments WHERE level_id = "'.$level.'"';
				$res = $wpdb->get_results($sql);
				$old_array = array();
				foreach ($res as $row) {
					$old_array[] = $row->project_id;
				}
				$projects = $assignments['projects'];
				$new_array = array();
				foreach ($projects as $project) {
					$project_id = $project['id'];
					$new_array[] = $project_id;
					$levels = $project['levels'];
					$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'mdid_assignments WHERE level_id = %s AND project_id = %s', $level, $project_id);
					$check = $wpdb->get_row($sql);
					if (empty($check)) {
						$sql = 'INSERT INTO '.$wpdb->prefix.'mdid_project_levels (levels) VALUES ("'.esc_sql(serialize($levels)).'")';
						$res = $wpdb->query($sql);
						$assignment_id = $wpdb->insert_id;
						$sql = 'INSERT INTO '.$wpdb->prefix.'mdid_assignments (level_id, project_id, assignment_id) VALUES ("'.$level.'", "'.$project_id.'", "'.$assignment_id.'")';
						$res = $wpdb->query($sql);
					}
					else {
						$sql = $wpdb->prepare('UPDATE '.$wpdb->prefix.'mdid_project_levels SET levels = %s WHERE id = %d', serialize($levels), $check->assignment_id);
						$update = $wpdb->query($sql);
					}
				}
				$array_diff = array_diff($old_array, $new_array);
				foreach ($array_diff as $diff) {
					if (!in_array($diff, $new_array)) {
						// wipe it
						$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'mdid_assignments WHERE project_id = %s', $diff);
						$check = $wpdb->get_row($sql);
						if (!empty($check)) {
							$sql = 'DELETE FROM '.$wpdb->prefix.'mdid_assignments WHERE id = '.$check->id;
							$res = $wpdb->query($sql);
							$sql = 'DELETE FROM '.$wpdb->prefix.'mdid_project_levels WHERE id = '.$check->assignment_id;
							$res = $wpdb->query($sql);
						}
					}
				}
			}
			else {
				// wipe it
				$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'mdid_assignments WHERE level_id = %s', $level);
				$check = $wpdb->get_row($sql);
				if (!empty($check)) {
					$sql = 'DELETE FROM '.$wpdb->prefix.'mdid_assignments WHERE id = '.$check->id;
					$res = $wpdb->query($sql);
					$sql = 'DELETE FROM '.$wpdb->prefix.'mdid_project_levels WHERE id = '.$check->assignment_id;
					$res = $wpdb->query($sql);
				}
			}
			
		}
	}
	exit;
}

if ($crowdfunding) {
	add_action('wp_ajax_mdid_save_assignments', 'mdid_save_assignments');
	add_action('wp_ajax_nopriv_mdid_save_assignments', 'mdid_save_assignments');
}

add_filter('login_redirect', 'mdid_login_redirect', 10, 3);
function mdid_login_redirect($redirect_to, $requested_redirect_to, $user) {
	$durl = md_get_durl();
	$referrer = (isset($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : $_SERVER['PHP_SELF'];
	$referrer = explode('?',$referrer);
	$referrer = empty($referrer[0])?$durl:$referrer[0];
	if (is_wp_error($user)) {
        $error_types = array_keys($user->errors);
        $error_type = 'both_empty';
        if (is_array($error_types) && !empty($error_types)) {
            $error_type = $error_types[0];
        }
		$qs = $error_type!='invalid_username'?'&error_code='.$error_type:'';
        wp_redirect( $durl . "?login_failure=1" . $qs ); 
        exit;
    } else {
        return $referrer;
    }
}

// Ajax Based ppForm
add_action('wp_ajax_load_pp_form', 'load_pp_form');
add_action('wp_ajax_nopriv_load_pp_form', 'load_pp_form');
function load_pp_form() {
	include('templates/'.$_POST['page'].'.php');
	exit;
}

if (defined('ID_DEV_MODE') && 'ID_DEV_MODE' == true) {
	add_action('activated_plugin','idc_save_activation_error');
}
function idc_save_activation_error(){
    update_option('plugin_error',  ob_get_contents());
}
if (defined('ID_DEV_MODE') && 'ID_DEV_MODE' == true) {
	//add_action('init', 'idc_debugging');
}

function idc_debugging() {
	//echo get_option('plugin_error');
}
?>