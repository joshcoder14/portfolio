<header class="header">
    <div class="header_container">
        <div class="logo_area">
            <a href="<?php echo get_home_url(); ?>" class="menu_logo">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo/logo.svg" class="logo" alt="prestaluz logo">
                <!-- LOGO -->
            </a>
        </div>
        <div class="navigation">
            <?php
                if ( has_nav_menu( 'header-menu' ) ) {
                    wp_nav_menu( array(
                        'theme_location'  => 'header-menu',
                        'menu_id'         => 'header-menu',
                        // 'menu_class'      => 'footer-menu-class',
                        // 'container'       => 'div',
                        // 'container_class' => 'footer-wrapper',
                    ) );
                }
            ?>     
        </div>
        <div class="navigation header_right_menu">
            <ul>
                <li>
                    <a href="<?php echo get_home_url() . '/contactanos-online/'; ?>">Contacto</a>
                </li>
                <li>
                    <a class="menu_registro" id="menu_registro" href="<?php echo get_home_url() . '/application-form/'; ?>">Registro</a>
                </li>
                <li>
                    <a href="<?php echo get_home_url() . '/iniciar-sesion/'; ?>" class="btn btn_primary login">Mi Cuenta</a>
                </li>
            </ul>
        </div>
        <div class="mobile_menu">
            <div class="menu_registro">
                <a style="text-decoration: none; color:#363531" href="<?php echo get_home_url() . '/application-form/'; ?>"><span id="registro_menu">Registro</span></a>
            </div>
            <div class="menu_btn">
                <span id="open_menu">Menu</span>
            </div>
        </div>
        
        <div class="mobile_menu_container">
            <?php
                if ( has_nav_menu( 'header-menu' ) ) {
                    wp_nav_menu( array(
                        'theme_location'  => 'header-menu',
                        'menu_id'         => 'header-menu',
                        // 'menu_class'      => 'footer-menu-class',
                        // 'container'       => 'div',
                        // 'container_class' => 'footer-wrapper',
                    ) );
                }
            ?>     
            <ul>
                <li>
                    <a href="<?php echo get_home_url() . '/contactanos-online/'; ?>">Contacto</a>
                </li>
                <li>
                    <a id="menu_registro" href="<?php echo get_home_url() . '/application-form/'; ?>">Registro</a>
                </li>
                <li>
                    <a href="<?php echo get_home_url() . '/iniciar-sesion/'; ?>" class="btn btn_primary login">Mi Cuenta</a>
                </li>
            </ul>
        </div>
        
    </div>
</header>

    
    