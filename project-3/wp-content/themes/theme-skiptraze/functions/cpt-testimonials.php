<?php

/*
* Creating a ACF Options CPT
*/
acf_add_options_page(array(
	'page_title' 	=> 'Testimonials',
	'menu_title'	=> 'Testimonials',
	'menu_slug' 	=> 'testimonials',
	'position' => '2.1',
	'capability'	=> 'edit_posts',
	'icon_url' => 'dashicons-format-quote',
	'redirect'		=> false
));
