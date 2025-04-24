<?php
$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );

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
$installed = 'active';
foreach($plugins as $plugin) {
    if ( in_array( $plugin['slug'], $active_plugins ) ) {
        //$installed = 'active';
    } else {
        $installed = 'not';
    }
}

$license = 'free';
if(get_option('is_idc_licensed')) {
    $license = 'echelon';
    if(get_option('is_id_pro')) {
        $license = 'enterprise';
    }
} else {
    $license = get_option('is_id_basic') ?? 'free';
}
if(empty($license)) {
    $license = 'free';
}
$idc_checked = get_option('idf_commerce_platform')=="idc"?'checked="checked"':'';
$woo_checked = get_option('idf_commerce_platform')=="wc"?'checked="checked"':false;
if(!get_option('idf_commerce_platform') || !$woo_checked) {
    $idc_checked = 'checked="checked"';
}
$payments = '<strong>Which e-commerce platform will you use for payments?</strong>
<ul class="payment-platform">
<li><input type="radio" name="payment-platform" value="idc" '.$idc_checked.' /> IgnitionDeck Commerce</li>';
$wc_class = '';
$wc_disabled = '';
$wc_title = '';
if ( !in_array( 'woocommerce/woocommerce.php', $active_plugins ) ) {
    $wc_class = 'disabled';
    $wc_disabled = 'disabled="disabled"';
    $wc_title = '<i>? <ul>
        <li>The WooCommerce plugin must be installed and activated to be selected.</li>
        <li>WooCommerce can be used with the lgnitionDeck Echelon version of the plugin.</li>
        <li>For more information, see <a href="https://docs.ignitiondeck.com/article/133-woocommerce-ignitiondeck" target="_blank">IgnitionDeck & WooCommerce documentation</a></li>
    </ul></i>';
}
$enterprise_notice = $license=='enterprise'?'notice="true"':'notice="false"';
$payments .= '<li class="'.$wc_class.'"><input type="radio" name="payment-platform" value="wc" '.$woo_checked.' '.$wc_disabled.' '.$enterprise_notice.' /> WooCommerce '.$wc_title.'</li>';
$payments .= '</ul>';

$payments .= '<div class="button-group">';
if(get_option('idf_commerce_platform')) {
    $payments .= '<button type="button" class="wiz-button" onclick="idWizardScreen(\'#wiz-themes\')">Continue</button>';
} else {
    $payments .= '<button type="button" class="wiz-button" onclick="wizard_action(\'save_payment\',this)">Save</button>';
}
$payments .= '<div class="clearfix"></div></div>';

$echelon = '<h3>IgnitionDeck Echelon</h3>
<ul>
<li>A year of code updates and support</li>
<li>Raise funds with credit cards and Bitcoin</li>
<li>Recurring payments</li>
<li>Includes All Premium Themes</li>
<li>Optional modules like Stretch Goals and affiliate sales</li>
<li>Optional WooCommerce integration<br><br><br></li>
</ul>';

$enterprise = '<h3>IgnitionDeck Enterprise</h3>
<ul>
<li>A year of code updates and support</li>
<li>Raise funds with credit cards and Bitcoin</li>
<li>Front end creator account and project management</li>
<li>Fee splitting and creator payment accounts</li>
<li>Includes All Premium Themes</li>
<li>Includes all modules like Fee Mods and Donations</li>
</ul>';

$button_text = 'Activate';
$license_expiry = get_option('license_expiry');
$item_id = get_option('license_item_id');
$license_post_id = get_option('license_post_id');

if(empty($license) || $license=='free') {
    $button_text = 'Activate';
    $expiry = '<p>You are currently using the <b>Free Edition</b> of IgnitionDeck. Click <a href="https://members.ignitiondeck.com/my-account/view-licenses/" target="_blank">here</a> if you have already purchased it.</p>';
} else {
    $button_text = 'Activated';
    $expiry = '<p>You are currently using the <b>'.ucfirst($license).' Edition</b> of IgnitionDeck.<br>Your license will renew automatically on ['. gmdate('F d, Y', strtotime($license_expiry)) .']</p>';
}
if($license_expiry=='no_activations_left') {
    $button_text = 'Activate';
    $item_id = get_option('license_item_id');
    $expiry = '<p>Activation limit exceeded. Please remove unused domains to activate here. <a href="https://members.ignitiondeck.com/checkout/purchase-history/?action=manage_licenses&payment_id='.get_option('license_payment_id').'&license_id='.get_option('id_license_key').'" target="_blank">Manage License</a></p>';
} elseif($license_expiry=='expired') {
    $button_text = 'Expired';
    $item_id = get_option('license_item_id');
    $expiry = '<p>Your license is expired. Please <a href="https://members.ignitiondeck.com/?edd_action=add_to_cart&edd_license_key='.get_option('id_license_key').'&download_id='.$item_id.'" target="_blank">renew</a> your license for full functionality.</p>';
} elseif($license_expiry=='lifetime' && $license!='free') {
    $button_text = 'Activated';
    $expiry = '<p>You are currently using the <b>Lifetime '.ucfirst($license).' Edition</b> of IgnitionDeck.</p>';
} elseif($license_expiry=='missing') {
    $button_text = 'Activate';
    $expiry = '<p>License Key is <b>invalid</b>. You are currently using the <b>Free Edition</b> of IgnitionDeck. Click <a href="https://members.ignitiondeck.com/my-account/view-licenses/" target="_blank">here</a> if you have already purchased it.</p>';
}
?>
<h2>Upgrade IgnitionDeck</h2>
<div id="license-details">
    <p class="wix-form-group">
        License Key
        <input type="text" class="wiz-control-inline" placeholder="Your IgnitionDeck License Key" value="<?php echo esc_attr(get_option('id_license_key')); ?>" />
        <button type="button" class="wiz-button" onclick="wizard_action('verify_license', this)" data-license="<?php echo esc_attr($license); ?>" <?php echo ($installed === 'active') ? '' : 'disabled="disabled" data-title="Please install and activate all dependencies"'; ?>>
            <?php echo esc_html($button_text); ?>
        </button>
    </p>
    <div class="license-details">
        <?php
        switch($license) {
            case 'enterprise':
                $html = $expiry.$payments;
                break;
            case 'echelon':
                    $html = $expiry.$payments.'
                    <div class="wiz-half">
                    '.$echelon.'
                    <p><a href="#" target="_blank" class="wiz-button disabled">Echelon Edition Enabled <span class="icon"></span></a></p>
                    </div>
                    <div class="wiz-half">
                    '.$enterprise.'
                    <p><a href="https://members.ignitiondeck.com/checkout/?edd_action=sl_license_upgrade&license_id=' . $license_post_id . '&upgrade_id=3" target="_blank" class="wiz-button">Upgrade To Enterprise</a></p>
                    </div>';
                    break;
            default:  
                    $html = $expiry .
                    '<div class="wiz-half">
                    '.$echelon.'
                    <p><a href="https://members.ignitiondeck.com/?edd_action=add_to_cart&download_id=83887" target="_blank" class="wiz-button">Buy Echelon License</a></p>
                    </div>
                    <div class="wiz-half">
                    '.$enterprise.'
                    <p><a href="https://members.ignitiondeck.com/?edd_action=add_to_cart&download_id=83885" target="_blank" class="wiz-button">Buy Enterprise License</a></p>
                    </div>';
                    break;           
        }
        $allowed_tags = wp_kses_allowed_html( 'post' );
        $allowed_tags['input']=array(
            'type'        => true,
            'name'        => true,
            'value'       => true,
            'placeholder' => true,
            'class'       => true,
            'id'          => true,
            'style'       => true,
            'onclick'       => true,
            'disabled'       => true,
            'checked'       => true,
            
        );
        $allowed_tags['button']=array(
            'type'        => true,
            'name'        => true,
            'value'       => true,
            'placeholder' => true,
            'class'       => true,
            'id'          => true,
            'style'       => true,
            'onclick'       => true,
            'disabled'       => true,
            'checked'       => true,
            
        );
        
        // Use wp_kses with the custom allowed tags
        echo wp_kses($html, $allowed_tags);
        ?>
    </div>
</div>
<a class="skip" href="#wiz-themes" onclick="idWizardScreen('#wiz-themes')">Skip this step</a>