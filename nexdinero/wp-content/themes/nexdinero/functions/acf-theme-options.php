<?php
if( function_exists('acf_add_options_page') ) {
	acf_add_options_page(array(
		'menu_title'	=> __('Theme Settings', 'nexdinero'),
		'menu_slug' 	=> 'theme-general-settings',
		'redirect'		=> true
	));
	acf_add_options_sub_page(array(
		'page_title' 	=> __('Options', 'nexdinero'),
		'menu_title'	=> __('Options', 'nexdinero'),
		'parent_slug'	=> 'theme-general-settings',
	));
	acf_add_options_sub_page(array(
		'page_title' 	=> __('Developers', 'nexdinero'),
		'menu_title'	=> __('Developers', 'nexdinero'),
		'parent_slug'	=> 'theme-general-settings',
	));

	acf_add_options_page(array(
		'page_title' 	=> 'Wall of offers',
		'menu_title'	=> 'Wall of offers',
		'menu_slug' 	=> 'wall-of-offers',
		'position' 		=> '3.4',
		'capability'	=> 'manage_options',
		'icon_url' 		=> 'dashicons-admin-site',
		'redirect'		=> false
	));
}