
<?php
$tabs = array( 'Register', 'Install', 'Upgrade', 'Themes', 'Configure', 'Connect' );
$tabs = apply_filters( 'wizard_tabs', $tabs );
?>
<div class="wizard-box">

    <ul class="wizard-tab">
        <?php
        foreach($tabs as $k=>$tab) {
            $slug = strtolower(str_replace(' ','-',$tab));
            ?>
            <li class="<?php echo $k==0?'active':''?>"><a href="#wiz-<?php echo esc_attr($slug)?>"><span><i class="wiz-icon icon-<?php echo esc_attr($slug)?>"></i> <?php echo esc_html($tab)?></span></a></li>
            <?php
        }
        ?>
    </ul>

    <div class="wizard-tabs">
        <?php
        foreach($tabs as $k=>$tab) {
            $slug = strtolower(str_replace(' ','-',$tab));
            ?>
            <div id="wiz-<?php echo esc_attr($slug)?>" class="wizard-tab-content <?php echo $k==0?'active':''?>">
                <?php require_once('_idfMenu/'.$slug.'.php'); ?>
            </div>
            <?php
        }
        ?>
    </div>

</div>
<div id="wiz-loader"><span>Setting Up Environment<em></em></span></div>

<div id="wiz-notice">
    <div class="wiz-notice-box">
        <h2><span id="wiz-notice-title"></span> <i class="wiz-notice-close">x</i></h2>
        <div id="wiz-notice-content"></div>
    </div>
</div>
