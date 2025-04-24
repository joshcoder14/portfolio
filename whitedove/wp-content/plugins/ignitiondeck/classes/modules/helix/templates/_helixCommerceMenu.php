<?php do_action('helix_above_commerce_menu'); ?>
<li><a href="<?php echo esc_url($params['durl']); ?>"><?php esc_html_e('Dashboard', 'idf'); ?></a></li>
<li><a href="<?php echo esc_url($params['edit_profile_url']); ?>"><?php esc_html_e('Account', 'idf'); ?></a></li>
<li><a href="<?php echo esc_url($params['orders_url']); ?>"><?php esc_html_e('Order History', 'idf'); ?></a></li>
<?php do_action('helix_below_commerce_menu'); ?>