<!DOCTYPE html>
<html>

<head>
    <title>
        <?php if (is_front_page() || is_home()) {
            echo get_bloginfo('name');
        } else {
            echo wp_title('|', true, 'right');
        } ?>

    </title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boostrap -->
    
    <!-- Google Font -->
    
    <!-- main stylesheet -->
    
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'body' ); ?>>
<?php wp_body_open(); ?>

<header class="header dashboard">
    <div class="header_container">
        <div class="logo_area">
            <a href="<?php echo get_home_url(); ?>" class="menu_logo">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo/logo.svg" class="logo" alt="prestaluz logo">
                <!-- LOGO -->
            </a>
        </div>
        <div class="navigation header_right_menu">
            <div class="dashboard-menu">
                <?php
                    if ( has_nav_menu( 'dashboard-menu' ) ) {
                        wp_nav_menu( array(
                            'theme_location'  => 'dashboard-menu',
                            'menu_id'         => 'dashboard-menu',
                            'menu_class'      => 'header-menu dashboard-menu-list',
                        ) );
                    }
                ?> 
            </div>

            <ul class="user_action">
                <li>
                    <!-- FIX ME: Name of user -->
                    <span>Petter Smith</span>
                </li>
                <div class="separator"></div>
                <li>
                    <a href="<?php echo get_home_url() . '/#/'; ?>">Cerrar sesión</a>
                </li>
            </ul>
        </div>
        <div class="mobile_menu">
            <div class="menu_btn">
                <span id="open_menu">Menu</span>
            </div>
        </div>
        
        <div class="mobile_menu_container">
            <div class="dashboard-menu">
                <?php
                    if ( has_nav_menu( 'dashboard-menu' ) ) {
                        wp_nav_menu( array(
                            'theme_location'  => 'dashboard-menu',
                            'menu_id'         => 'dashboard-menu',
                            'menu_class'      => 'header-menu dashboard-menu-list',
                        ) );
                    }
                ?> 
            </div>
            <ul class="user_action">
                <li>
                    <!-- FIX ME: Name of user -->
                    <span>Petter Smith</span>
                </li>
                <div class="separator"></div>
                <li>
                    <a href="<?php echo get_home_url() . '/#/'; ?>">Cerrar sesión</a>
                </li>
            </ul>
        </div>
        
    </div>
</header>