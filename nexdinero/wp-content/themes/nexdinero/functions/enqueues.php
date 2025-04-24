<?php

function nexdinero_enqueue_styles() {
    if(file_exists(get_stylesheet_directory().'/assets/css/app-style.css')){
        wp_enqueue_style( 'app-style', get_stylesheet_directory_uri().'/assets/css/app-style.css' );
    }
    wp_enqueue_style( 'nexdinero-style', get_stylesheet_directory_uri().'/style.css' );
    
    wp_enqueue_style( 'tailwind-style', 'https://cdn.tailwindcss.com', array(), false);
}

add_action( 'wp_enqueue_scripts', 'nexdinero_enqueue_styles', 200 );

function js_script(){
    wp_enqueue_script('app-js',get_stylesheet_directory_uri().'/assets/js/app.js', array ('jquery'), filemtime( get_stylesheet_directory() . '/assets/js/app.js' ), true);
}

add_action( 'wp_enqueue_scripts', 'js_script', 200 );
?>