<?php

function fivehundred_child_enqueue_styles() {
    if(file_exists(get_stylesheet_directory().'/assets/css/app-style.css')){
        wp_enqueue_style( 'app-style', get_stylesheet_directory_uri().'/assets/css/app-style.css' );
    }
    wp_enqueue_style( 'fivehundred-child-style', get_stylesheet_directory_uri().'/style.css' );
    
    wp_enqueue_style( 'swiper-min-style', get_stylesheet_directory_uri().'/assets/css/swiper-bundle.min.css' );
}

add_action( 'wp_enqueue_scripts', 'fivehundred_child_enqueue_styles', 200 );

function js_script(){
    wp_enqueue_script('app-js',get_stylesheet_directory_uri().'/assets/js/app.js', array ('jquery'), filemtime( get_stylesheet_directory() . '/assets/js/app.js' ), true);
    
    // Mixitup
    wp_enqueue_script('mixitup_min_js',get_stylesheet_directory_uri().'/assets/js/mixitup.min.js', array ('jquery'), filemtime( get_stylesheet_directory() . '/assets/js/mixitup.min.js' ), true);
    wp_enqueue_script('gallery_js',get_stylesheet_directory_uri().'/assets/js/gallery.js', array ('jquery'), filemtime( get_stylesheet_directory() . '/assets/js/gallery.js' ), true);

    // Swiper
    wp_enqueue_script('swiper_bundle_js',get_stylesheet_directory_uri().'/assets/js/swiper-bundle.min.js', array ('jquery'), filemtime( get_stylesheet_directory() . '/assets/js/swiper-bundle.min.js' ), true);
    wp_enqueue_script('swiper_js',get_stylesheet_directory_uri().'/assets/js/swiper.js', array ('jquery'), filemtime( get_stylesheet_directory() . '/assets/js/swiper.js' ), true);
}

add_action( 'wp_enqueue_scripts', 'js_script', 200 );
?>