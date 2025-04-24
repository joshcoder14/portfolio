<?php if (is_user_logged_in()) { ?>
<li><a href="<?php echo esc_url(wp_logout_url(home_url())); ?>"><?php esc_html_e('Logout', 'idf'); ?></a></li>
<?php } ?>