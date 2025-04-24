<?php

function prestaluz_custom_menu() {
    register_nav_menus(
        array(
            'header-menu' => __('Header Menu'),
            'footer-menu' => __('Footer Menu'),
            'policy-menu' => __('Policy Menu'),
            'dashboard-menu' => __('Dashboard Menu')
        )
    );
}
add_action('init', 'prestaluz_custom_menu');