<?php
class ID_Google_Ecommerce {

	function __construct() {
		self::autoload();
		self::set_filters();
	}

	private static function autoload() {
		require dirname(__FILE__) . '/' . 'google_ecommerce_hooks.php';
	}

	private static function set_filters() {
		add_action('plugins_loaded', array('ID_Google_Ecommerce', 'google_ecommerce_load'));
	}

	public static function google_ecommerce_load() {
		if (idf_has_idc() && idf_has_idcf()) {
			if (is_id_pro() || is_idc_licensed()) { //Load only if licensed version
				add_action('wp_enqueue_scripts', 'google_ecommerce_scripts');
				add_action('wp_head', 'google_tag_scripts', 999999); // Addition For Google Analytics 4, rest all can be removed like wp_enqueue_scripts, google_ecommerce_order_data, google_ecommerce_pay_triggers, google_ecommerce_free_triggers
				add_action('admin_menu', 'google_ecommerce_admin', 12);
				add_action('wp_ajax_google_ecommerce_order_data', 'google_ecommerce_order_data');
				add_action('wp_ajax_nopriv_google_ecommerce_order_data', 'google_ecommerce_order_data');
				add_action('memberdeck_payment_success', 'google_ecommerce_pay_triggers', 10, 5);
				add_action('memberdeck_free_success', 'google_ecommerce_free_triggers', 10, 2);
			}
		}
	}
	
}
new ID_Google_Ecommerce();
?>