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

    