<?php

/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package cashgo
 */

get_header();
?>

<div class="page-404">
    <div class="page-404-container">
        <h1 class="error-code">404</h1>
        <p class="error-message">Page not found..</p>
        <p class="help-text">
            Oops! The page you were looking for does not exist...
        </p>
        <div class="page-404-btn">
            <a class="primary-btn" href="<?php echo get_home_url(); ?>">Return to homepage</a>
        </div>
    </div>
</div>

<?php
get_footer();
