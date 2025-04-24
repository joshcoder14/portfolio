<?php 
if (isset($_GET['wp_id_nonce'])) {
		check_admin_referer('wp_id_nonce', 'wp_id_nonce');
}?>
<ul class="list-unstyled nav-icons">
    <?php do_action('helix_before_icon_menu'); ?>
    <li class="close-list"></li>
    <?php do_action('helix_above_icon_menu'); ?>
    <?php include '_helixIconMenu.php'; ?>
    <?php if (!empty($primary_nav)) { ?>
        <span class="helix-hamburg">
            <i class="fa fa-bars"></i>
        </span>
    <?php } ?>
    <?php do_action('helix_below_icon_menu'); ?>
    <?php do_action('helix_after_icon_menu'); ?>
    <li class="helix-logo-handler"></li>
    <span class="helix-logo">
		<?php echo wp_kses_post(apply_filters('helix_menu_logo', '<img src="' . esc_url(plugins_url('images/helix-logo.svg', dirname(__FILE__))) . '" >')); ?>
    </span>
</ul>
<ul class="nav-content list-unstyled">
    <?php do_action('helix_before_login_form'); ?>
    <li class="close-list <?php echo esc_attr($logged_in ? '' : 'login-frame'); ?>">
        <div class="media">
            <?php if ($logged_in) { ?>
                <div class="media-left">
                    <a href="<?php echo esc_url(apply_filters('helix_avatar_link', '#')); ?>" class="avatar">
                        <?php echo get_avatar($current_user->ID, 60); ?>
                    </a>
                </div>
                <div class="media-body">
                    <span class="media-heading"><?php echo esc_html(!empty($current_user->display_name) ? $current_user->display_name : $current_user->user_email); ?></span>
                    <?php if (helix_show_menu()) {
                        $user_text = apply_filters('helix_credits_display_text', '', $current_user->ID);
                        echo '<span class="helix-credit">' . esc_html($user_text) . '</span>';
                    } ?>
                </div>
            <?php } else { ?>
                <div class="media-left">
                    <a href="<?php echo esc_url(apply_filters('helix_register_url', $durl)); ?>" class="avatar">
                        <?php echo get_avatar($current_user->ID, 60); ?>
                    </a>
                </div>
                <?php if (helix_show_loggedout_menu()) { ?>
                    <div class="media-body">
                        <div class="helix-register-link"><a href="<?php echo esc_url(apply_filters('helix_register_url', $durl)); ?>"><?php echo esc_html(__('Create Account', 'idf')); ?></a></div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </li>
    <?php if (!$logged_in) { ?>
        <div class="helix-loginform">
            <?php echo wp_kses_post(do_action('helix_above_login_form')); ?>
            <?php
            $args = array(
                'echo' => false,
                'form_id' => 'helix-loginform',
                'id_submit' => 'helix-wp-submit',
                'label_log_in' => 'Login',
                'remember' => false,
                'id_username' => 'helix_login_user',
                'id_password' => 'helix_login_pass'
            );
            if (isset($_GET['helix_error']) && $_GET['helix_error'] == "login_failed") {
                $new_url = str_replace("?" . $_SERVER['QUERY_STRING'], "", (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
                $args['redirect'] = esc_url($new_url);
            }
            echo wp_login_form($args); ?>
            <?php if (isset($_GET['helix_error']) && $_GET['helix_error'] == "login_failed" && isset($_GET['framework_missing'])) { ?>
                <div class="helix-error wrong-credentials"><?php echo esc_html(__('Incorrect username or password', 'idf')); ?>
                    <div class="helix-critical-error"><strong><?php echo esc_html(__('Critical Issue', 'idf')); ?></strong>: <?php echo esc_html(__('Helix depends on IgnitionDeck Framework. Please install it first.', 'idf')); ?></div>
                </div>
            <?php } else if (isset($_GET['helix_error']) && $_GET['helix_error'] == "login_failed") { ?>
                <div class="helix-error wrong-credentials"><?php echo esc_html(apply_filters('helix_wrong_username_password_message', __('Incorrect username or password', 'idf'))); ?></div>
            <?php } ?>
            <div class="helix-error blank-field" style="display:none;"><?php echo esc_html(apply_filters('helix_username_password_empty_message', __('Username or Password should not be empty', 'idf'))); ?></div>
            <a class="forget-password" href="<?php echo esc_url(wp_lostpassword_url(home_url())); ?>"><?php echo esc_html(__('Forgot your password?', 'idf')); ?></a>
            <?php do_action('helix_below_login_form'); ?>
        </div>
    <?php } ?>
    <?php do_action('helix_after_login_form'); ?>
    <?php include_once('_helixMenuItems.php'); ?>
    <?php print_r($primary_nav); ?>
</ul>
<br />
<br />
