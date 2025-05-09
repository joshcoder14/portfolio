<?php

add_action('admin_menu', 'helix_admin_menus', 12);

function helix_admin_menus() {
	$settings = add_submenu_page('idf', __('Helix', 'idf'), __('Helix', 'idf'), 'manage_options', 'helix', 'helix_menu');
	add_action('admin_print_styles-'.$settings, 'idf_admin_enqueues');
}

function helix_menu() {
	// Check if the user has the required capability
	if (!current_user_can('manage_options')) {
		wp_die(esc_html__('You do not have sufficient permissions to perform this action.', 'memberdeck'));
    	exit;
	}
	
	$settings = get_option('helix_settings');	
	if (isset($_POST['submit_helix_settings'])) {
		check_admin_referer('helix_save_settings', 'helix_nonce');
		foreach ($_POST as $k=>$v) {
			// Don't save the button
			if ($k == "submit_helix_settings") {
				continue;
			}
			// If $v is an array, sanitize separately each value of that array then
			if (is_array($v)) {
				$sanitized_v = array();
				foreach ($v as $subkey => $subvalue) {
					$sanitized_v[$subkey] = sanitize_text_field($subvalue);
				}
				// Copying sanitized array into normal posted array
				$settings[$k] = $sanitized_v;
			}
			else {
				$settings[$k] = sanitize_text_field($v);
			}
		}
		update_option('helix_settings', $settings);
	}
	include HELIX_PATH.'templates/admin/_settingsMenu.php';
}
?>