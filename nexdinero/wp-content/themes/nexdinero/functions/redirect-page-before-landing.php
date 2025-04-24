<?php
add_action('template_redirect', function () {
    // Exit for admin or AJAX requests
    if (is_admin() || wp_doing_ajax()) {
        return;
    }

    if (is_front_page() && !isset($_GET['sub'])) {
        $redirect_url = home_url('/direct-wall-of-offers/');
        if (!empty($redirect_url)) {
            wp_redirect($redirect_url, 301);
            exit;
        } else {
            wp_redirect(home_url(), 301);
            exit;
        }
    }

    // Define redirect and landing page templates
    // $redirect_page_template = 'template-redirect-page.php';
    // $redirect_offer_template = 'template-redirect-landing.php';

    // Dynamically retrieve the redirect page URL
    // $redirect_page = get_posts([
    //     'post_type'  => 'page',
    //     'meta_key'   => '_wp_page_template',
    //     'meta_value' => $redirect_page_template,
    //     'numberposts' => 1,
    // ]);

    // Dynamically retrieve the redirect offer URL
    // $redirect_offer_page = get_posts([
    //     'post_type'  => 'page',
    //     'meta_key'   => '_wp_page_template',
    //     'meta_value' => $redirect_offer_template,
    //     'numberposts' => 1,
    // ]);

    // $redirect_page_url = !empty($redirect_page) ? get_permalink($redirect_page[0]->ID) : home_url('/');
    // $redirect_offer_url = !empty($redirect_offer_page) ? get_permalink($redirect_offer_page[0]->ID) : home_url('/');
    
    // Get the current page template
    // $current_template = get_page_template_slug(get_the_ID());

    // Case 1: Cookie not set, redirect to the redirect page
    // if (!isset($_COOKIE['user_continue']) && $current_template !== $redirect_page_template && $current_template !== $redirect_offer_template) {
    //     wp_redirect(!empty($redirect_page_url) ? $redirect_page_url : $redirect_offer_url);
    //     exit;
    // }

    // Case 2: On the redirect page and cookie is set, redirect to the home page
    // if (($current_template === $redirect_page_template || $current_template === $redirect_offer_template) && isset($_COOKIE['user_continue'])) {
    //     wp_redirect(home_url('?sub1=1')); // Add query parameter to flag the redirect
    //     exit;
    // }

    // Case 3: On the homepage and cookie is set, unset the cookie and redirect to the redirect page
    // Only unset the cookie if the query parameter `?sub1=1` is not present
    // if (is_front_page() && isset($_COOKIE['user_continue']) && !isset($_GET['sub1'])) {
    //     setcookie('user_continue', '', time() - 3600, '/'); // Expire the cookie
    //     wp_redirect(!empty($redirect_page_url) ? $redirect_page_url : $redirect_offer_url);
    //     exit;
    // }
});
