<?php
add_action('init', 'helix_menu_location');

function helix_menu_location() {
	$locations = array(
		'helix_primary' => __('Helix Primary Menu', 'idf'),
	);
	register_nav_menus($locations);
}

function helix_display_menu() {	
	if (isset($_GET['wp_id_nonce'])) {
		check_admin_referer('wp_id_nonce', 'wp_id_nonce');
	}
	$current_user = wp_get_current_user();
	$prefix = idf_get_querystring_prefix();
	// set user is logged in flag
	if(is_user_logged_in()) {
		$logged_in = true;
	} else {
		$logged_in = false;
	}
	$dash_id = apply_filters('helix_dashboard_id', '');
	$durl = apply_filters('helix_dashboard_url', home_url());
	$helix_register_url = apply_filters('helix_register_url', $durl);
	$settings = helix_settings();	
	if (isset($_GET['helix_error']) && $_GET['helix_error'] == "login_failed") {	
		$open_menu = true;
	}
	$menu_style = (isset($settings['menu_style']) && !empty($settings['menu_style'])) ? esc_attr($settings['menu_style']) : '';
	$menu_position = isset($settings['menu_position']) ? esc_attr($settings['menu_position']) : '';
	$active_class = isset($dash_id) && is_page($dash_id) ? 'active' : '';
	$avatar = get_avatar($current_user->ID, 60);
	echo '<div class="idhelix"><div class="helix_avatar helixopen active ' . esc_attr($menu_style) . ' ' . esc_attr($menu_position) . ' ' . esc_attr($active_class) . '">' . wp_kses_post($avatar) . '</div></div>';
	echo '<div class="idhelix"><aside class="dashboard-nav' . esc_attr($menu_style) . ' ' . esc_attr($menu_position) . ($active_class ? ' active open' : '') . (is_user_logged_in() ? ' logged-in' : ' logged-out') . ((isset($open_menu) && $open_menu) ? ' open-menu' : '') . '">';
	
	$primary_nav = helix_primary_nav();
	$count = substr_count($primary_nav, 'menu-item-object-page');
	include_once 'templates/_primaryMenu.php';
	echo '</aside></div>';
}

add_action('helix_above_icon_menu', 'helix_commerce_icons');

function helix_commerce_icons() {
	if (is_user_logged_in()) {
		$params = helix_params();
		$current_user = wp_get_current_user();
		$prefix = idf_get_querystring_prefix();
		ob_start();
		include_once('templates/_helixCommerceIcons.php');
		$content = ob_get_contents();
		ob_end_clean();
		echo wp_kses_post($content);
	}
}

add_action('helix_after_login_form', 'helix_commerce_menu');

function helix_commerce_menu() {
	if (is_user_logged_in()) {
		$params = helix_params();
		$current_user = wp_get_current_user();
		$prefix = idf_get_querystring_prefix();
		ob_start();
		include_once('templates/_helixCommerceMenu.php');
		$content = ob_get_contents();
		ob_end_clean();
		echo wp_kses_post($content);
	}
}
?>