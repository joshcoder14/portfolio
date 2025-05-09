<?php

//error_reporting(E_ALL);
//@ini_set('display_errors', 1);

// Auto-Updates for Theme 500
/**************************************************/
$api_url = 'https://ignitiondeck.com/id/pluginserv/';


$theme_data = wp_get_theme(get_option('template'));
$theme_version = $theme_data->Version;  
$theme_base = get_option('template');
/**************************************************/
add_filter('pre_set_site_transient_update_themes', 'check_fivehundred_update');

function check_fivehundred_update($checked_data) {
	global $wp_version, $theme_version, $theme_base, $api_url;

	$request = array(
		'slug' => $theme_base,
		'version' => $theme_version 
	);

	// Start checking for an update
	$send_for_check = array(
		'body' => array(
			'action' => 'theme_update', 
			'request' => serialize($request),
			'api-key' => md5(home_url())
		),
		'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url()
	);

	$raw_response = wp_remote_post($api_url, $send_for_check);

	if (!is_wp_error($raw_response) && ($raw_response['response']['code'] == 200))
		$response = unserialize($raw_response['body']);

	// Feed the update data into WP updater
	if (!empty($response)) 
		$checked_data->response[$theme_base] = $response;

	return $checked_data;
}

// Take over the Theme info screen on WP multisite
add_filter('themes_api', 'fivehundred_api_call', 10, 3);

function fivehundred_api_call($def, $action, $args) {
	global $theme_base, $api_url, $theme_version, $api_url;
	
	if ($args->slug != $theme_base)
		return false;
	
	// Get the current version

	$args->version = $theme_version;
	$request_string = wp_parse_args($action, $args);
	$request = wp_remote_post($api_url, $request_string);

	if (is_wp_error($request)) {
		$res = new WP_Error('themes_api_failed', __('An Unexpected HTTP Error occurred during the API request.</p> <p><a href="?" onclick="document.location.reload(); return false;">Try again</a>', 'fivehundred'), $request->get_error_message());
	} else {
		$res = unserialize($request['body']);
		if ($res === false)
			$res = new WP_Error('themes_api_failed', __('An unknown error occurred'), $request['body'], 'fivehundred');
	}
	
	return $res;
}

if (is_admin())
	$current = get_transient('update_themes');
/**************************************************/

include 'classes/class-video-widget.php';
//include 'classes/class-music-widget.php';
include 'classes/class-content-widget.php';
include 'classes/class-content-level-widget.php';
include 'classes/class-content-wide-widget.php';
include 'classes/class-content-wide-custom-widget.php';
include 'classes/class-content-alert-widget.php';
include 'classes/class-project-loop.php';
include 'inc/theme-functions.php';
include 'classes/class-project-grid-widget.php';

/**
 * Register ignitiondeck domain for translation texts
 */

function fivehundred_init() {
	load_theme_textdomain('fivehundred', get_template_directory().'/languages/');
	fh_settings_menu_actions();
}

add_action('after_setup_theme', 'fivehundred_init');

function fh_settings_menu_actions() {
	$settings_array = array(
		'fh_logo_setting',
		'fh_home_project_layout_setting',
		'fh_home_project_count_setting',
		'fh_featured_project_setting',
		'fh_blog_page_setting',
		'fh_about_setting',
		'fh_custom_css_setting',
		'fh_google_analytics_setting',
		'fh_credits_setting'
	);
	foreach ($settings_array as $setting) {
		add_action($setting, $setting);
	}
}

function fivehundred_dequeue() {
	$disable_skins = false;
	if (isset($_GET['purchaseform'])) {
		remove_filter('the_content', 'wpautop');
		$disable_skins = true;
	}
	else if (isset($_GET['create_project'])) {
		$disable_skins = true;
	}
	else if (isset($_GET['edit_project'])) {
		$disable_skins = true;
	}
	else if (isset($_GEt['action']) && $_GET['action'] == 'register') {
		$disable_skins = true;
	}
	if ($disable_skins) {
		global $theme_base;
		if (isset($theme_base) && $theme_base == 'fivehundred') {
			$theme_name = getThemeFileName();
			if (!empty($theme_name)) {
				wp_dequeue_style($theme_name);
			}
		}
	}
}

add_action('wp_enqueue_scripts', 'fivehundred_dequeue');

function fivehundred_register_admin_menu() {
	add_theme_page('500 Settings', '500 Settings', 'manage_options', 'theme-settings', 'fivehundred_admin_menu');
}

add_action('admin_menu', 'fivehundred_register_admin_menu');

function fivehundred_admin_menu() {
	if (isset($_POST['submit-theme-settings'])) {
		$posts = array();
		foreach ($_POST as $k=>$v) {
			$posts[$k] = esc_attr($v);
		}
		do_action('fh_settings_submit', $posts);
		$logo = esc_attr($_POST['logo-input']);
		if (isset($_POST['about-us'])) {
			$about = stripslashes($_POST['about-us']);
		}
		if (!empty($_POST['choose-home'])) {
			$home = absint($_POST['choose-home']);
		}
		else {
			$home = null;
		}
		$home_projects = absint($_POST['home-projects']);
		if (isset($_POST['blog_page'])) {
			$blog_page = absint($_POST['blog_page']);
		}
		$custom_css = stripslashes($_POST['custom_css']);
		$ga = stripslashes($_POST['ga']);
		
		$settings = array('logo' => $logo,
						'about' => (!empty($about) ? esc_html(html_entity_decode($about)) : ''),
						'home' => $home,
						'home_projects' => $home_projects,
						'blog_page' => (isset($blog_page) ? $blog_page : ''),
						'custom_css' => $custom_css,
						'ga' => $ga);
		update_option('fivehundred_theme_settings', apply_filters('fh_theme_settings', $settings, $posts));
		if (isset($_POST['choose-featured']) && $_POST['choose-featured'] > 0) {
			$project_id = absint($_POST['choose-featured']);
			$project = new ID_Project($project_id);
			$post_id = $project->get_project_postid();
			if (!empty($post_id)) {
				$options = array(
					'post_id' => $post_id,
					'project_id' => $project_id);
				update_option('fivehundred_featured', apply_filters('fh_featured', $options));
			}
		}
		else {
			delete_option('fivehundred_featured');
		}
		echo '<div class="updated fade below-h2" id="message"><p>'.__('Settings Saved', 'fivehundred').'</p></div>';
	}
	else {
		$settings = fh_settings();
		$logo = (!empty($settings['logo']) ? $settings['logo'] : '');
		$about = (!empty($settings['about']) ? html_entity_decode($settings['about']) : '');
		$home_projects =(!empty($settings['home_projects']) ?  $settings['home_projects'] : '');
		$blog_page = (!empty($settings['blog_page']) ? $settings['blog_page'] : '');
		$custom_css = (!empty($settings['custom_css']) ? stripslashes($settings['custom_css']) : '');
		$ga = (!empty($settings['ga']) ? stripslashes($settings['ga']) : '');
		$options = get_option('fivehundred_featured');
		if (!empty($options)) {
			$post_id = $options['post_id'];
			$project_id = $options['project_id'];
		}
	}
	// set up the project home page dropdown
	if (class_exists('ID_Project')) {
		$projects = ID_Project::get_all_projects();
		$levels = '<select id="choose-home" name="choose-home">'.
			apply_filters("home_grid_option", '<option value="">'.__("Grid Layout", "fivehundred").'</option>');
		foreach ($projects as $project) {
			$a_project = new ID_Project($project->id);
			$post_id = $a_project->get_project_postid();
			$selected = null;
			if (isset($_POST['choose-home']) && $_POST['choose-home'] == $project->id) {
				$selected = 'selected="selected"';
			}
			else if ( isset($settings['home']) && $settings['home'] == $project->id) {
				$selected = 'selected="selected"';
			}
			$post = get_post($post_id);
			if (!empty($post)) {
				$levels .= '<option value="'.$project->id.'" '.(isset($selected) ? $selected : '').'>'.__('Single Project', 'fivehundred').': '.stripslashes(get_the_title($post_id)).'</option>';
			}
		}
		$levels .='</select>';
	}
	else {
		$levels = null;
	}
	include 'templates/admin/_themeSettings.php';
}

add_action('fivehundred_extra_fields', 'fivehundred_credits_option', 999);

function fivehundred_credits_option() {
	if (fh_has_ide()) {
		$settings = fh_settings();
		$disable_credits = get_theme_mod('fivehundred_disable_credits', 0);
		if (isset($_POST['submit-theme-settings'])) {
			$disable_credits = isset($_POST['fivehundred_disable_credits']) ? absint($_POST['fivehundred_disable_credits']) : 0;
			set_theme_mod('fivehundred_disable_credits', $disable_credits);
			do_action('fivehundred_disable_credits_set', $disable_credits);
		}
		$content = '';
		$content .= '<tr>';
		$content .= '<td>';
		$content .= '<input type="checkbox" name="fh_show_creator_button" id="fh_show_creator_button" value="1" '.(!empty($settings['fh_show_creator_button']) && $settings['fh_show_creator_button'] ? 'checked="checked"' : '').'/>';
		$content .= '<label for="fh_show_creator_button">'.__('Display Create Project Button on Pages','fivehundred').'</label>';
		$content .= '</td>';
		$content .= '</tr>';
		if (has_action('fh_credits_setting')) {
			$content .= '<tr class="fivehundred_credits_option">';
			$content .= '<td>';
			$content .= '<p>';
			$content .= '<input type="checkbox" id="fivehundred_disable_credits" name="fivehundred_disable_credits" value="1" '.($disable_credits ? 'checked="checked"' : '').'/>';
			$content .= ' <label for="fivehundred_disable_credits">'.__('Hide Footer Credits', 'fivehundred').'</label>';
			$content .= '</p>';
			$content .= '</td>';
			$content .= '</tr>';
		}
		echo $content;
	}
}

add_filter('fh_theme_settings', 'fh_enterprise_settings', 10, 2);

function fh_enterprise_settings($settings, $posts) {
	$settings['fh_show_creator_button'] = (!empty($posts['fh_show_creator_button']) ? absint($posts['fh_show_creator_button']) : 0);
	return $settings;
}

/* A bunch of filters for category and archive pages */

add_action('init', 'custom_project_filters');

function custom_project_filters() {
	if (isset($_GET['project_filter'])) {
		add_filter('project_query', 'apply_project_filters');
	}
	else if (isset($_GET['id_category'])) {
		add_filter('project_query', 'apply_project_category', 1);
	}
}

function apply_project_filters($args) {
	$filter = $_GET['project_filter'];
	if (isset($_GET['order'])) {
		$order = $_GET['order'];
	}
	else {
		$order = 'DESC';
	}
	if ($filter == 'date') {
		$args['orderby'] = 'date';
	}
	else {
		$args['orderby'] = 'meta_value_num';
		$args['meta_key'] = $filter;
	}
	$args['order'] = $order;
	return $args;
}

function apply_project_category($args) {
	$tax_slug = $_GET['id_category'];
	if (!empty($tax_slug)) {
		$tax_cat = get_term_by('slug', $tax_slug, 'project_category');
		if (!empty($tax_cat)) {
			$args['project_category'] = $tax_slug;
		}
	}
	return $args;
}

add_action('pre_get_posts', 'set_home_project_query');

function set_home_project_query($query) {
	if (is_home() && $query->is_main_query()) {
		$settings = fh_settings();
		if (!empty($settings)) {
			$home = $settings['home'];
			if (!empty($home) && $home > 0) {
				$project_id = $home;
				if (class_exists('ID_Project')) {
					$project = new ID_Project($project_id);
					$post_id = $project->get_project_postid();
					if (isset($post_id) && $post_id > 0) {
						$query->set('p', $post_id);
						$query->set('post_type', 'ignition_product');
					}
				}
			}
		}
	}
	return;
}

//add_action('pre_get_posts', 'projects_archive_display');

function projects_archive_display($query) {
	if (is_post_type_archive('ignition_product')) {
		$query->set('posts_per_page', 9);
		return;
	}
}

add_filter('pre_get_posts', 'add_projects_to_cat');

function add_projects_to_cat($query) {
	if( is_category() || is_tag() && empty( $query->query_vars['suppress_filters'] ) ) {
		$post_types = get_post_types();
		$post_types = array_merge($post_types, array('ignition_product'));
		$query->set('post_type', $post_types);
	}
	return $query;
}

//add_filter('pre_get_posts', 'send_failed_to_bottom');

function send_failed_to_bottom($query) {
	$post_type = $query->get('post_type');
	if (!is_admin()) {
		if ((is_home() || is_post_type_archive('ignition_product')) && $post_type == 'ignition_product') {
			//$query->set('orderby', 'meta_value');
			//$query->set('order', 'DESC');
			$query->set('meta_query', array(
				array(
					'key' => 'ign_project_success',
					'value' => 'unsuccessful',
					'compare' => 'NOT IN'
				)
			));
		}
	}
}

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
	if ( $prod_settings ) {
		$currency_code = $prod_settings->currency_code;
	}else{
		$currency_code = 'USD';
	}
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

function the_project_content($id) {
	$post = get_post($id);
	$project_id = get_post_meta($id, 'ign_project_id', true);
	$name = $post->post_title;
	$short_desc = html_entity_decode(get_post_meta($id, 'ign_project_description', true));
	$long_desc = get_post_meta($id, 'ign_project_long_description', true);
	
	$content = new stdClass;
	$content->name = $name;
	$content->short_description = $short_desc;
	$content->long_description = apply_filters('fh_project_content', html_entity_decode($long_desc), $project_id);
	return $content;
}
/**
This function is now  deprecated -> now pulled from the IgnitionDeck Deck class hDeck method
*/
function the_project_hDeck($id) {
	// deprecated -> now pulled from the IgnitionDeck Deck class hDeck method
// *payment button, *learn more,
	$project_id = get_post_meta($id, 'ign_project_id', true);
	$project = new ID_Project($project_id);
	$goal = $project->the_goal();
	$total = $project->get_project_raised();
	// GETTING product default settings
	$default_prod_settings = getProductDefaultSettings();
	$end_type = $project->get_end_type();

	// Getting product settings and if they are not present, set the default settings as product settings
	$prod_settings = getProductSettings($project_id);
	if (empty($prod_settings)) {
		$prod_settings = $default_prod_settings;
	}
	if ( $prod_settings ) {
		$currency_code = $prod_settings->currency_code;
	}else{
		$currency_code = 'USD';
	}
	//GETTING the currency symbols
	$cCode = setCurrencyCode($currency_code);
	// date info
	$end_date = $project->end_date();
	$show_dates = false;
	if (!empty($end_date)) {
		$show_dates = true;
		$month = $project->end_month();
		$day = $project->end_day();
		$year = $project->end_year();
		$days_left = $project->days_left();
	}

	$percentage = $project->percent();
	$pledges_count = $project->get_project_orders();

	$hDeck = new stdClass;
	$hDeck->end_type = $end_type;
	$hDeck->goal = $goal;
	$hDeck->total = $total;
	$hDeck->show_dates = $show_dates;
	if ($show_dates == true) {
		$hDeck->end = $end_date;
		$hDeck->day = $day;
		$hDeck->month = apply_filters('id_end_month', date('F', mktime(0, 0, 0,$month, 10)));
		$hDeck->year = $year;
		$hDeck->days_left = $days_left;
	}
	
	$hDeck->percentage = $percentage;
	$hDeck->pledges = $pledges_count;
	$hDeck->currency_code = $cCode;
	return $hDeck;
}

function the_project_video($id) {
	$video = get_post_meta($id, 'ign_product_video', true);
	return idf_handle_video($video);
}

/**
This function is now  deprecated -> now pulled from the IgnitionDeck Deck class the_deck method
*/
function the_levels($id) {
	global $wpdb;
	$project_id = get_post_meta($id, 'ign_project_id', true);
	$level_count = get_post_meta($id, 'ign_product_level_count', true);

	// GETTING product default settings
	$default_prod_settings = getProductDefaultSettings();

	// Getting product settings and if they are not present, set the default settings as product settings
	$prod_settings = getProductSettings($project_id);
	if (empty($prod_settings)) {
		$prod_settings = $default_prod_settings;
	}
	if ( $prod_settings ) {
		$currency_code = $prod_settings->currency_code;
	}else{
		$currency_code = 'USD';
	}
	//GETTING the currency symbols
	$cCode = setCurrencyCode($currency_code);
	$level_data = array();
	for ($i=1; $i <= $level_count; $i++) {
		$level_sales = $wpdb->prepare('SELECT COUNT(*) as count FROM '.$wpdb->prefix.'ign_pay_info WHERE product_id=%d AND product_level = %d', $project_id, $i);
		$return_sales = $wpdb->get_row($level_sales);
		$level_sales = $return_sales->count;
		if ($i == 1) {
			$level_title = html_entity_decode(get_post_meta($id, 'ign_product_title', true));
			$level_desc = html_entity_decode(get_post_meta($id, 'ign_product_details', true));
			$level_price = get_post_meta($id, 'ign_product_price', true);
			if ($level_price > 0) {
				$level_price = number_format($level_price, 0, '.', ',');
			}
			$level_limit = get_post_meta($id, 'ign_product_limit', true);
			$level_order = get_post_meta($id, 'ign_projectmeta_level_order', true);
			$level_data[] = array('id' => $i,
			'title' => $level_title,
			'description' => $level_desc,
			'price' => $level_price,
			'sold' => $level_sales,
			'limit' => $level_limit,
			'currency_code' => $cCode,
			'order' => $level_order);	
		}
		else {
			$level_title = html_entity_decode(get_post_meta($id, 'ign_product_level_'.$i.'_title', true));
			$level_desc = html_entity_decode(get_post_meta($id, 'ign_product_level_'.$i.'_desc', true));
			$level_price = get_post_meta($id, 'ign_product_level_'.$i.'_price', true);
			if ($level_price > 0) {
				$level_price = number_format($level_price, 0, '.', ',');
			}
			$level_limit = get_post_meta($id, 'ign_product_level_'.$i.'_limit', true);
			$level_order = get_post_meta($id, 'ign_product_level_'.$i.'_order', true);
			$level_data[] = array('id' => $i,
			'title' => $level_title,
			'description' => $level_desc,
			'price' => $level_price,
			'limit' => $level_limit,
			'sold' => $level_sales,
			'currency_code' => $cCode,
			'order' => $level_order);	
		}
		
	}
	return $level_data;
}

function fh_level_sort($a, $b) {
	return $a->meta_order == $b->meta_order ? 0 : (($a->meta_order > $b->meta_order) ? 1 : -1);
}

function have_projects() {
	global $wpdb;

	$proj_return = get_ign_projects();
	if ($proj_return) {
		return true;
	}
	else {
		return false;
	}
}
// not in use --
function the_project($id) {
	$project_id = get_post_meta($id, 'ign_project_id', true);
	$project = get_ign_project($project_id);
	$pay_info = get_pay_info($project_id);
	$fund_total = array('fund_total' => get_fund_total($pay_info));
	$meta = get_post_meta($id);
	$the_project = array_merge( $project, $pay_info, $fund_total, $meta);
	return $the_project;
}

function get_ign_project($id) {
	global $wpdb;
	$proj_query = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'ign_products WHERE id=%d', absint($id));
	$proj_return = $wpdb->get_row($proj_query);
	return $proj_return;
}

function get_ign_projects() {
	global $wpdb;
	$proj_query = 'SELECT * FROM '.$wpdb->prefix.'ign_products';
	$proj_return = $wpdb->get_results($proj_query);
	return $proj_return;
}

function get_pay_info($id) {
	global $wpdb;
	$pay_query = $wpdb->prepare('SELECT * FROM '.$wpdb->prefix.'ign_pay_info WHERE product_id=%d', absint($id));
	$pay_return = $wpdb->get_results($pay_query);
	return $pay_return;
}

function get_fund_total($id) {
	$project_id = get_post_meta($id, 'ign_project_id', true);

	$pay_info = get_pay_info($project_id);
	$total = 0;
	foreach ($pay_info as $fund) {
		$total = $total + $fund->prod_price;
	}
	return $total;
}

function get_backer_total($id) {
	global $wpdb;
	$project_id = get_post_meta($id, 'ign_project_id', true);
	$get_pledgers = $wpdb->prepare('SELECT COUNT(*) AS count FROM '.$wpdb->prefix.'ign_pay_info WHERE product_id=%d', $project_id);
	$return_pledgers = $wpdb->get_row($get_pledgers);
	return $return_pledgers->count;
}

function the_project_image($id, $num) {
	if ($num == 1) {
		if (method_exists('ID_Project', 'get_project_thumbnail')) {
			$image = ID_Project::get_project_thumbnail($id, 'fivehundred_featured');
		}
		else {
			$project_id = get_post_meta($id, 'ign_project_id', true);
			global $wpdb;
			$url = get_post_meta($id, 'ign_product_image1', true);
			$sql = $wpdb->prepare('SELECT ID FROM '.$wpdb->prefix.'posts WHERE guid = %s', $url);
			$res = $wpdb->get_row($sql);
			if (isset($res->ID)) {
				$src = wp_get_attachment_image_src($res->ID, 'fivehundred_featured');
				$image = $src[0];
			} else {
				$image = $url;
			}
		}
	}
	else if ($num == 2) {
		$project_id = get_post_meta($id, 'ign_project_id', true);
		global $wpdb;
		$url = get_post_meta($id, 'ign_product_image2', true);
		$sql = $wpdb->prepare('SELECT ID FROM '.$wpdb->prefix.'posts WHERE guid = %s', $url);
		$res = $wpdb->get_row($sql);
		if (isset($res->ID)) {
			$src = wp_get_attachment_image_src($res->ID, '');
			$image = $src[0];
		} else {
			$image = $url;
		}
	}
	else {
		$key = 'ign_product_image'.$num;
		$image = get_post_meta($id, $key, true);
	}
	
	return $image;
}

function the_project_goal($id) {
	global $wpdb;
	$project_id = get_post_meta($id, 'ign_project_id', true);
	$goal_query = $wpdb->prepare('SELECT goal FROM '.$wpdb->prefix.'ign_products WHERE id=%d', $project_id);
	$goal_return = $wpdb->get_row($goal_query);
	if (!empty($goal_return->goal)) {
		$goal = $goal_return->goal;
	}
	else {
		$goal = 0;
	}
	return $goal;
}

add_action('init', 'fh_lightbox');

function fh_lightbox() {
	if (class_exists('IDF')) {
		add_action('id_after_levels', 'fh_level_select');
	}
}

function fh_level_select($project_id) {
	//ob_start();
	global $pwyw;
	$permalink_structure = get_option('permalink_structure');
	if (empty($permalink_structure)) {
		$prefix = '&';
	}
	else {
		$prefix = '?';
	}
	if (class_exists('Deck')) {
		$deck = new Deck($project_id);
		$the_deck = $deck->the_deck();
		$post_id = $the_deck->post_id;
		if (function_exists('idc_checkout_image')) {
			$image = idc_checkout_image($post_id);
			$level_data = $the_deck->level_data;
			$action = get_permalink($post_id).$prefix.'purchaseform=500&prodid='.$project_id;
			include_once ID_PATH.'templates/_lbLevelSelect.php';
		}
	}
	return;
}

function fh_fa_shortcodes($attrs) {
	if (isset($attrs)) {
		$icon = 'fa fa-'.$attrs['type'];
		if (isset($attrs['size'])) {
			$size = 'fa-'.$attrs['size'];
		}
		else {
			$size = null;
		}

		$output = '<i class="'.$icon.' '.$size.'"></i>';
	}
	else {
		$output = '';
	}
	return $output;
}

add_shortcode('icon', 'fh_fa_shortcodes');

// This is below

add_action('after_setup_theme', 'fivehundred_setup');

function fivehundred_setup(){
	global $crowdfunding;
	global $backer_list;
	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'menus' );
	register_nav_menus(
		array( 
			'main-menu' => __( 'Main Menu', 'fivehundred' ),
			'footer-menu' => __( 'Footer Menu', 'fivehundred' ) 
			)
	);
	add_theme_support( 'woocommerce' );
	add_filter('wp_nav_menu_items', 'fh_my_account_link', 5, 2);
	add_action('wp_head', 'fh_color_styles', 999);
	if ($crowdfunding) {
		add_action('fh_below_project', function() {
			echo '<div class="content_tab backers_tab">';
		});
		add_action('fh_below_project', 'mdid_backers_list');
		add_action('fh_below_project', function() {
			echo '</div>';
		});
	}
	add_filter('wp_title', 'fh_wp_title', 10, 2);
}

/** Wordpress Theme Customizer **/

function fivehundred_customize_register( $wp_customize ) {

	$colors = array();
	$colors[] = array(
		'slug'=>'fh_primary_color', 
		'default' => '',
		'priority'	=> 1,
		'label' => __('Primary Color', 'fivehundred')
	);
	$colors[] = array(
		'slug'=>'fh_primary_light_color', 
		'default' => '',
		'priority'	=> 2,
		'label' => __('Primary Light Color', 'fivehundred')
	);
	$colors[] = array(
		'slug'=>'fh_primary_dark_color', 
		'default' => '',
		'priority'	=> 3,
		'label' => __('Primary Dark Color', 'fivehundred')
	);
	$colors[] = array(
		'slug'=>'fh_secondary_color', 
		'default' => '',
		'priority'	=> 4,
		'label' => __('Secondary Color', 'fivehundred')
	);
	$colors[] = array(
		'slug'=>'fh_secondary_dark_color', 
		'default' => '',
		'priority'	=> 5,
		'label' => __('Secondary Dark Color', 'fivehundred')
	);
	$colors[] = array(
		'slug'=>'fh_text_color', 
		'default' => '',
		'priority'	=> 6,
		'label' => __('Text Color', 'fivehundred')
	);
	$colors[] = array(
		'slug'=>'fh_text_subtle_color', 
		'default' => '',
		'priority'	=> 7,
		'label' => __('Subtle Text Color', 'fivehundred')
	);
	$colors[] = array(
		'slug'=>'fh_text_onprimary_color', 
		'default' => '',
		'priority'	=> 8,
		'label' => __('Text Color On Primary Background', 'fivehundred')
	);
	$colors[] = array(
		'slug'=>'fh_container_background_color', 
		'default' => '',
		'priority'	=> 9,
		'label' => __('Container Background Color', 'fivehundred')
	);
	$colors[] = array(
		'slug'=>'fh_site_background_color', 
		'default' => '',
		'priority'	=> 10,
		'label' => __('Site Background Color', 'fivehundred')
	);

	foreach( $colors as $color ) {
		// SETTINGS
		$wp_customize->add_setting(
			$color['slug'], array(
				'default' => $color['default'],
				'type' => 'option',
				'sanitize_callback' => 'sanitize_hex_color'
			)
		);
		// CONTROLS
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				$color['slug'],
				array('label' => $color['label'], 
				'section' => 'colors',
				'settings' => $color['slug'],
				'priority' => $color['priority'])
			)
		);
	}
	return $wp_customize;

}
add_action( 'customize_register', 'fivehundred_customize_register' ); 

function fh_color_styles() {
	$primary_color = get_option('fh_primary_color');
	$primary_light_color = get_option('fh_primary_light_color');
	$primary_dark_color = get_option('fh_primary_dark_color');
	$secondary_color = get_option('fh_secondary_color');
	$secondary_dark_color = get_option('fh_secondary_dark_color');
	$text_color = get_option('fh_text_color');
	$text_subtle_color = get_option('fh_text_subtle_color');
	$text_onprimary_color = get_option('fh_text_onprimary_color');
	$site_background_color = get_option('fh_site_background_color');
	$container_background_color = get_option('fh_container_background_color');

	$customized = false;
	if (!empty($primary_color) || !empty($primary_light_color) || !empty($primary_dark_color) || !empty($secondary_color) || !empty($secondary_dark_color) || !empty($text_color) || !empty($text_subtle_color) || !empty($text_onprimary_color) || !empty($site_background_color) || !empty($container_background_color)) {
		$customized = true;
	}
	if ($customized) {
		// Convert Sidebar from Hex to RGB
		if ( !empty($primary_color) && $primary_color !== '#3B7BB3') {
			$hexs = str_replace("#", "", $primary_color);

			if (strlen($hexs) == 3) {
				$rs = hexdec(substr($hexs,0,1).substr($hexs,0,1));
				$gs = hexdec(substr($hexs,1,1).substr($hexs,1,1));
				$bs = hexdec(substr($hexs,2,1).substr($hexs,2,1));

			}
			else {
				$rs = hexdec(substr($hexs,0,2));
				$gs = hexdec(substr($hexs,2,2));
				$bs = hexdec(substr($hexs,4,2));
			}
		}

		if (!empty($site_background_color) && $site_background_color !== '#F1F4F7') {
			$hexs = str_replace("#", "", $site_background_color);

			if (strlen($hexs) == 3) {
				$rb = hexdec(substr($hexs,0,1).substr($hexs,0,1));
				$gb = hexdec(substr($hexs,1,1).substr($hexs,1,1));
				$bb = hexdec(substr($hexs,2,1).substr($hexs,2,1));
			}
			else {
				$rb = hexdec(substr($hexs,0,2));
				$gb = hexdec(substr($hexs,2,2));
				$bb = hexdec(substr($hexs,4,2));
			} 
		}
		$css =
		'<style>
	.ign-content-long, .ign-content-normal, .ign-content-alt, .ign-content-level, .ign-content-alert, .ign-video-headline, .ign-content-fullalt, #site-description, .entry-content, #comments, .ignitiondeck.id-purchase-form-full {padding-left: 20px !important; padding-right: 20px !important; box-sizing: border-box;}
		body {background-color: '.$site_background_color.';}
		body, .entry-content h1, .comment-content h1, .entry-content h2, .comment-content h2, .entry-content h3, .comment-content h3, .entry-content h4, .comment-content h4, .entry-content h5, .comment-content h5, .entry-content h6, .comment-content h6, #container .ign-content-level li, .ignition_project #site-description h1, #ign-product-levels .ign-level-title span, #ign-product-levels .ign-level-desc, .ignitiondeck form .form-row label, .ignitiondeck form .payment-type-selector a, .ignitiondeck form label.dd-option-text, .widget-area .widget-container h3, #content .ign-project-summary .ign-summary-desc, #content .ign-project-summary .title h3, footer .footer-finalwrap a, #menu-header ul.defaultMenu li ul.children li a, #menu-header .menu ul li ul.children li a, #menu-header ul li ul.sub-menu li.current-menu-item a, #menu-header ul.defaultMenu li ul.children li.current-menu-item a, #menu-header .menu ul ul.children li.current-menu-item a, #menu-header .menu ul li a:active, #menu-header ul.menu li a, #menu-header ul.defaultMenu li a, #menu-header .menu ul li a, footer, .memberdeck .md-box-wrapper, .memberdeck form .form-row label, .ignitiondeck form .finaldesc, .ignitiondeck form .finaldesc p, #fivehundred .ignitiondeck.id-creatorprofile, .container .ign-content-alt h3,  .ignition_project #site-description h2,.memberdeck form .form-row input, .memberdeck form .form-row textarea, .ign-project-summary .project-tag  { 
		color:  '.$text_color.';
	}
		#ign-hDeck-right, .ign-product-proposed-end, .home #site-description h1, #site-description h1, .commentlist > li.bypostauthor .comment-meta, .comment-meta, .dd-desc, footer #site-description h1, .entry-content blockquote, .comment-content blockquote, #content .ign-project-summary .ign-summary-days, #content .ign-project-summary .title h3:hover, .memberdeck .md-profile .md-registered { color: '.$text_color.';}
		#container .ign-content-level .ign-content-text {border-top-color: '.$text_color.';}
		#container .constrained, #menu-header ul.menu ul.sub-menu, #menu-header div.menu ul.defaultMenu ul.children, #menu-header .menu ul ul.children, #content .ign-project-summary .ign-progress-bar, #menu-header ul.menu li:hover, ul.menu li:active,  #menu-header ul.defaultMenu li:hover,  #menu-header ul.defaultMenu li:active, #menu-header .menu ul li:active, #menu-header .menu li.createaccount, #menu-header .menu li.login, .memberdeck .dashboardmenu { background-color: '.$primary_color.'; }
		#container .constrained, #container .constrained h3, footer .footer-finalwrap a:hover, #menu-header ul ul.sub-menu a:hover, #menu-header ul.defaultMenu li ul.children a:hover, #menu-header ul.menu li a:hover, ul.menu li a:active, #menu-header ul.defaultMenu li a:hover, #menu-header ul.defaultMenu li a:active, #menu-header ul li ul.sub-menu li a, #menu-header ul.menu li:hover, ul.menu li:active,  #menu-header ul.defaultMenu li:hover,  #menu-header ul.defaultMenu li:active, #menu-header .menu ul li:active, #menu-header .menu li.createaccount, #menu-header .menu li.login, #menu-header ul.menu li:hover a, ul.menu li:active a,  #menu-header ul.defaultMenu li:hover a,  #menu-header ul.defaultMenu li:active a, #menu-header .menu ul li:active a, #menu-header .menu li.createaccount a, #menu-header .menu li.login a, .ign-supportnow a, .ign-supportnow a:hover, .memberdeck button, .memberdeck input[type="submit"], .memberdeck form .form-row input[type="submit"], .memberdeck .button, .memberdeck .md-dash-sidebar ul li.widget_nav_menu ul.menu li a, .memberdeck .md-dash-sidebar ul li.widget_nav_menu ul.menu li a:hover, #content .ign-project-summary .ign-progress-raised, #content .ign-project-summary .ign-progress-percentage { color: '.$text_onprimary_color.'; }
		#menu-header ul.menu li a:hover, ul.menu li a:active, #menu-header ul.defaultMenu li a:hover,  #menu-header ul.defaultMenu li a:active, #menu-header .menu ul li a:active, #ign-hDeck-right .ign-progress-bar, .memberdeck .dashboardmenu li:hover {background-color: '.$primary_light_color.';}
		.title-wrap h2.entry-title, .single-post #content .title-wrap h2.entry-title  { color: '.$primary_dark_color.'; }
		a.comment-reply-link, .ignitiondeck form .main-btn, .ignitiondeck form input[type=submit] {background-color: '.$text_color.';}
		a.comment-reply-link:hover, a.comment-reply-link:focus, a.comment-reply-link:active, .ignitiondeck form .main-btn, .ignitiondeck form input[type=submit]:hover{background-color: '.$text_subtle_color.';}
		a.comment-reply-link, a.comment-reply-link:hover, a.comment-reply-link:focus, a.comment-reply-link:active, a.comment-reply-link, .ignitiondeck form .main-btn, .ignitiondeck form input[type=submit], a.comment-reply-link, .ignitiondeck form .main-btn, .ignitiondeck form input[type=submit]:hover, .memberdeck .dashboardmenu a, .memberdeck .dashboardmenu a:hover, #container, .title-wrap  { color:  '.$container_background_color.'; }
		.ignitiondeck.idc_lightbox .form-row.submit input[type=submit], #fivehundred .ignitiondeck.idc_lightbox .form-row.submit input[type=submit] {background-color: '.$primary_color.' !important;}
		.ignitiondeck.idc_lightbox .form-row.submit input[type=submit]:hover, #fivehundred .ignitiondeck.idc_lightbox .form-row.submit input[type=submit]:hover {background-color: '.$primary_light_color.' !important;}
		.ignitiondeck.idc_lightbox .form-row.submit input[type=submit], #fivehundred .ignitiondeck.idc_lightbox .form-row.submit input[type=submit] {color: '.$text_onprimary_color.' !important;}
		#home-sharing ul li.twitter-btn a, #home-sharing ul li.linkedin-btn a, #home-sharing ul li.facebook-btn a, #home-sharing ul li.gplus-btn a, #container .ign-content-alt h3, #container .ign-content-level h3 .amount, #ign-product-levels .ign-level-title .level-price, #ign-product-levels .ign-level-counts, .comment-meta .fn span, #container  .ign-video-headline h3, .ignitiondeck form .payment-type-selector a:hover, .ignitiondeck form .payment-type-selector a.active, .ignitiondeck form .payment-type-selector a.active:hover, .grid-header ul li a:hover, .grid-header ul li a.active, .grid-header ul li.filter_submenu:hover span, #content h2.entry-title a, #content .ign-project-summary .ign-summary-days strong, .ign-progress-raised strong, #container .ign-content-alt h3, #container .ign-content-level h3 .amount, #container .ign-content-alert h3, #site-title a, #ign-hDeck-right .ign-product-goal strong, #ign-hDeck-right .ign-product-supporters strong, #ign-hDeck-right .ign-product-proposed-end .ign-proposed-end, .ignitiondeck form .ign-checkout-price, .grid-header ul li.filter_submenu span, .filter_choice a, .memberdeck .md-profile .md-credits  { color: '.$primary_color.'; }
		.grid-header ul li a:hover, .grid-header ul li a.active, .grid-header ul li.filter_submenu:hover span {border-top-color: '.$primary_color.'; border-bottom-color: '.$primary_color.';}
		#home-sharing ul li.twitter-btn a:hover, #home-sharing ul li.linkedin-btn a:hover, #home-sharing ul li.facebook-btn a:hover, #home-sharing ul li.gplus-btn a:hover, #content h2.entry-title a:hover, #site-title a:hover { color: '.$primary_light_color.'; }
		.ignitiondeck form .payment-type-selector a:hover, .ignitiondeck form .payment-type-selector a.active, .entry-content blockquote, .comment-content blockquote, #content .ign-project-summary .ign-summary-container:hover, #menu-header ul.menu li:hover, ul.menu li:active,  #menu-header ul.defaultMenu li:hover,  #menu-header ul.defaultMenu li:active, #menu-header .menu ul li:active, #menu-header .menu li.createaccount, #menu-header .menu li.login, #menu-header ul.menu ul.sub-menu, #menu-header div.menu ul.defaultMenu ul.children, #menu-header .menu ul ul.children { border-color: '.$primary_color.'; }
		#container .ign-content-fullalt { border-top-color: '.$secondary_color.'; }
		body a, .ignitiondeck form .required-mark, .widget-area .widget-container a, #content .ign-project-summary .ign-summary-learnmore,  #menu-footer ul.menu li a, #menu-footer ul.defaultMenu li a, .memberdeck a  { color: '.$secondary_color.'; }
		body a:hover, .widget-area .widget-container a:hover, #content .ign-project-summary .ign-summary-learnmore:hover,  #menu-footer ul.menu li a:hover, ul.menu li a:active, #menu-footer ul.defaultMenu li a:hover, #menu-footer ul.defaultMenu li a:active, .memberdeck a:hover { color: '.$secondary_dark_color.'; }
		.memberdeck .md-dash-sidebar ul li.widget_nav_menu ul.menu li a { background-color: '.$secondary_color.'; }
		.memberdeck .md-dash-sidebar ul li.widget_nav_menu ul.menu li a:hover { background-color: '.$secondary_dark_color.'; }
		#container h3.product-dashed-heading, #container h3.product-dashed-heading1, #container #prodfaq, #container #produpdates, .ignitiondeck form .form-row input, .ignitiondeck form .form-row textarea, .ignitiondeck form .form-row select,
		.ignitiondeck form#fes .form-row textarea, .ignitiondeck form .form-row textarea, .ignitiondeck form#fes .form-row input, .ignitiondeck form .form-row input, .ignitiondeck form#fes .form-row .idc-dropdown__select, .ignitiondeck form .form-row .idc-dropdown__select, .ignitiondeck form#fes .form-row.pretty_dropdown select, .ignitiondeck form .form-row.pretty_dropdown select { color:  '.$text_color.'; }
		#menu-header ul.menu li.current-menu-item a, #menu-header ul.menu li.current_page_item a, #menu-header ul.menu li.current-menu-ancestor a, #menu-header .menu ul li.current-menu-ancestor a, .memberdeck button:hover, .memberdeck input[type="submit"]:hover, .memberdeck form .form-row input[type="submit"]:hover, .memberdeck .button:hover { color: '.$text_onprimary_color.'; background-color: '.$primary_dark_color.';}
		#menu-header ul.menu li.current-menu-item a:hover, #menu-header ul.menu li.current_page_item a:hover, #menu-header ul.menu li.current-menu-ancestor a:hover, #menu-header .menu ul li.current-menu-ancestor a:hover { color: '.$text_onprimary_color.'; background-color: '.$primary_color.';}
		#menu-footer ul.menu li, #menu-footer ul.defaultMenu li {border-right-color: '.$text_subtle_color.';}
		.ignitiondeck form .dd-option-description {border-left-color: '.$text_subtle_color.';}
		#container, .widget-area .widget-container h3, .entry-footer, header#header { border-bottom-color: '.$primary_light_color.'; }
		footer .footer-finalwrap, #ign-hDeck-right .ign-progress-wrapper { background-color:  '.$primary_dark_color.'; }
		.ignitiondeck form .form-row input, .ignitiondeck form .form-row textarea, .ignitiondeck form .form-row select, #content .ign-project-summary .ign-summary-container, #content .ign-project-summary .ign-summary-container .ign-summary-image, .ignitiondeck .id-purchase-form, .ignitiondeck .dd-select  { border-color: '.$text_subtle_color.';}
		.ign-supportnow a, .memberdeck button, .memberdeck input[type="submit"], .memberdeck form .form-row input[type="submit"], .memberdeck .button {background: '.$primary_color.'; background-color: '.$primary_color.' ;}
		
		
				.memberdeck .md-profile .md-credits span.green, .memberdeck .md-profile .md-credits span, .memberdeck .dashboardmenu a, .memberdeck .dashboardmenu a:visited { color: '.$text_onprimary_color.'; }
				a.comment-reply-link, a.comment-reply-link:hover, a.comment-reply-link:focus, a.comment-reply-link:active, a.comment-reply-link, .ignitiondeck form .main-btn, .ignitiondeck form input[type=submit], a.comment-reply-link, .ignitiondeck form .main-btn, .ignitiondeck form input[type=submit]:hover, .memberdeck .dashboardmenu a, .memberdeck .dashboardmenu a:hover { color: '.$text_subtle_color.'; }
			.memberdeck .md-profile .project-status 
			{background: '.$primary_color.'; background-color: '.$primary_color.' ; color: '.$text_onprimary_color.';}
			.memberdeck .dashboardmenu li:hover, .memberdeck .dashboardmenu li.active a { background-color: '.$primary_dark_color.'}
		.ignitiondeck .fes_section h3, .memberdeck .md-profile.paypal-settings h3, .memberdeck .md-profile.mail-chimp h3, .memberdeck .md-profile.stripe-settings h3, .memberdeck form .form-row label, .ignitiondeck form#fes .form-row label, .ignitiondeck form .form-row label{ color:  '.$text_color.'}
		.ignitiondeck form#fes input[type=submit], .ignitiondeck form input[type=submit]{background: '.$primary_color.'; background-color: '.$primary_color.' ; color: '.$text_onprimary_color.'!important;}
		.ignitiondeck form#fes input[type=submit]:hover, .ignitiondeck form input[type=submit]:hover {
			background: '.$primary_dark_color.'; background-color: '.$primary_dark_color.' ; }
			.memberdeck form .form-row input { background-color: '.$text_subtle_color.'; border-color: '.$text_subtle_color.';  color: '.$text_onprimary_color.' !important; }
			.memberdeck form a, .ignitiondeck.backer_profile .backer_data .backer_supported{ color: '.$primary_color.'; }
			.memberdeck form a:hover {color: '.$primary_dark_color.';}
			.ignitiondeck.backer_profile .backer_projects li.backer_project_mini .backers_days_left {background-color: '.$primary_color.' ; color: '.$text_onprimary_color.'!important;}
			.ignitiondeck.backer_profile .backer_projects li.backer_project_mini .backers_funded, .memberdeck .md-profile .project-funded { color: '.$primary_color.'; }
			.ignitiondeck.backer_profile .backer_projects li.backer_project_mini .backer_project_title a, .memberdeck .md-profile .project-name { color: '.$text_onprimary_color.' ;}
			
		 .ignition_project #ign-hDeck-right .internal strong, .ignition_project #ign-hDeck-right .internal div, #site-description.project-single span,
	 .ign-project-title .product-author-details i
	  {color: '.$text_onprimary_color.'; }
	 .ignitiondeck form#fes .form-row input, .ignitiondeck form .form-row input, #fivehundred .ignitiondeck#stellar_lightbox form input {color: '.	
	 $text_color.' !important;} 
	#ign-project-content .entry-content {background-color: '.$container_background_color.'; color: '.$text_color.';}
	.memberdeck .checkout-title-bar span.active {color: '.$text_subtle_color.'; }
	.memberdeck .checkout-title-bar span.active:after {border-bottom-color: '.$primary_color.';}
	.memberdeck .checkout-title-bar span.currency-symbol, {color: '.$primary_dark_color.';}
	.memberdeck form .payment-type-selector a.active, .memberdeck form .payment-type-selector a:hover {border-color: '.$primary_color.';}
	 .memberdeck .checkout-title-bar span.currency-symbol .checkout-tooltip i.tooltip-color { color: '.$primary_color.';}
		';

			if (!empty($primary_color) && $primary_color !== '#3B7BB3') {
				$css .= '	#container .fullwindow-internal, #container .ign-content-alert, #ign-hDeck-wrapper #ign-hdeck-wrapperbg, .grid-header ul li a:hover, .grid-header ul li a.active, .grid-header ul li.filter_submenu:hover span, #content .ign-project-summary .ign-progress-wrapper, .grid-header ul li ul li a:hover, .ignitiondeck form .payment-type-selector a   {background-color: rgba(' . $rs . ',' . $gs . ', ' . $bs . ', .2);}' . "\n";
			}
			if (!empty($site_background_color) && $site_background_color !== '#F1F4F7') {
				$css .= '	#container .ign-content-alt, #container .ign-content-level .ign-content-text, #ign-product-levels a .level-group:hover .ign-level-desc, .title-wrap h2.entry-title, #container h3.product-dashed-heading, #container h3.product-dashed-heading1, #container .ign-content-video, .dd-option:hover, .dd-option-selected, #content .ign-project-summary .ign-summary-container:hover, .memberdeck .md-list-thin > li:hover:nth-child(odd), .memberdeck .md-box li:hover, .ignitiondeck .id-purchase-form {background-color: rgba(' . $rb . ',' . $gb . ', ' . $bb . ', .5) ;}' . "\n";
			}
			if (!empty($site_background_color) && $site_background_color !== '#F1F4F7') {
				$css .= '.commentlist > li.bypostauthor, #container #prodfaq, #container #produpdates, .entry-content blockquote, .comment-content blockquote, .wp-caption, .grid-header, #content .ign-project-summary .ign-summary-container, .memberdeck .md-list-thin > li:nth-child(odd), .dd-selected, #fivehundred .ignitiondeck.id-creatorprofile, #ign-product-levels .ign-level-desc, #ign-product-levels .ign-level-counts {background-color: rgba(' . $rb . ',' . $gb . ', ' . $bb . ', .2);}' . "\n";
			}
			if (!empty($site_background_color) && $site_background_color !== '#F1F4F7') {
				$css .= '	#ign-product-levels .alt, .comment-meta .fn span {background-color: rgba(' . $rb . ',' . $gb . ', ' . $bb . ', .25);}' . "\n";
			}
			if (!empty($site_background_color) && $site_background_color !== '#F1F4F7') {
				$css .= ' .commentlist .commentarrow { border-color: transparent rgba(' . $rb . ',' . $gb . ', ' . $bb . ', .15) transparent transparent; }' . "\n";
			}
			if (!empty($primary_color) && $primary_color !== '#3B7BB3') {
				$css .= '#ign-product-levels .ign-level-desc, .memberdeck .md-box-wrapper, #fivehundred .ignitiondeck.id-creatorprofile, .memberdeck .md-box.half:nth-child(odd), .memberdeck .md-box.half:nth-child(4n+3), .memberdeck .md-box.half:nth-child(4n+4) { border-color: rgba(' . $rs . ',' . $gs . ', ' . $bs . ', .35); border-top-color: rgba(' . $rs . ',' . $gs . ', ' . $bs . ', .35); border-bottom-color: rgba(' . $rs . ',' . $gs . ', ' . $bs . ', .35); border-left-color: rgba(' . $rs . ',' . $gs . ', ' . $bs . ', .35); border-right-color: rgba(' . $rs . ',' . $gs . ', ' . $bs . ', .35); }' . "\n";
			}
		$css .= '</style>';
		echo apply_filters('fh_customization_style', (isset($css) ? $css : ''));
	}
}

function fh_wp_title($title, $sep) {
	global $page, $paged;

	if ( is_feed() ) {
		return $title;
	}

	// Add the blog name
	$title .= get_bloginfo( 'name' );

	if (is_home() || is_front_page()) {
		$site_description = get_bloginfo( 'description', 'display' );
		$title .= $sep." ".$site_description;
	}

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 )
		$title .= " $sep " . sprintf( __( 'Page %s', 'fivehundred' ), max( $paged, $page ) );

	return $title;
}


function fh_my_account_link($nav, $args) {
	if (class_exists('ID_Member')) {
		global $permalink_structure;
		if (empty($permalink_structure)) {
			$prefix = '&';
		}
		else {
			$prefix = '?';
		}
		$durl = md_get_durl();
		if ($args->theme_location == 'main-menu') {
			do_action('idc_menu_before');
			if (is_user_logged_in()) {
				$idc_menu = '<li class="createaccount buttonpadding"><a href="'.$durl.'">'.__('My Account', 'fivehundred').'</a></li>';
				$idc_menu .= '<li class="login right"><a href="'.wp_logout_url( home_url() ).'">'.__('Logout', 'fivehundred').'</a></li>';
			}
			else {
				$idc_menu = '<li class="createaccount buttonpadding"><a href="'.$durl.$prefix.'action=register">'.__('Create Account', 'fivehundred').'</a></li>';
				$idc_menu .= '<li class="login right"><a href="'.$durl.'">'.__('Login', 'fivehundred').'</a></li>';
			}
			do_action('idc_menu_after');
			$nav .= apply_filters('idc_menu', $idc_menu);
		}
	}
	return $nav;
}
// Image Sizes added and Allowing to select those image sizes in Media Insert Admin
if ( function_exists( 'add_image_size' ) ) { 
	add_image_size( 'projectpage-large', 640, 9999 ); // For Project Pages with Unlimited Height allowed
	add_image_size( 'single-thumb', 700, 105, true ); // For Single Posts (cropped)
	add_image_size( 'fivehundred_featured', 624, 360, true); // For 500 Featured Project
}

add_filter( 'image_size_names_choose', 'custom_image_sizes_choose' );  
function custom_image_sizes_choose( $sizes ) {  
    $custom_sizes = array(  
        'projectpage-large' => 'Project Page Full Width',
        'single-thumb' => 'Single Post Thumb',
        'fh_feature' => 'Fivehundred Feature'  
    );  
    return array_merge( $sizes, $custom_sizes );  
}


// for custom comments

if ( ! function_exists( 'fivehundred_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own fivehundred_comment(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 */
function fivehundred_comment( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	switch ( $comment->comment_type ) :
		case 'pingback' :
		case 'trackback' :
		// Display trackbacks differently than normal comments.
	?>
	<li <?php comment_class(); ?> id="comment-<?php comment_ID(); ?>">
		<p><?php _e( 'Pingback:', 'fivehundred' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( '(Edit)', 'fivehundred' ), '<span class="edit-link">', '</span>' ); ?></p>
	<?php
			break;
		default :
		// Proceed with normal comments.
		global $post;
	?>
	<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
		<div class="commentarrow"></div>
		<article id="comment-<?php comment_ID(); ?>" class="comment">
			<div class="comment-meta comment-author vcard">
				<?php
					echo get_avatar( $comment, 44 );
					printf( '<cite class="fn">%1$s %2$s</cite>',
						get_comment_author_link(),
						// If current post author is also comment author, make it known visually.
						( $comment->user_id === $post->post_author ) ? '<span> ' . __( 'Post author', 'fivehundred' ) . '</span>' : ''
					);
					printf( '<a href="%1$s"><time datetime="%2$s">%3$s</time></a>',
						esc_url( get_comment_link( $comment->comment_ID ) ),
						get_comment_time( 'c' ),
						/* translators: 1: date, 2: time */
						sprintf( __( '%1$s at %2$s', 'fivehundred' ), get_comment_date(), get_comment_time() )
					);
				?>
			</div><!-- .comment-meta -->

			<?php if ( '0' == $comment->comment_approved ) : ?>
				<p class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'fivehundred' ); ?></p>
			<?php endif; ?>

			<section class="comment-content comment">
				<?php comment_text(); ?>
				<?php edit_comment_link( __( 'Edit', 'fivehundred' ), '<p class="edit-link">', '</p>' ); ?>
			</section><!-- .comment-content -->

			<div class="reply">
				<?php comment_reply_link( array_merge( $args, array( 'reply_text' => __( 'Reply', 'fivehundred' ), 'after' => ' <span>&darr;</span>', 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
			</div><!-- .reply -->
		</article><!-- #comment-## -->
	<?php
		break;
	endswitch; // end comment_type check
}
endif;


add_action('comment_form_before', 'fivehundred_enqueue_comment_reply_script');

function fivehundred_scripts() {
	//wp_register_style('open-sans', 'http://fonts.googleapis.com/css?family=Open+Sans');
	wp_register_style('fivehundred', get_stylesheet_uri());
	wp_register_script('fivehundred-js', get_template_directory_uri().'/js/fivehundred-min.js');
	wp_enqueue_style('fivehundred');
	wp_enqueue_script('jQuery');
	wp_enqueue_script('thickbox');
	wp_enqueue_style('thickbox');
	//wp_enqueue_style('open-sans');
	wp_enqueue_script('fivehundred-js');
}

add_action('wp_enqueue_scripts', 'fivehundred_scripts');

function fivehundred_admin_scripts() {
	global $pagenow;
	wp_register_script('fivehundred-admin-js', get_template_directory_uri().'/js/fivehundred-admin-min.js');
	wp_register_style('fivehundred-admin', get_template_directory_uri().'/style-admin-min.css');
	if ($pagenow == 'themes.php') {
		wp_enqueue_media();
		wp_enqueue_script('fivehundred-admin-js');
		wp_enqueue_style('fivehundred-admin');
	}
	else if ($pagenow == 'widgets.php') {
		wp_enqueue_media();
		wp_enqueue_script('fivehundred-admin-js');
		wp_enqueue_style('fivehundred-admin');
	}
	wp_register_style('fh-style', get_template_directory_uri().'/admin-style-min.css');
	wp_enqueue_style('fh-style');
}

add_action('admin_enqueue_scripts', 'fivehundred_admin_scripts');

function fivehundred_enqueue_comment_reply_script() {
	if (get_option('thread_comments')) { 
		wp_enqueue_script('comment-reply'); 
	}
}



// adding search to projects custom post type
function template_chooser($template)   
{    
 global $wp_query;   
 $post_type = get_query_var('post_type');   
 if( isset($_GET['s']) && $post_type == 'ignition_product' )   
 {
  return locate_template('search.php');  //  redirect to project-summary.php
 }   
 return $template;   
}
add_filter('template_include', 'template_chooser');



// Need to set our widgets array
add_action( 'widgets_init', 'fivehundred_widgets_init' );

function fivehundred_widgets_init() {
	register_widget('Fh_Video_Widget');
    //register_widget('Fh_Music_Widget');
    register_widget('Fh_Content_Level_Widget');
    register_widget('Fh_Content_Fullalt_Widget');
    register_widget('Fh_Content_Fullalt_Bgimage_Widget');
    register_widget('Fh_Content_Alert_Widget');
    register_widget('Fh_Content_Widget');
    register_widget('Fh_Project_Grid_Widget');
	if (function_exists('register_sidebar')) {
		register_sidebar(array(
			'name' => __('Sidebar Widget Area', 'fivehundred'),
			'id' => 'primary-widget-area',
			'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
			'after_widget' => "</li>",
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		));
		register_sidebar(array(
			'name' => __('Projects Sidebar Area', 'fivehundred'),
			'id' => 'projects-widget-area',
			'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
			'after_widget' => "</li>",
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
			'description' => __( 'This sidebar is located below the Levels on each Project page.', 'fivehundred' )
		));
		register_sidebar(array(
			'name' => __('Home Sidebar Area', 'fivehundred'),
			'id' => 'home-widget-area',
			'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
			'after_widget' => "</li>",
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>',
			'description' => __( 'This sidebar is located on the Home Page, to the right of About Us', 'fivehundred' )
		));
		$sidebar = register_sidebar(array(
			'name' => __('Home Top Content Widgets', 'fivehundred'),
			'description' => __('This is a widget area on Project Grid home, directly above the Project Grid', 'fivehundred'),
			'id' => 'home-top-content-widget-area',
			'before_widget' => '<div class="home_widget">',
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		));
		$sidebar = register_sidebar(array(
			'name' => __('Home Content Widgets', 'fivehundred'),
			'description' => __('This is a widget area on Project Grid home and Single Project home, below Featured Projects or the Project Deck.', 'fivehundred'),
			'id' => 'home-content-widget-area',
			'before_widget' => '<div class="home_widget">',
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		));
		$sidebar = register_sidebar(array(
			'name' => __('Top of Footer', 'fivehundred'),
			'description' => __('This is a widget area at top of the footer, on every page of the site.', 'fivehundred'),
			'id' => 'footer-widget-area',
			'before_widget' => '<li id="%1$s" class="footer-widget-container %2$s">',
			'after_widget' => '</li>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		));
		do_action('fivehundred_widgets_init');
	}
}

$preset_widgets = array (
	'primary-aside'  => array( 'search', 'pages', 'categories', 'archives' ),
);

function fh_settings() {
	return get_option('fivehundred_theme_settings');
}

function fh_has_ide() {
	if (function_exists('is_id_pro') && is_id_pro()) {
		return true;
	}
	return false;
}

add_action('wp_head', 'fh_custom_css');

function fh_custom_css() {
	$settings = fh_settings();
	if (is_array($settings) && isset($settings['custom_css'])) {
		$custom_css = stripslashes($settings['custom_css']);
		echo '<style>';
		echo str_replace('"', '', $custom_css);
		echo '</style>';
	}
}

function fh_widont($str = '')
{
	$str = rtrim($str);
	$space = strrpos($str, ' ');
	if ($space !== false)
	{
		$str = substr($str, 0, $space).'&nbsp;'.substr($str, $space + 1);
	}
	return $str;
}

add_filter('the_title', 'fh_widont');

function fh_excerpt($excerpt) {
	global $post;
	if (!empty($post)) {
		if (empty($excerpt)) {
			$excerpt = wp_trim_excerpt($post->post_content);
		}
	}
  	return $excerpt;
}

add_filter('get_the_excerpt', 'fh_excerpt');

function fivehundred_admin_notice(){
	if (!is_plugin_active('ignitiondeck-crowdfunding/ignitiondeck.php')) {
	    echo '<div class="updated">
	       <p>'.__('This theme requires the', 'fivehundred').' <a href="'.admin_url('plugin-install.php?tab=search&s=ignitiondeck').'">'.__('IgnitionDeck WordPress Crowdfunding Plugin', 'fivehundred').'</a>.</p>
	    </div>';
	}
}

add_action('admin_notices', 'fivehundred_admin_notice');

function fh_font_awesome() {
	wp_enqueue_style('font-awesome');
}
add_action('wp_enqueue_scripts', 'fh_font_awesome');

function fh_show_creator_button() {
	$settings = fh_settings();
	if (empty($settings['fh_show_creator_button'])) {
		return;
	}
	if (!fh_has_ide()) {
		return;
	}
	if (!current_user_can('create_edit_projects')) {
		return;
	}
	return $settings['fh_show_creator_button'];
}

function fh_creator_button() {
	$prefix = idf_get_querystring_prefix();
	$button = '<a href="'.md_get_durl().$prefix.'create_project=1" class="button id-button creator_button">'.__('Create Project', 'fivehundred').'</a>';
	return $button;
}

add_filter('idc_create_project_button', 'fh_filter_create_project');

function fh_filter_create_project($button) {
	if (fh_show_creator_button()) {
		return null;
	}
	return $button;
}

/**
* Required by WordPress
**/

if ( ! isset( $content_width ) ) {
	$content_width = 960;
}

/**
* WooCommerce
*/

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

add_action('woocommerce_before_main_content', 'fivehundred_wc_wrapper_start', 10);
add_action('woocommerce_after_main_content', 'fivehundred_wc_wrapper_end', 10);

function fivehundred_wc_wrapper_start() {
	echo '<div id="container">';
	echo '<article id="content">';
}

function fivehundred_wc_wrapper_end() {
  echo '</article>';
  get_sidebar();
  echo '</div>';
}

function fivehundred_show_credits() {
	if (fh_has_ide()) {
		$disable = get_theme_mod('fivehundred_disable_credits');
		return !$disable;
	}
	return true;
}

/**
 * Search functions and filters
 */
function fivehundred_search_filters($query) {
	if ( !empty($_GET['s']) && $query->is_main_query() && isset($_GET['type']) && $_GET['type'] == "ignition_product" ) {
		// Adding project categories and tags in the search by changing the SQL query
		add_filter('posts_fields_request', 'fivehundred_select_fields_search_filter', 10, 2);
		add_filter('posts_search', 'fivehundred_sql_search_filter', 10, 2);
		add_filter('posts_join', 'fivehundred_join_categories_search_filter', 10, 2);
	}
}
add_filter( 'pre_get_posts', 'fivehundred_search_filters' );

function crowdpress_sql_search_filter($sql, $query) {
	// If Search query is coming, then add another condition
	if (!empty($sql)) {
		$search = $_GET['s'];
		$sql = substr($sql, 0, -3);
		$sql .= " OR (wp_terms.slug LIKE '%$search%')))";
	}

	return $sql;
}

function crowdpress_join_categories_search_filter($sql, $query) {
	$sql .= 'INNER JOIN wp_term_relationships ON wp_posts.ID = wp_term_relationships.object_id
			INNER JOIN wp_term_taxonomy ON wp_term_taxonomy.term_taxonomy_id = wp_term_relationships.term_taxonomy_id
			INNER JOIN wp_terms ON wp_term_taxonomy.term_id = wp_terms.term_id';
	return $sql;
}

function crowdpress_select_fields_search_filter($sql, $query) {
	$sql .= ', wp_terms.*';
	return $sql;
}
?>