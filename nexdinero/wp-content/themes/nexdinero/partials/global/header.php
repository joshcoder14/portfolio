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
    <script defer data-cfasync='false' src='https://s.clickiocdn.com/t/240272_wv.js'></script>

    <!-- Boostrap -->
    
    <!-- Google Font -->
    
    <!-- main stylesheet -->
    
    <?php wp_head(); ?>
</head>
<body <?php body_class( 'body' ); ?>>
    
    