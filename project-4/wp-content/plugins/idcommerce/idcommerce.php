<?php

//error_reporting(E_ALL);

//@ini_set('display_errors', 1);

/*
Plugin Name: IgnitionDeck Commerce
URI: http://IgnitionDeck.com
Description: A powerful, yet simple, content delivery system for WordPress. Features a widgetized dashboard so you can customize your product offerings, instant checkout, credits, and more.
Version: 1.15.1
Author: IgnitionDeck
Author URI: http://ignitiondeck.com
License: GPL2
*/

define( 'IDC_PATH', plugin_dir_path( __FILE__ ) );

add_action( 'plugins_loaded', 'idc_loaded' );
function idc_loaded() {
	id_idc_check();
}
function id_idc_check() {
	if ( ! class_exists( 'IDF' ) ) {
		// If the "IDF" class doesn't exist, then the ignitiondeck foundation 
		// plugin is either not installed, or not activated. In this case,
		// we need to deactivate the IDCommerce plugin as well to avoid
		// fatal dependency errors.

		// For backend
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		deactivate_plugins( plugin_basename( __FILE__ ) ); // this deactivates IDC
		
		if (function_exists('id_idf_check_2')) {
			// tell the IDCF plugin to also deactivate itself
			id_idf_check_2(); // <--- this function is declared in the ignitiondeck-crowdfunding plugin (IDCF)
		}

		wp_die( __( 'IgnitionDeck Commerce requires installation of the IgnitionDeck Foundation prior to activation.', 'ignitiondeck' ) . 
		"<br/> <a href='" . 
		admin_url( 'plugin-install.php?tab=search&s=ignitiondeck' ) . "'>" . 
		__( 'Click here to install', 'ignitiondeck' ) . '</a>' . 
		"<br><br>" . __( 'To uninstall the IgnitionDeck plugin suite, please reinstall the IgnitionDeck Foundation and delete the dependencies first (IgnitionDeck CrowdFunding and IgnitionDeck Commerce).', 'ignitiondeck' ) );
		
		// For frontend
		function idc_frontend_error() {
			wp_die( __( 'IgnitionDeck Commerce requires installation of the IgnitionDeck Foundation prior to activation.', 'ignitiondeck' ) .
			"<br>" . __( 'To uninstall the IgnitionDeck plugin suite, please reinstall the IgnitionDeck Foundation and delete the dependencies first (IgnitionDeck CrowdFunding and IgnitionDeck Commerce).', 'ignitiondeck' ) );
		}
		add_action( 'template_redirect', 'idc_frontend_error' );
		
	}
}

// This id_idc_check_2 function is called from the ignitiondeck-crowdfunding
// plugin so that IDCF can force the IDC plugin to deactivate itself if IDF 
// isn't active.
function id_idc_check_2() {
	if ( ! class_exists( 'IDF' ) ) {
		// If the "IDF" class doesn't exist, then the ignitiondeck foundation 
		// plugin is either not installed, or not activated. In this case,
		// we need to deactivate the IDCommerce plugin as well to avoid
		// fatal dependency errors.

		// For backend
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		deactivate_plugins( plugin_basename( __FILE__ ) ); // this deactivates IDC
	}
}

global $memberdeck_db_version;
$memberdeck_db_version = '1.15.1';
global $old_idc_version;
$old_idc_version = get_option( 'memberdeck_db_version' );

$active_plugins = get_option( 'active_plugins', true );
if ( in_array( 'ignitiondeck/idf.php', $active_plugins ) ) {
	include_once plugin_dir_path( dirname( __FILE__ ) ) . 'ignitiondeck/idf.php';
} elseif ( is_multisite() && file_exists( plugin_dir_path( dirname( __FILE__ ) ) . '/ignitiondeck/idf.php' ) ) {
	include_once plugin_dir_path( dirname( __FILE__ ) ) . 'ignitiondeck/idf.php';
}

if ( in_array( 'ignitiondeck-crowdfunding/ignitiondeck.php', $active_plugins ) ) {
	include_once plugin_dir_path( dirname( __FILE__ ) ) . 'ignitiondeck-crowdfunding/ignitiondeck.php';
} elseif ( is_multisite() && file_exists( plugin_dir_path( dirname( __FILE__ ) ) . 'ignitiondeck-crowdfunding/ignitiondeck.php' ) ) {
	include_once plugin_dir_path( dirname( __FILE__ ) ) . 'ignitiondeck-crowdfunding/ignitiondeck.php';
}
require_once 'idcommerce-update.php';
require_once 'classes/class-id-member.php';
require_once 'classes/class-id-member-level.php';
require_once 'classes/class-id-member-download.php';
require_once 'classes/class-id-member-order.php';
if ( ! is_idc_free() ) {
	include_once 'classes/class-id-member-subscription.php';
	include_once 'classes/class-id-member-credit.php';
}
require_once 'classes/class-id-member-metaboxes.php';
require_once 'classes/class-md-keys.php';
require_once 'classes/class-md-form.php';
require_once 'classes/class-id-member-email.php';
if ( ! is_idc_free() ) {
	include_once 'classes/class-id-authorize.net.php';
	include_once 'classes/class-id-member-pathways.php';
	include_once 'classes/class-id-combined-products.php';
	include_once 'classes/class-id-member-renewals.php';
}
require_once 'idcommerce-globals.php';
require_once 'idcommerce-admin.php';
require_once 'idcommerce-filters.php';

// Loading modules
if ( class_exists( 'ID_Modules' ) ) {
	include_once 'classes/class-idc-modules.php';
}
global $s3;
$s3_enabled = $s3;
if ( $s3_enabled ) {
	include_once IDC_PATH . 'lib/aws-config.php';
}
require_once 'idcommerce-functions.php';
require_once 'idcommerce-shortcodes.php';
if ( class_exists( 'ID_Project' ) ) {
	include_once 'inc/idcommerce-idcf.php';
}
if ( ! is_idc_free() && function_exists( 'is_id_pro' ) && is_id_pro() ) {
	$gateways = get_option( 'memberdeck_gateways' );
	$gateways = maybe_unserialize( $gateways );
	if ( isset( $gateways['esc'] ) && $gateways['esc'] ) {
		include_once 'idcommerce-sc.php';
	}
}
if ( function_exists( 'is_id_pro' ) && is_id_pro() ) {
	include_once 'inc/idcommerce-ide.php';
}
require_once 'inc/idcommerce-coinbase.php';
if ( ! is_idc_free() ) {
	include_once 'inc/idcommerce-adaptive.php';
}
global $crowdfunding;
global $global_currency;

if ( class_exists( 'IDF' ) ) {
	$platform = idf_platform();
	if ( $platform == 'idc' ) {
		$pwyw = true;
	}
}

// Adding image size for thumbnails on Dashboard
add_image_size( 'idc_dashboard_image_size', 370, 208, true );
add_image_size( 'idc_dashboard_download_image_size', 469, 264, false );

function idc_activation() {
	// If IDF doesn't exist, deactivate the plugin
	if ( ! class_exists( 'IDF' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( __( 'IgnitionDeck Commerce requires installation of the IgnitionDeck Framework prior to activation.', 'memberdeck' ) . "<br/> <a href='" . admin_url( 'plugin-install.php?tab=search&s=ignitiondeck' ) . "'>" . __( 'Click here to install', 'memberdeck' ) . '</a>' );
	}
}
register_activation_hook( __FILE__, 'idc_activation' );


function idc_languages() {
	load_plugin_textdomain( 'memberdeck', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	load_plugin_textdomain( 'idcommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

add_action( 'plugins_loaded', 'idc_languages' );

function idc_init() {
	do_action( 'idc_init' );
	$general = maybe_unserialize( get_option( 'md_receipt_settings' ) );
	if ( ! empty( $general ) ) {
		if ( isset( $general['disable_toolbar'] ) && $general['disable_toolbar'] ) {
			if ( ! current_user_can( 'administrator' ) && ! is_admin() ) {
				show_admin_bar( false );
			}
		}
	}
	idc_install_checks();
}
add_action( 'init', 'idc_init', 1 );

function idc_install_checks() {
	global $memberdeck_db_version;
	// #devnote do we need both install check and old_db_version?
	$install_check = get_option( 'idc_last_install_check' );
	if ( empty( $install_check ) || ! version_compare( $install_check, $memberdeck_db_version, '>=' ) ) {
		// perform changes specific to current version
		memberdeck_install();
	}
	idc_set_roles();
	update_option( 'idc_last_install_check', $memberdeck_db_version );
}

function idc_set_roles() {
	$admin     = get_role( 'administrator' );
	$cap_array = array(
		'idc_manage_members',
		'idc_manage_products',
		'idc_manage_gateways',
		'idc_manage_orders',
		'idc_manage_email',
		'idc_manage_crowdfunding',
		'idc_manage_extensions',
	);
	foreach ( $cap_array as $cap ) {
		$admin->add_cap( $cap );
	}
}

function idc_localization_strings() {
	$strings                                    = array();
	$strings['virtual_currency']                = __( 'Virtual Currency', 'memberdeck' );
	$strings['purchase_form_shortcode']         = __( 'Purchase form shortcode', 'memberdeck' );
	$strings['continue']                        = __( 'Continue', 'memberdeck' );
	$strings['complete_checkout']               = __( 'Complete Checkout', 'memberdeck' );
	$strings['use_idcf_settings']               = __( 'Use IDCF Setting', 'memberdeck' );
	$strings['continue_checkout']               = __( 'Continue Checkout', 'memberdeck' );
	$strings['choose_product']                  = __( 'Choose Product', 'memberdeck' );
	$strings['choose_download']                 = __( 'Choose Download', 'memberdeck' );
	$strings['choose_credit']                   = __( 'Choose Credit', 'memberdeck' );
	$strings['no_payment_options']              = __( 'No Payment Options', 'memberdeck' );
	$strings['select_payment_option']           = __( 'Select Payment Option', 'memberdeck' );
	$strings['pay_with_credits']                = __( 'Pay with ' . ucwords( apply_filters( 'idc_credits_label', 'Credits', true ) ), 'memberdeck' );
	$strings['processing']                      = __( 'Processing', 'memberdeck' );
	$strings['choose_payment_method']           = __( 'Choose Payment Method', 'memberdeck' );
	$strings['pay_with_cc']                     = __( 'Pay with Credit Card', 'memberdeck' );
	$strings['pay_with_paypal']                 = __( 'Pay with Paypal', 'memberdeck' );
	$strings['pay_with_coinbase']               = __( 'Pay with Coinbase', 'memberdeck' );
	$strings['no_payments_available']           = __( 'No Payment Options Available', 'memberdeck' );
	$strings['pass_dont_match']                 = __( 'Passwords do not match', 'memberdeck' );
	$strings['processing']                      = __( 'Processing', 'memberdeck' );
	$strings['stripe_credentials_problem_text'] = __( 'There is a problem with your Stripe credentials', 'memberdeck' );
	$strings['passwords_mismatch_text']         = __( 'Passwords do not match', 'memberdeck' );
	$strings['accept_terms']                    = __( 'Please accept our', 'memberdeck' );
	$strings['error_in_processing_registration_text'] = __( 'There was an error processing your registration. Please contact site administrator for assistance', 'memberdeck' );
	$strings['complete_all_fields']                   = __( 'Please complete all fields', 'memberdeck' );
	$strings['registration_fields_error_text']        = __( 'Please complete all fields and ensure password 5+ characters.', 'memberdeck' );
	$strings['email_already_exists']                  = __( 'Email already exists', 'memberdeck' );
	$strings['please']                                = __( 'Please', 'memberdeck' );
	$strings['login']                                 = __( 'Login', 'memberdeck' );
	$strings['update']                                = __( 'Update', 'memberdeck' );
	$strings['choose_product'] == __( 'Choose Product', 'memberdeck' );
	return apply_filters( 'idc_localization_strings', $strings );
}

// Let's determine whether we are installing on multisite or standard WordPress
// If multisite, we need to know whether we are network activated or activated on a per-site basis

if ( is_multisite() ) {
	// we only run this if we're network activating
	if ( is_network_admin() ) {
		register_activation_hook( __FILE__, 'memberdeck_blog_install' );
	}
	// we are not in network admin, so we run regular activation script
	else {
		register_activation_hook( __FILE__, 'memberdeck_install' );
	}
} else {
	// not multisite, standard install
	register_activation_hook( __FILE__, 'memberdeck_install' );
}

if ( is_md_network_activated() ) {
	// setup again when new blogs are added
	add_action( 'wpmu_new_blog', 'memberdeck_install', 1, 1 );
}

function memberdeck_blog_install() {
	global $wpdb;
	$sql = 'SELECT * FROM ' . $wpdb->base_prefix . 'blogs';
	$res = $wpdb->get_results( $sql );
	foreach ( $res as $blog ) {
		memberdeck_install( $blog->blog_id );
	}
}

function memberdeck_install( $blog_id = null ) {
	global $wpdb;
	global $memberdeck_db_version;
	global $old_idc_version;

	do_action( 'idc_before_install' );

	$prefix = md_wpdb_prefix( $blog_id );

	//
	$memberdeck_members = $prefix . 'memberdeck_members';
	$sql                = 'CREATE TABLE ' . $memberdeck_members . ' (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					user_id BIGINT(20) NOT NULL,
					access_level TEXT NOT NULL,
					add_ons VARCHAR(250) NOT NULL,
					credits MEDIUMINT(9) NOT NULL,
					r_date DATETIME,
					reg_key VARCHAR(250) NOT NULL,
					data TEXT NOT NULL,
					UNIQUE KEY id (id));';
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
	update_option( 'memberdeck_db_version', $memberdeck_db_version );

	$memberdeck_member_levels = $prefix . 'memberdeck_member_levels';
	$sql                      = 'CREATE TABLE ' . $memberdeck_member_levels . ' (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					user_id BIGINT(20) NOT NULL,
					level_id MEDIUMINT(9) NOT NULL,
					UNIQUE KEY id (id));';
	dbDelta( $sql );

	$memberdeck_key_assoc = $prefix . 'memberdeck_key_assoc';
	$sql                  = 'CREATE TABLE ' . $memberdeck_key_assoc . ' (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					user_id BIGINT(20) NOT NULL,
					download_id MEDIUMINT(9) NOT NULL,
					assoc MEDIUMINT(9) NOT NULL,
					UNIQUE KEY id (id));';
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	$memberdeck_keys = $prefix . 'memberdeck_keys';
	$sql             = 'CREATE TABLE ' . $memberdeck_keys . ' (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					license VARCHAR(250) NOT NULL,
					avail MEDIUMINT(9) NOT NULL,
					in_use MEDIUMINT(9) NOT NULL,
					UNIQUE KEY id (id));';
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	$memberdeck_levels = $prefix . 'memberdeck_levels';
	$sql               = 'CREATE TABLE ' . $memberdeck_levels . " (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					product_status VARCHAR(250) NOT NULL DEFAULT 'active',
					product_type VARCHAR(250) NOT NULL DEFAULT 'purchase',
					level_name VARCHAR(250) NOT NULL,
					level_price VARCHAR (250) NOT NULL,
					credit_value MEDIUMINT(9) NOT NULL,
					txn_type VARCHAR (250) NOT NULL DEFAULT 'capture',
					level_type VARCHAR(250) NOT NULL,
					recurring_type VARCHAR(250) NOT NULL DEFAULT 'NONE',
					trial_period TINYINT(1),
					trial_length MEDIUMINT(9),
					trial_type VARCHAR(250),
					limit_term TINYINT(1) NOT NULL,
					term_length MEDIUMINT(9),
					plan VARCHAR(250),
					license_count MEDIUMINT(9),
					enable_renewals TINYINT(1) NOT NULL,
					renewal_price VARCHAR(255) NOT NULL,
					enable_multiples TINYINT(1) NOT NULL,
					combined_product MEDIUMINT(9) DEFAULT '0',
					custom_message TINYINT(1) NOT NULL,
					UNIQUE KEY id (id));";
	dbDelta( $sql );

	$memberdeck_level_meta = $prefix . 'memberdeck_level_meta';
	$sql                   = 'CREATE TABLE ' . $memberdeck_level_meta . ' (
					id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			    	level_id MEDIUMINT(9) NOT NULL,
			    	meta_key VARCHAR(255),
			    	meta_value LONGTEXT,
			    	UNIQUE KEY id (id));';
	dbDelta( $sql );

	$memberdeck_credits = $prefix . 'memberdeck_credits';
	$sql                = 'CREATE TABLE ' . $memberdeck_credits . ' (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					credit_name VARCHAR(250) NOT NULL,
					credit_count MEDIUMINT(9) NOT NULL,
					credit_price VARCHAR (250) NOT NULL,
					credit_level MEDIUMINT(9) NOT NULL,
					UNIQUE KEY id (id));';
	dbDelta( $sql );

	$memberdeck_downloads = $prefix . 'memberdeck_downloads';
	$sql                  = 'CREATE TABLE ' . $memberdeck_downloads . ' (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					download_name VARCHAR(250) NOT NULL,
					download_levels TEXT NOT NULL,
					button_text VARCHAR (250) NOT NULL,
					download_link VARCHAR (250) NOT NULL,
					info_link VARCHAR (250) NOT NULL,
					doc_link VARCHAR (250) NOT NULL,
					image_link VARCHAR (250) NOT NULL,
					version VARCHAR(250) NOT NULL,
					position VARCHAR(250) NOT NULL,
					licensed TINYINT(1) NOT NULL,
					hidden TINYINT(1) NOT NULL,
					enable_s3 TINYINT(1) NOT NULL,
					enable_occ TINYINT(1) NOT NULL,
					occ_level MEDIUMINT(9) NOT NULL,
					id_project MEDIUMINT(9) NOT NULL,
					updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
					UNIQUE KEY id (id));';
	dbDelta( $sql );

	$memberdeck_orders = $prefix . 'memberdeck_orders';
	$sql               = 'CREATE TABLE ' . $memberdeck_orders . ' (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					user_id BIGINT(20) NOT NULL,
					level_id MEDIUMINT( 9 ) NOT NULL,
					order_date DATETIME,
					transaction_id VARCHAR (250) NOT NULL,
					subscription_id VARCHAR (250) NOT NULL,
					subscription_number MEDIUMINT( 9 ) NOT NULL,
					e_date DATETIME,
					status VARCHAR (250) NOT NULL,
					price VARCHAR(250) NOT NULL,
					UNIQUE KEY id (id));';
	dbDelta( $sql );

	$memberdeck_order_meta = $prefix . 'memberdeck_order_meta';
	$sql                   = 'CREATE TABLE ' . $memberdeck_order_meta . ' (
			    	id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
			    	order_id MEDIUMINT(9) NOT NULL,
			    	meta_key VARCHAR(255),
			    	meta_value LONGTEXT,
			    	UNIQUE KEY id (id));';
	dbDelta( $sql );

	$memberdeck_preorders = $prefix . 'memberdeck_preorder_tokens';
	$sql                  = 'CREATE TABLE ' . $memberdeck_preorders . ' (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					order_id MEDIUMINT( 9 ) NOT NULL,
					charge_token VARCHAR (250) NOT NULL,
					gateway VARCHAR (250) NOT NULL,
					UNIQUE KEY id (id));';
	dbDelta( $sql );

	$memberdeck_subscriptions = $prefix . 'memberdeck_subscriptions';
	$sql                      = 'CREATE TABLE ' . $memberdeck_subscriptions . ' (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					user_id BIGINT(20) NOT NULL,
					level_id MEDIUMINT( 9 ) NOT NULL,
					subscription_id VARCHAR(255) NOT NULL,
					payments MEDIUMINT( 9 ) NOT NULL,
					status VARCHAR(255) NOT NULL,
					source VARCHAR(250) NOT NULL,
					UNIQUE KEY id (id));';
	dbDelta( $sql );

	$mdid_assignments = $prefix . 'mdid_assignments';
	$sql              = 'CREATE TABLE ' . $mdid_assignments . ' (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					level_id BIGINT(20) NOT NULL,
					project_id BIGINT(20) NOT NULL,
					assignment_id BIGINT(20) NOT NULL,
					UNIQUE KEY id (id));';
	dbDelta( $sql );

	$project_levels = $prefix . 'mdid_project_levels';
	$sql            = 'CREATE TABLE ' . $project_levels . ' (
					id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
					levels VARCHAR(255) NOT NULL,
					UNIQUE KEY id (id));';
	dbDelta( $sql );

	$mdid_orders = $prefix . 'mdid_orders';
	$sql         = 'CREATE TABLE ' . $mdid_orders . ' (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
		customer_id VARCHAR(255) NOT NULL,
		subscription_id VARCHAR(255),
		order_id BIGINT(20),
		pay_info_id BIGINT(20) NOT NULL,
		UNIQUE KEY id (id));';
	dbDelta( $sql );

	$md_sc_params = $prefix . 'memberdeck_sc_params';
	$sql          = 'CREATE TABLE ' . $md_sc_params . ' (
		id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
		user_id BIGINT(20) NOT NULL,
		access_token VARCHAR(255) NOT NULL,
		refresh_token VARCHAR(255) NOT NULL,
		stripe_publishable_key VARCHAR(255) NOT NULL,
		stripe_user_id VARCHAR(255) NOT NULL,
		UNIQUE KEY id (id));';
	dbDelta( $sql );

	$memberdeck_upgrade_pathways = $prefix . 'memberdeck_upgrade_pathways';
	$sql                         = 'CREATE TABLE ' . $memberdeck_upgrade_pathways . ' (
			id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
			pathway_name VARCHAR(100) NOT NULL,
			upgrade_pathway LONGTEXT,
			UNIQUE KEY id (id));';
	dbDelta( $sql );

	$memberdeck_product_pathway = $prefix . 'memberdeck_product_pathway';
	$sql                        = 'CREATE TABLE ' . $memberdeck_product_pathway . ' (
			id MEDIUMINT( 9 ) NOT NULL AUTO_INCREMENT,
			product_id MEDIUMINT(9) NOT NULL,
			pathway_id MEDIUMINT(9) NOT NULL,
			UNIQUE KEY id (id));';
	dbDelta( $sql );

	do_action( 'idc_after_install' );
}

add_action( 'idc_after_install', 'idc_set_defaults' );

// function to alter the table structure for mking it compatible with wp hosted user IDs
function alter_bigint_ids(){
	global $wpdb;
	$prefix = $wpdb->base_prefix;

	$wpdb->query('ALTER TABLE `'.$prefix.'memberdeck_members` CHANGE `user_id` `user_id` BIGINT(20) NOT NULL;');
	$wpdb->query('ALTER TABLE `'.$prefix.'memberdeck_member_levels` CHANGE `user_id` `user_id` BIGINT(20) NOT NULL;');
	$wpdb->query('ALTER TABLE `'.$prefix.'memberdeck_key_assoc` CHANGE `user_id` `user_id` BIGINT(20) NOT NULL;');
	$wpdb->query('ALTER TABLE `'.$prefix.'memberdeck_orders` CHANGE `user_id` `user_id` BIGINT(20) NOT NULL;');
	$wpdb->query('ALTER TABLE `'.$prefix.'memberdeck_subscriptions` CHANGE `user_id` `user_id` BIGINT(20) NOT NULL;');
	$wpdb->query('ALTER TABLE `'.$prefix.'memberdeck_sc_params` CHANGE `user_id` `user_id` BIGINT(20) NOT NULL;');
}

function idc_set_defaults() {
	global $crowdfunding;
	global $memberdeck_db_version;
	global $old_idc_version;
	if ( version_compare( $old_idc_version, '1.8.1', '<=' ) ) {
		$levels = ID_Member_Level::get_levels();
		if ( empty( $levels ) ) {
			return;
		}
		foreach ( $levels as $level ) {
			if ( $level->level_type == 'standard' ) {
				$meta = idc_get_level_meta( $level->id, 'exp_data', true );
				if ( ! empty( $meta ) ) {
					return;
				}
				$data = array(
					'term'  => 'years',
					'count' => '1',
				);
				idc_update_level_meta( $level->id, 'exp_data', $data );
			}
		}
	}

	$registration_email_default    =
		'<h3>{{COMPANY_NAME}} Payment Receipt</h3>
		Hello {{NAME}},

		Thank you for your purchase of {{PRODUCT_NAME}}.

		Your order is almost ready to go. We just need you to click the link below to complete your registration:

		{{REG_LINK}}

		Thank you for your support!

		The {{COMPANY_NAME}} team.
		---------------------------------
		{{COMPANY_NAME}}
		{{COMPANY_EMAIL}}';
	$welcome_email_default         =
		'Hello {{NAME}},

		Your registration for {{SITE_NAME}} was successful.

		If you have already created a password, you can login at any time using the information below. Otherwise, please check your inbox for a second email with instructions for creating your password.
		<div style="border: 1px solid #333333; width: 500px;">
		<table border="0" width="500" cellspacing="0" cellpadding="5">
		<tbody>
		<tr style="color: white;" bgcolor="#333333">
		<td width="200"><span style="color: #ffffff;">Username</span></td>
		<td width="200"><span style="color: #ffffff;">Login URL</span></td>
		</tr>
		<tr>
		<td width="200">{{EMAIL}}</td>
		<td width="200">{{DURL}}</td>
		</tr>
		</tbody>
		</table>
		</div>
		The {{COMPANY_NAME}} team.
		---------------------------------
		{{COMPANY_NAME}}
		{{COMPANY_EMAIL}}';
	$purchase_receipt_default      =
		'<h3>{{COMPANY_NAME}} Payment Receipt</h3>
		Hello {{NAME}},

		You have successfully made a payment of {{AMOUNT}}.

		This transaction should appear on your credit card statement as {{COMPANY_NAME}}.
		<div>
		<div>
		<table border="" width="600" cellspacing="0" cellpadding="5">
		<tbody>
		<tr style="color: white;" bgcolor="#333333">
		<td width=""><span style="color: #ffffff;">DATE</span></td>
		<td width=""><span style="color: #ffffff;">PRODUCT</span></td>
		<td width=""><span style="color: #ffffff;">AMOUNT</span></td>
		<td width=""><span style="color: #ffffff;">ORDER ID</span></td>
		</tr>
		<tr>
		<td width="">{{DATE}}</td>
		<td width="">{{PRODUCT_NAME}}</td>
		<td width="">{{AMOUNT}}</td>
		<td width="">{{TXN_ID}}</td>
		</tr>
		</tbody>
		</table>
		</div>
		Thank you for your support!
		The {{COMPANY_NAME}} team

		</div>
		---------------------------------
		{{COMPANY_NAME}}
		{{COMPANY_EMAIL}}';
	$creator_receipt_default       =
		'<h3>{{COMPANY_NAME}} Payment Notification</h3>
		Hello {{NAME}},

		You have received a payment for {{PRODUCT_NAME}} in the amount of {{AMOUNT}}.

		<div>
		<div>
		<table border="" width="600" cellspacing="0" cellpadding="5">
		<tbody>
		<tr style="color: white;" bgcolor="#333333">
		<td width=""><span style="color: #ffffff;">DATE</span></td>
		<td width=""><span style="color: #ffffff;">PRODUCT</span></td>
		<td width=""><span style="color: #ffffff;">AMOUNT</span></td>
		<td width=""><span style="color: #ffffff;">ORDER ID</span></td>
		</tr>
		<tr>
		<td width="">{{DATE}}</td>
		<td width="">{{PRODUCT_NAME}}</td>
		<td width="">{{AMOUNT}}</td>
		<td width="">{{TXN_ID}}</td>
		</tr>
		</tbody>
		</table>
		</div>

		The {{COMPANY_NAME}} team

		</div>
		---------------------------------
		{{COMPANY_NAME}}
		{{COMPANY_EMAIL}}';
	$preorder_receipt_default      =
		'<h3>{{COMPANY_NAME}} Payment Receipt</h3>
		Hello {{NAME}},

		This is a confirmation of your pre-order of {{PRODUCT_NAME}} for {{AMOUNT}}.

		If this is a crowdfunding project, and funding is successful, your credit card will be charged on {{END_DATE}}.

		This transaction will appear on your credit card statement as {{COMPANY_NAME}}.
		<div>
		<div>
		<table border="" width="500" cellspacing="0" cellpadding="5">
		<tbody>
		<tr style="color: white" bgcolor="#333333">
		<td width=""><span style="color: #ffffff">DATE</span></td>
		<td width=""><span style="color: #ffffff">PRODUCT</span></td>
		<td width=""><span style="color: #ffffff">AMOUNT</span></td>
		</tr>
		<tr>
		<td width="">{{DATE}}</td>
		<td width="">{{PRODUCT_NAME}}</td>
		<td width="">{{AMOUNT}}</td>
		</tr>
		</tbody>
		</table>
		</div>
		Thank you for your support!
		The {{COMPANY_NAME}} team

		</div>
		---------------------------------
		{{COMPANY_NAME}}
		{{COMPANY_EMAIL}}';
	$product_renewal_email_default = 'Hello {{NAME}},

		Your product <strong>{{PRODUCT_NAME}}</strong> is about to expire.<br><br>

		Please renew your product to avoid any inconvenience.<br>
		<div style="border: 1px solid #333333; width: 500px;">
		<table border="0" width="500" cellspacing="0" cellpadding="5">
		<tbody>
		<tr style="color: white;" bgcolor="#333333">
		<td width="200"><span style="color: #ffffff;">Days remaining</span></td>
		</tr>
		<tr>
		<td width="200">{{DAYS_LEFT}}</td>
		</tr>
		</tbody>
		</table>
		</div>

		<br>
		<a href="{{RENEWAL_CHECKOUT_URL}}">' . __( 'Follow this link', 'memberdeck' ) . '</a> ' . __( 'to renew your product or use the address below', 'memberdeck' ) . '.<br/>
		<a href="{{RENEWAL_CHECKOUT_URL}}">{{RENEWAL_CHECKOUT_URL}}</a>
		<br>
		The {{COMPANY_NAME}} team.
		---------------------------------
		{{COMPANY_NAME}}
		{{COMPANY_EMAIL}}';

	$success_notification =
		'<h3>{{PROJECT_NAME}} Has Been Successfully Funded!</h3>
		Congratulations! Thanks to the help of backers like you, {{PROJECT_NAME}} has successfully reached its funding goal.

		Your credit card will be charged for the amount of {{AMOUNT}} on {{END_DATE}}, and a receipt will be issued at that time. Please ensure that you have the necessary funds available.

		Thanks for your support!';

	$success_notification_admin =
		'<h3>Project Success Notification</h3>
		One of your projects, {{PROJECT_NAME}}, has successfully reached its funding goal. This project is set to end on {{END_DATE}}, at which point you may process credit cards and export order information.';
		$update_notification    =
		'<h3>{{PROJECT_NAME}} Update</h3>
		<strong>{{UPDATE_TITLE}}</strong>

		{{UPDATE_CONTENT}}';

	$project_notify_admin_default = '
	  	You have a new project submission from user {{NAME}} with the following attributes:

		<div style="border: 1px solid #333333; width: 500px;">
			<table width="500" border="0" cellspacing="0" cellpadding="5">
				<tr bgcolor="#333333" style="color: white">
		            <td width="100"> Title</td>
		            <td width="275">Description</td>
		            <td width="125">Goal</td>
		        </tr>
		        <tr>
		           <td width="200">{{PROJECT_NAME}}</td>
		           <td width="275">{{PROJECT_DESCRIPTION}}</td>
		           <td width="125">{{PROJECT_GOAL}}</td>
		      	</tr>
			</table>
		</div>

		<div style="margin:10px 0;"><a href="{{EDIT_LINK}}">Use this link</a> to moderate the project</div>

		---------------------------------
		{{COMPANY_NAME}}
		<a href="mailto:{{COMPANY_EMAIL}}">{{COMPANY_EMAIL}}</a>';

	$project_notify_creator_default = '
		<h2>Project Submission Notification</h2>

		Congratulations. The following project has been submitted for approval:

		<div style="border: 1px solid #333333; width: 500px;">
			<table width="500" border="0" cellspacing="0" cellpadding="5">
				<tr bgcolor="#333333" style="color: white">
					<td width="100">Title</td>
					<td width="275">Description</td>
					<td width="125">Goal</td>
				</tr>
				<tr>
					<td width="200">{{PROJECT_NAME}}</td>
					<td width="275">{{PROJECT_DESCRIPTION}}</td>
					<td width="125">{{PROJECT_GOAL}}</td>
		      	</tr>
			</table>
		</div>

		<div style="margin:10px 0;">You will be notified when the review process has been completed. In the interim, you may use <a href="{{EDIT_LINK}}">Use this link</a> to continue editing the project</div>


		---------------------------------
		{{COMPANY_NAME}}
		<a href="mailto:{{COMPANY_EMAIL}}">{{COMPANY_EMAIL}}</a>';

	update_option( 'registration_email_default', $registration_email_default );
	update_option( 'welcome_email_default', $welcome_email_default );
	update_option( 'purchase_receipt_default', $purchase_receipt_default );
	update_option( 'creator_receipt_default', $creator_receipt_default );
	update_option( 'preorder_receipt_default', $preorder_receipt_default );
	update_option( 'product_renewal_email_default', $product_renewal_email_default );
	$currency_option = get_option( 'idc_global_currency' );
	if ( empty( $currency_option ) ) {
		update_option( 'idc_global_currency', 'USD' );
	}
	update_option( 'success_notification_default', $success_notification );
	update_option( 'success_notification_admin_default', $success_notification_admin );
	update_option( 'update_notification_default', $update_notification );
	update_option( 'project_notify_admin_default', $project_notify_admin_default );
	update_option( 'project_notify_creator_default', $project_notify_creator_default );

	$fund_type = get_option( 'idc_cf_fund_type' );
	if ( empty( $fund_type ) ) {
		update_option( 'idc_cf_fund_type', 'both' );
	}
	/* Install Default Pages */
	$reg = array(
		'menu_order'     => 100,
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
		'post_name'      => 'membership-registration',
		'post_status'    => 'publish',
		'post_title'     => 'Membership Registration',
		'post_type'      => 'page',
	);

	$db = array(
		'menu_order'     => 100,
		'comment_status' => 'closed',
		'ping_status'    => 'closed',
		'post_name'      => 'dashboard',
		'post_status'    => 'publish',
		'post_title'     => 'Dashboard',
		'post_type'      => 'page',
		'post_content'   => '[idc_dashboard]',
	);

	$theme = wp_get_theme();

	if ( $theme->name == '500 Framework' || $theme->parent_theme == '500 Framework' ) {
		$db['page_template'] = 'page-fullwidth.php';
	}

	$get_reg = get_page_by_title( 'Membership Registration' );
	$get_db  = get_page_by_title( 'Dashboard' );

	if ( empty( $get_reg ) ) {
		$reg_page = wp_insert_post( $reg );
		if ( isset( $wp_error ) ) {
			echo $wp_error;
		}
	}
	if ( empty( $get_db ) ) {
		$d_page = wp_insert_post( $db );
		if ( isset( $wp_error ) ) {
			echo $wp_error;
		}
	}
	/* Install Default Options */
	$dash_settings = get_option( 'md_dash_settings' );
	if ( empty( $dash_settings ) ) {
		$dash_settings = array(
			'durl'       => ( isset( $d_page ) ? $d_page : $get_db->ID ),
			'alayout'    => 'md-featured',
			'aname'      => '',
			'blayout'    => 'md-featured',
			'bname'      => '',
			'clayout'    => 'md-featured',
			'cname'      => '',
			'layout'     => 1,
			'powered_by' => 1,
			'aff_link'   => '',
		);
		update_option( 'md_dash_settings', $dash_settings );
	}
	$receipt_settings = get_option( 'md_receipt_settings' );
	if ( empty( $receipt_settings ) ) {
		$receipt_settings = array(
			'co-name'  => get_option( 'blogname' ),
			'co-email' => get_option( 'admin_email' ),
		);
		if ( function_exists( 'is_id_pro' ) && is_id_pro() ) {
			$receipt_settings['creator_permissions'] = '3';
		}
		update_option( 'md_receipt_settings', $receipt_settings );
	}
}

// prepare deletion hooks
if ( is_md_network_activated() ) {
	add_action( 'delete_blog', 'memberdeck_uninstall', 1, 1 );
	register_uninstall_hook( __FILE__, 'md_remove_all_traces' );
} else {
	register_uninstall_hook( __FILE__, 'memberdeck_uninstall' );
}

function md_remove_all_traces() {
	global $wpdb;
	$sql = 'SELECT * FROM ' . $wpdb->base_prefix . 'blogs';
	$res = $wpdb->get_results( $sql );
	foreach ( $res as $blog ) {
		memberdeck_uninstall( $blog->blog_id );
	}
}

function memberdeck_uninstall( $blog_id = null ) {
	global $wpdb;
	// once again, check for type of install and get proper prefixes
	$prefix = md_wpdb_prefix( $blog_id );

	$sql    = 'DROP TABLE IF EXISTS ' . $prefix . 'memberdeck_members, ' . $prefix . 'memberdeck_levels, ' . $prefix
	. 'memberdeck_credits, ' . $prefix . 'memberdeck_downloads, ' . $prefix . 'memberdeck_orders, ' . $prefix . 'memberdeck_preorder_tokens, ' . $prefix
	. 'mdid_assignments, ' . $prefix . 'mdid_project_levels, ' . $prefix . 'mdid_orders, ' . $prefix . 'memberdeck_keys, ' . $prefix . 'memberdeck_key_assoc, ' . $prefix
	. 'memberdeck_sc_params';
	$option = get_option( 'testme' );
	update_option( 'testme', $option . ', ' . $sql );
	$res = $wpdb->query( $sql );
	delete_option( 'memberdeck_gateways' );
	delete_option( 'md_dash_settings' );
	delete_option( 'md_receipt_settings' );
	$email_defaults = array( 'registration_email_default', 'welcome_email_default', 'purchase_receipt_default', 'creator_receipt_default', 'preorder_receipt_default' );
	foreach ( $email_defaults as $default ) {
		delete_option( $default );
	}
}

global $crowdfunding;

function memberdeck_styles() {
	global $permalink_structure;
	global $global_currency;
	if ( empty( $permalink_structure ) ) {
		$prefix = '&';
	} else {
		$prefix = '?';
	}
	wp_register_script( 'idcommerce-js', plugins_url( 'js/idcommerce-min.js', __FILE__ ) );
	wp_register_script( 'idlightbox-js', plugins_url( 'js/lightbox-min.js', __FILE__ ) );
	wp_register_style( 'idcommerce', plugins_url( 'css/style-min.css', __FILE__ ) );
	wp_register_style( 'font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css' );
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'idcommerce-js' );
	wp_enqueue_script( 'idlightbox-js' );
	wp_enqueue_style( 'font-awesome' );

	$ajaxurl      = site_url( '/wp-admin/admin-ajax.php' );
	$pluginsurl   = plugins_url( '', __FILE__ );
	$homeurl      = home_url( '/' );
	$durl         = md_get_durl();
	$settings     = get_option( 'memberdeck_gateways' );
	$gen_settings = get_option( 'md_receipt_settings' );
	$test         = '0';
	if ( ! empty( $settings ) ) {
		//$settings = unserialize($settings);
		if ( is_array( $settings ) ) {
			if ( isset( $settings['test'] ) ) {
				$test = (string) $settings['test'];
			} else {
				$test = '0';
			}
			if ( isset( $settings['es'] ) && ! is_idc_free() ) {
				$es = $settings['es'];
			} else {
				$es = '0';
			}
			if ( isset( $settings['esc'] ) && ! is_idc_free() ) {
				$esc = $settings['esc'];
			} else {
				$esc = '0';
			}
			if ( isset( $settings['epp'] ) ) {
				$epp = $settings['epp'];
			} else {
				$epp = '0';
			}
			if ( isset( $settings['manual_checkout'] ) ) {
				$mc = (string) $settings['manual_checkout'];
			} else {
				$mc = '0';
			}
			// if (isset($settings['ecb']) && !is_idc_free()) { hard-setting it to 0 for already activated installs
			if ( false ) {
				$ecb = $settings['ecb'];
			} else {
				$ecb = '0';
			}
			if ( isset( $settings['eauthnet'] ) && ! is_idc_free() ) {
				$eauthnet = $settings['eauthnet'];
			} else {
				$eauthnet = '0';
			}
			if ( isset( $settings['eppadap'] ) && ! is_idc_free() ) {
				$eppadap = $settings['eppadap'];
			} else {
				$eppadap = '0';
			}
			if ( isset( $settings['elw'] ) && ! is_idc_free() ) {
				$elw = $settings['elw'];
			} else {
				$elw = '0';
			}

			global $post;
			if ( class_exists( 'IDF' ) ) {
				if ( isset( $post ) ) {
					$platform = idf_platform();
					if ( 'idc' === $platform && has_shortcode( $post->post_content, 'idc_checkout' ) || has_shortcode( $post->post_content, 'memberdeck_checkout' ) || isset( $_GET['mdid_checkout'] ) ) {
						wp_register_script( 'stripe', 'https://js.stripe.com/v1/' );
					}
					if ( has_shortcode( $post->post_content, 'idc_checkout' ) || has_shortcode( $post->post_content, 'memberdeck_checkout' ) || has_shortcode( $post->post_content, 'idc_dashboard' ) || has_shortcode( $post->post_content, 'memberdeck_dashboard' ) || isset( $_GET['mdid_checkout'] ) || isset( $_GET['idc_renew'] ) || isset( $_GET['idc_button_submit'] ) ) {
						if ( $es == '1' ) {
							wp_enqueue_script( 'stripe' );
						}
					}
				}
			}

			wp_add_inline_script( 'idcommerce-js', "var memberdeck_mc = '".$mc."';", 'before');
			if ( ! empty( $global_currency ) ) {
				wp_add_inline_script( 'idcommerce-js', "var memberdeck_global_currency = '".$global_currency."';", 'before');
			}
			$ccode = md_currency_symbol( $global_currency );
			if ( ! empty( $ccode ) ) {
				wp_add_inline_script( 'idcommerce-js', "var memberdeck_ccode = '".$ccode."';", 'before');
			}
			if ( $es == '1' ) {
				wp_add_inline_script( 'idcommerce-js', "var memberdeck_es = '1';", 'before');
				$pk  = $settings['pk'];
				$tpk = $settings['tpk'];
				if ( $test == '1' ) {
					wp_add_inline_script( 'idcommerce-js', "var memberdeck_pk = '".( ! empty( $tpk ) ? $tpk : '0' )."';", 'before');
				} else {
					wp_add_inline_script( 'idcommerce-js', "var memberdeck_pk = '".( ! empty( $pk ) ? $pk : '0' )."';", 'before');
				}
			} else {
				wp_add_inline_script( 'idcommerce-js', "var memberdeck_es = '0';", 'before');
			}
			if ( $esc == '1' ) {
				wp_register_style( 'sc_buttons', plugins_url( '/lib/connect-buttons-min.css', __FILE__ ) );
				wp_enqueue_style( 'sc_buttons' );
			}
			if ( $epp == '1' ) {
				wp_add_inline_script( 'idcommerce-js', "var memberdeck_epp = '1';", 'before');
				$pp_email   = ( ! empty( $settings['pp_email'] ) ? $settings['pp_email'] : array() );
				$test_email = ( ! empty( $settings['test_email'] ) ? $settings['test_email'] : array() );
				$return_url = ( ! empty( $settings['paypal_redirect'] ) ? $settings['paypal_redirect'] : home_url() );
				if ( $test == '1' ) {
					wp_add_inline_script( 'idcommerce-js', "var memberdeck_pp = '".( ! empty( $test_email ) ? $test_email : '' )."';", 'before');
					wp_add_inline_script( 'idcommerce-js', "var memberdeck_paypal = 'https://www.sandbox.paypal.com/cgi-bin/webscr';", 'before');
					wp_add_inline_script( 'idcommerce-js', "var memberdeck_returnurl = '".( ! empty( $settings['paypal_test_redirect'] ) ? $settings['paypal_test_redirect'] : '' )."';", 'before');
				} else {
					wp_add_inline_script( 'idcommerce-js', "var memberdeck_pp = '".( ! empty( $pp_email ) ? $pp_email : '' )."';", 'before');
					wp_add_inline_script( 'idcommerce-js', "var memberdeck_paypal = 'https://www.paypal.com/cgi-bin/webscr';", 'before');
					wp_add_inline_script( 'idcommerce-js', "var memberdeck_returnurl = '".( ! empty( $settings['paypal_redirect'] ) ? $settings['paypal_redirect'] : '' )."';", 'before');
				}
			} else {
				wp_add_inline_script( 'idcommerce-js', "var memberdeck_epp = '0';", 'before');
			}
			if ( $eppadap == 1 ) {
				wp_add_inline_script( 'idcommerce-js', "var memberdeck_eppadap = '1';", 'before');
				if ( $test == '1' ) {
					wp_add_inline_script( 'idcommerce-js', "var memberdeck_paypal_adaptive = 'https://www.sandbox.paypal.com/webapps/adaptivepayment/flow/pay';", 'before');
					wp_add_inline_script( 'idcommerce-js', "var memberdeck_paypal_adaptive_preapproval = 'https://www.sandbox.paypal.com/webapps/adaptivepayment/flow/preapproval';", 'before');
				} else {
					wp_add_inline_script( 'idcommerce-js', "var memberdeck_paypal_adaptive = 'https://www.paypal.com/webapps/adaptivepayment/flow/pay';", 'before');
					wp_add_inline_script( 'idcommerce-js', "var memberdeck_paypal_adaptive_preapproval = 'https://www.paypal.com/webapps/adaptivepayment/flow/preapproval';", 'before');
				}
			} else {
				wp_add_inline_script( 'idcommerce-js', "var memberdeck_eppadap = '0';", 'before');
			}
			if ( $ecb == '1' ) {
				wp_add_inline_script( 'idcommerce-js', "var memberdeck_ecb = '1';", 'before');
			} else {
				wp_add_inline_script( 'idcommerce-js', "var memberdeck_ecb = '0';", 'before');
			}
			if ( $eauthnet == '1' ) {
				wp_add_inline_script( 'idcommerce-js', "var memberdeck_eauthnet = '1';", 'before');
			} else {
				wp_add_inline_script( 'idcommerce-js', "var memberdeck_eauthnet = '0';", 'before');
			}
			if ( $elw == '1' ) {
				$lemonway_3ds_enabled = ( isset( $settings['lemonway_3ds_enabled'] ) ? $settings['lemonway_3ds_enabled'] : '' );
				wp_add_inline_script( 'idcommerce-js', "var idc_lemonway_method = '".( ( ! empty( $lemonway_3ds_enabled ) && $lemonway_3ds_enabled == '1' ) ? '3dsecure' : 'non3dsecure' )."';", 'before');
				wp_add_inline_script( 'idcommerce-js', "var idc_elw = '1';", 'before');
			} else {
				wp_add_inline_script( 'idcommerce-js', "var idc_elw = '0';", 'before');
			}
			wp_add_inline_script( 'idcommerce-js', "var memberdeck_testmode = '".$test."';", 'before');
		}
	} else {
		wp_add_inline_script( 'idcommerce-js', "var memberdeck_epp = '0';", 'before');
		wp_add_inline_script( 'idcommerce-js', "var memberdeck_es = '0';", 'before');
		wp_add_inline_script( 'idcommerce-js', "var memberdeck_mc = '0';", 'before');
		// wp_add_inline_script( 'idcommerce-js', "var memberdeck_ecb = '0';", 'before');
		wp_add_inline_script( 'idcommerce-js', "var memberdeck_eauthnet = '0';", 'before');
		wp_add_inline_script( 'idcommerce-js', "var memberdeck_eppadap = '0';", 'before');
	}
	if ( isset( $_GET['edit-profile'] ) && $_GET['edit-profile'] > 0 ) {
		wp_enqueue_media();
		wp_enqueue_script( 'idf-admin-media' );
	}
	wp_add_inline_script( 'idcommerce-js', "var memberdeck_ajaxurl = '".$ajaxurl."';", 'before');
	wp_add_inline_script( 'idcommerce-js', "var memberdeck_siteurl = '".$homeurl."';", 'before');
	wp_add_inline_script( 'idcommerce-js', "var memberdeck_pluginsurl = '".$pluginsurl."';", 'before');
	wp_add_inline_script( 'idcommerce-js', "var memberdeck_durl = '".$durl."';", 'before', 'before');
	wp_add_inline_script( 'idcommerce-js', "var idc_localization_strings = ".json_encode(idc_localization_strings()).";", 'before');
	wp_add_inline_script( 'idcommerce-js', "var permalink_prefix = '".$prefix."';", 'before');
	wp_add_inline_script( 'idcommerce-js', "var is_idc_free = '".( is_idc_free() ? '1' : '0' )."';", 'before');
	wp_enqueue_style( 'idcommerce' );
};

add_action( 'wp_enqueue_scripts', 'memberdeck_styles' );

/**
 * Adding a schedular which runs daily in IDC
 */

add_action( 'wp', 'idc_daily_scheduler' );
function idc_daily_scheduler() {
	if ( ! wp_next_scheduled( 'idc_daily_event' ) ) {
		wp_schedule_event( time(), 'daily', 'idc_daily_event' );
	}
}

add_action( 'wp', 'idc_hourly_scheduler' );

function idc_hourly_scheduler() {
	if ( ! wp_next_scheduled( 'idc_hourly_event' ) ) {
		wp_schedule_event( time(), 'hourly', 'idc_hourly_event' );
	}
}

add_action( 'idc_daily_event', 'idc_send_renewal_notifications' );
function idc_send_renewal_notifications() {
	// Getting orders only that are of standard level
	// Looping forever and breaking loop when orders are done, picking order in chunks of 200
	$limit_offset = 0;
	$limit        = 200;
	// $total_orders = ID_Member_Order::get_order_count();
	while ( true ) {
		$limit_string = $limit_offset . ',' . $limit;
		$orders_list  = ID_Member_Order::get_orders( null, $limit_string, " WHERE transaction_id != 'pre' AND subscription_id = '' AND e_date >= '" . date( 'Y-m-d H:i:s' ) . "' AND status = 'active' " );
		foreach ( $orders_list as $order ) {
			// Check using meta_data that order is renewed, if yes, we don't need to check that order
			$renewed = ID_Member_Order::get_order_meta( $order->id, 'idc_order_renewed' );
			if ( empty( $renewed ) ) {
				// Getting level for checking if it's enabled for renewal
				$level = ID_Member_Level::get_level( $order->level_id );
				if ( ! empty( $level ) && $level->enable_renewals ) {
					if ( $level->product_status == 'active' ) {
						$tz = get_option( 'timezone_string' );
						if ( empty( $tz ) ) {
							$tz = 'UTC';
						}
						date_default_timezone_set( $tz );
						$reminder_days = array( 30, 14, 7, 1 );
						$e_date        = $order->e_date;
						$current_date  = time();
						$days_left     = idmember_e_date_format( $e_date );
						// if the left number of days to expiry lies in the reminder (interval) days array, then send an email
						if ( in_array( $days_left, $reminder_days ) ) {
							$renewal  = new ID_Member_Renewal( $order->user_id );
							$response = $renewal->send_notification_for_renewal( $days_left, $order->level_id, $level );
							unset( $renewal );
						}
					}
				}
			}
		}
		if ( count( $orders_list ) < $limit ) {
			break;
		}
		$limit_offset += $limit;
	}
}

function memberdeck_webhook_listener() {
	//We need to merge GET with POST for everything to work
	if ( isset( $_GET ) ) {
		if(isset($_GET['first_name']) || isset($_GET['last_name'])) {
			$unwanted_array = array(
				'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E',
				'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y',
				'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i',
				'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y',
				'Ğ'=>'G', 'İ'=>'I', 'Ş'=>'S', 'ğ'=>'g', 'ı'=>'i', 'ş'=>'s', 'ü'=>'u', 'ă'=>'a', 'Ă'=>'A', 'ș'=>'s', 'Ș'=>'S', 'ț'=>'t', 'Ț'=>'T',  );
			$_GET['first_name'] = strtr( $_GET['first_name'], $unwanted_array );
			$_GET['last_name'] = strtr( $_GET['last_name'], $unwanted_array );
			$_GET['item_name'] = strtr( $_GET['item_name'], $unwanted_array );
			$_GET['email'] = isset($_GET['email'])?$_GET['email']:$_GET['payer_email'];
		}
		if( isset($_GET['memberdeck_notify']) || 
		    isset($_GET['reg']) || 
			isset($_GET['ppsuccess']) ||
			isset($_GET['transaction_id']) ||
			isset($_GET['preapproval_key']) ||
			isset($_GET['mdid_checkout']) ||
			isset($_GET['transaction_id']) ||
			isset($_GET['guest_checkout']) ||
			isset($_GET['user_email']) ||
			isset($_GET['transaction_type']) ||
			isset($_GET['reg']) ||
			isset($_GET['subscr_id']) ||
			isset($_GET['project_id']) ||
			isset($_GET['product_id']) ||
			//isset($_GET['status']) ||
			isset($_GET['coinbase_success'])
			) {
			$_POST = array_merge($_POST,$_GET);
		}
	}
	global $crowdfunding;
	global $global_currency;
	global $old_db_version;
	if ( isset( $_POST ) ) {
		//$log = fopen('idmlog.txt', 'a+');
		ini_set( 'post_max_size', '12M' );
		if ( isset( $_GET['memberdeck_notify'] ) && ($_GET['memberdeck_notify'] == 'pp' || $_GET['memberdeck_notify'] == 'pp_notify') ) {
			global $wpdb;
			// need to generate a secure key
			// need to redirect them tto registration url with that key
			//$key = md5(strtotime('now'),

			$vars = array();

			$payment_complete = false;
			$status           = null;
			$trial            = 0;
			foreach ( $_POST as $key => $val ) {
				$data = array( $key => $val );

				$vars[ $key ] = $val;
				//fwrite($log, $key.' = '.$val."\n");
				if ( $key == 'payment_status' && strtoupper( $val ) == 'COMPLETED' ) {
					$payment_complete = true;
					//fwrite($log, 'complete'."\n");
				} elseif ( $key == 'txn_type' && strtoupper( $val ) == 'SUBSCR_SIGNUP' ) {
					$payment_complete = true;
					$trial            = true;
				} elseif ( $key == 'txn_type' && strtoupper( $val ) == 'SUBSCR_CANCEL' ) {
					$subscription_cancel = true;
				} elseif ( $key == 'txn_type' && strtoupper( $val ) == 'NEW_CASE' ) {
					if ( strtoupper( $vars['case_type'] ) == 'COMPLAINT' ) {
						$dispute = true;
					}
				}
			}
			if ( $payment_complete ) {
				// lets get our vars
				$guest_checkout = ( isset( $_GET['guest_checkout'] ) ? $_GET['guest_checkout'] : 0 );
				$fname          = $vars['first_name'];
				$lname          = $vars['last_name'];
				$price          = ( empty( $trial ) ? $vars['mc_gross'] : $vars['mc_amount1'] );
				$payer_email    = $vars['payer_email'];
				$email          = $_GET['email'];
				$product_id     = $vars['item_number'];
				$ipn_id         = $vars['ipn_track_id'];
				$txn_id         = ( ! $trial ? $vars['txn_id'] : $vars['subscr_id'] );
				$txn_check      = ID_Member_Order::check_order_exists( $txn_id );

				if ( ! empty( $txn_check ) ) {
					return;
				}

				$customer = array(
					'product_id' => $product_id,
					'first_name' => $fname,
					'last_name'  => $lname,
					'email'      => $email,
				);

				$new_data = array( 'ipn_id' => $ipn_id );

				$level = ID_Member_Level::get_level( $product_id );
				if ( $level->limit_term == '1' ) {
					$term_length = $level->term_length;
				}
				if ( isset( $vars['txn_type'] ) && ( $vars['txn_type'] == 'subscr_payment' || $vars['txn_type'] == 'subscr_signup' ) ) {
					$recurring          = true;
					$sub_id             = $vars['subscr_id'];
					$new_data['sub_id'] = $sub_id;
					//fwrite($log, 'sub id: '.$sub_id."\n");
				} else {
					$recurring = false;
					$sub_id    = '';
				}
				$e_date        = ID_Member_Order::set_e_date( $level );
				$access_levels = array( absint( $product_id ) );
				//fwrite($log, 'id: '.$product_id."\n");
				//fwrite($log, $email."\n");
				// now we need to see if this user exists in our db
				$member     = new ID_Member();
				$check_user = $member->check_user( $email );
				//fwrite($log, serialize($check_user)."\n");
				if ( ! empty( $check_user ) ) {
					//fwrite($log, 'user exists'."\n");
					// now we know this user exists we need to see if he is a current ID_Member
					$user_id    = $check_user->ID;
					$match_user = $member->match_user( $user_id );

					if ( empty( $match_user ) ) {
						//fwrite($log, 'first purchase'."\n");
						// not a member, this is their first purchase
						$data      = $new_data;
						$user      = array(
							'user_id' => $user_id,
							'level'   => $access_levels,
							'data'    => $data,
						);
						$new       = ID_Member::add_user( $user );
						$order     = new ID_Member_Order( null, $user_id, $product_id, null, $txn_id, $sub_id, 'active', $e_date, $price );
						$new_order = $order->add_order();
					} else {
						//fwrite($log, 'more than one purchase'."\n");
						// is a member, we need to merge new product access with old product access
						if ( isset( $match_user->access_level ) ) {
							$levels = maybe_unserialize( $match_user->access_level );
							if ( ! empty( $levels ) ) {
								foreach ( $levels as $lvl ) {
									$access_levels[] = absint( $lvl );
								}
							}
						}

						if ( isset( $match_user->data ) ) {
							$data = unserialize( $match_user->data );
							if ( ! is_array( $data ) ) {
								$data = array( $data );
							}
							$data[] = $new_data;
						} else {
							$data = $new_data;
						}

						$user = array(
							'user_id' => $user_id,
							'level'   => $access_levels,
							'data'    => $data,
						);
						$new  = ID_Member::update_user( $user );
						//fwrite($log, $user_id);
						$order     = new ID_Member_Order( null, $user_id, $product_id, null, $txn_id, $sub_id, 'active', $e_date, $price );
						$new_order = $order->add_order();
					}
				} else {
					$data = $new_data;
					//fwrite($log, 'new user: '."\n");
					// user does not exist, we must create them
					if ( ! $guest_checkout ) {
						// gen random pw they can change later
						$pw = idmember_pw_gen();
						// gen our user input
						$userdata = array(
							'user_pass'    => $pw,
							'first_name'   => $fname,
							'last_name'    => $lname,
							'user_login'   => $email,
							'user_email'   => $email,
							'display_name' => $fname,
						);
						//fwrite($log, serialize($userdata));
						// insert user into WP db and return user id
						$user_id = wp_insert_user( $userdata );
						//fwrite($log, $user_id."\n");
						// now add user to our member table
						$reg_key = md5( $email . time() );
						$user    = array(
							'user_id' => $user_id,
							'level'   => $access_levels,
							'reg_key' => $reg_key,
							'data'    => $data,
						);
						$new     = ID_Member::add_ipn_user( $user );
						//fwrite($log, $new."\n");
					}
					$order     = new ID_Member_Order( null, ( isset( $user_id ) ? $user_id : null ), $product_id, null, $txn_id, $sub_id, 'active', $e_date, $price );
					$new_order = $order->add_order();
					if ( $guest_checkout ) {
						//fwrite($log, 'order added: '.$new_order."\n");
						do_action( 'idc_guest_checkout_order', $new_order, $customer );
					} else {
						do_action( 'idmember_registration_email', $user_id, $reg_key, $new_order );
					}
				}
				// we need to pass any extra post fields set during checkout
				if ( isset( $_GET ) ) {
					//Setup data if not present
					$_GET['project_id'] = isset($_GET['project_id']) && $_GET['project_id']!=''?$_GET['project_id']:$product_id;
					//$_GET['project_level'] = isset($_GET['project_level']) && $_GET['project_level']!=''?$_GET['project_level']:$_GET['item_name'];
					$_GET['project_level'] = isset($_GET['project_level']) && $_GET['project_level']!=''?$_GET['project_level']:1;
					$fields = $_GET;
					$status = 'P';
					$created_at = $_GET['payment_date'];
				} else {
					$fields = array();
				}
				if ( empty( $reg_key ) ) {
					$reg_key = '';
				}
				//
				if ( $crowdfunding ) {
					if ( isset( $fields['mdid_checkout'] ) ) {
						$mdid_checkout = $fields['mdid_checkout'];
					}
					if ( isset( $fields['project_id'] ) ) {
						$project_id = $fields['project_id'];
					}
					if ( isset( $fields['project_level'] ) ) {
						$proj_level = $fields['project_level'];
					}
					if ( ! empty( $project_id ) && ! empty( $proj_level ) ) {
						$order      = new ID_Member_Order( $new_order );
						$order_info = $order->get_order();
						$created_at = $order_info->order_date;
						$pay_id     = mdid_insert_payinfo( $fname, $lname, $email, $project_id, $txn_id, $proj_level, $price, $status, $created_at );
						if ( isset( $pay_id ) ) {
							if ( $recurring ) {
								$start   = strtotime( 'now' );
								$mdid_id = mdid_insert_order( '', $pay_id, $start, $txn_id );
							} else {
								$mdid_id = mdid_insert_order( '', $pay_id, $new_order, null );
							}
							do_action( 'id_payment_success', $pay_id );
						}
					}
				}
				do_action( 'memberdeck_payment_success', ( isset( $user_id ) ? $user_id : $user_id ), $new_order, $reg_key, $fields, 'paypal' );
				if ( $recurring ) {
					do_action( 'memberdeck_recurring_success', 'paypal', $user_id, $new_order, ( isset( $term_length ) ? $term_length : null ) );
				}
				do_action( 'idmember_receipt', ( isset( $user_id ) ? $user_id : '' ), $price, $product_id, 'paypal', $new_order, $fields );
				//Redirect to Invoice
				$settings = get_option( 'memberdeck_gateways' );
				if ( ! empty( $settings ) ) {
					if ( is_array( $settings ) && isset( $settings['paypal_redirect'] ) ) {
						$url = $settings['test']==1?$settings['paypal_test_redirect']:$settings['paypal_redirect'];
						if ( empty( $url ) ) {
							$url = home_url('dashboard/');
							wp_safe_redirect($url . '?idc_orders=1&view_receipt=' . ($new_order));
							exit;
						} elseif ( ! empty( $url ) ) {
							$url = (strpos($url, 'dashboard') !== false)?$url:rtrim($url,"/").'/dashboard/';
							//echo '<script>location.href="' . $url . '?idc_orders=1&view_receipt=' . $new_order . '";</script>';
							wp_safe_redirect($url . '?idc_orders=1&view_receipt=' . ($new_order));
							exit;
						}
					}
				}
				//fwrite($log, 'user added');
			} elseif ( isset( $subscription_cancel ) && $subscription_cancel == true ) {
				$sub_id = $vars['subscr_id'];
				//fwrite($log, 'subscription cancelled with id: '.$sub_id."\n");
				$order    = new ID_Member_Order( null, null, null, null, null, $sub_id );
				$sub_data = $order->get_subscription( $sub_id );
				if ( ! empty( $sub_data ) ) {
					//fwrite($log, $sub_data->user_id."\n");
					$sub_id        = $sub_data->subscription_id;
					$level_to_drop = $sub_data->level_id;
					$user_id       = $sub_data->user_id;
					$match_user    = ID_Member::match_user( $user_id );
					if ( isset( $match_user ) ) {
						$level_array = unserialize( $match_user->access_level );
						$key         = array_search( $level_to_drop, $level_array );
						unset( $level_array[ $key ] );
						$cancel = ID_Member_Order::cancel_subscription( $sub_data->id );
						//fwrite($log, $cancel);
						$data = unserialize( $match_user->data );
						$i    = 0;
						foreach ( $data as $record ) {
							//fwrite($log, 'record'."\n");
							foreach ( $record as $key => $value ) {
								//fwrite($log, $key."\n");
								//fwrite($log, $value."\n");
								if ( $value == $sub_id ) {
									//fwrite($log, 'value = sub id'."\n");
									$record_id = $i;
									//fwrite($log, $record_id);
								}
							}
							$i++;
						}
						if ( isset( $record_id ) ) {
							$cut_data                = $data[ $record_id ];
							$cut_data['cancel_date'] = date( 'Y-m-d H:i:s' );
							unset( $data[ $record_id ] );
							$data[] = $cut_data;
						}
						$data         = serialize( $data );
						$access_level = serialize( $level_array );
						//fwrite($log, $data."\n");
						//fwrite($log, $access_level."\n");
						$user        = array(
							'user_id' => $user_id,
							'level'   => $access_level,
							'data'    => $data,
						);
						$update_user = ID_Member::update_user( $user );
					}
				}
			} elseif ( isset( $dispute ) && $dispute == true ) {
				$txn_id      = $vars['txn_id'];
				$order       = new ID_Member_Order( null, null, null, null, $txn_id );
				$transaction = $order->get_transaction();
				if ( ! empty( $transaction->subscription_id ) ) {
					$sub_id        = $transaction->subscription_id;
					$level_to_drop = $transaction->level_id;
					$user_id       = $transaction->user_id;
					$match_user    = ID_Member::match_user( $user_id );
					if ( isset( $match_user ) ) {
						$level_array = unserialize( $match_user->access_level );
						$key         = array_search( $level_to_drop, $level_array );
						unset( $level_array[ $key ] );
						$cancel = ID_Member_Order::cancel_subscription( $transaction->id );
						//fwrite($log, $cancel);
						$data = unserialize( $match_user->data );
						$i    = 0;
						foreach ( $data as $record ) {
							foreach ( $record as $key => $value ) {
								if ( $value == $sub_id ) {
									$record_id = $i;
								}
							}
							$i++;
						}
						if ( isset( $record_id ) ) {
							$cut_data                 = $data[ $record_id ];
							$cut_data['dispute_date'] = date( 'Y-m-d H:i:s' );
							unset( $data[ $record_id ] );
							$data[] = $cut_data;
						}
						$data         = serialize( $data );
						$access_level = serialize( $level_array );
						$user         = array(
							'user_id' => $user_id,
							'level'   => $access_level,
							'data'    => $data,
						);
						$update_user  = ID_Member::update_user( $user );
					}
				} else {
					// not a subscription, but a regular purchase
					$level_to_drop = $transaction->level_id;
					$user_id       = $transaction->user_id;
					$match_user    = ID_Member::match_user( $user_id );
					if ( isset( $match_user ) ) {
						$level_array = unserialize( $match_user->access_level );
						$key         = array_search( $level_to_drop, $level_array );
						unset( $level_array[ $key ] );
						$cancel               = ID_Member_Order::cancel_subscription( $transaction->id );
						$data                 = unserialize( $match_user->data );
						$data['dispute_date'] = date( 'Y-m-d H:i:s' );
						$data                 = serialize( $data );
						$access_level         = serialize( $level_array );
						$user                 = array(
							'user_id' => $user_id,
							'level'   => $access_level,
							'data'    => $data,
						);
						$update_user          = ID_Member::update_user( $user );
					}
				}
			}
		}
		// Paypal Payment functions ends here #paypalpayments
		elseif ( isset( $_GET['memberdeck_notify'] ) && $_GET['memberdeck_notify'] == 'stripe' ) {
			//fwrite($log, 'inside stripe'."\n");

			$json = @file_get_contents( 'php://input' );
			//fwrite($log, $json."\n");

			$object = json_decode( $json );
			//fwrite($log, $object->type."\n");
			if ( $object->type == 'invoice.payment_succeeded' ) {
				$data     = $object->data;
				$is_trial = ( ( isset( $data->object->lines->data[0]->plan->trial_period_days ) && $data->object->lines->data[0]->plan->trial_period_days > 0 ) ? true : false );
				$txn_id   = ( $is_trial ? $data->object->number : $data->object->charge );
				//fwrite($log, $txn_id."\n");
				$customer = $data->object->customer;
				//fwrite($log, $customer."\n");
				$plan  = $data->object->lines->data[0]->plan->id;
				$start = $data->object->lines->data[0]->period->start;
				//fwrite($log, 'start: '.$start."\n");
				//fwrite($log, $plan."\n");
				if ( isset( $customer ) ) {
					$member     = ID_Member::get_customer_data( $customer );
					$user_id    = $member->user_id;
					$userdata   = get_userdata( $user_id );
					$user_email = $userdata->user_email;
					//fwrite($log, $user_id."\n");
					if ( ! empty( $user_id ) ) {
						// If it's a subscrition, and txn_id is null
						if ( empty( $txn_id ) && $is_trial ) {
							$ignore_txn_check = true;
						} else {
							$ignore_txn_check = false;
						}
						$txn_check = ID_Member_Order::check_order_exists( $txn_id );
						if ( empty( $txn_check ) || $ignore_txn_check ) {
							//fwrite($log, 'check is empty'."\n");
							$product_id = ID_Member_Level::get_level_by_plan( $plan );
							//fwrite($log, $product_id."\n");
							$level          = ID_Member_Level::get_level( $product_id );
							$recurring_type = $level->recurring_type;
							if ( $recurring_type == 'weekly' ) {
								// weekly
								$exp = strtotime( '+1 week' );
							} elseif ( $recurring_type == 'monthly' ) {
								// monthly
								$exp = strtotime( '+1 month' );
							} else {
								// annually
								$exp = strtotime( '+1 years' );
							}
							$e_date = date( 'Y-m-d H:i:s', $exp );
							//fwrite($log, $e_date);
							if ( $level->limit_term == 1 ) {
								$term_length = $level->term_length;
							}
							$paykey    = md5( $user_email . time() );
							$order     = new ID_Member_Order( null, $user_id, $product_id, null, $txn_id, $plan, 'active', $e_date, ( $data->object->amount_paid / 100 ) );
							$new_order = $order->add_order();
							//fwrite($log, 'new order: '.$new_order."\n");
							// we need to pass any extra post fields set during checkout
							if ( isset( $_GET ) ) {
								$fields = $_GET;
							} else {
								$fields = array();
							}
							//
							if ( $crowdfunding ) {
								#devnote only post cf order if it's matched
								$user_meta  = get_user_meta( $user_id );
								$fname      = $user_meta['first_name'][0]; // var
								$lname      = $user_meta['last_name'][0]; // var
								$price      = $level->level_price; // var
								$order      = new ID_Member_Order( $new_order );
								$the_order  = $order->get_order();
								$created_at = $the_order->order_date; // var
								// txn id is null, so this won't work fug
								$check = mdid_start_check( $start );
								//fwrite($log, serialize($check)."\n");
								if ( ! empty( $check ) ) {
									// this is the first payment, pay id and mdid order are already set. time to update.
									$pay_id = $check->pay_info_id;
									//fwrite($log, 'pay id: '.$pay_id."\n");
									if ( isset( $pay_id ) ) {
										$mdid_order = mdid_payid_check( $pay_id );
										if ( isset( $mdid_order ) ) {
											//fwrite($log, 'mdid order id: '.$mdid_order->id."\n");
											mdid_transaction_to_order( $mdid_order->id, $txn_id );
											mdid_payinfo_transaction( $pay_id, $txn_id );
										}
									}
								} else {
									// this is 2+ payments
									$order_check = mdid_order_by_customer_plan( $customer, $plan );
									if ( ! empty( $order_check ) ) {
										$pay_info = $order_check->pay_info_id;
										if ( isset( $pay_info_id ) ) {
											$id_order = getOrderById( $pay_info_id );
											if ( isset( $id_order ) ) {
												$project_id = $id_order->product_id;
												$proj_level = $id_order->product_level;
												$pay_id     = mdid_insert_payinfo( $fname, $lname, $user_email, $project_id, $txn_id, $proj_level, $price, 'C', $created_at );
												$mdid_order = mdid_insert_order( $customer, $pay_id, null, $plan );
												do_action( 'id_payment_success', $pay_id );
											}
										}
									}
								}
								//
							}
							//
							do_action( 'memberdeck_payment_success', $user_id, $new_order, $paykey, $fields, 'stripe' );
							do_action( 'memberdeck_recurring_success', 'stripe', $user_id, $new_order, ( isset( $term_length ) ? $term_length : null ) );
							do_action( 'memberdeck_stripe_success', $user_id, $user_email );
							do_action( 'idmember_receipt', $user_id, $level->level_price, $product_id, 'stripe', $new_order, $fields );
						}
					}
				}
			}
		} elseif ( isset( $_GET['reg'] ) && $_GET['reg'] !== '' ) {
			$reg_key = $_GET['reg'];
			$user    = ID_Member::retrieve_user_key( $reg_key );
			//print_r($user);
			// maybe do some sort of email verification here
			if ( ! empty( $user ) ) {
				$userdata = get_userdata( $user->user_id );
				$url      = home_url( '/membership-registration' ) . '?email=' . urlencode( $userdata->user_email ) . '&key_valid=' . $reg_key;
				echo '<script>location.href="' . $url . '";</script>';
			}
		} elseif ( isset( $_GET['ppsuccess'] ) && $_GET['ppsuccess'] == 1 ) {
			/*$settings = get_option( 'memberdeck_gateways' );
			if ( ! empty( $settings ) ) {
				if ( is_array( $settings ) && isset( $settings['paypal_redirect'] ) ) {
					$url = $settings['paypal_redirect'];
					if ( ! empty( $url ) ) {
						echo '<script>location.href="' . $url . '";</script>';
					}
				}
			}*/
		} elseif ( isset( $_GET['coinbase_success'] ) && $_GET['coinbase_success'] == 1 ) {
			$json = @file_get_contents( 'php://input' );

			$object = json_decode( $json );

			// File writing for testing
			$filename = __( 'CoinbaseCallback', 'memberdeck' ) . '-' . date( 'Y-m-d h-i-s' ) . '.txt';
			$uploads  = wp_upload_dir();
			$filepath = trailingslashit( $uploads['basedir'] ) . $filename;
			$baseurl  = trailingslashit( $uploads['baseurl'] ) . $filename;
			file_put_contents( $filepath, $json );

			$status = null;

			if ( isset( $object->order ) && is_object( $object->order ) ) {
				$order = $object->order;
				if ( $order->status == 'completed' ) {
					// Getting the custom variable sent using the button
					$custom     = json_decode( $order->custom );
					$product_id = $custom->product_id;
					if ( $global_currency == 'BTC' ) {
						$price = sprintf( '%f', floatval( $order->total_btc->cents / 100000000 ) );
					} else {
						$price = $order->total_native->cents / 100;
					}
					$fname          = $custom->user_fname;
					$lname          = $custom->user_lname;
					$email          = $custom->user_email;
					$txn_id         = $order->transaction->id;
					$guest_checkout = $custom->guest_checkout;

					$customer = array(
						'product_id' => $product_id,
						'first_name' => $fname,
						'last_name'  => $lname,
						'email'      => $email,
					);

					// Payment is successful
					// Checking if the level is recurring
					if ( isset( $order->button->subscription ) && ! empty( $order->button->subscription ) ) {
						$sub_id    = $order->button->id;
						$recurring = true;
					} else {
						$recurring = false;
					}
					// Setting the access level as array
					$access_levels = array( absint( $product_id ) );

					// Getting level details, will be used later
					$level  = ID_Member_Level::get_level( $custom->product_id );
					$e_date = ID_Member_Order::set_e_date( $level );

					// now we need to see if this user exists in our db
					$member     = new ID_Member();
					$check_user = $member->check_user( $email );
					$txn_check  = ID_Member_Order::check_order_exists( $txn_id );
					if ( empty( $txn_check ) ) {
						//fwrite($log, serialize($check_user)."\n");
						if ( ! empty( $check_user ) ) {
							//fwrite($log, 'user exists'."\n");
							// now we know this user exists we need to see if he is a current ID_Member
							$user_id    = $check_user->ID;
							$match_user = $member->match_user( $user_id );
							if ( ! isset( $match_user ) ) {
								//fwrite($log, 'first purchase'."\n");
								// not a member, this is their first purchase

								$user      = array(
									'user_id' => $user_id,
									'level'   => $access_levels, /*, 'data' => $data*/
								);
								$new       = ID_Member::add_user( $user );
								$order     = new ID_Member_Order( null, $user_id, $product_id, null, $txn_id, $sub_id, 'active', $e_date, $price );
								$new_order = $order->add_order();
							} else {
								//fwrite($log, 'more than one purchase'."\n");
								// is a member, we need to push new data to their info table
								if ( isset( $match_user->access_level ) ) {
									$levels = unserialize( $match_user->access_level );
									if ( ! empty( $levels ) ) {
										foreach ( $levels as $key['val'] ) {
											$access_levels[] = absint( $key['val'] );
										}
									}
								}

								// IF the data field is set and contains some data already, we need to append our new transaction data
								if ( isset( $match_user->data ) ) {
									$data = unserialize( $match_user->data );
									if ( ! is_array( $data ) ) {
										$data = array( $data );
									}
								}

								$user = array(
									'user_id' => $user_id,
									'level'   => $access_levels, /*, 'data' => $data*/
								);
								$new  = ID_Member::update_user( $user );
								//fwrite($log, $user_id);
								$order     = new ID_Member_Order( null, $user_id, $product_id, null, $txn_id, $sub_id, 'active', $e_date, $price );
								$new_order = $order->add_order();
							}
						} else {
							//fwrite($log, 'new user: '."\n");
							if ( ! $guest_checkout ) {
								// user does not exist, we must create them
								// gen random pw they can change later
								$pw = idmember_pw_gen();
								// gen our user input
								$userdata = array(
									'user_pass'    => $pw,
									'first_name'   => $fname,
									'last_name'    => $lname,
									'user_login'   => $email,
									'user_email'   => $email,
									'display_name' => $fname,
								);
								//fwrite($log, serialize($userdata));
								// insert user into WP db and return user id
								$user_id = wp_insert_user( $userdata );
								//fwrite($log, $user_id."\n");
								// now add user to our member table
								//fwrite($log, 'exp: '.$exp."\n");
								$reg_key = md5( $email . time() );
								$user    = array(
									'user_id' => $user_id,
									'level'   => $access_levels,
									'reg_key' => $reg_key, /*, 'data' => $data*/
								);
								$new     = ID_Member::add_ipn_user( $user );
								//fwrite($log, $new."\n");
							}
							$order     = new ID_Member_Order( null, ( isset( $user_id ) ? $user_id : null ), $product_id, null, $txn_id, $sub_id, 'active', $e_date, $price );
							$new_order = $order->add_order();
							//fwrite($log, 'order added: '.$new_order."\n");
							if ( $guest_checkout ) {
								do_action( 'idc_guest_checkout_order', $new_order, $customer );
							} else {
								do_action( 'idmember_registration_email', $user_id, $reg_key, $new_order );
							}
						}

						// we need to pass any extra post fields set during checkout
						if ( isset( $_GET ) ) {
							$fields = $_GET;
						} else {
							$fields = array();
						}

						// If crowdfunding is enabled
						if ( $crowdfunding ) {
							if ( isset( $fields['mdid_checkout'] ) ) {
								$mdid_checkout = $fields['mdid_checkout'];
							}
							if ( isset( $fields['project_id'] ) ) {
								$project_id = $fields['project_id'];
							}
							if ( isset( $fields['project_level'] ) ) {
								$proj_level = $fields['project_level'];
							}
							if ( ! empty( $project_id ) && ! empty( $proj_level ) ) {
								$order      = new ID_Member_Order( $new_order );
								$order_info = $order->get_order();
								$created_at = $order_info->order_date;
								$pay_id     = mdid_insert_payinfo( $fname, $lname, $email, $project_id, $txn_id, $proj_level, $price, $status, $created_at );
								if ( isset( $pay_id ) ) {
									if ( $recurring ) {
										$start   = strtotime( 'now' );
										$mdid_id = mdid_insert_order( '', $pay_id, $start, $sub_id );
									} else {
										$mdid_id = mdid_insert_order( '', $pay_id, $new_order, null );
									}
									do_action( 'id_payment_success', $pay_id );
								}
							}
						}
						// Calling the actions for hooks
						do_action( 'memberdeck_payment_success', ( isset( $user_id ) ? $user_id : null ), $new_order, $reg_key, $fields, 'coinbase' );
						if ( $recurring ) {
							do_action( 'memberdeck_recurring_success', 'coinbase', ( isset( $user_id ) ? $user_id : null ), $new_order, ( isset( $term_length ) ? $term_length : null ) );
						}
					}
				}
			}
		} elseif ( isset( $_GET['memberdeck_notify'] ) && $_GET['memberdeck_notify'] == 'pp_adaptive' ) {
			// fwrite($log, print_r($_POST));
			$preauth          = false;
			$payment_complete = false;
			$recurring        = false;
			$new_data         = array();
			$preauth_check    = ( isset( $_GET['preauth_check'] ) ? $_GET['preauth_check'] : '' );
			$vars             = array();
			$plain_content    = @file_get_contents( 'php://input' );
			$plain_content    = str_replace( 'transaction%5B0%5D', 'transaction', $plain_content );
			parse_str( $plain_content, $vars );
			// fwrite($log, "plain_content:\n ".$plain_content."\n");
			// fwrite($log, print_r($vars, true)."\n");
			// fwrite($log, 'GET vars:'."\n");
			// fwrite($log, print_r($_GET, true)."\n");
			// fwrite($log, "payment_complete: ".$payment_complete."\n");

			 // we need to pass any extra post fields set during checkout
			if ( isset( $_GET ) ) {
				$fields = $_GET;
			} else {
				$fields = array();
			}

			if ( strtoupper( $vars['status'] ) == 'COMPLETED' ) {
				$payment_complete = true;
				// fwrite($log, 'complete'."\n");
				// Setting transaction id
				if ( isset( $vars['preapproval_key'] ) && isset( $vars['pay_key'] ) ) {
					// its a completed payment of Preauth
					if ( $crowdfunding ) {
						$txn_id    = ( ! empty( $vars['transaction_id'] ) ? $vars['transaction_id'] : $vars['transaction_id_for_sender_txn'] );
						$preorders = ID_Member_Order::get_md_preorders( $product_id );
						// fwrite($log, '------- txn_id: '.$txn_id."\n");
						$mdid_order = mdid_orders_bycustid( $vars['preapproval_key'] );
						$mdid_order = $mdid_order[0];
						// fwrite($log, 'mdid_orders_bycustid: '.print_r($mdid_order, true)."\n");
						if ( ! empty( $mdid_order ) ) {
							$customer_id = $mdid_order->customer_id;
							if ( isset( $mdid_order->pay_info_id ) && $mdid_order->pay_info_id !== '' ) {
								$pay_id = $mdid_order->pay_info_id;
							}
						}
						// fwrite($log, '------- pay_id: '.$pay_id."\n");
						// Setting IDCF order as complete
						if ( isset( $pay_id ) ) {
							mdid_set_collected( $pay_id, $txn_id );
						}
					}
				}
			}
			// If status is active and the call is from Pre Auth then make payment as complete here except that we have W
			// instead of 'C' in pay_info
			elseif ( strtoupper( $vars['status'] ) == 'ACTIVE' ) {
				$payment_complete = true;
				if ( ! empty( $preauth_check ) && $preauth_check == 'PREAPPROVAL-Authorization' ) {
					$preauth = true;
				} else {
					$recurring          = true;
					$preauth            = true;
					$sub_id             = $vars['preapproval_key'];
					$new_data['sub_id'] = $sub_id;
				}
			} elseif ( strtoupper( $vars['status'] ) == 'CANCELED' && strtoupper( $vars['transaction_type'] ) == 'ADAPTIVE PAYMENT PREAPPROVAL' ) {
				$subscription_cancel = true;
			} elseif ( strtoupper( $vars['transaction_type'] ) == 'NEW_CASE' ) {
				if ( strtoupper( $vars['case_type'] ) == 'COMPLAINT' ) {
					$dispute = true;
				}
			}

			if ( $payment_complete ) {
				// lets get our vars
				$fname               = $_GET['user_fname'];
				$lname               = $_GET['user_lname'];
				$price               = $_GET['price'];
				$payer_email         = $vars['sender_email'];
				$email               = $_GET['user_email'];
				$product_id          = $_GET['product_id'];
				$pay_key             = ( isset( $vars['preapproval_key'] ) ? $vars['preapproval_key'] : '' );
				$new_data['pay_key'] = $pay_key;
				$guest_checkout      = ( isset( $_GET['guest_checkout'] ) ? $_GET['guest_checkout'] : 0 );

				$customer = array(
					'product_id' => $product_id,
					'first_name' => $fname,
					'last_name'  => $lname,
					'email'      => $email,
				);

				$level  = ID_Member_Level::get_level( $product_id );
				$e_date = ID_Member_Order::set_e_date( $level );
				if ( $level->limit_term == '1' ) {
					$term_length = $level->term_length;
				}
				$store_new = true;
				if ( $preauth ) {
					$txn_id         = 'pre';
					$prior_preorder = ID_Member_Order::get_preorder_by_token( $pay_key );
					if ( ! empty( $prior_preorder ) ) {
						$prior_order_obj = new ID_Member_Order( $prior_preorder->order_id );
						$prior_order     = $prior_order_obj->get_order();
						if ( ! empty( $prior_order ) ) {
							$store_new = false;
							// reserved for future use
							$prior_order_status = $prior_order->status;
							$prior_order_txn    = $prior_order->transaction_id;
						}
					}
				} elseif ( $recurring ) {
					$txn_id    = $sub_id;
					$prior_sub = ID_Member_Order::get_subscription_by_sub( $sub_id );
					if ( ! empty( $prior_sub ) ) {
						$store_new = false;
					}
				} else {
					$txn_id      = ( isset( $vars['transaction_id'] ) && ! empty( $vars['transaction_id'] ) ? $vars['transaction_id'] : $vars['transaction_id_for_sender_txn'] );
					$prior_order = ID_Member_Order::check_order_exists( $txn_id );
					if ( ! empty( $prior_order ) ) {
						// this may not ever happen?
						$store_new = false;
					} else {
						// is still in pre status
						$prior_preorder = ID_Member_Order::get_preorder_by_token( $pay_key );
						if ( ! empty( $prior_preorder ) ) {
							$prior_order_obj = new ID_Member_Order( $prior_preorder->order_id );
							$prior_order     = $prior_order_obj->get_order();
							if ( ! empty( $prior_order ) && $prior_order->transaction_id == 'pre' ) {
								$store_new = false;
								// reserved for future use
								$prior_order_status = $prior_order->status;
							}
						}
					}
				}

				$access_levels = array( absint( $product_id ) );
				//fwrite($log, 'id: '.$product_id."\n");
				//fwrite($log, $email."\n");
				// now we need to see if this user exists in our db
				$ID_Member  = new ID_Member();
				$check_user = $ID_Member->check_user( $email );
				//fwrite($log, serialize($check_user)."\n");
				if ( ! empty( $check_user ) && $store_new ) {
					//fwrite($log, 'user exists'."\n");
					// now we know this user exists we need to see if he is a current ID_Member
					$user_id    = $check_user->ID;
					$match_user = $ID_Member->match_user( $user_id );
					if ( ! isset( $match_user ) ) {
						//fwrite($log, 'first purchase'."\n");
						// not a member, this is their first purchase
						// does this ever happen?
						// not a duplicate because user is in wp_users but not in memberdeck table
						if ( ! $guest_checkout ) {
							$user = array(
								'user_id' => $user_id,
								'level'   => $access_levels,
								'data'    => $data,
							);
							$new  = ID_Member::add_user( $user );
						}
					} else {
						//fwrite($log, 'more than one purchase'."\n");
						// is a member, we need to push new data to their info table
						if ( isset( $match_user->access_level ) ) {
							$levels = unserialize( $match_user->access_level );
							if ( ! empty( $levels ) ) {
								foreach ( $levels as $key['val'] ) {
									$access_levels[] = absint( $key['val'] );
								}
							}
						}

						if ( isset( $match_user->data ) ) {
							$data = unserialize( $match_user->data );
							if ( ! is_array( $data ) ) {
								$data = array( $data );
							}
							$data[] = $new_data;
						}
						if ( ! $guest_checkout ) {
							$user = array(
								'user_id' => $user_id,
								'level'   => $access_levels,
								'data'    => $data,
							);
							$new  = ID_Member::update_user( $user );
							//fwrite($log, $user_id);
						}
					}
					$order     = new ID_Member_Order( null, ( isset( $user_id ) ? $user_id : null ), $product_id, null, $txn_id, ( isset( $sub_id ) ? $sub_id : '' ), 'active', $e_date, $price );
					$new_order = $order->add_order();
					if ( $guest_checkout ) {
						do_action( 'idc_guest_checkout_order', $new_order, $customer );
					}
					// Adding pre-auth order
					if ( isset( $preauth ) && $preauth == true ) {
						//echo 'sending a preorder';
						$preorder_entry = ID_Member_Order::add_preorder( $new_order, $pay_key, 'pp-adaptive' );
						do_action( 'memberdeck_preauth_success', ( isset( $user_id ) ? $user_id : null ), $new_order, $txn_id, $fields, 'pp-adaptive' );
						do_action( 'memberdeck_preauth_receipt', ( isset( $user_id ) ? $user_id : null ), $price, $product_id, 'pp-adaptive', $new_order );
					} else {
						do_action( 'idmember_receipt', ( isset( $user_id ) ? $user_id : null ), $price, $product_id, 'pp-adaptive', $new_order, $fields );
					}
				} elseif ( $store_new ) {
					// users first purchase via paypal, which does not require login info
					//fwrite($log, 'new user: '."\n");
					// user does not exist, we must create them
					// gen random pw they can change later
					if ( ! $guest_checkout ) {
						$pw = idmember_pw_gen();
						// gen our user input
						$userdata = array(
							'user_pass'    => $pw,
							'first_name'   => $fname,
							'last_name'    => $lname,
							'user_login'   => $email,
							'user_email'   => $email,
							'display_name' => $fname,
						);
						//fwrite($log, serialize($userdata));
						// insert user into WP db and return user id
						$user_id = wp_insert_user( $userdata );
						//fwrite($log, $user_id."\n");
						// now add user to our member table
						//fwrite($log, 'exp: '.$exp."\n");
						$reg_key = md5( $email . time() );
						$user    = array(
							'user_id' => $user_id,
							'level'   => $access_levels,
							'reg_key' => $reg_key,
							'data'    => $data,
						);
						$new     = ID_Member::add_ipn_user( $user );
						//fwrite($log, $new."\n");
					}
					$order     = new ID_Member_Order( null, ( isset( $user_id ) ? $user_id : null ), $product_id, null, $txn_id, ( isset( $sub_id ) ? $sub_id : '' ), 'active', $e_date, $price );
					$new_order = $order->add_order();
					//fwrite($log, 'order added: '.$new_order."\n");
					if ( $guest_checkout ) {
						do_action( 'idc_guest_checkout_order', $new_order, $customer );
					} else {
						do_action( 'idmember_registration_email', $user_id, $reg_key, $new_order );
					}
					// Adding pre-auth order
					if ( isset( $preauth ) && $preauth == true ) {
						$preorder_entry = ID_Member_Order::add_preorder( $new_order, $pay_key, 'pp-adaptive' );
						do_action( 'memberdeck_preauth_success', ( isset( $user_id ) ? $user_id : null ), $new_order, $txn_id, $fields, 'pp-adaptive' );
						do_action( 'memberdeck_preauth_receipt', ( isset( $user_id ) ? $user_id : null ), $price, $product_id, 'pp-adaptive', $new_order );
					} else {
						do_action( 'idmember_receipt', ( isset( $user_id ) ? $user_id : null ), $price, $product_id, 'pp-adaptive', $new_order, $fields );
					}
				}
				if ( $store_new ) {
					if ( empty( $reg_key ) ) {
						$reg_key = '';
					}
					//
					// fwrite($log, 'crowdfunding: '.$crowdfunding."\n");
					if ( $crowdfunding ) {
						if ( isset( $fields['mdid_checkout'] ) ) {
							$mdid_checkout = $fields['mdid_checkout'];
						}
						if ( isset( $fields['project_id'] ) ) {
							$project_id = $fields['project_id'];
						}
						if ( isset( $fields['project_level'] ) ) {
							$proj_level = $fields['project_level'];
						}
						// fwrite($log, 'product_id: '.$product_id."\n");
						// fwrite($log, 'proj_level: '.$proj_level."\n");
						if ( ! empty( $project_id ) && ! empty( $proj_level ) ) {
							$order      = new ID_Member_Order( $new_order );
							$order_info = $order->get_order();
							$created_at = $order_info->order_date;
							if ( $preauth ) {
								$status = 'W';
							} else {
								// we need to update the IDCF order
								$status = 'C';
							}
							$pay_id = mdid_insert_payinfo( $fname, $lname, $email, $project_id, $txn_id, $proj_level, $price, $status, $created_at );
							if ( isset( $pay_id ) ) {
								if ( $recurring ) {
									$start   = strtotime( 'now' );
									$mdid_id = mdid_insert_order( $pay_key, $pay_id, $new_order, $sub_id );
								} else {
									$mdid_id = mdid_insert_order( $pay_key, $pay_id, $new_order, null );
								}
								do_action( 'id_payment_success', $pay_id );
							}
						}
					}
					//
					do_action( 'memberdeck_payment_success', ( isset( $user_id ) ? $user_id : null ), $new_order, $reg_key, $fields, 'pp-adaptive' );
					if ( $recurring ) {
						if ( $preauth ) {
							$new_sub          = new ID_Member_Subscription( null, ( isset( $user_id ) ? $user_id : null ), $level->id, $sub_id, 'paypal' );
							$filed_sub        = $new_sub->add_subscription();
							$item             = new stdClass();
							$item->product_id = $product_id;
							$item->price      = $price;
							$item->first_name = $fname;
							$item->last_name  = $lname;
							$item->email      = $email;
							$item->key        = $pay_key;
							$response         = adaptive_pay_request( $item, $fields );
							update_option( 'adaptive_response', $reponse );
						} else {
							update_option( 'term_length', $level->term_length );
							do_action( 'memberdeck_recurring_success', 'adaptive', ( isset( $user_id ) ? $user_id : null ), $new_order, ( isset( $level->term_length ) ? $level->term_length : null ) );
						}
					}
					//fwrite($log, 'user added');
				} else {
					// reserved for future use
				}
			} elseif ( isset( $subscription_cancel ) && $subscription_cancel == true ) {
				// we shouldn't see this because we aren't doing subscriptions, but keep as a watch this
				$sub_id = $vars['preapproval_key'];
				//fwrite($log, 'subscription cancelled with id: '.$sub_id."\n");
				$order    = new ID_Member_Order( null, null, null, null, null, $sub_id );
				$sub_data = $order->get_subscription( $sub_id );
				if ( ! empty( $sub_data ) ) {
					//fwrite($log, $sub_data->user_id."\n");
					$sub_id        = $sub_data->subscription_id;
					$level_to_drop = $sub_data->level_id;
					$user_id       = $sub_data->user_id;
					$match_user    = ID_Member::match_user( $user_id );
					if ( isset( $match_user ) ) {
						$level_array = unserialize( $match_user->access_level );
						$key         = array_search( $level_to_drop, $level_array );
						unset( $level_array[ $key ] );
						$cancel = ID_Member_Order::cancel_subscription( $sub_data->id );
						//fwrite($log, $cancel);
						$data = unserialize( $match_user->data );
						$i    = 0;
						foreach ( $data as $record ) {
							//fwrite($log, 'record'."\n");
							foreach ( $record as $key => $value ) {
								//fwrite($log, $key."\n");
								//fwrite($log, $value."\n");
								if ( $value == $sub_id ) {
									//fwrite($log, 'value = sub id'."\n");
									$record_id = $i;
									//fwrite($log, $record_id);
								}
							}
							$i++;
						}
						if ( isset( $record_id ) ) {
							$cut_data                = $data[ $record_id ];
							$cut_data['cancel_date'] = date( 'Y-m-d H:i:s' );
							unset( $data[ $record_id ] );
							$data[] = $cut_data;
						}
						$data         = serialize( $data );
						$access_level = serialize( $level_array );
						//fwrite($log, $data."\n");
						//fwrite($log, $access_level."\n");
						$user        = array(
							'user_id' => $user_id,
							'level'   => $access_level,
							'data'    => $data,
						);
						$update_user = ID_Member::update_user( $user );
					}
				}
			} elseif ( isset( $dispute ) && $dispute == true ) {
				$txn_id      = $vars['transaction_id'];
				$order       = new ID_Member_Order( null, null, null, null, $txn_id );
				$transaction = $order->get_transaction();
				if ( ! empty( $transaction->subscription_id ) ) {
					$sub_id        = $transaction->subscription_id;
					$level_to_drop = $transaction->level_id;
					$user_id       = $transaction->user_id;
					$match_user    = ID_Member::match_user( $user_id );
					if ( isset( $match_user ) ) {
						$level_array = unserialize( $match_user->access_level );
						$key         = array_search( $level_to_drop, $level_array );
						unset( $level_array[ $key ] );
						$cancel = ID_Member_Order::cancel_subscription( $transaction->id );
						//fwrite($log, $cancel);
						$data = unserialize( $match_user->data );
						$i    = 0;
						foreach ( $data as $record ) {
							foreach ( $record as $key => $value ) {
								if ( $value == $sub_id ) {
									$record_id = $i;
								}
							}
							$i++;
						}
						if ( isset( $record_id ) ) {
							$cut_data                 = $data[ $record_id ];
							$cut_data['dispute_date'] = date( 'Y-m-d H:i:s' );
							unset( $data[ $record_id ] );
							$data[] = $cut_data;
						}
						$data         = serialize( $data );
						$access_level = serialize( $level_array );
						$user         = array(
							'user_id' => $user_id,
							'level'   => $access_level,
							'data'    => $data,
						);
						$update_user  = ID_Member::update_user( $user );
					}
				} else {
					// not a subscription, but a regular purchase
					$level_to_drop = $transaction->level_id;
					$user_id       = $transaction->user_id;
					$match_user    = ID_Member::match_user( $user_id );
					if ( isset( $match_user ) ) {
						$level_array = unserialize( $match_user->access_level );
						$key         = array_search( $level_to_drop, $level_array );
						unset( $level_array[ $key ] );
						$cancel               = ID_Member_Order::cancel_subscription( $transaction->id );
						$data                 = unserialize( $match_user->data );
						$data['dispute_date'] = date( 'Y-m-d H:i:s' );
						$data                 = serialize( $data );
						$access_level         = serialize( $level_array );
						$user                 = array(
							'user_id' => $user_id,
							'level'   => $access_level,
							'data'    => $data,
						);
						$update_user          = ID_Member::update_user( $user );
					}
				}
			}
		}
		//fwrite($log, 'booyah');
		//fclose($log);
	}
}

add_action( 'init', 'memberdeck_webhook_listener' );

add_action( 'init', 'memberdeck_disable_others', 1 );

function memberdeck_disable_others() {
	$get_array = array( 'payment_settings', apply_filters( 'idc_backer_profile_slug', 'backer_profile' ), 'edit-profile', apply_filters( 'idc_creator_profile_slug', 'creator_profile' ), apply_filters( 'idc_creator_projects_slug', 'creator_projects' ), 'mdid_checkout', 'idc_orders', 'key_valid', 'idc_button_submit' );
	if ( isset( $_GET['action'] ) && $_GET['action'] == 'register' ) {
		if ( class_exists( 'WPSEO_OpenGraph' ) ) {
			remove_action( 'init', 'initialize_wpseo_front' );
		}
		add_filter( 'jetpack_enable_open_graph', '__return_false', 99 );
		remove_filter( 'the_content', 'wpautop' );
	} elseif ( isset( $_GET['key_valid'] ) && isset( $_GET['email'] ) ) {
		remove_filter( 'the_content', 'wpautop' );
	} else {
		if ( strpos( idf_current_url(), md_get_durl() ) !== false ) {
			remove_action( 'init', 'initialize_wpseo_front' );
			add_filter( 'jetpack_enable_open_graph', '__return_false', 99 );
			remove_filter( 'the_content', 'wpautop' );
		}
	}
	foreach ( $get_array as $get ) {
		if ( isset( $_GET[ $get ] ) ) {
			if ( class_exists( 'WPSEO_OpenGraph' ) ) {
				remove_action( 'init', 'initialize_wpseo_front' );
			}
			add_filter( 'jetpack_enable_open_graph', '__return_false', 99 );
		}
	}
}

add_filter( 'the_content', 'idmember_registration_form', 1 );

function idmember_registration_form( $content ) {
	if ( isset( $_GET['key_valid'] ) && isset( $_GET['email'] ) ) {
		$reg_key    = $_GET['key_valid'];
		$email      = urldecode( $_GET['email'] );
		$user       = ID_Member::retrieve_user_key( $reg_key );
		$member     = new ID_Member();
		$check_user = $member->check_user( $email );

		if ( isset( $user ) && isset( $check_user ) && $check_user->ID == $user->user_id ) {
			$valid = true;
		} else {
			$valid = false;
		}
		if ( $valid == true ) {
			ob_start();
			$user_id        = $user->user_id;
			$current_user   = get_userdata( $user_id );
			$user_firstname = $current_user->user_firstname;
			$user_lastname  = $current_user->user_lastname;
			$extra_fields   = null;
			include 'templates/_regForm.php';
			$content = ob_get_contents();
			ob_end_clean();
			do_action( 'memberdeck_reg_form', $user_id );
			return $content;
		} else {
			$durl = md_get_durl();
			echo '<script>window.location="' . $durl . '";</script>';
		}
	} elseif ( isset( $_GET['action'] ) && $_GET['action'] == 'register' ) {
		if ( ! is_user_logged_in() ) {
			ob_start();
			include 'templates/_regForm.php';
			$content = ob_get_contents();
			ob_end_clean();
		} else {
			$durl = md_get_durl();
			echo '<script>window.location="' . $durl . '";</script>';
		}
	}
	return $content;
}

/**
 * PayPal Adaptive the_content filter to close the embedded box using javascript
 */
function ppadap_webhook_content( $content ) {
	$content .= '<div id="idc_ppadap_return"></div>';
	return $content;
}
add_action( 'init', 'ppadap_webhook_content_check' );

function ppadap_webhook_content_check() {
	if ( ( isset( $_GET['ppadap_success'] ) && $_GET['ppadap_success'] ) || ( isset( $_GET['ppadap_cancel'] ) && $_GET['ppadap_cancel'] == 1 ) ) {
		add_filter( 'the_content', 'ppadap_webhook_content' );
	}
}

add_action( 'init', 'md_export_handler' );

function md_export_handler() {
	//global $phpmailer;
	//print_r($phpmailer);
	if ( isset( $_POST['export_customers'] ) ) {
		$product_id     = absint( $_POST['export_product_choice'] );
		$force_download = ID_Member::export_members( $product_id );
	}
}

function md_s3_enabled() {
	// a function to see if any downloads are using S3
	global $wpdb;
	$sql = $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'memberdeck_downloads WHERE enable_s3 = %d LIMIT 1', absint( 1 ) );
	$res = $wpdb->get_row( $sql );
	if ( ! empty( $res ) ) {
		return true;
	} else {
		return false;
	}
}

// re-enqueing dashicons for FES module
function ww_load_dashicons(){
   wp_enqueue_style("dashicons-css", get_site_url().'/wp-includes/css/dashicons.min.css');
}
add_action('wp_enqueue_scripts', 'ww_load_dashicons', 999);

//Dequeueing jq-migrate.js
function dequeue_jquery_migrate( $scripts ) {
	if ( ! is_admin() && ! empty( $scripts->registered['jquery'] ) ) {
		$scripts->registered['jquery']->deps = array_diff(
			$scripts->registered['jquery']->deps,
			[ 'jquery-migrate' ]
		);
	}
}
add_action( 'wp_default_scripts', 'dequeue_jquery_migrate' );