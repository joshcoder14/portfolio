<?php

function register_theme_menus() {
	register_nav_menus(
		array(
			'main-menu' => esc_html__( 'Main Menu', 'skiptraze' ),
			'footer-menu' => esc_html__( 'Footer Menu', 'skiptraze' ),
			'footer-right-menu' => esc_html__( 'Footer Right Menu', 'skiptraze' )
		)
	);
}

add_action( 'init', 'register_theme_menus' );