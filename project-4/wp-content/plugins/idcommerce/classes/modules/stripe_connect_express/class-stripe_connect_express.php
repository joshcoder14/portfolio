<?php

class ID_Stripe_Connect_Express {
	
	function __construct() {
		self::set_filters();
	}

	function set_filters() {
		add_filter('md_sc_url', array($this, 'filter_sc_url'));
		add_filter('md_sc_url_redirect_params', array($this, 'filter_url_redirect_params'));
	}

	function filter_sc_url($url) {
		return 'https://connect.stripe.com/express/oauth/authorize';
	}

	function filter_url_redirect_params($params) {
		$params['suggested_capabilities[]'] = 'card_payments';
		return $params;
	}

	function sc_dash_link() {
		global $stripe_api_version;
		$user = wp_get_current_user();

		if (empty($user)) {
			return;
		}

		$params = get_sc_params($user->ID);

		if (empty($params)) {
			return;
		}

		if (!class_exists('Stripe')) {
			require_once IDC_PATH.'lib/stripe-php-4.2.0/init.php';
		}
		\Stripe\Stripe::setApiKey(idc_stripe_sk());
		$link = \Stripe\Account::createLoginLink($params->stripe_user_id);
	}

}
new ID_stripe_connect_express();
?>