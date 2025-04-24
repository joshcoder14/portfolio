<?php
// background-image: url(' . get_stylesheet_directory_uri() . '/images/thgarage-logo.png);
function login_logo() { 
    echo    '<style type="text/css">
                #login h1 a, .login h1 a {
                    background-image: url(' . get_stylesheet_directory_uri() . '/assets/images/nexdinero-logo.svg);
                    width: 353px;
                    height: 201px;
                    background-size: 320px 213px;
                    background-repeat: no-repeat;
                }
            </style>';
 }
add_action( 'login_enqueue_scripts', 'login_logo' );

function login_logo_url() {
    return home_url();
}
add_filter( 'login_headerurl', 'login_logo_url' );