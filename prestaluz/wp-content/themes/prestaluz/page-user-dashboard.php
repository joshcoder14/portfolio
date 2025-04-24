<?php

/* Template Name: User Dashboard Template */

get_header('user');
?>
<?php
    if(is_user_logged_in()){
        get_template_part('templates/user-dashboard');
    }else{
        get_template_part('templates/login');
    }
?>
<?php get_footer(); ?>