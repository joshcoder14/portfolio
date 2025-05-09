<div class="pop-out-content">
    <p>
        <span class="waitlist-length"><?php echo esc_html(idhelix_waitlist_length() > 0 ? idhelix_waitlist_length() : '0'); ?></span>
        <?php 
            $login_action = is_user_logged_in() ? __('Sign up', 'idf') : __('Login', 'idf');      
            echo esc_html(sprintf(
                /* translators: %s: "Sign up" or "Login" depending on user status */
                __('People are on the Helix waiting list. %s to reserve your spot!', 'idf'), 
                $login_action
            )); 
        ?>
    </p>
    <div class="<?php echo esc_attr(is_user_logged_in() ? 'helix-popup-logo' : ''); ?>">
        <?php echo '<img src="' . esc_url(plugins_url('images/helix-logo-hover-proper.png', dirname(__FILE__))) . '" >'; ?>
    </div>
    <?php if (is_user_logged_in()) { ?>
        <div class="helix-popup-logo-link" data-id="<?php echo esc_attr(get_current_user_id()); ?>">
            <?php 
                echo '<a href="#" class="' . esc_attr(!idhelix_user_waitlisted() ? 'unlisted' : '') . '"><img src="' . esc_url(plugins_url(idhelix_user_waitlisted() ? 'images/helix-join-saved.png' : 'images/helix-join.png', dirname(__FILE__))) . '" ></a>'; 
            ?>
        </div>
    <?php } ?>
</div>