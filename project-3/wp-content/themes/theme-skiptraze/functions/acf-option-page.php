<?php
// Add ACF options page
if( function_exists('acf_add_options_page') ) {
    acf_add_options_page(array(
        'menu_title'    => __('Theme Settings', 'skiptraze'),
        'menu_slug'     => 'theme-general-settings',
        'redirect'      => true
    ));

	acf_add_options_sub_page(array(
		'page_title' 	=> __('Options', 'skiptraze'),
		'menu_title'	=> __('Options', 'skiptraze'),
		'parent_slug'	=> 'theme-general-settings',
	));

	acf_add_options_sub_page(array(
		'page_title' 	=> __('Developers', 'skiptraze'),
		'menu_title'	=> __('Developers', 'skiptraze'),
		'parent_slug'	=> 'theme-general-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> false,
	));

}