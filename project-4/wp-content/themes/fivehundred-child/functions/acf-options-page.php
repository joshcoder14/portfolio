<?php
if( function_exists('acf_add_options_page') ) {
	acf_add_options_page(array(
		'menu_title'	=> __('Theme Settings', 'whitedove'),
		'menu_slug' 	=> 'theme-general-settings',
		'redirect'		=> true
	));
	acf_add_options_sub_page(array(
		'page_title' 	=> __('Options', 'whitedove'),
		'menu_title'	=> __('Options', 'whitedove'),
		'parent_slug'	=> 'theme-general-settings',
	));
	acf_add_options_sub_page(array(
		'page_title' 	=> __('Developers', 'whitedove'),
		'menu_title'	=> __('Developers', 'whitedove'),
		'parent_slug'	=> 'theme-general-settings',
	));
}