<?php
class ID_Coinpayments {

	function __construct() {
		self::set_filters();
	}

	function set_filters() {
		add_action('plugins_loaded', array($this, 'coinpayments_load'));
	}

	function coinpayments_load() {
		if (idf_has_idc() && idf_has_idcf()) {
			if (is_id_pro() || is_idc_licensed()) { //Load only if licensed version
				//Add CoinPayment Link in Admin
				add_action('admin_menu', array($this, 'coinpayments_admin'), 13);
				add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts_admin'));
				//Add frontend css
				add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
				//Add frontedn checkout option
				add_filter('idc_localization_strings', array($this, 'localization_strings'), 11);
				add_action('idc_after_credit_card_selectors', array($this, 'cps_checkout_selector'));
				add_filter('idc_checkout_descriptions', array($this, 'cps_checkout_description'), 10, 7);
				//Add Merchant ID box in Creator Dashboard
				add_action('md_profile_extratabs', array($this, 'coin_payment_dashboard_menu'), 1 );
				add_action('init', array($this, 'md_check_show_coinpayments') );
				//Ajax for currecy conversion
				add_action('wp_ajax_cps_currency_conversion', array($this, 'cps_currency_conversion'));
				add_action('wp_ajax_nopriv_cps_currency_conversion', array($this, 'cps_currency_conversion'));
				//Payment Request
				add_action('wp_ajax_id_coinpayments_submit', array($this, 'payment_request'));
				add_action('wp_ajax_nopriv_id_coinpayments_submit', array($this, 'payment_request'));
				//Add iDFC order
				//global $crowdfunding;
				//if ($crowdfunding) {
					add_filter('memberdeck_payment_success', array($this, 'add_idcf_order'), 3, 5);
				//}
				//add_filter('memberdeck_preauth_success', array($this, 'add_idcf_order'), 3, 5);
				
				//Handle response
				add_action('init', array($this, 'webhook_handler'));
				//Update cps payment to pending status
				add_action('template_redirect', array($this, 'update_order_status'));
				//Add details to order detail popup
				add_action('idc_order_sharing_before', array($this, 'order_details'), 10, 2);
				//Ajax for order status
				add_action('wp_ajax_cps_order_status', array($this, 'cps_order_status'));
				add_action('wp_ajax_nopriv_cps_order_status', array($this, 'cps_order_status'));

				//Fee Mods Compatibility
				add_action('plugins_loaded', array($this, 'modules_loaded'));

				//Add cron job functions
				add_action('cps_hourly_event', array($this, 'cps_check_payment_status_hourly') );
				add_action('wp', array($this, 'cps_activation') );

				//Add Email Notication
				add_action('init', array($this, 'cps_email_defaults'));
				add_action('idc_email_template_option', array($this, 'cps_success_notification_options'));
				add_action('idc_email_template', array($this, 'cps_success_notification_text'));

				add_action('coinpayment_receipt', array($this, 'cps_purchase_receipt'), 1, 5);

				//Add IPN check
				add_action('init', array($this, 'ipn_handler'));
			}
		}
	}

	function modules_loaded() {
		if(class_exists('ID_Fee_Mods')) {
			add_action('ide_after_cps_settings', array('ID_Fee_Mods', 'sc_menu'));
			add_action('init', array($this, 'install_options'));
			add_action('wp_enqueue_scripts', array('ID_Fee_Mods', 'enqueue_scripts'));
		}
	}

	function get_default_currency() { //Stripe Based Function
		$gateways = get_option('memberdeck_gateways');
		$sc_settings = get_option('id_stripe_settings');
		$currency = 'USD';
		if (!empty($gateways) && is_array($gateways)) {
			$currency = isset($gateways['stripe_currency'])?$gateways['stripe_currency']:$currency;
		}
		return $currency;
	}

	function enqueue_scripts_admin() {
		//Check if admin script is required or not
		if(isset($_GET['page']) && $_GET['page']=='idc-coinpayments') {
			wp_register_script('id_coinpayments', plugins_url('assets/js/coinpayments.js', __FILE__));
			wp_register_style('id_coinpayments', plugins_url('assets/css/coinpayments.css', __FILE__));
			wp_enqueue_script('id_coinpayments');
			wp_enqueue_style('id_coinpayments');
		}
	}

	function enqueue_scripts() {
		global $post;
		wp_register_script('id_coinpayments', plugins_url('assets/js/coinpayments.js', __FILE__));
		wp_register_style('id_coinpayments', plugins_url('assets/css/coinpayments.css', __FILE__));
		if (isset($_GET['payment_settings']) && $_GET['payment_settings']) {
			wp_enqueue_style('id_coinpayments');
		}
		if ( isset($post->post_content) && 
			( has_shortcode($post->post_content, 'idc_checkout') || 
			has_shortcode($post->post_content, 'memberdeck_checkout') || 
			has_shortcode($post->post_content, 'idc_dashboard') || 
			has_shortcode($post->post_content, 'memberdeck_dashboard') ) ) {
				wp_enqueue_style('id_coinpayments');
				wp_enqueue_script('id_coinpayments');
		} elseif(isset($_GET['mdid_checkout']) || isset($_GET['idc_renew']) || isset($_GET['idc_button_submit']) || isset($_GET['idc_orders'])) {
			wp_enqueue_style('id_coinpayments');
			wp_enqueue_script('id_coinpayments');
		}
	}

	function coinpayments_admin() {
		$coinpayments_admin = add_submenu_page('idc', __('CoinPayments', 'memberdeck'), __('CoinPayments', 'memberdeck'), 'manage_options', 'idc-coinpayments', array($this, 'coinpayments_admin_menu'),10);
	}

	function coinpayments_admin_menu() {
		if(isset($_GET['tab']) && $_GET['tab']=='log') {
			include_once(dirname(__FILE__) . '/' . 'templates/admin/_cpsLog.php');
		} else {
			$coinpayments_settings = get_option('id_coinpayments_settings');
			$md_sc_settings = get_option('md_sc_settings');
			if (isset($_POST['id_coinpayments_settings_submit']) || isset($_POST['id_coinpayments_settings_submit_2'])) {
				$coinpayments_settings['cps_accepted_coins'] = '';
				foreach ($_POST as $k=>$v) {
					if(
						$k == 'fee_mods' ||
						$k == 'fee_mods_donations_on_checkout' ||
						$k == 'fee_mods_donations_on_checkout_label' ||
						$k == 'fee_mods_donations_on_checkout_text' ||
						$k == 'fee_mods_cover_fees_on_checkout' ||
						$k == 'fee_mods_cover_fees_on_checkout_label' ||
						$k == 'fee_mods_cover_fees_on_checkout_text') {
							$k = $k=='fee_mods' ? $k : str_replace('fee_mods_','',$k);
							$md_sc_settings[$k] = sanitize_text_field($v);

					} else if ($k !== 'id_coinpayments_settings_submit' || $k !== 'id_coinpayments_settings_submit_2') {
						if ($k == 'cps_accepted_coins') {
							$coinpayments_settings[$k] = serialize($v);
						} else {
							$coinpayments_settings[$k] = sanitize_text_field($v);
						}
					}
				}
				$coinpayments_settings['cps_enable'] = isset($_POST['cps_enable']) && $_POST['cps_enable'] == 1?1:0;
				$coinpayments_settings['cps_creator_enable'] = isset($_POST['cps_creator_enable']) && $_POST['cps_creator_enable'] == 1?1:0;

				$md_sc_settings['fee_mods'] = isset($_POST['fee_mods']) && $_POST['fee_mods'] == 1?1:0;
				$md_sc_settings['donations_on_checkout'] = isset($_POST['fee_mods_donations_on_checkout']) && $_POST['fee_mods_donations_on_checkout'] == 1?1:0;
				$md_sc_settings['cover_fees_on_checkout'] = isset($_POST['fee_mods_cover_fees_on_checkout']) && $_POST['fee_mods_cover_fees_on_checkout'] == 1?1:0;
				
				update_option('id_coinpayments_settings', $coinpayments_settings);
				$md_sc_settings['app_fee'] = isset($coinpayments_settings['coinpayments_fee']) && $coinpayments_settings['coinpayments_fee']?$coinpayments_settings['coinpayments_fee']:null;
				update_option('md_sc_settings', $md_sc_settings);
				$sc_settings = array_merge($coinpayments_settings,$md_sc_settings);
			}
			$cps_api = $this->cps_api();
			include_once(dirname(__FILE__) . '/' . 'templates/admin/_adminMenu.php');
		}
	}


	//Add Menu in User Dashboard if no already added
	function coin_payment_dashboard_menu() {
		global $permalink_structure;
		if (empty($permalink_structure)) {
			$prefix = '&';
		} else {
			$prefix = '?';
		}
		$active_modules = idf_get_modules();
		//Memberdeck Gateways Setting
		$memberdeck_gateways = maybe_unserialize(get_option('memberdeck_gateways'));
		//Check Stripe Connect
		$stripe_connect_settings = apply_filters('id_stripe_settings', get_option('id_stripe_settings'));
		$epp_fes = (isset($memberdeck_gateways['epp_fes']) ? $memberdeck_gateways['epp_fes'] : 0);
		$esc = (isset($memberdeck_gateways['esc']) ? $memberdeck_gateways['esc'] : 0);
		$sce = in_array('stripecheckout', $active_modules) && isset($stripe_connect_settings['stripe_connect_enable']) && $stripe_connect_settings['stripe_connect_enable'] == 1?1:0;
		if(!$epp_fes && !$esc && !$sce) { //If menu is not already added
			// if licensed
			if ( !get_option( 'is_id_basic', false ) && get_option('is_id_pro') && get_option('is_idc_licensed') ) {
				$coinpayments_settings = get_option('id_coinpayments_settings');
				$cps_enable = (isset($coinpayments_settings['cps_enable']) && $coinpayments_settings['cps_enable']==1 ? true : false);
				$cps_creator_enable = (isset($coinpayments_settings['cps_creator_enable']) && $coinpayments_settings['cps_creator_enable']==1 ? true : false);
				if ($cps_enable && $cps_creator_enable && current_user_can('create_edit_projects')) {
					echo '<li class="dashtab creator_settings '.(isset($_GET['payment_settings']) ? ' active' : '').'"><a href="'.md_get_durl().$prefix.'payment_settings=1">'.__('Creator Account', 'memberdeck').'</a></li>';
				}
			}
		}
	}

	function install_options() {
		$sc_settings = get_option('md_sc_settings');
		if (isset($sc_settings['donations_on_checkout']) && $sc_settings['donations_on_checkout']) {
			add_action('md_purchase_extrafields', array('ID_Fee_Mods', 'donations_on_checkout'));
		}
		if (isset($sc_settings['cover_fees_on_checkout']) && $sc_settings['cover_fees_on_checkout']) {
			add_action('md_purchase_extrafields', array('ID_Fee_Mods', 'cover_fees_on_checkout'));
		}
		if (isset($sc_settings['fee_mods']) && $sc_settings['fee_mods']) {
			add_action('admin_init', 'fee_mods_metabox');
		}
	}

	function md_check_show_coinpayments() {
		if(isset($_GET['payment_settings'])) {
			if ( !get_option( 'is_id_basic', false ) && get_option('is_id_pro') && get_option('is_idc_licensed') ) {
				add_filter('the_content', 'md_ide_payment_settings');
				$coinpayments_settings = get_option('id_coinpayments_settings');
				$cps_enable = (isset($coinpayments_settings['cps_enable']) && $coinpayments_settings['cps_enable']==1 ? true : false);
				$cps_creator_enable = (isset($coinpayments_settings['cps_creator_enable']) && $coinpayments_settings['cps_creator_enable']==1 ? true : false);
				// if licensed
				if ( get_option('is_id_pro') || get_option('is_idc_licensed') ) {
					if ($cps_enable && $cps_creator_enable) {
						add_action('md_payment_settings_extrafields', array($this, 'md_coinpayments_signup') );
					}
				}
			}
		}
	}

	function md_coinpayments_signup() {
		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;
			$cps_message = '';
			if (isset($_POST['cps_merchant_id']) && isset($_GET['payment_settings'])) {
				if($_POST['cps_merchant_id'] != '' && get_user_meta($user_id, 'cps_merchant_id', true) != sanitize_text_field($_POST['cps_merchant_id'])) {
					update_user_meta($user_id, 'cps_merchant_id', sanitize_text_field($_POST['cps_merchant_id']));
					$cps_message = 'Your Merchant ID has been updated succesfully.';
				} else {
					update_user_meta($user_id, 'cps_merchant_id', sanitize_text_field($_POST['cps_merchant_id']));
				}
			}
			$merchant_id = get_user_meta($user_id, 'cps_merchant_id', true);
			$class = $merchant_id != ''? 'active': '';
			include 'templates/_cps_signup.php';
		}
	}

	function localization_strings($strings) {
		$strings['pay_with_cps'] = __('Pay with Crypto', 'memberdeck');
		return $strings;
	}

	function cps_checkout_selector($gateways) {
		$selector = '<div>';
		$selector .= '<a id="pay-with-cps" class="pay_selector" href="#">';
        $selector .= '<i class="fa fa-btc"></i>';
		$selector .= '<span>'.__('Crypto', 'memberdeck').'</span>';
		$selector .= '</a>';
		$selector .= '</div>';
		echo $selector;
	}

	function cps_checkout_description($content, $level, $level_price, $user_data, $gateways, $general, $credit_value) {
		ob_start();
		$gateway_settings = get_option('memberdeck_gateways');
		$coinpayments_settings = get_option('id_coinpayments_settings');
		$currency = $this->get_default_currency();
		$idc_cps_coins = (isset($coinpayments_settings['cps_accepted_coins']) ? unserialize($coinpayments_settings['cps_accepted_coins']) : array());
		$coname = (!empty($gateway_settings['coname']) ? apply_filters('idc_company_name', $gateway_settings['coname']) : get_option('blogname', ''));
		if($level->txn_type=='preauth') {
			include 'templates/_checkoutCoinPaymentsDescription.php';
		} else {
			include 'templates/_checkoutCoinPaymentsDescription.php';
		}
		$cps_description = ob_get_contents();
		ob_end_clean();
		$content .= apply_filters('id_coin_payments_description', $cps_description, $level, $level_price, $user_data, $gateways, $general);

		return $content;
	}

	function cps_currency_conversion($args=NULL) {
		$_POST = $args!=NULL?$args:$_POST;
		$error = false;
		$cps_api = $this->cps_api();
		$fiat_currency = $_POST['base_currency'];
		$currency = $_POST['currency'];
		$fiat_price = $_POST['amount'];
		try {
			$rates = $cps_api->GetShortRates();
		} catch (Exception $e) {
			$error = $e->getMessage();
		}
		if ($rates["error"] == "ok") {
			// See supported fiat currencies here: https://www.coinpayments.net/supported-coins-fiat
			if (!empty($rates['result'][$fiat_currency])) {
				$fiat_to_btc = $rates['result'][$fiat_currency]['rate_btc'];
				$price_in_btc = ($fiat_price * $fiat_to_btc);
			} else {
				/**
				 * No rate available for that fiat currency. Through manual population of the USD rate
				 * for your chosen currency, you can still output coin currency prices.
				 * This example uses the Cambodian Riel (KHR). At the time of this example the exchange rate of
				 * 1 KHR to 1 USD was 0.000245585 so that is the value we'll use below.
				 */
				$custom_fiat_to_usd = 0.000245585; // Set only this value.
		
				// Use USD as a baseline BTC rate to determine our custom fiat currency to BTC rate
				$usd_to_btc = $rates['result']['USD']['rate_btc'];
				$price_in_usd = ($fiat_price * $custom_fiat_to_usd);
				$price_in_btc = ($price_in_usd * $usd_to_btc);
			}		
			$this_currency_rate_btc = $rates['result'][$currency]['rate_btc'];
			$this_currency_price = ($price_in_btc / $this_currency_rate_btc);
			$crypto_price = number_format((float)$this_currency_price, 8, '.', '');
			$return = [
				'success' => true,
				'message' => '',
				'result'  => $crypto_price.' '.$currency,
				'amount'  => $crypto_price,
			];
			if($args==NULL) {
				echo json_encode($return);
				exit;
			} else {
				return $crypto_price;
			}
		} else {
			$error = $rates["error"];
		}
		$return = [
			'success' => false,
			'message' => $error,
			'result'  => '',
		];
		echo json_encode($return);
		exit;
	}

	function cps_api() {
		require('lib/autoload.php');
		$coinpayments_settings = get_option('id_coinpayments_settings');
		$public_key = (isset($coinpayments_settings['coinpayments_public_key']) ? $coinpayments_settings['coinpayments_public_key'] : '');
		$private_key = (isset($coinpayments_settings['coinpayments_private_key']) ? $coinpayments_settings['coinpayments_private_key'] : '');
		$cps_api = new CoinpaymentsAPI($private_key, $public_key, 'json');
		return $cps_api;
	}

	function payment_request() {
		global $crowdfunding;
		
		//Return array
		$return = ['response'=>__('failure', 'memberdeck'),'message'=>'','result'=>''];

		//idc post data
		do_action('idc_create_customer', $_POST);
		$customer = idf_sanitize_array($_POST['customer']);
		$fields = (isset($_POST['Fields']) ? $_POST['Fields'] : null);
		$txn_type = $_POST['txnType'];
		$renewable = ((isset($_POST['Renewable'])) ? $_POST['Renewable'] : '');
		$original_price = $pwyw_price = ((isset($_POST['pwyw_price'])) ? sanitize_text_field($_POST['pwyw_price']) : '');
		$customer_id = ((isset($_POST['customer_id'])) ? sanitize_text_field($_POST['customer_id']) : '');
		$product_id = absint(sanitize_text_field($customer['product_id']));
		$token = 'customer';
		//Return if 
		if (empty($product_id)) {
			//Missing product ID
			$return['message'] = __('Product ID is missing or incomplete', 'memberdeck').':'.__LINE__;
			print_r(json_encode($return));
			exit;
		}
		$level_data = apply_filters('idc_level_data', ID_Member_Level::get_level($product_id), 'checkout');
		if (empty($level_data)) {
			//Missing level data
			$return['message'] = __('Level data is missing or incomplete', 'memberdeck').':'.__LINE__;
			print_r(json_encode($return));
			exit;
		}

		$level_id = $level_data->id;

		//convert fields value to params
		$params = array();
		if(count($fields)) {
			foreach($fields as $field) {
				$params[$field['name']] = $field['value'];
			}
		}
		$source = 'coinpayments';

		if(isset($_POST['checkout_donation'])) {
			$pwyw_price = $pwyw_price + $_POST['checkout_donation'];
		}
		if(isset($_POST['cover_fees'])) {
			$pwyw_price = $pwyw_price + $_POST['cover_fees'];
		}

		//get customer id
		$CUST_ID = 'coinpayments';
		$customer_id = customer_id();
		$customer_id = apply_filters('idc_customer_id_checkout', (isset($customer_id) ? $customer_id : ''), $source, null, $_POST['Fields']);

		//Init Payment Gateway
		$cps_api = $this->cps_api();

		//get customer details
		$fname = sanitize_text_field($customer['first_name']);
		$lname = sanitize_text_field($customer['last_name']);
		if (isset($customer['email'])) {
			$email = sanitize_email($customer['email']);
		} else {
			// they have used 1cc or some other mechanism and we don't have their email
			if (is_user_logged_in()) {
				$current_user = wp_get_current_user();
				$email = $current_user->user_email;
			}
		}
		if (is_user_logged_in()) {
			$current_user = wp_get_current_user();
			get_user_meta( $current_user->ID, 'first_name', true )?'':update_user_meta( $current_user->ID, 'first_name', $fname );
			get_user_meta( $current_user->ID, 'last_name', true )?'':update_user_meta( $current_user->ID, 'last_name', $lname );
		}
		$pw = null;
		if (!empty($customer['pw'])) {
			$pw = sanitize_text_field($customer['pw']);
		}
		$member = new ID_Member();
		$check_user = $member->check_user($email);
		if (!empty($check_user)) { //user already exists
			$user_id = $check_user->ID;
		} else {
			$data = $new_data;
			if (!$guest_checkout) {
				$pw = idmember_pw_gen(); //random password to reset later
				//user insert
				$userdata = array('user_pass' => $pw,
					'first_name' => $fname,
					'last_name' => $lname,
					'user_login' => $email,
					'user_email' => $email,
					'display_name' => $fname);

				$user_id = wp_insert_user($userdata);

				$reg_key = md5($email.time());
				$user = array('user_id' => $user_id, 'level' => $access_levels, 'reg_key' => $reg_key, 'data' => $data);
				$new = ID_Member::add_ipn_user($user);
			}
		}

		//Add order with failed status
		$e_date = date('Y-m-d H:i:s',strtotime('+1 year'));//idc_set_order_edate($level_data);
		$txn_id = 'pre';
		$paykey = md5($email.time());
		$order = new ID_Member_Order(null, $user_id, $product_id, null, $paykey, null, 'pending', $e_date, $pwyw_price);
		$new_order = $order->add_order(); //returns order id
		$order->update_order_meta($new_order,'original_price',$original_price);
		if(isset($_POST['checkout_donation'])) {
			$order->update_order_meta($new_order,'checkout_donation',$_POST['checkout_donation']);
		}
		if(isset($_POST['cover_fees'])) {
			$order->update_order_meta($new_order,'cover_fees',$_POST['cover_fees']);
		}

		//params for coinpayment success url
		$amount = $pwyw_price;
		// The currency for the amount above (original price)
		$currency1 = $_POST['currency'];
		// The currency the buyer will be sending equal to amount of $currency1
		$currency2 = $_POST['cryptoCurrency'];
		// Enter buyer email below
		$buyer_email = $email;
		// Set a custom address to send the funds to.
		// Will override the settings on the Coin Acceptance Settings page
		$address = '';
		// Enter a buyer name for later reference
		$buyer_name = $fname.' '.$lname;
		// Enter additional transaction details
		$item_name = $level_data->level_name;
		$item_number = 'Level ID: '.$product_id;
		$custom = json_encode(['name'=>$fname.' '.$lname,'level'=>$level_data->level_name,'order_id'=>$new_order,'pay_key'=>$paykey]);
		$invoice = $paykey;
		$params = array (
			'memberdeck_notify' => $source,
			'email' => $email,
			'mdid_checkout' => $product_id,
			'product_id' => $product_id,
			'project_id' => $params['project_id'],
			'price' => $pwyw_price,
			'project_level' => $params['project_level'],
			'guest_checkout' => 0,
			'success' => 1,
			'order_id' => $new_order,
			'pay_key' => $paykey
		);
		$redirect_url = apply_filters('idc_coinpayments_callback_url', md_get_durl().idf_get_querystring_prefix().http_build_query($params));
		$ipn_params = array (
			'memberdeck_notify' => 'cps_ipn',
			'order_id' => $new_order,
		);
		$ipn_url = apply_filters('idc_coinpayments_ipn_url', md_get_durl().idf_get_querystring_prefix().http_build_query($ipn_params));
		// Make call to API to create the transaction
		try {
			$transaction_response = $cps_api->CreateComplexTransaction($amount, $currency1, $currency2, $buyer_email, $address, $buyer_name, $item_name, $item_number, $invoice, $custom, $ipn_url);
			if ($transaction_response["error"] == "ok") {
				$return['response'] = __('success', 'memberdeck');
				$return['result'] = $transaction_response['result'];
				$txn_id = $return['result']['txn_id'];
				$parts = parse_url($return['result']['checkout_url']);
				parse_str($parts['query'], $qs);
				$key = $qs['key'];
				$return['redirectTo'] = str_replace('https://','http://',$redirect_url.'&txn_id='.$txn_id.'&txn_key='.$key);
				print_r(json_encode($return));
				exit;
			} else {
				$return['message'] = $transaction_response["error"].':'.__LINE__;
				print_r(json_encode($return));
				exit;
			}
		} catch (Exception $e) {
			$return['message'] = $e->getMessage().':'.__LINE__;
			print_r(json_encode($return));
			exit;
		}
		if ($transaction_response["error"] == "ok") {
		} else {
			$return['message'] = $transaction_response["error"].':'.__LINE__;
			print_r(json_encode($return));
			exit;
		}
	}

	//Check response and redirect to order success
	function webhook_handler() {
		if (isset($_GET['memberdeck_notify']) && $_GET['memberdeck_notify'] == 'coinpayments') {
			//Set vars
			$email = $_GET['email'];
			$member = new ID_Member();
			$check_user = $member->check_user($email);
			$user_id = $check_user->ID;
			$price = $_GET['price'];
			$order_id = $_GET['order_id'];
			//cps txn id
			$cps_tx_id = $_GET['txn_id'];
			$cps_key = $_GET['txn_key'];
			$product_id = $_GET['mdid_checkout'];
			$source = $_GET['memberdeck_notify'];
			$fields = $_GET;

			$order = new ID_Member_Order($order_id);

			//Add cps txn id to order
			$order->update_order_meta($order_id,'cps_txn_id',$cps_tx_id);
			$order->update_order_meta($order_id,'cps_key',$cps_key);

			//Add IDCF Order
			do_action('memberdeck_payment_success', (isset($user_id) ? $user_id : ''), $order_id, (isset($reg_key) ? $reg_key : null), $fields, $source);

			//send mail to backer & redirect to order success page
			do_action('coinpayment_receipt', (isset($user_id) ? $user_id : ''), $price, $product_id, $source, $order_id);
			header('Location:'.home_url('dashboard/?idc_orders=1&view_receipt='.$order_id.'&cpsstats=Y2FuY2VsbGVkdG9wZW5kaW5n'));
		}
	}

	// Add IDCF Order
	function add_idcf_order($user_id, $order_id, $reg_key, $fields, $source) {
		if ($source !== 'coinpayments') {
			return;
		}
		if (empty($fields)) {
			//not $fields params passed
			return;
		}

		if (isset($fields['mdid_checkout'])) {
			$mdid_checkout = $fields['mdid_checkout'];
		}
		if (isset($fields['project_id'])) {
			$project_id = $fields['project_id'];
		}
		if (isset($fields['project_level'])) {
			$proj_level = $fields['project_level'];
		}
		if (!empty($project_id) && !empty($proj_level)) {
			$order = new ID_Member_Order($order_id);
			$order_info = $order->get_order();
			if (empty($order_info)) {
				return;
			}
			$user_id = $order_info->user_id;
			$user = get_user_by('id', $user_id);
			if (empty($user)) {
				return;
			}
			$level_data = ID_Member_Level::get_level($proj_level);
			$type = $level_data->recurring_type;
			$created_at = $order_info->order_date;
			$original_price = ID_Member_Order::get_order_meta($order_id, 'original_price', true);
			$pay_id = mdid_insert_payinfo($user->user_firstname, $user->user_lastname, $user->user_email, $project_id, $order_info->transaction_id, $proj_level, $order_info->price, $order_info->status, $created_at);
			if (isset($pay_id)) {
				if ($type == 'recurring') {
					$mdid_id = mdid_insert_order($user_id, $pay_id, $order_id, $order_info->transaction_id);
				}
				else {
					$mdid_id = mdid_insert_order($user_id, $pay_id, $order_id, null);
				}
				do_action('id_payment_success', $pay_id);
			}
		}			
	}

	//order status mark it pending
	function update_order_status() {
		if(isset($_GET['cpsstats']) && base64_decode($_GET['cpsstats'])=='cancelledtopending') {
			$order_id = $_GET['view_receipt'];
			$order = new ID_Member_Order($order_id);
			$order->update_order_by_field($order_id,'status','pending');
		}
	}

	//Display details in order details
	function order_details($order,$levels) {
		$order_obj = new ID_Member_Order($order->id);
		if($txn_id = $order_obj->get_order_meta($order->id,'cps_txn_id',true)) {
			//Init Payment Gateway
			$cps_api = $this->cps_api();
			ob_start();
			include 'templates/_orderDetails.php';
			$order_details = ob_get_contents();
			ob_end_clean();
			echo $order_details;
		}
	}

	//Update status in order details
	function cps_order_status() {
		$order->id = $order_id = $_POST['order_id'];
		$order_obj = new ID_Member_Order($order_id);
		if($txn_id = $order_obj->get_order_meta($order_id,'cps_txn_id',true)) {
			//Init Payment Gateway
			$cps_api = $this->cps_api();
			ob_start();
			include 'templates/_orderDetails.php';
			$order_details = ob_get_contents();
			ob_end_clean();
			echo $order_details;
			exit;
		}
		echo 'false';
		exit;
	}

	//Hourly cron job to check payment status
	function cps_activation() {
		if ( !wp_next_scheduled( 'cps_hourly_event' ) ) {
			wp_schedule_event(time(), 'hourly', 'cps_hourly_event');
		}
	}		
	function cps_check_payment_status_hourly() {
		//Init Payment Gateway
		$cps_api = $this->cps_api();
		//Create order object
		$order_obj = new ID_Member_Order();
		$orders = $order_obj->get_orders();
		foreach($orders as $order) {
			//Check only pending orders
			if($order->status=='pending') {
				//check if has txn key
				if($txn_id = idc_get_order_meta($order->id,'cps_txn_id',true)) {
					//Call cps API to verify status
					try {
						$txn_response = $cps_api->GetTxInfoSingleWithRaw($txn_id);
						$result = $txn_response['result'];
						$new_order = new ID_Member_Order($order->id);
						if($result['status'] >= 100 || $result['status']==2) {
							//Mark Complete
							$new_order->activate_status(date('Y-m-d H:i:s',strtotime('+1 year')));
							//Merchant transfer
							$level_id = $order->level_id;
							$amount2 = $result['receivedf'];
							$currency2 = $result['coin'];
							if($transfer = $this->merchant_transfers($level_id,$amount2, $currency2, $order->id)) {
								//Successfully transferred
							}
						} else if ($result['status'] < 0) {
							//Mark Cancelled
							//$new_order->cancel_status();
						} else {
							//payment is pending, you can optionally add a note to the order page
							$new_order->update_order_by_field($order->id,'status','pending');
						}
					} catch (Exception $e) {
						$error = $e->getMessage();
					}
				}
			}
		}
	}

	function ipn_handler() {
		if (isset($_GET['memberdeck_notify']) && $_GET['memberdeck_notify'] == 'cps_ipn') {
			cps_log('\r\n<pre>POST:'.print_r($_POST,true).'</pre>');
			cps_log('\r\n<pre>GET:'.print_r($_GET,true).'</pre>');
			//Load CPS Settings
			$cps_settings = get_option('id_coinpayments_settings');
			$cp_merchant_id = $cps_settings['coinpayments_merchant_id'];
			$cp_ipn_secret = $cps_settings['coinpayments_ipn'];
			
			//Load order
			$order_id = $_REQUEST['order_id'];
			$order = new ID_Member_Order($order_id);
			$neworder = $order->get_order();
			//cps_log('\r\nOrder '.print_r($order,true).')','transfers');
			
			//Order is already processed
			if($neworder->status!='pending') return;
			
			// IPN Post data from coinpayments
			if (!isset($_POST['ipn_mode']) || $_POST['ipn_mode'] != 'hmac') {
				cps_log('IPN Mode is not HMAC on line : '.__LINE__);
			}
		
			if (!isset($_SERVER['HTTP_HMAC']) || empty($_SERVER['HTTP_HMAC'])) {
				cps_log('No HMAC signature sent on line : '.__LINE__);
			}
		
			$request = file_get_contents('php://input');
			if ($request === FALSE || empty($request)) {
				cps_log('Error reading POST data on line : '.__LINE__);
			}
		
			if (!isset($_POST['merchant']) || $_POST['merchant'] != trim($cp_merchant_id)) {
				cps_log('No or incorrect Merchant ID passed on line : '.__LINE__);
			}
		
			$hmac = hash_hmac("sha512", $request, trim($cp_ipn_secret));
			if (!hash_equals($hmac, $_SERVER['HTTP_HMAC'])) {
			//if ($hmac != $_SERVER['HTTP_HMAC']) { <-- Use this if you are running a version of PHP below 5.6.0 without the hash_equals function
				cps_log('HMAC signature does not match on line : '.__LINE__);
			}
		
			// HMAC Signature verified at this point, load some variables.
			$ipn_type = $_POST['ipn_type'];
			$txn_id = $_POST['txn_id'];
			$item_name = $_POST['item_name'];
			$item_number = $_POST['item_number'];
			$amount1 = floatval($_POST['amount1']);
			$amount2 = floatval($_POST['amount2']);
			$currency1 = $_POST['currency1'];
			$currency2 = $_POST['currency2'];
			$status = intval($_POST['status']);
			$status_text = $_POST['status_text'];
		
			if ($ipn_type != 'button') { // Advanced Button payment
				cps_log("IPN OK: Not a button payment on line : ".__LINE__);
			}
		

			$level_id = str_replace('Level ID: ','',$item_number);
			//depending on the API of your system, you may want to check and see if the transaction ID $txn_id has already been handled before at this point
			//cps_log('\r\nCondition (Status:'.$status.', Level ID:'.$level_id.')','transfers');

			if ($status >= 100 || $status == 2) {
				// payment is complete or queued for nightly payout, success
				$order->activate_status(date('Y-m-d H:i:s',strtotime('+1 year')));
				
				//Merchant transfer
				if($transfer = $this->merchant_transfers($level_id,$amount2, $currency2, $order_id)) {
					//Successfully transferred
				}

			} else if ($status < 0) {
				//payment error, this is usually final but payments will sometimes be reopened if there was no exchange rate conversion or with seller consent
				//$order->cancel_status();
			} else {
				//payment is pending, you can optionally add a note to the order page
				$order->update_order_by_field($order_id,'status','pending');
			}
		}
	}

	function cps_log($error_message, $file) {
		$error_message = "\r\n".'Date:'.date('Y-m-d H:i:s')."\r\n".$error_message;
		$log_file = plugin_dir_path( __DIR__ )."coinpayments/log/".$file.".log"; 
		error_log($error_message, 3, $log_file);
	}

	function merchant_transfers($level_id,$amount, $currency, $order_id) {
		//echo '\r\nReceived Args: (Amount:'.$amount.', Currency:'.$currency.', Level ID:'.$level_id.')';
		cps_log('\r\nReceived Args: (Amount:'.$amount.', Currency:'.$currency.', Level ID:'.$level_id.')','transfers');

		//Get merchant id from level
		$user_id = get_option('md_level_'.$level_id.'_owner')? get_option('md_level_'.$level_id.'_owner') : 1;
		cps_log('\r\nUser ID: '.$user_id.')','transfers');
		$merchant_id = get_user_meta($user_id, 'cps_merchant_id', true);
		
		if(empty($merchant_id)) { 
			$author_obj = get_user_by('id', $user_id);
			cps_log('\r\nMerchant ID: Not Exists for Project Owner,  User ID #'.$user_id.', User Email: '.$author_obj->user_email.')','transfers');
			return; 
		}
		cps_log('\r\nMerchant ID: '.$merchant_id.')','transfers');

		//Calculate amount to be send
		//If project has fee
		$sc_settings = get_option('md_sc_settings');
		$cps_settings = get_option('id_coinpayments_settings');

		global $wpdb;
		$sql = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'mdid_assignments WHERE level_id = %s', $level_id);
		$result = $wpdb->get_results($sql);
		//echo '<pre>'; print_r($result); echo '</pre>'; exit;
		$project = new ID_Project($result[0]->project_id);
		//echo '<pre>'; print_r($project); echo '</pre>';
		$post_id = $project->get_project_postid();

		//echo '<br>PID:'.$post_id.'<br>SC:'.get_post_meta($post_id, 'application_fee', true); exit;

		if(($sc_settings['fee_mods'] || $cps_settings['fee_mods']) && $fee = get_post_meta($post_id, 'application_fee', true)) {
			$cps_fee_percent = $fee;
		} else {
			//Load CPS Settings
			$cps_fee_percent = $cps_settings['coinpayments_fee'];
		}
		cps_log('\r\nFee Percent: '.$cps_fee_percent.')','transfers');
		if(empty($cps_fee_percent) || $cps_fee_percent<=0) return;
		
		$fee = ($amount*$cps_fee_percent)/100;
		//$amount = $amount - $fee;

		if($checkout_donation = ID_Member_Order::get_order_meta($order_id, 'checkout_donation', true)) {
			$checkout_donation = $this->cps_currency_conversion(['base_currency'=>'USD', 'currency'=>$currency, 'amount'=>$checkout_donation]);
			$amount = $amount - $checkout_donation;
		}
		if($cover_fees = ID_Member_Order::get_order_meta($order_id, 'cover_fees', true)) {
			$cover_fees = $this->cps_currency_conversion(['bas_currency'=>'USD', 'currency'=>$currency, 'amount'=>$cover_fees]);
			$amount = $amount - $cover_fees;
		}
		//echo '\r\nCreateMerchantTransfer Args: (Amount:'.$amount.', Currency:'.$currency.', Merchant ID:'.$merchant_id.')'; exit;
		cps_log('\r\nCreateMerchantTransfer Args: (Amount:'.$amount.', Currency:'.$currency.', Merchant ID:'.$merchant_id.')','transfers');
		//Init Payment Gateway
		$cps_api = $this->cps_api();
		try {
			$transfer = $cps_api->CreateMerchantTransfer($amount, $currency, $merchant_id, 1);
			cps_log('\r\nCreateMerchantTransfer Response: <pre>'.print_r($transfer,true).'</pre>','transfers');
			//echo '<pre>'; print_r($transfer); exit;
			return $transfer['result'];
		} catch (Exception $e) {
			cps_log('\r\nCreateMerchantTransfer Error: <pre>'.$e->getMessage().'</pre>','transfers');
		}
		return false;
	}

	//Add Email Template
	function cps_email_defaults() {
		$cps_receipt_default      =
		'<h3>{{COMPANY_NAME}} Payment Receipt</h3>
		Hello {{NAME}},

		You have successfully made a payment of {{AMOUNT}}.

		This transaction should appear on your CoinPayments transactions as {{COMPANY_NAME}}.
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
		update_option('coinpayments_receipt_default', $cps_receipt_default);
		//Restore default template
		if (isset($_POST['restore_default_coinpayments_receipt'])) {
			$coinpayments_receipt = get_option('coinpayments_receipt_default');
			update_option('coinpayments_receipt', $coinpayments_receipt);
		}
		//Update template text
		if (isset($_POST['edit_template'])) {
			$coinpayments_receipt_text = wp_kses_post($_POST['coinpayments_receipt_text']);
			update_option('coinpayments_receipt', $coinpayments_receipt_text);
		}
	}

	function cps_success_notification_options() {
		echo '<option name="coinpayments_receipt">'.__('CoinPayments Receipt', 'memberdeck').'</option>';
		return;
	}

	function cps_success_notification_text() {
		$coinpayments_receipt = stripslashes(get_option('coinpayments_receipt'));
		echo '<div class="form-row coinpayments_receipt email_text" style="display: none">';
		wp_editor((!empty($coinpayments_receipt) ? $coinpayments_receipt : get_option('coinpayments_receipt_default')), "coinpayments_receipt_text");
		echo '</div>';
		return;
	}

	function cps_purchase_receipt($user_id, $price, $level_id, $source, $new_order) {
		//error_reporting(0);
		$settings = get_option('md_receipt_settings');
		if (!empty($settings)) {
			$settings = maybe_unserialize($settings);
			$coname = apply_filters('idc_company_name', $settings['coname']);
			$coemail = $settings['coemail'];
		} else {
			$coname = apply_filters('idc_company_name', '');
			$coemail = get_option('admin_email', null);
		}
		$price = apply_filters('idc_order_price', $price, $new_order);
		$user = get_userdata($user_id);
		if (!empty($user)) {
			$email = $user->user_email;
			$fname = idc_text_format($user->first_name);
			$lname = idc_text_format($user->last_name);
		} else {
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
			$text = idc_text_format(get_option('coinpayments_receipt'));
			if (empty($text)) {
				$text = idc_text_format(get_option('coinpayments_receipt_default'));
			}
			if (empty($text)) {
				$message .= '<div style="padding:10px;background-color:#f2f2f2;">
							<div style="padding:10px;border:1px solid #eee;background-color:#fff;">
							<h2>'.$coname.' '.__('Payment Receipt', 'memberdeck').'</h2>
	
								<div style="margin:10px;">
	
									 '.__('Hello', 'memberdeck'). ' ' . $fname .' '. $lname .', <br /><br />
		  
									  '.__('You have successfully made a payment of ', 'memberdeck').$price.'<br /><br />
			
									'.__('This transaction should appear on your CoinPayments transactions as', 'memberdeck').' '.$coname.'<br /><br />
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
			} else {
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
}
new ID_Coinpayments();

function cps_log($error_message, $file='ipn') {
	ID_Coinpayments::cps_log($error_message, $file);
}
?>