<?php
Class ID_Fee_Mods {

	private static $instance = null;


	function __construct() {
		self::autoload();
		self::set_filters();
	}

	function autoload() {
		require_once dirname(__FILE__) . '/' . 'fee_mods_hooks.php';
		require_once dirname(__FILE__) . '/' . 'class-fee_mods_metaboxes.php';
	}

	function is_active() {
		// #devnote global method within IDC?
		$settings = get_option('memberdeck_gateways', true);
		return (isset($settings['esc']) ? $settings['esc'] : 0);
	}

	function set_filters() {
		if (self::is_active()) {
			add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
			add_action('init', array($this, 'install_options'));
			add_action('admin_init', 'fee_mods_metabox');
			add_action('ide_after_sc_settings', array($this, 'sc_menu'));
			add_filter('idc_sc_settings', array($this, 'sc_settings'));
			add_filter('idc_app_fee', 'fee_mods_fee', 10, 2);
			add_action('idc_create_customer', array($this, 'update_fee'));
			add_action('memberdeck_payment_success', array($this, 'set_meta'), 100, 5);
			add_action('memberdeck_preauth_success', array($this, 'set_meta'), 100, 5);
			add_filter('id_funds_raised', array($this, 'filter_funds_raised'), 10, 2);
		}
	}

	static function enqueue_scripts() {
		wp_register_script('fee_mods', plugins_url('/js/fee_mods-min.js', __FILE__), array('jquery', 'idcommerce-js'));
		wp_register_style('fee_mods', plugins_url('/css/fee_mods-min.css', __FILE__));
		wp_enqueue_script('fee_mods');
		wp_enqueue_style('fee_mods');
	}

	function install_options() {
		$sc_settings = get_option('md_sc_settings');
		if (isset($sc_settings['donations_on_checkout']) && $sc_settings['donations_on_checkout']) {
			add_action('md_purchase_extrafields', array($this, 'donations_on_checkout'));
		}
		if (isset($sc_settings['cover_fees_on_checkout']) && $sc_settings['cover_fees_on_checkout']) {
			add_action('md_purchase_extrafields', array($this, 'cover_fees_on_checkout'));
		}
	}

	function sc_menu() {
		$sc_settings = get_option('md_sc_settings');
		if (!empty($sc_settings)) {
			foreach ($sc_settings as $k=>$v) {
				$sc_settings[$k] = idc_text_format($v);
			}
		}
		include('templates/admin/_scMenu.php');
	}

	function sc_settings($settings) {
		if (empty($_POST)) {
			return $settings;
		}
		foreach ($_POST as $k=>$v) {
			if ($k == 'fee_mods') {
				$settings[$k] = sanitize_text_field($v);
			}
			else if (strpos($k, 'fee_mods_') !== false) {
				$key = str_replace('fee_mods_', '', $k);
				$settings[$key] = sanitize_text_field($v);
			}
		}
		return $settings;
	}

	static function donations_on_checkout() {
		$sc_settings = get_option('md_sc_settings');
		if (!empty($sc_settings)) {
			foreach ($sc_settings as $k=>$v) {
				$sc_settings[$k] = idc_text_format($v);
			}
		}
		include('templates/_checkoutDonations.php');
	}

	static function cover_fees_on_checkout() {
		global $post;
		if (empty($post->ID)) {
			return;
		}
		$sc_settings = get_option('md_sc_settings');
		if (!empty($sc_settings)) {
			foreach ($sc_settings as $k=>$v) {
				$sc_settings[$k] = idc_text_format($v);
			}
		}
		$custom_fee = get_post_meta($post->ID, 'application_fee', true);
		if (!empty($custom_fee)) {
			$application_fee = $custom_fee;
		}
		else {
			$application_fee = (isset($sc_settings['app_fee']) ? $sc_settings['app_fee'] : null);
		}
		if (!empty($application_fee)) {
			if ($sc_settings['fee_type'] == 'flat') {
				$application_fee = $application_fee / 100;
			}
			$price_in_cents = (float) $_GET['price'] * 100;
			$application_fee = apply_filters('idc_fee_amount', $application_fee, $price_in_cents, $sc_settings['fee_type'], 'stripe');
			$gateway_settings = get_option('memberdeck_gateways');
			$currency = (isset($gateway_settings['stripe_currency']) ? $gateway_settings['stripe_currency'] : '$');
			$currency_code = md_currency_symbol($currency);
			$formatted_fee = idc_price_format($application_fee / 100);
			$fee_with_code = $currency_code.$formatted_fee;
			include('templates/_checkoutFees.php');
		}
	}

	function update_fee($post_data) {
		if (empty($post_data['Fields'])) {
			return;
		}
		foreach ($post_data['Fields'] as $field) {
			if ($field['name'] == 'checkout_donation') {
				$donation = sanitize_text_field($field['value']);
			}
		}
		if (!empty($donation)) {
			// #devnote account for % donations?
			add_filter('idc_fee_amount', function($fee, $price, $fee_type, $gateway) use ($donation) {
				return ($donation * 100) + $fee;
			}, 99, 4);
		}
	}

	function set_meta($user_id, $order_id, $paykey = '', $fields = null, $source = '') {

		if (empty($fields)) {
			return;
		}

		foreach ($fields as $field) {
			if ($field['name'] == 'cover_fees_on_checkout') {
				$fee = $field['value'];

				if ($fee > 0) {
					ID_Member_Order::update_order_meta($order_id, 'fee_mods_covered_fee', $fee);
				}
			}
			else if ($field['name'] == 'project_id') {
				$project_id = $field['value'];
			}
		}

		if (!empty($fee) && !empty($project_id)) {
			$project = new ID_Project($project_id);
			$post_id = $project->get_project_postid();

			if (empty($post_id)) {
				return;
			}

			$total_fees = get_post_meta($post_id, 'fee_mods_total_fees_covered', true);
			if ($total_fees > 0) {
				$total_fees = $fee + $total_fees;
			}
			else {
				$total_fees = $fee;
			}
			update_post_meta($post_id, 'fee_mods_total_fees_covered', $total_fees);
			idf_cache_object('id_project-total_fees_covered-'.$project_id, $total_fees);
		}
	}

	function filter_funds_raised($raised, $post_id) {

		$total_fees = get_post_meta($post_id, 'fee_mods_total_fees_covered', true);

		if (empty($total_fees)) {
			return $raised;
		}

		return $raised - $total_fees;
	}
}
new ID_Fee_Mods();