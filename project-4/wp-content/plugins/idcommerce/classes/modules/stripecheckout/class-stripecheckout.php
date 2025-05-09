<?php
class ID_Stripecheckout {

	function __construct() {
		self::set_filters();
	}

	function set_filters() {
		add_action('plugins_loaded', array($this, 'stripe_checkout_load'));
	}

	function stripe_checkout_load() {
		if (idf_has_idc() && idf_has_idcf()) {
			if (is_id_pro() || is_idc_licensed()) { //Load only if licensed version
				//Add Stripe Checkout Link in Admin
				add_action('admin_menu', array($this, 'stripe_admin'), 13);
				//Add Script in Admin to disable other checkboxes
				add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts_admin'));
				if (self::settings_complete()) {
					add_action('init', array($this, 'stripe_init'));
					add_action('init', array($this, 'webhook_handler'));
					add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
					add_filter('id_stripe_settings', array($this, 'filter_settings'));
					add_filter('idc_localization_strings', array($this, 'localization_strings'), 11);
					add_action('idc_after_credit_card_selectors', array($this, 'stripe_checkout_selector'));
					add_filter('idc_checkout_descriptions', array($this, 'stripe_checkout_description'), 10, 7);
					add_action('wp_ajax_id_stripe_checkout_submit', array($this, 'payment_request'));
					add_action('wp_ajax_nopriv_id_stripe_checkout_submit', array($this, 'payment_request'));
					add_action('wp_ajax_id_stripe_checkout_paymentIntent', array($this, 'get_payment_intent'));
					add_action('wp_ajax_nopriv_id_stripe_checkout_paymentIntent', array($this, 'get_payment_intent'));
					add_filter('idc_stripe_callback_url', array($this, 'prepare_callback'));
					add_filter('idc_order_currency', array($this, 'filter_currency_code'), 10, 3);
					if(class_exists('ID_Fee_Mods')) {
						$id_fee_mods_instance = new ID_Fee_Mods();
						add_action('ide_after_ssc_settings', array($id_fee_mods_instance, 'sc_menu'));
						add_action('init', array($this, 'install_options'));
						add_action('wp_enqueue_scripts', array($id_fee_mods_instance, 'enqueue_scripts'));
					}
					global $crowdfunding;
					if ($crowdfunding) {
						add_filter('memberdeck_payment_success', array($this, 'add_idcf_order'), 3, 5);
					}
					add_filter('memberdeck_preauth_success', array($this, 'add_idcf_order'), 3, 5);
				}
				//Add action to hook on md_process_preauth
				add_action('idc_before_preauth_processing', array($this, 'stripe_checkout_preauth_processing'));

				//For Stripe Connect
				add_action('wp_enqueue_scripts', array($this, 'sc_button_style'));
				add_action('md_profile_extratabs', array($this, 'stripe_connect_add_menu'), 1 );
				add_action('init', array($this, 'md_check_show_stripe_connect') );
				add_action('wp_ajax_idsc_revoke_creds', array($this, 'idsc_revoke_creds') );
				add_action('wp_ajax_nopriv_idsc_revoke_creds', array($this, 'idsc_revoke_creds'));
				add_action('wp_ajax_idsc_get_users', array($this, 'idsc_get_users'));
				add_action('wp_ajax_nopriv_idsc_get_users', array($this, 'idsc_get_users'));
				add_action('init', array($this, 'md_stripe_connect_return_handler'));

				add_filter('idc_order_price', array($this, 'backer_list_order_price'), 10, 2);
			}
		}
	}

	function stripe_init() {
		wp_register_script('id_stripe_gateway', 'https://js.stripe.com/v3/');
		wp_register_script('id_stripe', plugins_url('js/id_stripe-min.js', __FILE__));
	}

	function settings_complete() {
		$settings = self::get_settings();
		return !empty($settings);
	}

	function get_settings() {
		$sc_settings = apply_filters('id_stripe_settings', get_option('id_stripe_settings'));
		return $sc_settings;
	}

	function filter_settings($sc_settings) {
		$gateway_settings = get_option('memberdeck_gateways');
		if (isset($gateway_settings['test']) && $gateway_settings['test']) {
			return self::get_test_settings($sc_settings);
		}
		return $sc_settings;
	}

	function get_test_settings($sc_settings) {
		$sc_settings['stripe_publishable_key'] = $sc_settings['stripe_staging_publishable_key'];
		$sc_settings['stripe_secret_key'] = $sc_settings['stripe_staging_secret_key'];
		return $sc_settings;
	}

	function enqueue_scripts() {
		global $post;
		if (!empty($post)) {
			//Check if stripe checkout is required or not
			if (has_shortcode($post->post_content, 'idc_checkout') || has_shortcode($post->post_content, 'memberdeck_checkout') || has_shortcode($post->post_content, 'idc_dashboard') || has_shortcode($post->post_content, 'memberdeck_dashboard') || isset($_GET['mdid_checkout']) || isset($_GET['idc_renew']) || isset($_GET['idc_button_submit'])) {
				wp_enqueue_script('jquery');
				wp_enqueue_script('id_stripe_gateway');
				wp_enqueue_script('id_stripe');
				$sc_settings = self::get_settings();
				//wp_localize_script( 'id_stripe', 'stripecheckout_pk', ( ! empty( $sc_settings['stripe_publishable_key'] ) ? $sc_settings['stripe_publishable_key'] : '0' ) );
				wp_add_inline_script( 'id_stripe', "var stripecheckout_pk = '". ( ! empty( $sc_settings['stripe_publishable_key'] ) ? $sc_settings['stripe_publishable_key'] : 0 ) . "';" );
			}
		}
	}

	function enqueue_scripts_admin() {
		//Check if admin script is required or not
		if(isset($_GET['page']) && $_GET['page']=='idc-stripe-checkout') {
			wp_register_script('id_stripe_connect', plugins_url('js/id_stripe_connect.js', __FILE__));
			wp_register_script('md_sc', plugins_url('../../../js/mdSC-min.js', __FILE__));
			wp_register_style('sc_buttons', plugins_url('../../../lib/connect-buttons-min.css', __FILE__));
			wp_enqueue_script('id_stripe_connect');
			wp_enqueue_script('md_sc');
			wp_enqueue_style('sc_buttons');
		}
		if(isset($_GET['page']) && $_GET['page']=='idc-gateways') {
			wp_enqueue_script('id_stripe_admin', plugins_url('js/id_stripe_admin-min.js', __FILE__));
			$active_modules = idf_get_modules();
			$new_status = (!empty($active_modules) && in_array('stripecheckout', $active_modules) ? 1 : 0);
			wp_localize_script( 'id_stripe_admin', 'stripecheckout_enable', $new_status );
		}
	}

	function stripe_admin() {
		$stripe_admin = add_submenu_page('idc', __('Stripe Checkout', 'memberdeck'), __('Stripe Checkout', 'memberdeck'), 'manage_options', 'idc-stripe-checkout', array($this, 'stripe_checkout_admin_menu'),10);
	}

	function stripe_checkout_admin_menu() {
		//echo '<pre>'; print_r($_POST); echo '</pre>'; exit;
		$sc_settings = get_option('id_stripe_settings');
		$md_sc_settings = get_option('md_sc_settings');
		if (isset($_POST['id_stripe_settings_submit']) || isset($_POST['id_sc_settings_submit'])) {
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

				} else if ( $k !== 'id_stripe_settings_submit' || $k !== 'id_sc_settings_submit' ) {
					$sc_settings[$k] = sanitize_text_field($v);
				}
			}
			$sc_settings['stripe_connect_enable'] = isset($_POST['stripe_connect_enable']) && $_POST['stripe_connect_enable'] == 1?1:0;
			$sc_settings['dev_mode'] = isset($_POST['dev_mode']) && $_POST['dev_mode'] == 1?1:0;

			$md_sc_settings['fee_mods'] = isset($_POST['fee_mods']) && $_POST['fee_mods'] == 1?1:0;
			$md_sc_settings['donations_on_checkout'] = isset($_POST['fee_mods_donations_on_checkout']) && $_POST['fee_mods_donations_on_checkout'] == 1?1:0;
			$md_sc_settings['cover_fees_on_checkout'] = isset($_POST['fee_mods_cover_fees_on_checkout']) && $_POST['fee_mods_cover_fees_on_checkout'] == 1?1:0;
			
			update_option('id_stripe_settings', $sc_settings);
			update_option('md_sc_settings', $md_sc_settings);
		}
		if(is_array($sc_settings)){
			$sc_settings = array_merge($sc_settings,$md_sc_settings);
		}
		include_once(dirname(__FILE__) . '/' . 'templates/admin/_adminMenu.php');
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

	function localization_strings($strings) {
		$strings['pay_with_stripe'] = __('Pay with Stripe', 'memberdeck');
		return $strings;
	}

	function stripe_checkout_selector($gateways) {
		$selector = '<div>';
		$selector .= '<a id="pay-with-stripe-checkout" class="pay_selector" href="#">';
        $selector .= '<i class="fa fa-credit-card"></i>';
		$selector .= '<span>'.__('Credit Card', 'memberdeck').'</span>';
		$selector .= '</a>';
		$selector .= '</div>';
		echo $selector;
	}

	function stripe_checkout_description($content, $level, $level_price, $user_data, $gateways, $general, $credit_value) {
		ob_start();
		$gateway_settings = get_option('memberdeck_gateways');
		$sc_settings = self::get_settings();
		$stripe_currency = $cc_currency = $this->stripe_default_currency();
		$coname = (!empty($gateway_settings['coname']) ? apply_filters('idc_company_name', $gateway_settings['coname']) : get_option('blogname', ''));
		if($level->txn_type=='preauth') {
			include 'templates/_checkoutStripeCheckoutDescriptionPreAuth.php';
		} else {
			include 'templates/_checkoutStripeCheckoutDescription.php';
		}
		$stripe_description = ob_get_contents();
		ob_end_clean();
		$content .= apply_filters('id_stripe_checkout_description', $stripe_description, $level, $level_price, $user_data, $gateways, $general);

		return $content;
	}

	function load_gateway() {
		//load settings
		$gateway_settings = get_option('memberdeck_gateways');
		$sc_settings = self::get_settings();

		//load library
		require (dirname(__FILE__) . '/' . 'lib/stripe-php-7.67.0/init.php');
		\Stripe\Stripe::setApiKey($sc_settings['stripe_secret_key']);
	}

	function create_customer($fname,$lname,$email,$token,$settings,$source,$fields,$cc_number='',$cc_expiry='',$cc_code='') {
		$customer = idf_sanitize_array($_POST['customer']);
		try {
			$newcust = \Stripe\Customer::create(array(
			'description' => $fname . ' ' . $lname,
			'email' => $email));
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
		//Create user if not logged in
		if($user = get_user_by( 'email', $email )) {
			$user_id = $user->ID;
		} else {
			$userdata = array(
				'user_login'	=> $email,
				'user_email'	=> $email,
				'first_name' 	=> $fname,
    			'last_name'  	=> $lname,
				'user_pass'  	=> md5($customer['pw']),
				'display_name'	=> $fname . ' ' . $lname,
    			'nickname'      => $fname
			);
			$user_id = wp_insert_user( $userdata );
		}
		$custid = apply_filters('idc_create_customer_checkout', (isset($custid) ? $custid : ''), $user_id, array(
			"fname" => $fname,
			"lname" => $lname,
			"email" => $email,
			"cc_number" => ((isset($cc_number)) ? $cc_number : ''),
			"cc_expiry" => ((isset($cc_expiry)) ? $cc_expiry : ''),
			"cc_code" => ((isset($cc_code)) ? $cc_code : ''),
			"settings" => $settings,
			"source" => $source,
			"extra_fields" => $fields,
			"insert" => true
		));
		return $custid;
	}

	function stripe_default_currency() {
		$gateways = get_option('memberdeck_gateways');
		$sc_settings = get_option('id_stripe_settings');
		$stripe_currency = 'USD';
		if (!empty($gateways) && is_array($gateways)) {
			$stripe_currency = $gateways['stripe_currency'];
		}
		return $stripe_currency;
	}

	function payment_request() {
		global $crowdfunding;
		$sc_settings = self::get_settings();
		$gateway_settings = get_option('memberdeck_gateways');

		require (dirname(__FILE__) . '/../../../' . 'idcommerce-sc.php');
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
			$message = __('Product ID is missing or incomplete', 'memberdeck').' '.__LINE__;
			print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
			exit;
		}
		$level_data = apply_filters('idc_level_data', ID_Member_Level::get_level($product_id), 'checkout');
		if (empty($level_data)) {
			//Missing level data
			$message = __('Level data is missing or incomplete', 'memberdeck').' '.__LINE__;
			print_r(json_encode(array('response' => __('failure', 'memberdeck'), 'message' => $message)));
			exit;
		}

		$level_id = $level_data->id;

		//Check Stripe Connect
		$check_claim = get_option('md_level_'.$level_id.'_owner');
		if (!empty($check_claim)) {
			$md_sc_creds = get_sc_params($check_claim);
			if (!empty($md_sc_creds)) {
				$sc_accesstoken = $md_sc_creds->access_token;
			}
		}
		
		//convert fields value to params
		$params = array();
		if(count($fields)) {
			foreach($fields as $field) {
				$params[$field['name']] = $field['value'];
			}
		}
		$source = 'stripe-checkout';
		//get customer id
		$CUST_ID = 'stripe-checkout';
		$customer_id = customer_id();
		$customer_id = apply_filters('idc_customer_id_checkout', (isset($customer_id) ? $customer_id : ''), $source, null, $_POST['Fields']);

		//Init Payment Gateway
		$this->load_gateway();

		//check purchase type and set price
		// If product is renewable, then we need to use renewable price, and avoid any pwyw we might have
		if ($renewable) {
			$level_data->level_price = $level_data->renewal_price;
			$ignore_upgrade = true;
		} else {
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


		$price = $level_data->level_price;
		if(isset($_POST['checkout_donation'])) {
			$price = $price + $_POST['checkout_donation'];
		}
		if(isset($_POST['cover_fees'])) {
			$price = $price + $_POST['cover_fees'];
		}


		//params for stripe success url
		$params = array (
			'memberdeck_notify' => $source,
			'email' => $email,
			'first_name' => $customer['first_name'],
			'last_name' => $customer['last_name'],
			'mdid_checkout' => $product_id,
			'product_id' => $product_id,
			'project_id' => $params['project_id'],
			'original_price' => $original_price,
			'price' => $price,
			'project_level' => $params['project_level'],
			'guest_checkout' => 0,
			'success' => 1
		);

		if(isset($_POST['anonymous_checkout'])) {
			$params['anonymous_checkout'] = $_POST['anonymous_checkout'];
		}
		if(isset($_POST['idc_checkout_comments'])) {
			$params['idc_checkout_comments'] = $_POST['idc_checkout_comments'];
		}
		if(isset($_POST['checkout_donation'])) {
			$params['checkout_donation'] = $_POST['checkout_donation'];
		}
		if(isset($_POST['cover_fees'])) {
			$params['cover_fees'] = $_POST['cover_fees'];
		}

		//params for stripe session creation
		$args = array(
			'success_url' => apply_filters('idc_stripe_callback_url', md_get_durl().idf_get_querystring_prefix().http_build_query($params).'&session_id={CHECKOUT_SESSION_ID}'),
			'cancel_url' => apply_filters('idc_stripe_callback_url', $_POST['current_url']),
			'payment_method_types' => ['card'],
			'customer_email' => $email,
		);

		//check product type and set price accordingly
		$level_data->level_price = apply_filters( 'idc_checkout_level_price', $price, $product_id, ((!empty($check_user)) ? $check_user->ID : ''), ((isset($ignore_upgrade)) ? $ignore_upgrade : false) );
		$level_data = apply_filters('idc_level_data', $level_data, 'checkout');
		$recurring_type = $level_data->recurring_type;
		if ($level_data->level_type == 'recurring') {
			$plan = $level_data->plan;
			if ($recurring_type == 'weekly') {
				// weekly
				$exp = strtotime('+1 week');
				$interval = 'week';
			} else if ($recurring_type == 'monthly') {
				// monthly
				$exp = strtotime('+1 month');
				$interval = 'month';
			} else {
				// annually
				$exp = strtotime('+1 years');
				$interval = 'year';
			}
			$e_date = date('Y-m-d H:i:s', $exp);
			$recurring = true;
			//$interval = $level_data->recurring_type;
			// check for limits
			if ($level_data->limit_term) {
				$term_length = $level_data->term_length;
			}
			//create subscription product
			$api_product = array(
				'name'			=>	$level_data->level_name,
				'description'	=>	$level_data->level_name,
			);
			//In case of Stripe Connect
			if(isset($sc_accesstoken)) {
				$stripeproduct = \Stripe\Product::create($api_product, array('stripe_account' => $md_sc_creds->stripe_user_id));
			} else {
				$stripeproduct = \Stripe\Product::create($api_product);
			}
			$api_price = array(
				'unit_amount' => $price*100,
				'currency' => $this->stripe_default_currency(),
				'recurring' => ['interval' => $interval],
				'product' => $stripeproduct->id,
			);
			//create subscription product price
			if(isset($sc_accesstoken)) {//In case of Stripe Connect
				$stripeprice = \Stripe\Price::create($api_price, array('stripe_account' => $md_sc_creds->stripe_user_id));
			} else {
				$stripeprice = \Stripe\Price::create($api_price);
			}

			$args['mode'] = 'subscription';
			$args['line_items'] = [[
				'quantity' => 1,
				'price' => $stripeprice->id,
			]];
		} else if ($level_data->level_type == 'lifetime') {
			$e_date = null;
			$recurring = false;
		} else {
			$e_date = idc_set_order_edate($level_data);
			$recurring = false;
		}


		if ($level_data->level_type == 'recurring') {
			//if recurring
		} elseif ($txn_type == 'preauth') {
			#Is pre auth payment
			$args['customer'] = isset($customer_id) && $customer_id!=''?$customer_id:$CUST_ID;
			$args['mode'] = 'setup';
			unset($args['customer_email']);
		} else {
			#Is one time payment
			$args['mode'] = 'payment';
			$args['line_items'] = [[
				'name' => $level_data->level_name,
				'quantity' => 1,
				'amount' => $price*100,
				'currency' => $this->stripe_default_currency()
			]];
			//check if post id sent and has attachment
			if(isset($_POST['post_id']) && wp_get_attachment_image_src( get_post_thumbnail_id($_POST['post_id']))!='') {
				$attachment = wp_get_attachment_image_src( get_post_thumbnail_id($_POST['post_id']));
				$args['line_items'][0]['images'] = [$attachment[0]];
			}  
		}


		if (!empty($check_user)) { //If user not exists
			$user_id = $check_user->ID;
			$customer_id = customer_id_ajax($user_id);
			$customer_id = apply_filters('idc_customer_id_checkout', $customer_id, $source, $user_id, $_POST['Fields']);
			$match_user = $member->match_user($user_id);
			if (!isset($match_user->data) && empty($customer_id)) {// no customer ID exists
				// No customer exists, create a new one
				$custid = $this->create_customer($fname,$lname,$email,$token,$sc_settings,$source,$_POST['Fields']);

			} else {// we have a customer ID
				if (!empty($customer_id)) {
					// echo 'cust id not empty and equal to '.$customer_id."\n";
					$custid = $customer_id;
					// there is a customer id saved, so we have the option to use it
					if (!empty($token) && $token == 'customer') {
						// they used 1cc
						//echo 'option 1';
						// echo "token is 'customer'\n";
					} else {
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
							"settings" => $sc_settings,
							"source" => $source,
							"extra_fields" => $_POST['Fields']
						));
						//get cards object
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
						//get cards
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
						} else {
							$card_id = $token;
						}

						//if no match
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
				} else {
					// echo __line__.'. new cust'."\n";
					$new_customer = true;
				}
				if (isset($new_customer)) {
					// we didn't find a custid so we have to make one
					$custid = $this->create_customer($fname,$lname,$email,$token,$sc_settings,$source,$fields);
				}
			}

		} else { //User exists
			$newuser = true;
			// New User, create a new Customer
			$custid = $this->create_customer($fname,$lname,$email,$token,$sc_settings,$source,$fields);
		}

		if ($txn_type == 'preauth') {//Pre-Authrization
			$args['customer'] = isset($customer_id) && $customer_id!=''?$customer_id:$custid;
			unset($args['payment_method_types']);
			$params = array(
				"description" => $fname.' '.$lname. ' - '.$level_data->level_name.'-Failed',
				"amount" => $price*100,
				"currency" => $this->stripe_default_currency(),
				//"customer" => isset($customer_id) && $customer_id!=''?$customer_id:$custid,
				"capture_method" => "manual",
				"payment_method_types"	=> ['card'],
				"metadata" => $args
			);
			//In case of Stripe Connect
			if(isset($sc_accesstoken)) {
				//Calculate Fee for Pre-Auth
				$fee = $sc_settings['fee_type']=='flat'?$sc_settings['app_fee']:0;
				if($sc_settings['fee_type']=='percentage') {
					$amount = $price*100;
					$fee = $amount*$sc_settings['app_fee']/100;
				}
				$tranfer_amount = $amount-$fee;

				if(isset($_POST['cover_fees'])) {
					$fee = $_POST['cover_fees'];
					$fee += isset($_POST['checkout_donation'])?$_POST['checkout_donation']:0;
					$fee = $fee*100;
					$params["application_fee_amount"] = $fee;
					//$tranfer_amount = $tranfer_amount-$_POST['cover_fees'];
				} else {
					$params["application_fee_amount"] = $fee;
				}
				try {
					//In Case Of Connected Account as per https://stripe.com/docs/connect/direct-charges#collecting-fees
					$payment_intent = \Stripe\PaymentIntent::create($params, array('stripe_account' => $md_sc_creds->stripe_user_id));
					//Add Connect ID for JS Init
					$payment_intent['connected_account'] = $md_sc_creds->stripe_user_id;

				} catch(Stripe_CardError $e) {
					$error = $e->getMessage().' '.__LINE__;
				} catch (Stripe_InvalidRequestError $e) {
					// Invalid parameters were supplied to Stripe's API
					$error = $e->getMessage().' '.__LINE__;
				} catch (Stripe_AuthenticationError $e) {
					// Authentication with Stripe's API failed
					$error = $e->getMessage().' '.__LINE__;
				} catch (Stripe_ApiConnectionError $e) {
					// Network communication with Stripe failed
					$error = $e->getMessage().' '.__LINE__;
				} catch (Stripe_Error $e) {
					// Display a very generic error to the user, and maybe send
					// yourself an email
					$error = $e->getMessage().' '.__LINE__;
				} catch (Exception $e) {
					// Something else happened, completely unrelated to Stripe
					$error = $e->getMessage().' '.__LINE__;
				}
			} else { //If not a connected account
				try {
					$payment_intent = \Stripe\PaymentIntent::create($params);
				} catch(Stripe_CardError $e) {
					$error = $e->getMessage().' '.__LINE__;
				} catch (Stripe_InvalidRequestError $e) {
					// Invalid parameters were supplied to Stripe's API
					$error = $e->getMessage().' '.__LINE__;
				} catch (Stripe_AuthenticationError $e) {
					// Authentication with Stripe's API failed
					$error = $e->getMessage().' '.__LINE__;
				} catch (Stripe_ApiConnectionError $e) {
					// Network communication with Stripe failed
					$error = $e->getMessage().' '.__LINE__;
				} catch (Stripe_Error $e) {
					// Display a very generic error to the user, and maybe send
					// yourself an email
					$error = $e->getMessage().' '.__LINE__;
				} catch (Exception $e) {
					// Something else happened, completely unrelated to Stripe
					$error = $e->getMessage().' '.__LINE__;
				}
			}
			if(isset($error) && $error!='') {
				print_r(json_encode(array('response' => 'failure', 'message' => $error)));
			} else {
				print_r(json_encode(array('response' => 'success', 'message' => $payment_intent)));
			}
		} else {
			$params = $args;
			//In case of Stripe Connect
			if(isset($sc_accesstoken)) {
				//Calculate Fee
				if($params['mode']=='payment') {
					$fee = $sc_settings['fee_type']=='flat'?$sc_settings['app_fee']:0;
					if($sc_settings['fee_type']=='percentage') {
						$amount = $price*100;
						$fee = $amount*$sc_settings['app_fee']/100;
					}
					$tranfer_amount = $amount-$fee;
					if(isset($_POST['cover_fees'])) {
						$fee = $_POST['cover_fees'];
						$fee += isset($_POST['checkout_donation'])?$_POST['checkout_donation']:0;
						$fee = $fee*100;
						$params['payment_intent_data']["application_fee_amount"] = $fee;
					} else {
						$params['payment_intent_data']["application_fee_amount"] = $fee;
					}
				} else {
					//Calculate Fee Percent for Subscription
					$fee = $sc_settings['fee_type']=='percentage'?$sc_settings['app_fee']:0;
					if($sc_settings['fee_type']=='flat') {
						$amount = $price*100;
						$fee = ($sc_settings['app_fee']*100) / $amount;
					}
					$tranfer_amount_percent = 100-$fee;
					if(isset($_POST['cover_fees'])) {
						$fee = $_POST['cover_fees'];
						$fee += isset($_POST['checkout_donation'])?$_POST['checkout_donation']:0;
						$fee = $fee*100;
						$params['subscription_data']["application_fee_percent"] = $fee;
					} else {
						$params['subscription_data']["application_fee_percent"] = $fee;
					}
				}
				//Add Connect ID for Webhook Handler
				$params['success_url'] .= '&connect_id='.$md_sc_creds->stripe_user_id;
			}
			//Else default arguments for checkout session
			try {
				//Params change in in new version
				$params['line_items'] = [[
					'price_data' => [
						'currency' => $params['line_items'][0]['currency'],
						'unit_amount' => $params['line_items'][0]['amount'],
						'product_data' => [
							'name' => $params['line_items'][0]['name'],
							//'description' => '',
							//'images' => [$params['line_items'][0]['images'][0]],
						],
					],
					'quantity' => 1
				]];
				//echo '<pre>'; print_r($params); exit;
				if(isset($params['line_items'][0]['images'][0]) && $params['line_items'][0]['images'][0]!='') {
					$params['line_items'][0]['price_data']['product_data']['images'] = [$params['line_items'][0]['images'][0]];
				}

				//Create session with connected account
				if(isset($sc_accesstoken)) {
					$checkout_session = \Stripe\Checkout\Session::create($params, [ 'stripe_account' => $md_sc_creds->stripe_user_id ] );
					//Add Connect ID for JS Init
					$checkout_session['connected_account'] = $md_sc_creds->stripe_user_id;
				} else { //Not a connected account
					$checkout_session = \Stripe\Checkout\Session::create($params);
				}
			} catch(Stripe_CardError $e) {
				$error = $e->getMessage().' '.__LINE__;
			} catch (Stripe_InvalidRequestError $e) {
				// Invalid parameters were supplied to Stripe's API
				$error = $e->getMessage().' '.__LINE__;
			} catch (Stripe_AuthenticationError $e) {
				// Authentication with Stripe's API failed
				$error = $e->getMessage().' '.__LINE__;
			} catch (Stripe_ApiConnectionError $e) {
				// Network communication with Stripe failed
				$error = $e->getMessage().' '.__LINE__;
			} catch (Stripe_Error $e) {
				// Display a very generic error to the user, and maybe send
				// yourself an email
				$error = $e->getMessage().' '.__LINE__;
			} catch (Exception $e) {
				// Something else happened, completely unrelated to Stripe
				$error = $e->getMessage().' '.__LINE__;
			}
			if(isset($error) && $error!='') {
				print_r(json_encode(array('response' => 'failure', 'message' => $error)));
			} else {
				print_r(json_encode(array('response' => 'success', 'message' => $checkout_session)));
			}
		}

		exit;
	}

	function prepare_callback($url) {
		return $url;
	}

	function filter_currency_code($currency_code, $global_currency, $source) {
		return $this->stripe_default_currency();
	}

	function webhook_handler() {
		if (isset($_GET['memberdeck_notify']) && $_GET['memberdeck_notify'] == 'stripe-checkout') {
		
			update_option('stripe_get_test', $_GET);
			update_option('stripe_post_test', $_POST);

			$source = 'stripe-checkout';
			$txn_type = '';
			$vars = array();
			$checkout_session = false;

			$payment_complete = false;
			$status = null;

			foreach($_POST as $key=>$val) {
	            $vars[$key] = sanitize_text_field($val);
	        }
	        foreach($_GET as $k=>$v) {
	        	if ($k == 'email') {	
        			$vars[$k] = sanitize_email($v);
        		}
        		else {
        			$vars[$k] = sanitize_text_field($v);
        		}
			}
			

	        if (empty($vars)) {
	        	return;
	        }

	        if (!isset($vars['success'])) {
	        	return;
			}
			$original_price = $vars['original_price'];
			if(isset($vars['checkout_donation'])) {
				$original_price += $vars['checkout_donation'];
			}
			if(isset($vars['cover_fees'])) {
				$original_price += $vars['cover_fees'];
			}
			$gateway_settings = get_option('memberdeck_gateways');
			$sc_settings = self::get_settings();
			
			//load sc function
			require (dirname(__FILE__) . '/../../../' . 'idcommerce-sc.php');

			//load stripe library
			require (dirname(__FILE__) . '/' . 'lib/stripe-php-7.67.0/init.php');

			//retrive session info
			\Stripe\Stripe::setApiKey($sc_settings['stripe_secret_key']);
			//$stripe = new \Stripe\StripeClient($sc_settings['stripe_secret_key']);
			if(isset($vars['session_id'])) {
				try {
					if(isset($vars['connect_id']) && $vars['connect_id']!='') {
						$checkout_session = \Stripe\Checkout\Session::retrieve($vars['session_id'],['stripe_account' => $vars['connect_id']]);
					} else {
						$checkout_session = \Stripe\Checkout\Session::retrieve($vars['session_id']);
					}
				} catch (Exception $e) {
					$error = $e->getMessage().' '.__LINE__;
					die($error);
				}
				update_option('stripe_checkout_session', $checkout_session);
				switch ($checkout_session->payment_status) {
					case 'paid':
						$payment_complete = true;
						break;
					case 'no_payment_required':
						$payment_complete = true;
						break;
					default:
						return;
						break;
				}
			} else {
				//If Connected Account
				if(isset($vars['connect_id']) && $vars['connect_id']!='') {
					$payment_intent = \Stripe\PaymentIntent::retrieve($vars['intent_id'],['stripe_account' => $vars['connect_id']]);
				} else {
					$payment_intent = \Stripe\PaymentIntent::retrieve($vars['intent_id']);		
				}
				update_option('stripe_payment_intent', $payment_intent);
				switch ($payment_intent->status) {
					case 'requires_capture':
						$payment_complete = true;
						break;
					default:
						return;
						break;
				}
			}
			

			if (!$payment_complete) {
	        	return;
	        }

			//get variables from return url
			$product_id = $vars['mdid_checkout'];
			$level_data = ID_Member_Level::get_level($product_id);
			$fields = $_GET;
        	$guest_checkout = (isset($vars['guest_checkout']) ? $vars['guest_checkout'] : 0);
            $fname = $vars['first_name'];
            $lname = $vars['last_name'];

            if($checkout_session) {
				//if payment intent update description on stripe dashboard
				if(in_array($checkout_session->mode, array('payment'))){//, 'subscription') ) ) {
					$args = array('description' => $vars['first_name'].' '.$vars['last_name']. ' - '.$level_data->level_name);
					if(isset($vars['connect_id']) && $vars['connect_id']!='') {
						$payment = \Stripe\PaymentIntent::update($checkout_session->payment_intent,$args,['stripe_account' => $vars['connect_id']]);
					} else {
						$payment = \Stripe\PaymentIntent::update($checkout_session->payment_intent,$args);
					}
				}
				$price = $checkout_session->amount_total>0?$checkout_session->amount_total/100:$level_data->level_price;
				if(isset($payment->latest_charge)) {
					$txn_id = $payment->latest_charge;
				} else {
					$txn_id = isset($payment->charges->data[0]->balance_transaction)?$payment->charges->data[0]->balance_transaction:$checkout_session->subscription;
				}
			} else {
				$price = $payment_intent->amount>0?$payment_intent->amount/100:$level_data->level_price;
				$txn_id = 'pre';
			}
            $email = $vars['email'];
			
			$e_date = idc_set_order_edate($level_data);

			//set txn id
			if(isset($payment_intent)){//, 'preauth') ) ) {
				$txn_id = 'pre';
				$txn_type = 'preauth';
				$preauth = true;
			} else {//If Not PreAuth
				$txn_check = ID_Member_Order::check_order_exists($txn_id);            
				if (!empty($txn_check)) {
					return;
				}
			}

			$recurring_type = $level_data->recurring_type;
			if ($level_data->level_type == 'recurring') {
				$recurring = true;
				if ($level_data->limit_term) {
					$term_length = $level_data->term_length;
				}
			} elseif ($level_data->level_type == 'lifetime') {
				$recurring = true;
			} else {
				$recurring = false;
			}

			//Update Description in Recurring
			if($checkout_session) {
				if($checkout_session->subscription!=null || $checkout_session->subscription!='') {
					//If from Connected Account
					if(isset($vars['connect_id']) && $vars['connect_id']!='') {
						$subscription = \Stripe\Subscription::retrieve($checkout_session->subscription,['stripe_account' => $vars['connect_id']]);
						$invoice = \Stripe\Invoice::retrieve($subscription->latest_invoice,['stripe_account' => $vars['connect_id']]);

						$args = array('description' => $vars['first_name'].' '.$vars['last_name']. ' - '.$level_data->level_name);
						$payment = \Stripe\PaymentIntent::update($invoice->payment_intent,$args,['stripe_account' => $vars['connect_id']]);
					} else {
						$subscription = \Stripe\Subscription::retrieve($checkout_session->subscription);
						$invoice = \Stripe\Invoice::retrieve($subscription->latest_invoice);

						$args = array('description' => $vars['first_name'].' '.$vars['last_name']. ' - '.$level_data->level_name);
						$payment = \Stripe\PaymentIntent::update($invoice->payment_intent,$args);
					}
				}
			} else {
				//If from Connected Account
				if(isset($vars['connect_id']) && $vars['connect_id']!='') {
					$payment = \Stripe\PaymentIntent::update($payment_intent->id,
						['description' => $vars['first_name'].' '.$vars['last_name']. ' - '.$level_data->level_name.'-Uncaptured'],
						['stripe_account' => $vars['connect_id']]
					);
				} else {
					$payment = \Stripe\PaymentIntent::update($payment_intent->id,
						['description' => $vars['first_name'].' '.$vars['last_name']. ' - '.$level_data->level_name.'-Uncaptured']
					);
				}
			}

			$customer = array(
	   			'product_id' => $product_id,
	   			'first_name' => $fname,
	   			'last_name' => $lname,
	   			'email' => $email
			   );
			   
			$paykey = md5($email.time());

			$new_data = array('checksum' => $vars['session_id']);

			$access_levels = array(absint($product_id));
			$member = new ID_Member();
			$check_user = $member->check_user($email);

			if (!empty($check_user)) { //user already exists
				$user_id = $check_user->ID;
	        	$match_user = $member->match_user($user_id);
	        	if (empty($match_user)) { //first purchase
	        		$data = $new_data;
	        		$user = array('user_id' => $user_id, 'level' => $access_levels, 'data' => $data);
					$new = ID_Member::add_user($user);

					if ( !$recurring ) {
						$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, null, 'active', $e_date, $original_price);
					} else {
						$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, $txn_id, 'active', $e_date, $original_price);
					}
					$new_order = $order->add_order();
				} else {
					//merge access of products
	        		if (isset($match_user->access_level)) {
	        			$levels = maybe_unserialize($match_user->access_level);
	        			if (!empty($levels)) {
	            			foreach ($levels as $lvl) {
								$access_levels[] = absint($lvl);
							}
						}
					}

					if (isset($match_user->data)) {
						$data = unserialize($match_user->data);
						if (!is_array($data)) {
							$data = array($data);
						}
						$data[] = $new_data;
					} else {
						$data = $new_data;
					}

					$user = array('user_id' => $user_id, 'level' => $access_levels, 'data' => $data);
					$new = ID_Member::update_user($user);

					if ( !$recurring ) {
						$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, null, 'active', $e_date, $original_price);
					} else {
						$order = new ID_Member_Order(null, $user_id, $product_id, null, $txn_id, $txn_id, 'active', $e_date, $original_price);
					}
					$new_order = $order->add_order();
				}
			} else { //user not exists
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
				if( !$recurring ) {
					$order = new ID_Member_Order(null, (isset($user_id) ? $user_id : null), $product_id, null, $txn_id, null, 'active', $e_date, $original_price);
				} else {
					$order = new ID_Member_Order(null, (isset($user_id) ? $user_id : null), $product_id, null, $txn_id, $txn_id, 'active', $e_date, $original_price);
				}
				$new_order = $order->add_order();
				if ($guest_checkout) {
					//fwrite($log, 'order added: '.$new_order."\n");
					do_action('idc_guest_checkout_order', $new_order, $customer);
				}
				else {
					do_action('idmember_registration_email', $user_id, $reg_key, $new_order);
				}
			}
			// Add fees data to order meta
			if(isset($vars['checkout_donation'])) {
				idc_update_order_meta($new_order, 'checkout_donation', $vars['checkout_donation']);
			}
			if(isset($vars['cover_fees'])) {
				idc_update_order_meta($new_order, 'cover_fees', $vars['cover_fees']);
			}
			if (isset($preauth) && $preauth == true) {
				$custid = $payment_intent->id;

				// echo 'sending a preorder'."\n";
				if (isset($use_token) && $use_token == true) {
					$charge_token = apply_filters('idc_card_id_checkout', ((isset($card_id)) ? $card_id : ''), $use_token, $preauth = true, $source);
				} else {
					$charge_token = $custid;
				}

				
				$charge_token = apply_filters('idc_preorder_charge_token', $charge_token, $txn_id, array(
					"txn_type" => 'preauth',
					"custid" => $custid,
					"email" => $email,
					"card_id" => (isset($card_id) ? $card_id : ''),
					"amount" => $level_data->level_price,
					"settings" => $gateway_settings,
					"source" => $source,
					"extra_fields" => $fields
				));

				$preorder_entry = ID_Member_Order::add_preorder($new_order, $charge_token, $source);
				do_action('memberdeck_preauth_success', $user_id, $new_order, $paykey, $fields, $source);
				do_action('memberdeck_preauth_receipt', $user_id, $level_data->level_price, $product_id, $source, $new_order);
			} else {
				do_action('memberdeck_payment_success', (isset($user_id) ? $user_id : $user_id), $new_order, (isset($reg_key) ? $reg_key : null), $fields, $source);
			}
            if ($recurring) {
           		do_action('memberdeck_recurring_success', 'stripe-checkout', $user_id, $new_order, (isset($term_length) ? $term_length : null));
           		$order->activate_status();
			}
			do_action('idmember_receipt', (isset($user_id) ? $user_id : ''), $original_price, $product_id, $source, $new_order, $fields);
			header('Location:'.home_url('dashboard/?idc_orders=1' . ($recurring?'&sr_checkout':'') . '&view_receipt='.$new_order));   
		}
	}

	function backer_list_order_price($price, $order_id) {
		$deduction = 0;
		if(is_single()) {
			$donation = $fee = 0;
			if($donation = idc_get_order_meta($order_id, 'checkout_donation', true)) {
				$deduction += $donation;
			}
			if($fee = idc_get_order_meta($order_id, 'cover_fees', true)) {
				$deduction += $fee;
			}
		}
		if($deduction>0) {
			return substr($price, 1)>0 ? substr($price, 0, 1).(substr($price, 1) - $deduction) : substr($price, 0, 1).'0.00';
		} else {
			return $price;
		}
	}

	function add_idcf_order($user_id, $order_id, $reg_key, $fields, $source) {
		if ($source !== 'stripe-checkout') {
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

	function get_payment_intent() {
		
		$sc_settings = self::get_settings();
		//load stripe library
		require (dirname(__FILE__) . '/' . 'lib/stripe-php-7.67.0/init.php');
		\Stripe\Stripe::setApiKey($sc_settings['stripe_secret_key']);
		//If from Connect Stripe
		if(isset($_POST['connect_id']) && $_POST['connect_id']!='') {
			$payment_intent = \Stripe\PaymentIntent::retrieve($_POST['intent_id'],['stripe_account' => $_POST['connect_id']]);
		} else {
			$payment_intent = \Stripe\PaymentIntent::retrieve($_POST['intent_id']);
		}
		if($payment_intent->status=='requires_capture') {
			$redirect = $payment_intent->metadata->success_url;
			$redirect = str_replace('&session_id={CHECKOUT_SESSION_ID}','',$redirect);
			$redirect .= '&intent_id='.$payment_intent->id;
			if(isset($_POST['connect_id']) && $_POST['connect_id']!='') {
				$redirect .= '&connect_id='.$_POST['connect_id'];
			}
		} else {
			$redirect = $payment_intent->metadata->cancel_url;
		}
		print_r(json_encode(array('response' => 'success', 'message' => $redirect)));
		exit;
	}


	function stripe_checkout_preauth_processing($level_id) {
		//load stripe library
		$sc_settings = self::get_settings();
		//load sc function
		require (dirname(__FILE__) . '/../../../' . 'idcommerce-sc.php');
		//load library
		require (dirname(__FILE__) . '/' . 'lib/stripe-php-7.67.0/init.php');
		//Initialize Stripe Secret Key
		$stripe = new \Stripe\StripeClient($sc_settings['stripe_secret_key']);
		
		$preorders = ID_Member_Order::get_md_preorders($level_id);
		$success = array();
		$fail = array();
		$response = array();
		if (!empty($preorders)) {
			$level = ID_Member_Level::get_level($level_id);
			$price = $level->level_price;
		}
		//Check Stripe Connect
		$check_claim = get_option('md_level_'.$level_id.'_owner');
		if (!empty($check_claim)) {
			$md_sc_creds = get_sc_params($check_claim);
			if (!empty($md_sc_creds)) {
				$sc_accesstoken = $md_sc_creds->access_token;
			}
		}
		
		foreach ($preorders as $capture) {
			$user_id = $capture->user_id;
			if($user_id) {
				$level_id = $capture->level_id;
				$userdata = get_userdata($user_id);
				$fname = $userdata->user_firstname;
				$lname = $userdata->user_lastname;
				$email = (isset($userdata->user_email) ? $userdata->user_email : '');
				$pre_info = ID_Member_Order::get_preorder_by_orderid($capture->id);
				if (empty($pre_info)) {
					// no pre-order data
					continue;
				}
				$order_id = $pre_info->order_id;
				$order = new ID_Member_Order($order_id);
				$the_order = $order->get_order();
				if (empty($the_order)) {
					// need order to get order data
					continue;
				}
				$order->activate_status();
				$gateway = $pre_info->gateway;
				if (empty($gateway) || $gateway == 'stripe-checkout') {
					$customer_id = ID_Member::get_customer_id($user_id);
				}
				
				if (!empty($gateway) && $gateway == 'stripe-checkout') {
					if (empty($pre_info->charge_token)) {
						// need this to process the transaction
						continue;
					}
					if(strpos($pre_info->charge_token,'seti_') > -1) {
						continue;
					}
					$txn_id = 'pre';
					$priceincents = str_replace(',', '', $price) * 100;

					try {
						$params['description'] = $fname.' '.$lname. ' - '.$level->level_name;
						if($sc_accesstoken) {
							$payment = $stripe->paymentIntents->update($pre_info->charge_token, $params, array('stripe_account' => $md_sc_creds->stripe_user_id));
							$intent = $stripe->paymentIntents->capture(
								$pre_info->charge_token, [], array('stripe_account' => $md_sc_creds->stripe_user_id)
							);	
						} else {
							$payment = $stripe->paymentIntents->update($pre_info->charge_token, $params);
							$intent = $stripe->paymentIntents->capture(
								$pre_info->charge_token
							);
						}
						
						$paid = 1;
						$refunded = 0;
						$txn_id = $intent->charges->data[0]->balance_transaction;
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

					$preauth_data = (object) array(
						'paid' => $paid,
						'refunded' => $refunded,
						'txn_id' => $txn_id,
						'error' => __('Transaction could not be captured', 'memberdeck').': '.__LINE__,
					);
		
					add_filter('idc_preauth_data_'.$pre_info->charge_token, function() use ($preauth_data) {
						return $preauth_data;
					});
				}
			}
		}
	}

	//Add Menu in User Dashboard if SC Enable
	function stripe_connect_add_menu() {
		global $permalink_structure;
		if (empty($permalink_structure)) {
			$prefix = '&';
		} else {
			$prefix = '?';
		}
		$sc_settings = self::get_settings();
		$sce = isset($sc_settings['stripe_connect_enable']) && $sc_settings['stripe_connect_enable'] == 1?1:0;
		if ($sce && current_user_can('create_edit_projects')) {
			echo '<li class="dashtab creator_settings '.(isset($_GET['payment_settings']) ? ' active' : '').'"><a href="'.md_get_durl().$prefix.'payment_settings=1">'.__('Creator Account', 'memberdeck').'</a></li>';
		}
	}

	function sc_button_style() {
		$sc_settings = self::get_settings();
		$sce = isset($sc_settings['stripe_connect_enable']) && $sc_settings['stripe_connect_enable'] == 1?1:0;
		if ($sce && isset($_GET['payment_settings']) && $_GET['payment_settings']) {
			wp_register_style('sc_buttons', plugins_url('../../../lib/connect-buttons-min.css', __FILE__));
			wp_enqueue_style( 'sc_buttons' );
		}
	}

	function md_check_show_stripe_connect() {
		if (isset($_GET['payment_settings']) && $_GET['payment_settings']) {
			add_filter('the_content', 'md_ide_payment_settings');
			$sc_settings = self::get_settings();
			$sce = isset($sc_settings['stripe_connect_enable']) && $sc_settings['stripe_connect_enable'] == 1?1:0;
			if ($sce) {
				add_action('md_payment_settings_extrafields', array($this, 'md_stripe_connect_signup') );
			}
		}
	}

	function md_stripe_connect_signup() {
		if (is_user_logged_in()) {
			include_once(dirname(__FILE__) . '/../../../' . 'idcommerce-sc.php');
			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;
			$check_creds = md_sc_creds($user_id);
			$sc_settings = self::get_settings();
			if (!empty($sc_settings)) {
				$sc_settings = maybe_unserialize($sc_settings);
				if (is_array($sc_settings)) {
					$client_id = $sc_settings['client_id'];
					$dev_client_id = $sc_settings['dev_client_id'];
					$dev_mode = $sc_settings['dev_mode'];
					$button_style = $sc_settings['button-style'];
					if ($dev_mode == 1) {
						$client_id = $dev_client_id;
					}
					$base_url = apply_filters('md_sc_url', 'https://connect.stripe.com/oauth/authorize');
					$params = apply_filters('md_sc_url_redirect_params', array(
						'response_type' => 'code',
						'client_id' => $client_id,
						'scope' => 'read_write',
						'state' => $user_id,
					));
					$query_params = '?'.urldecode(http_build_query($params));
					$redirect_url = $base_url.$query_params;
					include 'templates/_scSignup.php';
					if (empty($client_id)) {
						$message = __('No client id set', 'memberdeck');
					} else {
						$message = null;
					}
				}
			}
		}
	}
	
	//For User Access Revoke In Stripe Connect Settings
	function idsc_revoke_creds() {
		if (isset($_POST['user_id'])) {
			include_once(dirname(__FILE__) . '/../../../' . 'idcommerce-sc.php');
			$user_id = absint($_POST['user_id']);
			$delete = delete_sc_params($user_id);
		}
		exit;
	}

	function idsc_get_users() {
		include_once(dirname(__FILE__) . '/../../../' . 'idcommerce-sc.php');

		$users = md_sc_users();
		if (!empty($users)) {
			$new_users = array();
			foreach ($users as $user) {
				$user_id = $user->user_id;
				$wp_user = get_user_by('id', $user_id);
				if (!empty($wp_user)) {
					$email = $wp_user->user_email;
					$fname = $wp_user->user_firstname;
					$lname = $wp_user->user_lastname;
					$user->display = $fname.' '.$lname.', '.$email;
				}
				$new_users[] = $user;
			}
		}
		if (isset($new_users)) {
			$users = $new_users;
		}
		print_r(json_encode($users));
		exit;
	}

	function md_stripe_connect_return_handler() {
		if (isset($_GET['ipn_handler']) && $_GET['ipn_handler'] == 'sc_return') {
			require_once(dirname(__FILE__) . '/../../../' . 'idcommerce-sc.php');
			if (isset($_GET['error'])) {
				$error = $_GET['error'];
			}
			if (isset($_GET['code'])) {
				$code = $_GET['code'];
			}
			if (isset($_GET['state'])) {
				$state = $_GET['state'];
			}
			else {
				$current_user = wp_get_current_user();
				$user_id = $current_user->ID;
				$state = $user_id;
			}
			if (isset($code) && isset($state)) {
				if (is_user_logged_in()) {
					if (is_multisite()) {
						require (ABSPATH . WPINC . '/pluggable.php');
					}
					$current_user = wp_get_current_user();
					$user_id = $current_user->ID;
					$check_creds = md_sc_creds($user_id);
					if (empty($check_creds)) {
						$url = 'https://connect.stripe.com/oauth/token?code='.$code.'&grant_type=authorization_code';
						$ch = curl_init($url);
						$sc_settings = self::get_settings();
						if (!empty($sc_settings)) {
							if (is_array($sc_settings)) {
								$key = $sc_settings['stripe_secret_key'];
								if (!empty($key)) {
									$params = array('client_secret' => $key);
									curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
									curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
									$json = curl_exec($ch);
									curl_close($ch);
									if (isset($json)) {
										$response = json_decode($json);
										if (isset($response->error_description)) {
											$message = $response->error_description;
										}
										else {
											$access_token = $response->access_token;
											$refresh_token = $response->refresh_token;
											$stripe_publishable_key = $response->stripe_publishable_key;
											$stripe_user_id = $response->stripe_user_id;
											$params = array('access_token' => $access_token,
												'refresh_token' => $refresh_token,
												'stripe_publishable_key' => $stripe_publishable_key,
												'stripe_user_id' => $stripe_user_id);
											$user_id = $_GET['state'];
											$insert_id = save_sc_params($user_id, $params);
											if ($insert_id > 0) {
												$message = 'Success';
												do_action('idc_stripe_connect_success', $insert_id, $user_id);
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}

}
new ID_Stripecheckout();
?>