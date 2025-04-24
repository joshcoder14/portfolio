<?php
/*
This file is for general functions that modify or snag the WordPress defaults
*/

/**
 * Check if Gutenberg is available.
 *
 * This function checks if the Gutenberg project is available by testing the existence
 * of the 'the_gutenberg_project' function.
 *
 * @return bool Whether Gutenberg is available.
 */
function idf_has_gutenberg() {
	return function_exists('the_gutenberg_project');
}

/**
 * Get the active widgets.
 *
 * This function retrieves the active widgets by returning the value of the 'sidebars_widgets' option.
 *
 * @return mixed The active widgets.
 */
function idf_active_widgets() {
	return get_option('sidebars_widgets');
}

/**
 * Check if the current user has super admin privileges.
 *
 * This function checks if the current user has super admin privileges by testing
 * if the user is an administrator or a super admin in a multisite environment.
 *
 * @return bool Whether the current user has super admin privileges.
 */
function idf_is_super() {
	$super = current_user_can('administrator');
	if (is_multisite()) {
		$super = is_super_admin();
	}
	return $super;
}

//add_action('pre_get_posts', 'idf_restrict_media_view');

/**
 * Restrict media view for non-admin users.
 *
 * This function restricts the media view for non-admin users by modifying the query
 * to only display attachments authored by the current user.
 *
 * @param WP_Query $query The WP_Query instance.
 * @return void
 */
function idf_restrict_media_view($query) {
	if ($query->get('post_type') == 'attachment' && !current_user_can('manage_options') && is_admin()) {
		if (!current_user_can('editor')) {
			if (is_multisite()) {
				require (ABSPATH . WPINC . '/pluggable.php');
			}
			$current_user = wp_get_current_user();
			$user_id = $current_user->ID;
			if ($user_id > 0) {
				$query->set('author', $user_id);
			}
		}
	}
}

/**
 * Add creator upload capability.
 *
 * This function adds the upload capability to the user based on certain conditions. It checks if the user is logged in,
 * if the current page is the upload page, and if the user has the necessary permissions to create or edit projects.
 * If these conditions are met, the function grants the user the capability to upload files and filters the media files
 * based on the user's ID. It also adds the capability to filter the upload capability for the user profile page.
 *
 * @return void
 */
function idf_add_creator_upload_cap() {
	$pass = false;
	global $pagenow;

	if (is_user_logged_in()) {

		// If this is the Upload page
		if ($pagenow == 'async-upload.php' || $pagenow == 'admin-ajax.php') {
			// If we have a referer page
			if (isset($_SERVER['HTTP_REFERER'])) {
				// Getting query string, and then it's variables
				$query_string = explode("?", $_SERVER['HTTP_REFERER']);
				$query_vars = array();
				// If we have no exploded array then there is no query string coming, so just return out of the function
				if (!isset($query_string[1])) {
					return;
				}
				parse_str($query_string[1], $query_vars);
				if (is_multisite()) {
					require (ABSPATH . WPINC . '/pluggable.php');
				}
				$current_user = wp_get_current_user();
				$user_id = $current_user->ID;
				$user = get_user_by('id', $user_id);
				$add_cap = false;
				$dash_settings = get_option('md_dash_settings');
				if (!empty($dash_settings)) {
					$dash_settings = maybe_unserialize($dash_settings);
					$dash_id = $dash_settings['durl'];		
					if (isset($dash_id)) {
						// If edit_project or create_project page we are on, then move forward
						if (isset($query_vars['edit_project']) || array_key_exists('edit_project', $query_vars) || isset($query_vars['create_project']) || array_key_exists('create_project', $query_vars)) {
							if (current_user_can('create_edit_projects')) {
								if (!current_user_can('upload_files')) {
									if (!empty($user)) {
										$user->add_cap('upload_files');
									}
								}
								if (isset($query_vars['create_project']) && $query_vars['create_project']) {
									$pass = true;
								}
								else if (isset($query_vars['edit_project'])) {
									$post_id = absint($query_vars['edit_project']);
									$post = get_post($post_id);
									if (!empty($post->ID) && $post->post_author == $user_id) {
										$pass = true;
									}
								}
							}
							if ($pass) {
								idc_add_upload_cap($user);
								idf_filter_media_files($user_id);
							}
							else {
								idc_remove_upload_cap($user);
							}
						}
						else if (isset($query_vars['edit-profile']) || array_key_exists('edit-profile', $query_vars)) {
							idf_filter_media_files($user_id);
							add_filter('user_has_cap', 'idf_filter_upload_cap', 10, 3);
						}
					}
				}
			}
		}
	}
}

add_action('init', 'idf_add_creator_upload_cap', 10);
add_action('wp', 'idf_add_media_buttons');

/**
 * Add media buttons for the current user.
 *
 * This function checks if the user is logged in and if the current page is the dashboard page.
 * If the user has the capability to create or edit projects, it checks if the user can upload files.
 * If not, it adds the capability to upload files. It also checks if the user is creating or editing
 * a project and if they have the capability to publish posts. If not, it sets the $pass variable to true.
 * If the conditions are met, it calls idc_add_upload_cap() to add the upload capabilities for the user.
 * Otherwise, it calls idc_remove_upload_cap() to remove the upload capabilities for the user.
 *
 * @return void
 */
function idf_add_media_buttons() {
	//retrieve the query string variables without using GET[] to bypass the nonce check issues
	$query_string = explode("?", $_SERVER['REQUEST_URI']);
	$querystring_variables = array();
	if(isset($query_string[1])){
		parse_str($query_string[1], $querystring_variables);
	}
	$pass = false;
	if (is_user_logged_in()) {
		if (is_multisite()) {
			require (ABSPATH . WPINC . '/pluggable.php');
		}
		$add_cap = false;
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;
		$user = get_user_by('id', $user_id);
		$dash_settings = get_option('md_dash_settings');
		if (!empty($dash_settings)) {
			$dash_settings = maybe_unserialize($dash_settings);
			$dash_id = $dash_settings['durl'];
		}
		if (isset($dash_id) && is_page($dash_id) && current_user_can('create_edit_projects')) {
			if (!current_user_can('upload_files')) {
				if (!empty($user)) {
					$user->add_cap('upload_files');
				}
			}
			if (isset($querystring_variables['create_project'])) {
				if (!current_user_can('publish_posts')) {
					$pass = true;
				}
			}
			else if (isset($querystring_variables['edit_project'])) {
				$post_id = absint($querystring_variables['edit_project']);
				$post = get_post($post_id);
				if (!empty($post->ID) && $post->post_author == $user_id) {
					if (!current_user_can('publish_posts')) {
						$pass = true;
					}
				}
			}
		}
		if ($pass) {
			idc_add_upload_cap($user);
		}
		else {
			idc_remove_upload_cap($user);
		}
	}
}

/**
 * Add upload capabilities to the user.
 *
 * This function adds the specified upload capabilities to the user if the user is not empty.
 *
 * @param WP_User $user The user object to which the capabilities will be added.
 * @return void
 */
function idc_add_upload_cap($user) {
	if (!empty($user)) {
		$user->add_cap('edit_others_pages');
		$user->add_cap('edit_others_posts');
		$user->add_cap('edit_pages');
		$user->add_cap('edit_posts');
		$user->add_cap('edit_private_pages');
		$user->add_cap('edit_private_posts');
		$user->add_cap('edit_published_pages');
		$user->add_cap('edit_published_posts');
	}
}

/**
 * Remove upload capabilities from the user.
 *
 * This function removes the specified upload capabilities from the user if the user is not empty.
 *
 * @param WP_User $user The user object from which the capabilities will be removed.
 * @return void
 */
function idc_remove_upload_cap($user) {
	if (!empty($user)) {
		// Getting the user's role
		$user_roles = $user->roles;
		$role_caps = array();
		foreach ($user_roles as $user_role) {
			$role_details = get_role($user_role);
			$role_caps = array_merge($role_caps, $role_details->capabilities);
		}

		// Making an array of keys, to get only caps in a single array
		$role_caps = array_keys($role_caps);
		
		// ensure we don't remove caps from users with roles that enable them
		if (!in_array('edit_others_pages', $role_caps)) {
			$user->remove_cap('edit_others_pages');
		}
		if (!in_array('edit_others_posts', $role_caps)) {
			$user->remove_cap('edit_others_posts');
		}
		if (!in_array('edit_pages', $role_caps)) {
			$user->remove_cap('edit_pages');
		}
		if (!in_array('edit_posts', $role_caps)) {
			$user->remove_cap('edit_posts');
		}
		if (!in_array('edit_private_pages', $role_caps)) {
			$user->remove_cap('edit_private_pages');
		}
		if (!in_array('edit_private_posts', $role_caps)) {
			$user->remove_cap('edit_private_posts');
		}
		if (!in_array('edit_published_pages', $role_caps)) {
			$user->remove_cap('edit_published_pages');
		}
		if (!in_array('edit_published_posts', $role_caps)) {
			$user->remove_cap('edit_published_posts');
		}
	}
}

/**
 * Filter media files by user ID.
 *
 * This function filters the media files by the specified user ID using the pre_get_posts hook.
 *
 * @param int $user_id The user ID to filter the media files by.
 * @return void
 */
function idf_filter_media_files($user_id) {
	add_action('pre_get_posts', function($query) use ($user_id) {
		$query->set('author', $user_id);
	});
}

/**
 * Filter upload capability for the user.
 *
 * This function filters the upload capability for the user by modifying the allcaps array to grant the
 * user the capability to upload files.
 *
 * @param array $allcaps An array of all the user's capabilities.
 * @param string $cap The capability name.
 * @param array $args Optional parameters for the capability.
 * @return array The modified allcaps array.
 */
function idf_filter_upload_cap($allcaps, $cap, $args) {
	$allcaps['upload_files'] = true;
	return $allcaps;
}
?>