<div class="wrap">
    <div class="dev_tools_header">
        <h1><?php echo esc_html__('Dev Tools', 'idf'); ?></h1>
        <p>
            <a class="openLBGlobal idf_php_info_click" href=".idc_lightbox"><?php echo esc_html__('Show PHP Info', 'idf'); ?></a>
        </p>
        <div class="idf_php_info idc_lightbox mfp-hide">
            <div class="idc_lightbox_wrapper">
                <?php echo wp_kses_post($php_info); ?>
            </div>
        </div>
    </div>
</div>