<?php
function nxdinero_custom_menu() {
    register_nav_menus(
        array(
            'policy-menu' => __('Policy Menu')
        )
    );
}
add_action('init', 'nxdinero_custom_menu');