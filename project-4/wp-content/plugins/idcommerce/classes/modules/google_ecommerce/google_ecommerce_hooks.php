<?php
function google_tag_scripts() {
	$property_code = get_transient('idc_ga_property_code');
	?>
	<!-- Google tag (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $property_code;?>"></script>
	<script>
	window.dataLayer = window.dataLayer || [];
	<?php
	if( is_archive('ignition_product') ) { // View Item List
		$projects = array();
		while(have_posts()) {
			the_post();
			$id = get_the_ID();
			$summary = the_project_summary($id);
			$projects[] = array(
				'item_name'	=>	$summary->name,
				'item_id'	=>	$id,
				'price'	=>	$summary->goal,
				'quantity'	=>	'1',
			);
		}
		?>
		window.dataLayer.push({
			event: 'view_item_list',
			ecommerce: {
				items: <?php echo json_encode($projects, JSON_PRETTY_PRINT)?>
			}
		});
		<?php
	}

	if ( isset($_GET['purchaseform']) ) { // Select Item
		global $post;
		$id = $post->ID;
		$summary = the_project_summary($id);
		$projects[] = array(
			'item_name'	=>	$summary->name,
			'item_id'	=>	$id,
			'price'	=>	$summary->goal,
			'quantity'	=>	'1',
		);
		?>
		window.dataLayer.push({
			event: 'select_item',
			ecommerce: {
				items: <?php echo json_encode($projects, JSON_PRETTY_PRINT)?>
			}
		});
		<?php
	} elseif ( is_single() && 'ignition_product' == get_post_type() ) { // View Item
		global $post;
		$id = $post->ID;
		$summary = the_project_summary($id);
		$projects[] = array(
			'item_name'	=>	$summary->name,
			'item_id'	=>	$id,
			'price'	=>	$summary->goal,
			'quantity'	=>	'1',
		);
		?>
		window.dataLayer.push({
			event: 'view_item',
			ecommerce: {
				items: <?php echo json_encode($projects, JSON_PRETTY_PRINT)?>
			}
		});
		<?php
	}

	if ( isset($_GET['view_receipt']) ) { // Purchase 
		global $global_currency;
		$order_id = $_GET['view_receipt'];
		$order = new ID_Member_Order($order_id);
		$last_order = $order->get_order();
		$price = apply_filters('idc_order_price', $last_order->price, $last_order->id);
		$price = preg_replace('/[^0-9]/', '', $price);
		$price = $price/100;
		$levels = ID_Member_Level::get_levels();
		$i = 0;
		foreach ($levels as $level) {
			$level_id = $level->id;
			if ($last_order->level_id == $level_id) {
				$order_level_key = $i;
				break;
			}
			$i++;
		}
		?>
		window.dataLayer.push({
			event: 'purchase',
			ecommerce: {
				currency: '<?php echo $global_currency;?>',
				value: <?php echo $price;?>,
				transaction_id: '<?php echo $last_order->id;?>',
				items: [{
					item_name: '<?php echo apply_filters('idc_order_level_title', (isset($order_level_key) ? $levels[$order_level_key]->level_name : $level->level_name), $last_order); ?>',
					item_id: '<?php echo $level_id;?>',
					price: '<?php echo $price;?>',
					quantity: '1',
				}]
			}
		});
		<?php
	}
	?>

	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());

	gtag('config', '<?php echo $property_code;?>');
	</script>
	<?php
}

function google_ecommerce_scripts() {
	$property_code = get_transient('idc_ga_property_code');
	wp_register_script('google_ecommerce-script', plugins_url('js/google_ecommerce-min.js', __FILE__));
	wp_enqueue_script('jquery');
	wp_enqueue_script('google_ecommerce-script');
	wp_localize_script('google_ecommerce-script', 'idc_ga_property_code', $property_code);
}

function google_ecommerce_admin() {
	add_submenu_page('idf', __('Google Ecommerce', 'memberdeck'), __('Google Ecommerce', 'memberdeck'), 'manage_options', 'idc-google-ecommerce', 'google_ecommerce_menu');
}

function google_ecommerce_menu() {
	$property_code = get_transient('idc_ga_property_code');
	if (isset($_POST['idc_ga_property_code'])) {
		$property_code = sanitize_text_field($_POST['idc_ga_property_code']);
		set_transient('idc_ga_property_code', $property_code, 0);
	}
	include_once(dirname(__FILE__) . '/templates/admin/_googleEcommerceMenu.php');
}

function google_ecommerce_pay_triggers($user_id, $order_id, $paykey = '', $fields = 'fields', $source = '') {
	// send via Google Meaturement Protocol
	$source_list = array('paypal', 'pp-adaptive', 'coinbase');
	if (in_array($source, $source_list)) {
		$order = new ID_Member_Order($order_id);
		$order_details = $order->get_order();
		if (!empty($order_details)) {
			$level = ID_Member_Level::get_level($order_details->level_id);
			if (!empty($level)) {
				$user = get_user_by('id', $user_id);
				$args = array(
					'uniqid' => uniqid('idc_ga_'),
					'order_id' => $order_id,
					'user_id' => $user_id,
					'level' => $level,
					'user' => $user
				);
				wp_schedule_single_event(time(), 'google_ecommerce_trigger', array($args['uniqid'], $args['order_id'], $args['user_id'], $args['level'], $args['user']));
			}
		}
	}
}

function google_ecommerce_free_triggers($user_id, $order_id) {
	//do_action('memberdeck_free_success', $user_id, $new_order);
	if (empty($order_id)) {
		return;
	}
	$order = new ID_Member_Order($order_id);
	$order_details = $order->get_order();
	if (empty($order_details)) {
		return;
	}
	$level = ID_Member_Level::get_level($order_details->level_id);
	if (empty($level)) {
		return;
	}
	$user = get_user_by('id', $user_id);
	$args = array(
		'uniqid' => uniqid('idc_ga_'),
		'order_id' => $order_id,
		'user_id' => $user_id,
		'level' => $level,
		'user' => $user
	);
	wp_schedule_single_event(time(), 'google_ecommerce_trigger', array($args['uniqid'], $args['order_id'], $args['user_id'], $args['level'], $args['user']));
}

add_action('google_ecommerce_trigger', 'google_ecommerce_trigger', 10, 5);

function google_ecommerce_trigger($uniqid, $order_id, $user_id, $level, $user) {
	if (empty($order_id)) {
		return;
	}
	$order = new ID_Member_Order($order_id);
	$the_order = $order->get_order();
	if (empty($the_order)) {
		return;
	}
	$transaction_args = array(
		'method' => 'POST',
		'timeout' => 60,
		'httpversion' => '1.0',
		'sslverify' => false,
		'body' => array(
			'v' => 1,
			'tid' => get_transient('idc_ga_property_code'),
			'cid' => $uniqid,
			't' => 'transaction',
			'ti' => $the_order->transaction_id,
			'tr' => $the_order->price
		),
	);
	$item_args = array(
		'method' => 'POST',
		'timeout' => 60,
		'httpversion' => '1.0',
		'sslverify' => false,
		'body' => array(
			'v' => 1,
			'tid' => get_transient('idc_ga_property_code'),
			'cid' => $uniqid,
			't' => 'item',
			'ti' => $the_order->transaction_id,
			'in' => $level->level_name,
			'ip' => $the_order->price,
			'iq' => '1'
		),
	);
	$transaction_post = wp_remote_post('https://www.google-analytics.com/collect', $transaction_args);
	$item_post = wp_remote_post('https://www.google-analytics.com/collect', $item_args);
}

function google_ecommerce_order_data() {
	if (isset($_POST['Order'])) {
		$order_id = absint($_POST['Order']);
		if ($order_id > 0) {
			$order = new ID_Member_Order($order_id);
			$the_order = $order->get_order();
			if (!empty($the_order)) {
				$level_id = $the_order->level_id;
				$level = ID_Member_Level::get_level($level_id);
			}
		}
	}
	if (isset($_POST['User'])) {
		$user_id = absint($_POST['User']);
		if ($user_id > 0) {
			$user = get_user_by('id', $user_id);
		}
	}
	$data = array(
		'order' => (isset($the_order) ? $the_order : null),
		'user' => (isset($user) ? $user : null),
		'level' => (isset($level) ? $level : null),
	);
	print_r(json_encode($data));
	exit;
}

function _idf_is_id_theme() {
	$theme_info = wp_get_theme();
	$theme_author = strtolower($theme_info->get('Author'));
	if ($theme_author == 'ignitiondeck' || $theme_author == 'virtuousgiant') {
		return true;
	}
	if (get_template_directory() !== get_stylesheet_directory()) {
		$parent_array = array(
			'fivehundred',
			'fundify',
			'crowdpress'
		);
		if (in_array($theme_info->template, $parent_array)) {
			return true;
		}
	}
	return false;
}

add_action('after_setup_theme', 'add_project_summary_function');
function add_project_summary_function() {
	if ( !_idf_is_id_theme() || !function_exists('the_project_summary') ) {
		function the_project_summary($id) {
			$post = get_post($id);
			$project_id = get_post_meta($id, 'ign_project_id', true);
			$project = new ID_Project($project_id);
			$image_url = $project->get_project_thumbnail($id);
			$name = $post->post_title;
			$short_desc = html_entity_decode(get_post_meta($id, 'ign_project_description', true));
			$total = $project->get_project_raised();
			$goal = apply_filters('id_project_goal', $project->the_goal(), $id);
			$end = $project->end_date($id);
			$end_type = get_post_meta($id, 'ign_end_type', true);
			$days_left = $project->days_left();
			$pledgers = apply_filters('id_number_pledges', $project->get_project_orders(), $id);
			// ID Function
			// GETTING product default settings
			$default_prod_settings = getProductDefaultSettings();

			// Getting product settings and if they are not present, set the default settings as product settings
			$prod_settings = getProductSettings($project_id);
			if (empty($prod_settings)) {
				$prod_settings = $default_prod_settings;
			}
			$currency_code = $prod_settings->currency_code;
			//GETTING the currency symbols
			$cCode = setCurrencyCode($currency_code);

			if ($end !== '') {
				$show_dates = true;
			}
			else {
				$show_dates = false;
			}
			
			// percentage bar
			$percentage = apply_filters('id_percentage_raised', $project->percent(), $project->get_project_raised(true), $id, apply_filters('id_project_goal', $project->the_goal(), $id, true));
			$successful = get_post_meta($id, 'ign_project_success', true);
			
			$summary =  new stdClass;
			$summary->end_type = $end_type;
			$summary->image_url = $image_url;
			$summary->name = apply_filters('the_title', $name, $id);
			$summary->short_description = $short_desc;
			$summary->total = $total;
			$summary->goal = $goal;
			$summary->pledgers = $pledgers;
			$summary->show_dates = $show_dates;
			if ($show_dates == true) {
				$summary->days_left = $days_left;
			}
			$summary->percentage = $percentage;
			$summary->successful = $successful;
			$summary->currency_code = $cCode;
			return $summary;
		}
	}
}
?>