<?php

add_action('admin_init', 'idf_admin_init');

/**
 * Initialize the IDF admin functionality.
 *
 * This function triggers the 'idf_notice_checks' action hook during admin initialization.
 *
 * @return void
 */
function idf_admin_init() {
	do_action('idf_notice_checks');
}

add_action('admin_menu', 'idf_admin_menus');

/**
 * Add admin menus for IgnitionDeck.
 *
 * This function adds admin menus for IgnitionDeck, including the dashboard and various
 * extensions. It also handles the display of notice counts and menu items based on user
 * capabilities and plugin licenses.
 */
function idf_admin_menus() {
	if (current_user_can('manage_options')) {
		global $admin_page_hooks;
		// pretty red bubble
		$notice_count = apply_filters('idf_notice_count', 0);
		$menu_array = array();
		$notice_counter = sprintf(
			/* translators: %1$d: number of notices */
			__('<span class="update-plugins count-%1$d"><span class="plugin-count">%1$d</span></span>', 'idf'),
			$notice_count
		);

		$home = add_menu_page(__('Dashboard', 'idf'), __('IgnitionDeck', 'idf')/*.' '.$notice_counter*/, 'manage_options', 'idf', 'idf_main_menu', 'dashicons-ignitiondeck');
		if (!empty($home)) {
			$menu_array[] = $home;
		}
		$admin_page_hooks['idf'] = 'ignitiondeck'; // Wipe notification bits from hooks. Thank you WP SEO.

		$dashboard = add_submenu_page( 'idf', __('IgnitionDeck Dashboard', 'idf'), apply_filters('idf_menu_title_idf', __('Dashboard', 'idf')), 'manage_options', 'idf');
		/*
		$theme_list = add_submenu_page( 'idf', __('Themes', 'idf'), apply_filters('idf_menu_title_idf-themes', __('Themes', 'idf')), 'manage_options', 'idf-themes', 'idf_theme_list');
		if (!empty($theme_list)) {
			$menu_array[] = $theme_list;
		}
		*/
		if (idf_has_idc() && idf_has_idcf()) {
			if (is_id_basic() || is_id_pro() || is_idc_licensed() || idf_registered()) {
				$extension_list = add_submenu_page( 'idf', __('Modules', 'idf'), apply_filters('idf_menu_title_idf-extensions', __('Modules', 'idf')), 'manage_options', 'idf-extensions', 'idf_modules_menu');
				if (!empty($extension_list)) {
					$menu_array[] = $extension_list;
				}
			}
		}
		foreach ($menu_array as $menu) {
			add_action('admin_print_styles-'.$menu, 'idf_admin_enqueues');
		}
	}
}

add_action('admin_menu', 'idf_dev_menus', 100);

/**
 * Add developer tools submenu to IgnitionDeck admin menu.
 *
 * This function adds a developer tools submenu to the IgnitionDeck admin menu
 * if the development mode is enabled. It also enqueues the necessary admin styles.
 */
function idf_dev_menus() {
	if (idf_dev_mode()) {
		$dev_menu = add_submenu_page( 'idf' , __('Dev Tools', 'idf'), apply_filters('idf_menu_title_idf-dev-tools', __('Dev Tools', 'idf')), 'manage_options', 'idf-dev-tools', 'idf_dev_tools');
		add_action('admin_print_styles-'.$dev_menu, 'idf_admin_enqueues');
		//add_action('admin_print_styles-'.$dev_menu, 'idf_dev_tools_enqueues');
	}
}

/**
 * Main menu for IgnitionDeck.
 *
 * This function handles the main menu for IgnitionDeck, including license checks,
 * product activation, and module listing.
 *
 * @return void
 */
function idf_main_menu() {
	// Verify nonce if _idf_main_menu_helper is set, regardless of request method
	if ( isset($_POST['_idf_main_menu_helper']) && 
	    ( ! isset($_POST['_wpnonce']) || ! wp_verify_nonce($_POST['_wpnonce'], '_wpnonce'))) {
	    return false;
	}
    // Check user capabilities.
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'You don\'t have sufficient permissions to manage options.' );
    }

	$requirements = new IDF_Requirements;
	$install_data = $requirements->install_check();
	$idf_registered = idf_registered();
	$license_option = get_option('idf_license_entry_options');
	$id_account = get_option('id_account');
	$platform = idf_platform();
	$plugins_path = plugin_dir_path(dirname(__FILE__));
	$super = idf_is_super();
	$active_products = array();
	$is_id_licensed = false;
	$is_idc_licensed = false;
	$platforms = idf_platforms();
	if (idf_has_idcf()) {
		$idcf_license_key = get_option('id_license_key');
	}
	if (idf_has_idc()) {
		$general = get_option('md_receipt_settings');
		$general = maybe_unserialize($general);
		$idc_license_key = (isset($general['license_key']) ? $general['license_key'] : '');
	}
	if (isset($_POST['idf_license_entry_options'])) {
		$license_option = sanitize_text_field($_POST['idf_license_entry_options']);
		update_option('idf_license_entry_options', $license_option);
		switch ($license_option) {
			case 'email':
				$id_account = sanitize_text_field($_POST['id_account']);
				do_action('idf_id_update_account', $id_account);
				break;
			case 'keys':
				if (isset($_POST['idcf_license_key'])) {
					$idcf_license_key = sanitize_text_field($_POST['idcf_license_key']);
					do_action('idcf_license_update', $idcf_license_key);
				}
				if (isset($_POST['idc_license_key'])) {
					$idc_license_key = sanitize_text_field($_POST['idc_license_key']);
					do_action('idc_license_update', $idc_license_key);
				}
				break;
		}
		$platforms = idf_platforms();
	}
	if (idf_has_idcf()) {
		$is_pro = is_id_pro();
		$is_basic = is_id_basic();
		if ($is_pro) {
			$active_products[] = 'IgnitionDeck Enterprise';
			$is_id_licensed = true;
		}
		else if ($is_basic) {
			$active_products[] = 'IgnitionDeck Echelon';
			$is_id_licensed = true;
		}
	}
	if (idf_has_idc()) {
		$is_idc_licensed = is_idc_licensed();
		if ($is_idc_licensed) {
			$active_products[] = 'IgnitionDeck Commerce';
		}
	}
	$type_msg = '';
	if (!empty($active_products)) {
		$count = count($active_products);
		$type_msg = ' '.$active_products[0];
		if ($count > 1) {
			$i = 0;
			foreach ($active_products as $product) {
				if ($i > 0) {
					$type_msg .= ', '.$active_products[$i];
				}
				$i++;
			}
		}
	}
	$show_takeover = false;
	if (isset($_GET['action']) && sanitize_text_field($_GET['action']) == 'idf_registered') {
		$show_takeover = true;
	}
	if (isset($_POST['commerce_submit'])) {
		$platform = sanitize_text_field($_POST['commerce_selection']);
		update_option('idf_commerce_platform', $platform);
		do_action('idf_update_commerce_platform', $platform);
	}
	if (isset($_POST['update_idcf'])) {
		if (file_exists($plugins_path.'ignitiondeck-crowdfunding')) {
			deactivate_plugins($plugins_path.'ignitiondeck-crowdfunding/ignitiondeck.php');
			$dir = $plugins_path.'ignitiondeck-crowdfunding';
			rrmdir($dir);
		}
		idf_idcf_delivery();
		echo '<script>location.href="'.esc_url(site_url('/wp-admin/admin.php?page=idf')).'";</script>';
	}
	// modules list
	$data = idf_extension_list($filter = array(
		'key' => 'status',
		'value' => 'live'
	));
	$extension_data = (!empty($data) ? array_slice($data, -3) : array());
	// upgrades
	$license_type = 'free';
	$qs_url = 'https://ignitiondeck.com/id/documentation/quickstart/';
	if (idf_has_idcf()) {
		$pro = get_option('is_id_pro', false);
		if ($pro) {
			$license_type = 'ide';
			$qs_url = 'https://ignitiondeck.com/id/documentation/quickstart/ignitiondeck-enterprise/';
		}
		else {
			if (idf_has_idc()) {
				if (is_idc_licensed()) {
					$license_type = 'idc';
					$qs_url = 'https://ignitiondeck.com/id/documentation/quickstart/ignitiondeck-membership/';
				}
			}
		}
	}
	include_once 'templates/admin/_idfMenu.php';
}

/**
 * Display the list of active modules.
 *
 * This function retrieves the list of active modules and the extension list, then
 * includes the template to display the list of active modules.
 */
function idf_modules_menu() {
	$active_modules = idf_get_modules();
	$data = idf_extension_list();
	include_once 'templates/admin/_extensionList.php';
}

/**
 * Display the list of themes.
 *
 * This function retrieves the list of themes, fetches the theme data from the
 * IgnitionDeck website, and includes the template to display the list of themes.
 */
function idf_theme_list() {
	$themes = wp_get_themes();
	$name_array = array();
	if (!empty($themes)) {
		foreach ($themes as $theme) {
			$name_array[] = $theme->Name;
		}
	}
	$active_theme = wp_get_theme();
	$active_name = $active_theme->Name;
	$prefix = is_ssl() ? 'https' : 'http';
    $api = $prefix . '://ignitiondeck.com/id/?action=get_themes';

    // Perform the GET request using wp_remote_get
    $response = wp_remote_get($api, array(
        'timeout' => 15, // Optional: specify a timeout in seconds
        'sslverify' => false, // Optional: verify SSL certificates (set to true for production)
    ));

    $response_body = wp_remote_retrieve_body($response);
    $data = json_decode($response_body);
	include_once 'templates/admin/_themeList.php';
}

/**
 * Display the developer tools.
 *
 * This function captures the output of phpinfo, removes the surrounding HTML, and
 * includes the template to display the developer tools.
 */
function idf_dev_tools() {
	ob_start();
	phpinfo();
	$php_info = ob_get_contents();
	ob_end_clean();
	$php_info = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $php_info);
	include_once 'templates/admin/_devTools.php';
}

add_action('idf_notice_checks', 'idf_notice_checks');

/**
 * Check for IgnitionDeck Commerce version and display notice if out of date.
 *
 * This function checks the version of IgnitionDeck Commerce and displays a notice
 * if it is out of date. It retrieves the current and new versions, compares them,
 * and adds an admin notice if the current version is older than the new version.
 */
function idf_notice_checks() {
	// IDC version check
	if (idf_has_idc()) {
		$idc_data = get_plugin_data(WP_PLUGIN_DIR . '/idcommerce/idcommerce.php');
		if (!empty($idc_data['Version'])) {
			$current_idc_version = $idc_data['Version'];
			$versions = get_transient('idf_plugin_versions');
			if (isset($versions['idcommerce/idcommerce.php'])) {
				$new_idc_version = $versions['idcommerce/idcommerce.php'];
				if (version_compare($current_idc_version, $new_idc_version, '<')) {
					add_action('admin_notices', 'idf_idc_notice');
				}
			}
		}
	}
}

/**
 * Modify the IgnitionDeck extensions menu title to include a notice indicator.
 *
 * This function modifies the title of the IgnitionDeck extensions menu to include
 * a notice indicator. It adds a star icon to the menu title to indicate the presence
 * of a notice.
 *
 * @param int $count The count of notices.
 * @return int The modified count of notices.
 */
function idf_notice_count($count) {
	add_filter('idf_menu_title_idf-extensions', function($title) {
		return $title.' <i class="fa fa-star idf_menu_notice"></i>';
	});
	return 1;
}

/**
 * Display a notice for an out-of-date IgnitionDeck Commerce installation.
 *
 * This function displays a notice for an out-of-date IgnitionDeck Commerce installation.
 * It outputs an admin notice with a message and a link to update to the latest version.
 */
function idf_idc_notice() {
	echo '<div class="updated">
            <p>' . 
                esc_html__('Your IgnitionDeck Commerce installation is out of date.', 'ignitiondeck') . ' <a href="' . esc_url(admin_url('update-core.php')) . '">' . esc_html__('Click here', 'ignitiondeck') . '</a> ' . esc_html__('to update to the latest version.', 'ignitiondeck') . 
            '</p>
        </div>';
}


add_action('admin_enqueue_scripts', 'idf_prepare_admin_scripts');

/**
 * Prepare admin scripts for IgnitionDeck.
 *
 * This function registers the necessary scripts and styles for the IgnitionDeck
 * admin area, including the main admin script, wizard script, admin media script,
 * magnific script, admin styles, wizard styles, and magnific styles.
 */
function idf_prepare_admin_scripts() {
	global $idf_current_version;

	wp_register_script('idf-admin', plugins_url('/js/idf-admin-min.js', __FILE__), array(), $idf_current_version, true);
	wp_register_script('idf-wizard', plugins_url('/js/idf-wizard.js', __FILE__), array(), $idf_current_version, true);
	wp_register_script('idf-admin-media', plugins_url('/js/idf-admin-media-min.js', __FILE__), array(), $idf_current_version, true);
	wp_register_script('magnific', plugins_url('lib/magnific/magnific-min.js', __FILE__), array(), $idf_current_version, true);
	wp_register_style('idf-admin', plugins_url('/css/idf-admin-min.css', __FILE__), array(), $idf_current_version);
	wp_register_style('idf-wizard', plugins_url('/css/idf-wizard.css', __FILE__), array(), $idf_current_version);
	wp_register_style('magnific', plugins_url('lib/magnific/magnific-min.css', __FILE__), array(), $idf_current_version);
}

add_action('admin_enqueue_scripts', 'idf_prepare_admin_localization');

/**
 * Prepare admin localization for IgnitionDeck.
 *
 * This function prepares the necessary localization for the IgnitionDeck admin area,
 * including site URL, AJAX URL, platform, launchpad link, and IgnitionDeck version.
 *
 * @return void
 */
function idf_prepare_admin_localization() {
	$platform = idf_platform();
	$prefix = 'http';
	if (is_ssl()) {
		$prefix = 'https';
	}
	if (function_exists('get_plugin_data')) {
		$idf_data = get_plugin_data(DIRNAME(__FILE__).'/idf.php');
	}
	//wp_localize_script('idf-admin', 'idf_admin_siteurl', site_url());
	//wp_localize_script('idf-admin', 'idf_admin_ajaxurl', site_url('/wp-admin/admin-ajax.php'));
	//wp_localize_script('idf-admin', 'idf_platform', $platform);
	//wp_localize_script('idf-admin', 'launchpad_link', $prefix.'://ignitiondeck.com/id/id-launchpad-checkout/');
	//wp_localize_script('idf-admin', 'idf_version', (isset($idf_data['Version']) ? $idf_data['Version'] : '0.0.0'));

	wp_add_inline_script( 'idf-admin', 'var idf_admin_siteurl = "'. site_url() . '";' );
	wp_add_inline_script( 'idf-admin', 'var idf_admin_ajaxurl = "'. site_url('/wp-admin/admin-ajax.php') . '";' );
	wp_add_inline_script( 'idf-admin', 'var idf_platform = "'. $platform . '";' );
	wp_add_inline_script( 'idf-admin', 'var launchpad_link = "'. $prefix.'://ignitiondeck.com/id/id-launchpad-checkout/' . '";' );
	wp_add_inline_script( 'idf-admin', 'var idf_version = "'. (isset($idf_data['Version']) ? $idf_data['Version'] : '0.0.0') . '";' );
}

add_action('admin_enqueue_scripts', 'idf_additional_enqueues');

/**
 * Enqueue additional scripts and styles for IgnitionDeck admin area.
 *
 * This function enqueues additional scripts and styles for the IgnitionDeck
 * admin area, including the ignitiondeck-font style, conditional loading of
 * selective css/scripts based on the post type, and admin enqueues if the
 * platform is not legacy.
 */
function idf_additional_enqueues() {
	global $post;
	global $idf_current_version;
	
	wp_register_style('ignitiondeck-font', plugins_url('/lib/ignitiondeckfont/ignitiondeckfont-min.css', __FILE__), array(), $idf_current_version);
	wp_enqueue_style('ignitiondeck-font');
	if (isset($post->post_type) && $post->post_type == 'ignition_product') {
		// load selective css/scripts
		$platform = idf_platform();
		if (empty($platform) || $platform !== 'legacy') {
			idf_admin_enqueues();
		}
	}
}

/**
 * Enqueue admin scripts and styles for IgnitionDeck.
 *
 * This function enqueues admin scripts and styles for the IgnitionDeck admin
 * area, including jquery, dashboard script, media, magnific, admin, wizard,
 * admin media, and conditional loading of dashboard and magnific styles.
 */
function idf_admin_enqueues() {
	wp_enqueue_script('jquery');
	if (menu_page_url('idf', false) == idf_current_url()) {
		wp_enqueue_script('dashboard');
	}
	wp_enqueue_media();
	wp_enqueue_script('magnific');
	wp_enqueue_script('idf-admin');
	wp_enqueue_script('idf-wizard');
	$idf_ajaxurl = site_url( '/wp-admin/admin-ajax.php' );
	wp_add_inline_script( 'idf-wizard', 'var idf_ajaxurl = "'. $idf_ajaxurl . '";' );
	wp_enqueue_script('idf-admin-media');
	if (menu_page_url('idf', false) == idf_current_url()) {
		wp_enqueue_style('dashboard');
	}
	wp_enqueue_style('magnific');
	wp_enqueue_style('idf-admin');
	wp_enqueue_style('idf-wizard');
}

add_action('admin_init', 'filter_idcf_admin');

/**
 * Enqueue scripts and styles for IgnitionDeck dev tools.
 *
 * This function enqueues scripts and styles for the IgnitionDeck dev tools, including
 * the idf-dev_tools script, jquery, and conditional loading of idf-dev_tools script.
 */
function idf_dev_tools_enqueues() {
	global $idf_current_version;
	wp_register_script('idf-dev_tools', plugins_url('js/idf-admin-dev_tools-min.js', __FILE__), array(), $idf_current_version, true);
	wp_enqueue_script('jquery');
	wp_enqueue_script('idf-dev_tools');
}

/**
 * Filter admin actions for IgnitionDeck Crowdfunding.
 *
 * This function filters admin actions for IgnitionDeck Crowdfunding based on the
 * platform. It removes the action 'add_meta_boxes' for 'add_ty_url' if the
 * platform is not empty and not 'legacy'. If the platform is 'wc', it adds the
 * action 'idcf_below_project_settings' for 'idf_wc_settings' and removes the
 * action 'add_meta_boxes' for 'add_purchase_url'.
 */
function filter_idcf_admin() {
	$platform = idf_platform();
	if (!empty($platform) && $platform !== 'legacy') {
		remove_action('add_meta_boxes', 'add_ty_url');
	}
	if ($platform == 'wc') {
		add_action('idcf_below_project_settings', 'idf_wc_settings');
		remove_action('add_meta_boxes', 'add_purchase_url');
	}
}

add_action('plugins_loaded', 'filter_idc_admin');

/**
 * Filter admin actions for IgnitionDeck Commerce.
 *
 * This function filters admin actions for IgnitionDeck Commerce based on the
 * platform. It removes the action 'add_meta_boxes' for 'mdid_project_metaboxes' if the
 * platform is not 'idc'.
 */
function filter_idc_admin() {
	$platform = idf_platform();
	if ($platform !== 'idc') {
		remove_action('add_meta_boxes', 'mdid_project_metaboxes');
	}
}

/**
 * Set and display IgnitionDeck WooCommerce settings.
 *
 * This function sets and displays the IgnitionDeck WooCommerce settings. It retrieves
 * the checkout URL option, updates it if a new value is posted, and includes the
 * WooCommerce settings template.
 *
 * @return void
 */
function idf_wc_settings() {
	if (isset($_GET['wp_id_nonce'])) {
		check_admin_referer('wp_id_nonce', 'wp_id_nonce');
	}

    // Check user capabilities.
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'You don\'t have sufficient permissions to manage options.' );
    }
	
	// #devnote create a function for this
	$idf_wc_checkout_url = get_option('idf_wc_checkout_url', 'get_cart_url');
	if (isset($_POST['idf_wc_checkout_url'])) {
		$idf_wc_checkout_url = sanitize_text_field($_POST['idf_wc_checkout_url']);
		update_option('idf_wc_checkout_url', $idf_wc_checkout_url);
	}
	include_once('templates/admin/_wcSettings.php');
}
?>