<?php do_action('helix_above_crowdfunding_menu'); ?>
<li><a href="<?php echo esc_url($params['backer_profile_url'] . $current_user->ID); ?>"><?php esc_html_e('Backer Profile', 'idf'); ?></a></li>
<?php if (is_id_pro() && current_user_can('create_edit_projects')) { ?>
    <li><a href="<?php echo esc_url($params['creator_profile_url'] . $current_user->ID); ?>"><?php esc_html_e('Creator Profile', 'idf'); ?></a></li>
    <?php if (idc_creator_settings_enabled()) { ?>
        <li><a href="<?php echo esc_url($params['creator_settings_url']); ?>"><?php esc_html_e('Creator Settings', 'idf'); ?></a></li>
    <?php } ?>
    <li><a href="<?php echo esc_url($params['my_projects_url']); ?>"><?php echo esc_html($project_count > 0 ? __('My Projects', 'idf') : __('Create Project', 'idf')); ?></a></li>
<?php } ?>
<?php do_action('helix_below_crowdfunding_menu'); ?>