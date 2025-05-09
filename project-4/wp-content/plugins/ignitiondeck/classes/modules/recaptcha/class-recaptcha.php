<?php

class ID_Recaptcha {
	
	/**
	 * Constructor for ID_Recaptcha class.
	 * 
	 * Initializes the set_filters method.
	 */
	function __construct() {
		self::set_filters();
	}

	/**
	 * Set filters for reCAPTCHA functionality.
	 *
	 * This function sets the filters for reCAPTCHA functionality by adding an action
	 * to the 'plugins_loaded' hook, which calls the recaptcha_load method.
	 */
	function set_filters() {
		add_action('plugins_loaded', array($this, 'recaptcha_load'));
	}

	/**
	 * Load reCAPTCHA functionality.
	 *
	 * This function loads reCAPTCHA functionality if IDC and IDCF are present. It
	 * adds various actions and filters for reCAPTCHA on login, registration, and
	 * other forms. It also verifies reCAPTCHA on login and registration.
	 */
	function recaptcha_load() {
		if (idf_has_idc() && idf_has_idcf()) {
			if (is_id_pro() || is_idc_licensed()) {
				add_action('init', array($this, 'recaptcha_init'));
				add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
				add_action('login_enqueue_scripts', array($this, 'enqueue_scripts'));
				// check for idc
				add_action('md_register_extrafields', array($this, 'render_reg_captcha'));
				add_filter('login_form_middle', array($this, 'render_login_captcha'));
				// default forms
				add_action('login_form', array($this, 'echo_login_captcha'));
				// wc forms
				// reserved
				add_action('admin_menu', array($this, 'admin_menus'), 20);
				// Verify on login
				add_filter('authenticate', array($this, 'login_verify_gcaptcha3'), 9, 3);
				// Verify on registration
				add_filter( 'pre_user_login' , array($this, 'register_verify_gcaptcha3') );
			}
		}
	}

	/**
	 * Initialize reCAPTCHA functionality.
	 *
	 * This function initializes reCAPTCHA functionality by registering the necessary scripts.
	 * It is called on the 'init' hook and calls the register_scripts method.
	 */
	function recaptcha_init() {
		self::register_scripts();
	}

	/**
	 * Add admin menus for reCAPTCHA settings.
	 *
	 * This function adds a submenu page under 'idf' for managing reCAPTCHA settings.
	 *
	 * @return void
	 */
	function admin_menus() {
		add_submenu_page('idf', __('reCAPTCHA', 'idf'), __('reCAPTCHA', 'idf'), 'manage_options', 'idc_recaptcha', array($this, 'admin_menu'));
	}

	/**
	 * Admin menu for managing reCAPTCHA settings.
	 *
	 * This function handles the submission of reCAPTCHA settings and includes the
	 * settings menu template for display.
	 *
	 * @return void
	 */
	function admin_menu() {		
		// Check if the user has the required capability
		if (!current_user_can('manage_options')) {
			wp_die(esc_html__('You do not have sufficient permissions to perform this action.', 'memberdeck'));
    		exit;
		}
		$settings = get_option('id_recaptcha_settings');
		if (isset($_POST['submit_id_recaptcha_settings'])) {
			check_admin_referer('recaptcha_save_settings', 'recaptcha_nonce');
			foreach ($_POST as $k=>$v) {
				$settings[$k] = sanitize_text_field($v);
				update_option('id_recaptcha_settings', $settings);
			}
		}
		include_once('templates/admin/_settingsMenu.php');
	}

	/**
	 * Register scripts for reCAPTCHA functionality.
	 *
	 * This function registers the necessary scripts for reCAPTCHA functionality based on the
	 * settings provided. It registers the reCAPTCHA script, the id_recaptcha script, and the
	 * id_recaptcha style. It also localizes the id_recaptcha script with the site ID and
	 * reCAPTCHA version.
	 *
	 * @global string $idf_current_version The current version of the plugin.
	 * @return void
	 */
	function register_scripts() {
		global $idf_current_version;
		$language = get_bloginfo('language');
		$settings = get_option('id_recaptcha_settings');
		if(isset($settings['id_recaptcha_type'])) {
			if($settings['id_recaptcha_type'] == 'v3') {
				wp_register_script('recaptcha', 'https://www.google.com/recaptcha/api.js?render='.$settings['id_recaptcha_site_id'].'&hl='.$language.' async defer', array(), $idf_current_version, true);
			} else {
				wp_register_script('recaptcha', 'https://www.google.com/recaptcha/api.js?onload=idRecaptchaLoad&render=explicit&hl='.$language.' async defer', array(), $idf_current_version, true);
			}
			wp_register_script('id_recaptcha', plugins_url('js/id_recaptcha-min.js', __FILE__), array(), time(), true);
			wp_register_style('id_recaptcha', plugins_url('css/id_recaptcha-min.css', __FILE__), array(), $idf_current_version);
			wp_localize_script('id_recaptcha', 'id_recaptcha_site_id', (isset($settings['id_recaptcha_site_id']) ? $settings['id_recaptcha_site_id'] : ''));
			wp_localize_script('id_recaptcha', 'id_recaptcha_version', (isset($settings['id_recaptcha_type']) ? $settings['id_recaptcha_type'] : 'v2'));
		}
	}

	/**
	 * Enqueue scripts for reCAPTCHA functionality.
	 *
	 * This function enqueues the necessary scripts for reCAPTCHA functionality based on the
	 * settings provided. It enqueues the jQuery script, the reCAPTCHA script, the id_recaptcha
	 * script, and the id_recaptcha style if the site ID is available.
	 *
	 * @return void
	 */
	function enqueue_scripts() {
		if ($this::has_site_id()) {
			wp_enqueue_script('jquery');
			wp_enqueue_script('recaptcha');
			wp_enqueue_script('id_recaptcha');
			wp_enqueue_style('id_recaptcha');
		}
	}

	/**
	 * Check if the site has a reCAPTCHA site ID set.
	 *
	 * This function checks if the site has a reCAPTCHA site ID set in the plugin settings.
	 *
	 * @return bool True if the site has a reCAPTCHA site ID set, false otherwise.
	 */
	function has_site_id() {
		$settings = get_option('id_recaptcha_settings');
		return !empty($settings['id_recaptcha_site_id']);
	}

	/**
	 * Generate the reCAPTCHA content for a form.
	 *
	 * This function generates the reCAPTCHA content for a form based on the plugin settings.
	 *
	 * @param string $wrapper The HTML tag to wrap the reCAPTCHA content in. Default is 'div'.
	 * @return string The generated reCAPTCHA content.
	 */
	function captcha_content($wrapper = 'div') {
		$settings = get_option('id_recaptcha_settings');
		if(isset($settings['id_recaptcha_type']) && $settings['id_recaptcha_type'] == 'v3') {
			return '';
		} else {
			return '<'.$wrapper.' class="form-row id_recaptcha_placeholder" data-callback="idRecaptchaCallback"></'.$wrapper.'>';
		}
	}

	/**
	 * Render the reCAPTCHA content for registration form.
	 *
	 * This function renders the reCAPTCHA content for the registration form.
	 */
	function render_reg_captcha() {
		echo wp_kses_post($this::captcha_content());
	}

	/**
	 * Render the reCAPTCHA content for login form.
	 *
	 * This function renders the reCAPTCHA content for the login form.
	 *
	 * @param string $content The content to be rendered. Default is an empty string.
	 * @return string The rendered reCAPTCHA content.
	 */
	function render_login_captcha($content = '') {
		return self::captcha_content('p');
	}

	/**
	 * Echo the reCAPTCHA content for login form.
	 *
	 * This function echoes the reCAPTCHA content for the login form if the site has a reCAPTCHA site ID set.
	 */
	function echo_login_captcha() {
		if ($this::has_site_id()) {
			echo wp_kses_post(self::render_login_captcha());
		}
	}

	/**
	 * Verify reCAPTCHA on login.
	 *
	 * This function checks the reCAPTCHA response on login and returns a WP_Error
	 * if the reCAPTCHA is not verified or not submitted. It uses the secret key
	 * to verify the response with Google's reCAPTCHA API.
	 *
	 * @return WP_Error|void Returns WP_Error if reCAPTCHA verification fails.
	 */
	function login_verify_gcaptcha3() {		
		if( isset($_POST['g-recaptcha-response']) ) {
			if (isset($_GET['wp_id_nonce'])) {
				check_admin_referer('wp_id_nonce', 'wp_id_nonce');
			}
			$settings = get_option('id_recaptcha_settings');
			$secret   = $settings['id_recaptcha_secret_key'];
			$captcha  = isset($_POST['g-recaptcha-response']) ? sanitize_text_field($_POST['g-recaptcha-response']) : '';

			$response = wp_remote_post(
				'https://www.google.com/recaptcha/api/siteverify',
				array(
					'method'    => 'POST',
					'body'      => array(
						'secret'   => $secret,
						'response' => $captcha
					),
					'timeout'   => 10, // Optional: specify a timeout in seconds
				)
			);

			if (is_wp_error($response)) {
				// Handle error
				$error_message = $response->get_error_message();
				$error = new WP_Error('recaptcha_request_failed', __('ERROR: Recaptcha verification request failed.'));
				return $error;
			}

			$response_body = wp_remote_retrieve_body($response);
			$arrResponse = json_decode($response_body, true);

			// Verify the response
			if (isset($arrResponse['success']) && $arrResponse['success'] === true && 
				isset($arrResponse['action']) && $arrResponse['action'] === 'login' &&
				isset($arrResponse['score']) && $arrResponse['score'] >= 0.5) {
				// Valid submission
				return true;
			} else {
				// Spam submission
				$error = new WP_Error('authentication_failed', __('ERROR: Recaptcha not verified.'));
				return $error;
			}
		} else {
			$error = new WP_Error();
			$user  = new WP_Error( 'authentication_failed', __( 'ERROR: Recaptcha is not submitted.' ) );
			return $error;
		}
	}

	/**
	 * Register and verify reCAPTCHA for user registration.
	 *
	 * This function processes the reCAPTCHA response submitted with the user
	 * registration form. It sends a request to Google's reCAPTCHA API for
	 * verification and handles the response. If the reCAPTCHA is verified,
	 * the function returns true, otherwise it outputs a JSON encoded failure
	 * response and exits.
	 *
	 * @return bool True if reCAPTCHA is verified, otherwise exits script.
	 */
	function register_verify_gcaptcha3() {
		if (isset($_POST['Fields'])) {
			if (isset($_GET['wp_id_nonce'])) {
				check_admin_referer('wp_id_nonce', 'wp_id_nonce');
			}
			foreach ($_POST['Fields'] as $f) {
				if ($f['name'] == 'g-recaptcha-response' && !empty($f['value'])) {
					$settings = get_option('id_recaptcha_settings');
					$secret   = $settings['id_recaptcha_secret_key'];
					$captcha  = ($f['value']);
					
					// Perform the POST request using wp_remote_post
					$response = wp_remote_post(
						'https://www.google.com/recaptcha/api/siteverify',
						array(
							'method'    => 'POST',
							'body'      => array(
								'secret'   => $secret,
								'response' => $captcha,
							),
							'timeout'   => 10, // Optional: specify a timeout in seconds
						)
					);
	
					if (is_wp_error($response)) {
						// Handle error
						$error_message = $response->get_error_message();
						echo wp_json_encode(array('response' => 'failure', 'message' => __('ERROR: Recaptcha verification request failed.')));
						exit;
					}
	
					$response_body = wp_remote_retrieve_body($response);
					$arrResponse = json_decode($response_body, true);
	
					// Verify the response
					if (isset($arrResponse['success']) && $arrResponse['success'] === true) {
						// Valid submission
					} else {
						// Spam submission
						echo wp_json_encode(array('response' => 'failure', 'message' => __('ERROR: Recaptcha not verified.')));
						exit;
					}
	
					break;
				} else {
					echo wp_json_encode(array('response' => 'failure', 'message' => __('ERROR: Recaptcha is not submitted.')));
					exit;
				}
			}
		} else {
			echo wp_json_encode(array('response' => 'failure', 'message' => __('ERROR: Recaptcha is not submitted.')));
			exit;
		}
		
		return true;
	}	
}
new ID_Recaptcha(); ?>