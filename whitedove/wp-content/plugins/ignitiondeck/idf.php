<?php

//error_reporting(E_ALL);
//@ini_set('display_errors', 1);

/*
Plugin Name: IgnitionDeck
URI: https://IgnitionDeck.com
Description: A crowdfunding and ecommerce plugin for WordPress that helps you crowdfund, pre-order, and sell goods online.
Version: 1.10.3
Author: IgnitionDeck
Author URI: https://IgnitionDeck.com
License: GPL2
*/

define( 'IDF_PATH', plugin_dir_path( __FILE__ ) );

require_once 'idf-globals.php';
global $active_plugins, $idf_current_version;
$idf_current_version = '1.10.3';
require_once 'idf-update.php';
require_once 'classes/class-idf_requirements.php';
require_once 'classes/class-idf.php';
require_once 'classes/class-idf-wizard.php';
require_once 'classes/class-idf_cache.php';
require_once 'classes/class-id_dev_tools.php';
require_once 'idf-cron.php';
require_once 'idf-functions.php';
require_once 'idf-filters.php';
require_once 'idf-cache.php';
require_once 'idf-admin.php';
require_once 'classes/class-id_modules.php';
require_once 'classes/class-tgm-plugin-activation.php';
require_once 'idf-modules.php';
require_once 'idf-roles.php';
require_once 'idf-wp.php';
require_once 'idf-actions.php';
if ( idf_has_idc() ) {
	include_once 'idf-idc.php';
}
if ( idf_has_idcf() ) {
	include_once 'idf-idcf.php';
}
//include_once 'idf-stock-browser.php'; #commented code

add_action( 'init', 'idf_init' );

/**
 * Initialize the IgnitionDeck plugin.
 *
 * This function sets the default options and transfers the key for the IgnitionDeck plugin.
 */
function idf_init() {
	// idf-admin.php
	# For menu item notices
	//add_filter('idf_notice_count', 'idf_notice_count');
}

register_activation_hook( __FILE__, 'idf_activation' );

/**
 * Activate the IgnitionDeck plugin.
 *
 * This function initializes the plugin by setting default options and transferring the key for the IgnitionDeck plugin.
 */
function idf_activation() {
	do_action( 'idf_before_activation' );
	idf_init_set_defaults();
	idf_init_transfer_key();
	if ( ! idf_dev_mode() ) {
		//idf_update_products();
	}
	do_action( 'idf_activation' );
}

/**
 * Set default options for the IgnitionDeck plugin initialization.
 *
 * This function sets the default options for the IgnitionDeck plugin, including the current version,
 * commerce platform, and plugin versions.
 */
function idf_init_set_defaults() {
	global $idf_current_version;
	update_option( 'idf_current_version', $idf_current_version );
	$platform = idf_platform();
	if ( empty( $platform ) ) {
		update_option( 'idf_commerce_platform', 'idc' );
	}
	$version_array = array(
		'ignitiondeck-crowdfunding/ignitiondeck.php' => '2.3.0',
		'idcommerce/idcommerce.php'                  => '1.15.1',
	);
	set_transient( 'idf_plugin_versions', $version_array );
	//set_site_transient( 'update_plugins', null );
}

/**
 * Transfer the key for the IgnitionDeck plugin initialization.
 *
 * This function transfers the key for the IgnitionDeck plugin by deleting the old key transfer option,
 * retrieving the transfer key, and processing the key data for IDCF and IDC.
 */
function idf_init_transfer_key() {
	delete_option( 'idf_key_transfer' );
	$key_transfer = get_option( 'idf_transfer_key' );
	if ( ! $key_transfer ) {
		$key_data = array(
			'keys'  => array(
				'idcf_key' => '',
				'idc_key'  => '',
			),
			'types' => array(
				'idcf_type' => 0,
				'idc_type'  => 0,
			),
		);
		// Key transfer for IDCF
		$idcf_key = get_option( 'id_license_key' );
		if ( function_exists( 'idcf_license_key' ) ) {
			$idcf_response = idcf_license_key( $idcf_key );
			$idcf_valid    = is_idcf_key_valid( $idcf_response );
			if ( $idcf_valid ) {
				$key_data['types']['idcf_type'] = idf_idcf_license_type( $idcf_response );
				$key_data['keys']['idcf_key']   = $idcf_key;
			}
		}
		// Key transfer for IDC
		$idc_gen = get_option( 'md_receipt_settings' );
		if ( ! empty( $idc_gen ) ) {
			$idc_gen = maybe_unserialize( $idc_gen );
			$idc_key = ( isset( $idc_gen['license_key'] ) ? $idc_gen['license_key'] : '' );
			if ( function_exists( 'idf_idc_validate_key' ) ) {
				$idc_response = idf_idc_validate_key( $idc_key );
				$idc_valid    = is_idc_key_valid( $idc_response );
				if ( $idc_valid ) {
					$key_data['types']['idc_type'] = idf_idc_license_type();
					$key_data['keys']['idc_key']   = $idc_key;
				}
			}
		}
		$license_type = idf_parse_license( $key_data );
		if ( $license_type ) {
			do_action( 'idf_transfer_key' );
		}
	}
}

/**
 * Redirects to the IDF menu page.
 *
 * This function redirects to the IDF menu page and exits the script.
 */
function idf_menu_redirect() {
	wp_redirect( menu_page_url( 'idf', 0 ) );
	exit;
}

/**
 * Update products function.
 *
 * This function is responsible for updating products. It deactivates specific plugins, triggers
 * IDCF delivery, and forces an update if IDC is installed.
 */
function idf_update_products() {
	// no longer running this as our auto-update uses license handling for this feature
	$idc_installed = false;
	if ( class_exists( 'ID_Member' ) ) {
		if ( function_exists( 'is_idc_licensed' ) && function_exists( 'was_idc_licensed' ) ) {
			if ( is_idc_licensed() || was_idc_licensed() ) {
				$idc_installed = true;
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
				$idc_data = get_plugin_data( WP_PLUGIN_DIR . '/idcommerce/idcommerce.php' );
			}
		}
	}
	$plugin_array = array(
		'ignitiondeck-crowdfunding/ignitiondeck.php',
		'idcommerce/idcommerce.php',
	);
	deactivate_plugins( $plugin_array );
	idf_idcf_delivery( true );
	if ( $idc_installed ) {
		do_action( 'idc_force_update' );
	}
}

add_action( 'admin_init', 'idf_install_flags' );

/**
 * Install flags function.
 *
 * This function sets installation flags to prevent duplicate runs of certain scripts and redirects to the plugin main menu.
 *
 * @global int $idf_current_version The current version of the IgnitionDeck plugin.
 * @return void
 */
function idf_install_flags() {
	global $idf_current_version;
	$install_flags = get_option( 'idf_install_flags' );
	if ( empty( $install_flags ) || $install_flags < $idf_current_version ) {
		// install flag to prevent duplicate runs of these scripts
		update_option( 'idf_install_flags', $idf_current_version );
		// redirect to plugin main menu
		idf_menu_redirect();
	}
}

add_action( 'plugins_loaded', 'idf_textdomain' );

/**
 * Load the text domain for the IgnitionDeck plugin.
 *
 * This function loads the text domain for the IgnitionDeck plugin, enabling the translation of plugin
 * strings into the specified language.
 */
function idf_textdomain() {
	load_plugin_textdomain( 'idf', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

//add_action( 'idc_force_update', 'idc_force_update' );

/**
 * Force update the IDC plugin.
 *
 * This function forces an update for the IDC plugin by checking for available updates,
 * and initiating the update process if an update is available.
 */
function idc_force_update() {
	require WP_PLUGIN_DIR . '/idcommerce/idcommerce-update.php';
	require ABSPATH . 'wp-admin/includes/plugin.php';
	$idc_data = get_plugin_data( WP_PLUGIN_DIR . '/idcommerce/idcommerce.php' );
	if ( ! empty( $idc_data ) ) {
		$update_data = idc_update_info(
			'basic_check',
			array(
				'slug'    => 'idcommerce/idcommerce.php',
				'version' => $idc_data['Version'],
			)
		);
		$response    = unserialize( $update_data['body'] );
		if ( isset( $response->package ) && is_admin() ) {
			require ABSPATH . 'wp-admin/update.php';
			require ABSPATH . 'wp-admin/includes/file.php';
			require ABSPATH . 'wp-admin/includes/misc.php';
			$plugin_args = array(
				'plugin' => 'idcommerce/idcommerce.php',
				'url'    => admin_url() . 'update.php?action=upgrade-plugin&plugin=' . urlencode( 'idcommerce/idcommerce.php' ),
				'title'  => __( 'Update Plugin' ),
				'nonce'  => 'upgrade-plugin_' . 'idcommerce/idcommerce.php',
			);
			$upgrader    = new Plugin_Upgrader( new Plugin_Upgrader_Skin( $plugin_args ) );
			//$upgrader->upgrade('idcommerce/idcommerce.php');
		}
	}
}

add_action( 'init', 'idf_prepare_scripts' );

/**
 * Prepare scripts for the IgnitionDeck plugin.
 *
 * This function prepares the necessary scripts for the IgnitionDeck plugin, including
 * registering and enqueueing the required JavaScript files, and adding inline scripts
 * for current URL, date format, site URL, and user login status.
 */
function idf_prepare_scripts() {
	global $idf_current_version;
	wp_register_script( 'idf', plugins_url( 'js/idf-min.js', __FILE__ ), array(), $idf_current_version, true );
	wp_register_script( 'idf-functions', plugins_url( 'js/idf-functions-min.js', __FILE__ ), array(), $idf_current_version, true );
	wp_enqueue_script( 'idf-functions' );
	//wp_localize_script( 'idf-functions', 'idf_current_url', idf_current_url() );
	//wp_localize_script( 'idf-functions', 'idf_date_format', idf_date_format() );
	//wp_localize_script( 'idf-functions', 'idf_siteurl', site_url() );
	//wp_localize_script( 'idf-functions', 'idf_logged_in', ( is_user_logged_in() ? '1' : '0' ) );

	wp_add_inline_script( 'idf-functions', 'var idf_current_url = "'. idf_current_url() . '";' );
	wp_add_inline_script( 'idf-functions', 'var idf_date_format = "'. idf_date_format() . '";' );
	wp_add_inline_script( 'idf-functions', 'var idf_siteurl = "'. site_url() . '";' );
	wp_add_inline_script( 'idf-functions', 'var idf_logged_in = "'. ( is_user_logged_in() ? '1' : '0' ) . '";' );
}

add_action( 'init', 'idf_lightbox' );
add_action( 'login_enqueue_scripts', 'idf_lightbox' );

/**
 * Register and enqueue the lightbox scripts and styles.
 *
 * This function registers and enqueues the necessary scripts and styles for the lightbox,
 * including the magnific CSS and JS files, admin media JS, IDF CSS, IDF stock browser JS,
 * and jQuery. It also localizes and adds inline scripts for the platform, ajax URL, checkout URL,
 * and plugin version.
 */
function idf_lightbox() {
	global $idf_current_version;
	if ( function_exists( 'get_plugin_data' ) ) {
		$idf_data = get_plugin_data( __FILE__ );
	}
	wp_register_style( 'magnific', plugins_url( 'lib/magnific/magnific-min.css', __FILE__ ), array(), $idf_current_version );
	wp_register_script( 'magnific', plugins_url( 'lib/magnific/magnific-min.js', __FILE__ ), array(), $idf_current_version, true );
	wp_register_script( 'idf-admin-media', plugins_url( '/js/idf-admin-media-min.js', __FILE__ ), array(), $idf_current_version, true );
	wp_register_style( 'idf', plugins_url( 'css/idf-min.css', __FILE__ ), array(), $idf_current_version );
	wp_register_script( 'idf-stock-browser', plugins_url( 'js/idf-stock-browser-min.js', __FILE__ ), array(), $idf_current_version, true );
	wp_enqueue_script( 'jquery' );
	$checkout_url = array();
	$platform     = idf_platform();
	if ( $platform == 'wc' && ! is_admin() ) {
		if ( class_exists( 'WooCommerce' ) ) {
			global $woocommerce;
			$idf_wc_checkout_url = get_option( 'idf_wc_checkout_url', 'get_cart_url' );
			switch ( $idf_wc_checkout_url ) {
				case 'get_cart_url':
					$checkout_url = wc_get_cart_url();
					break;
				default:
					$checkout_url = wc_get_checkout_url();
					break;
			}
		}
	} elseif ( $platform == 'edd' && class_exists( 'Easy_Digital_Downloads' ) && ! is_admin() ) {
		$checkout_url = edd_get_checkout_uri();
	}
	wp_enqueue_style( 'magnific' );
	wp_enqueue_style( 'idf' );
	wp_enqueue_script( 'idf' );
	wp_enqueue_script( 'magnific' );
	if ( $platform == 'legacy' || $platform == 'wc' ) {
		wp_register_script( 'idflegacy-js', plugins_url( 'js/idf-legacy-min.js', __FILE__ ), array(), $idf_current_version, true );
		wp_enqueue_script( 'idflegacy-js' );
	}
	//wp_localize_script( 'idf', 'idf_platform', $platform );
	wp_add_inline_script( 'idf', 'var idf_platform = "'. $platform . '";' );
	// Let's set the ajax url
	$idf_ajaxurl = site_url( '/wp-admin/admin-ajax.php' );
	//wp_localize_script( 'idf', 'idf_ajaxurl', $idf_ajaxurl );
	wp_add_inline_script( 'idf', 'var idf_ajaxurl = "'. $idf_ajaxurl . '";' );
	if ( isset( $checkout_url ) ) {
		//wp_localize_script( 'idf', 'idf_checkout_url', $checkout_url );
		//wp_add_inline_script( 'idf', 'var idf_checkout_url = "'. $checkout_url . '";' );
		wp_localize_script( 'idf', 'idf_checkout_url', $checkout_url );
	}
	if ( isset( $idf_data['Version'] ) ) {
		//wp_localize_script( 'idf', 'idf_version', $idf_data['Version'] );
		wp_add_inline_script( 'idf', 'var idf_version = "'. $idf_data['Version'] . '";' );
	}
	//wp_enqueue_script('idf-stock-browser');
}

/**
 * Register and enqueue Font Awesome stylesheet.
 *
 * @return void
 */
function idf_font_awesome() {
	global $idf_current_version;
	wp_register_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css', array(), $idf_current_version );
	wp_enqueue_style( 'font-awesome' );
}

add_action( 'wp_enqueue_scripts', 'idf_font_awesome' );
add_action( 'admin_enqueue_scripts', 'idf_font_awesome' );

//Use TGM to show plugin dependency
add_action( 'tgmpa_register', 'ignitiondeck_register_required_plugins' );

/**
 * Register required plugins using TGM Plugin Activation.
 *
 * This function registers and enqueues the required plugins using TGM Plugin Activation.
 *
 * @return void
 */
function ignitiondeck_register_required_plugins() {
	$plugins = array(
		array(
			'name'               => 'ID Commerce',
			'slug'               => 'idcommerce',
			'source'       		 => 'https://files.ignitiondeck.com/idc_latest.zip',
			'required'           => true,
			'version'            => '1.15.1',
			'force_activation'   => false,
			'force_deactivation' => false,
			'external_url' 		 => 'https://files.ignitiondeck.com/idc_latest.zip', // If set, overrides default API URL and points to an external URL.
			'is_callable'        => '',
		),

		array(
			'name'               => 'IgnitionDeck Crowdfunding',
			'slug'               => 'ignitiondeck-crowdfunding',
			'source'       		 => 'https://files.ignitiondeck.com/idcf_latest.zip',
			'required'           => true,
			'version'            => '2.3.0',
			'force_activation'   => false,
			'force_deactivation' => false,
			'external_url' 		 => 'https://files.ignitiondeck.com/idcf_latest.zip', // If set, overrides default API URL and points to an external URL.
			'is_callable'        => '',
		),
	);

	$config = array(
		'id'           => 'ignitiondeck-plugins',
		'default_path' => '',
		'menu'         => 'idf-plugins',
		'parent_slug'  => 'plugins.php',
		'capability'   => 'edit_theme_options',
		'has_notices'  => true,
		'dismissable'  => false,
		'dismiss_msg'  => '',
		'is_automatic' => false,
		'message'      => '',
		'strings'      => array(
			// Translators: %1$s: plugin name
			'notice_can_install_required'     => _n_noop(
				'You have not installed dependency plugins. Click here to Install before using IDC: %1$s.',
				'You have not installed dependency plugins. Click here to Install before using IDC: %1$s.',
				'ignitiondeck'
			)
		)
	);

	tgmpa( $plugins, $config );
}

//Show theme dependency
//add_action( 'admin_notices', 'ignitiondeck_register_required_themes' );
/**
 * Register required themes for IgnitionDeck.
 *
 * This function checks if the 'fivehundred' theme exists and displays an error notice if it doesn't,
 * prompting the user to download and activate the free crowdfunding theme framework, Theme 500.
 *
 * @return void
 */
function ignitiondeck_register_required_themes() {
	$my_theme = wp_get_theme( 'fivehundred' );
	if ( !$my_theme->exists() ) {
		$class = 'notice-error';
		?>
		<div class="notice settings-error is-dismissible <?php echo esc_attr($class);?>">
			<p><?php esc_html__('The free version of IgnitionDeck requires our free crowdfunding theme framework, Theme 500. You may', 'idf'); ?> <a href="https://files.ignitiondeck.com/fh_latest.zip" target="_blank"><?php esc_html__('download', 'idf'); ?></a> <?php esc_html__('and activate via your'); ?> <a href="<?php echo esc_url(site_url('wp-admin/themes.php')); ?>"><?php esc_html__('themes menu', 'idf'); ?></a> <?php esc_html__('at any time.', 'idf'); ?></p>
		</div>
		<?php
	}
}
