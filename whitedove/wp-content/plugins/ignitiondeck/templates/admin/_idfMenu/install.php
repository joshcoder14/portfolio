<h2>Install Dependencies</h2>
<p>IgnitionDeck has two dependencies that must be installed before you may proceed:</p>
<?php
$all_plugins = get_plugins();
$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
?>
<ul>
    <?php
    $plugins = array();
    $plugins['idc'] = array(
        'name'	=>	'ID Commerce',
        'slug'	=>	'idcommerce/idcommerce.php',
        'url'	=>	'https://files.ignitiondeck.com/idc_latest.zip'
    );
    $plugins['idcf'] = array(
        'name'	=>	'IgnitionDeck Crowdfunding',
        'slug'	=>	'ignitiondeck-crowdfunding/ignitiondeck.php',
        'url'	=>	'https://files.ignitiondeck.com/idcf_latest.zip'
    );
    $flag = $ins = $act = true;
    foreach($plugins as $plugin) {
        if ( isset( $all_plugins[$plugin['slug']] ) )  {
            $status = 'Not Active';
            $installed = 'installed';
        } else {
            $installed = 'not';
            $status = 'Not Installed';
            $ins = false;
        }
        if ( in_array( $plugin['slug'], $active_plugins ) ) {
            $status = 'Installed and Activated';
            $installed = 'active';
        } else {
            $act = false;
        }
        ?>
        <li data-status="<?php echo esc_attr($installed); ?>" 
            data-name="<?php echo esc_attr($plugin['name']); ?>" 
            data-slug="<?php echo esc_attr($plugin['slug']); ?>" 
            data-url="<?php echo esc_url($plugin['url']); ?>">
            <?php echo esc_html($plugin['name']); ?> - [<span><?php echo esc_html($status); ?></span>]
        </li>
        <?php 
    }
    if(!$ins) {
        $flag = false;
        $button_text = 'Install and Activate Dependencies';
    } elseif (!$act) {
        $button_text = 'Activate Dependencies';
        $flag = false;
    }
    ?>
</ul>
<?php if(!$flag) { ?>
<p class="text-center"><button type="button" class="wiz-button install" onclick="wizard_action( 'plugin_install' )"><?php echo esc_html($button_text); ?></button></p>
<?php } else { ?> 
<p class="text-center"><button type="button" class="wiz-button continue" onclick="idWizardScreen('#wiz-upgrade')">Continue</button></p>
<?php }?>
