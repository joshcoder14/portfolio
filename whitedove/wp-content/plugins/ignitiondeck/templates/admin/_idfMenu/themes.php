<h2>Choose Your Theme</h2>
<div id="configure-details" class="configure-details">
    <div class="themes-list">
        <?php
        $all_themes = wp_get_themes();
        $current_theme = wp_get_theme();
        //echo '<pre>'; print_r($all_themes); exit;
        $themes = array();
        $themes[] = array (
            'name'  =>  '500 Framework',
            'slug'  =>  'fivehundred',
            'description'  =>  'Its a crowdfunding theme framework created for use with the IgnitionDeck plugin.',
            'image'  =>  '',
            'demo'  =>  'https://theme500.com/framework/',
            'doc'  =>  'https://docs.ignitiondeck.com/article/37-500-framework',
            'url'  =>  'http://files.ignitiondeck.com/fh_latest.zip',
            'locked'  =>  false,
            'required-500'  =>  false,
        );

        $themes[] = array (
            'name'  =>  '500 Settlement',
            'slug'  =>  'fivehundred-settlement',
            'description'  =>  'Settlement is here to build your non-profit website from start to finish, easing the creation and management of fundraising and crowdfunding projects for your charity.',
            'image'  =>  plugins_url( '/ignitiondeck/images/fivehundred-settlement.png' ),
            'demo'  =>  'https://theme500.com/settlement/',
            'doc'  =>  'https://docs.ignitiondeck.com/article/40-settlement',
            //'url'  =>  'https://www.ignitiondeck.com/crowdfunding/product/settlement-theme/',
            'url'  =>  'https://members.ignitiondeck.com/my-account/view-licenses/',
            'locked'  =>  true,
            'required-500'  =>  true,
        );

        $themes[] = array (
            'name'  =>  '500 Stellar',
            'slug'  =>  'fivehundred-stellar',
            'description'  =>  'The Stellar IgnitionDeck Platform Theme is for serious individuals, not-for-profit organizations and businesses, who aim to develop large-scale crowdfunding or fundraising platform.',
            'image'  =>  plugins_url( '/ignitiondeck/images/fivehundred-stellar.png' ),
            'demo'  =>  'https://theme500.com/stellar',
            'doc'  =>  'https://docs.ignitiondeck.com/article/39-stellar',
            //'url'  =>  'https://www.ignitiondeck.com/crowdfunding/product/stellar-theme/',
            'url'  =>  'https://members.ignitiondeck.com/my-account/view-licenses/',
            'locked'  =>  true,
            'required-500'  =>  true,
        );
        
        $themes[] = array (
            'name'  =>  'Multifondo',
            'slug'  =>  'multifondo',
            'description'  =>  'Multifondo is a fully responsive and mobile-read fundraising theme for WordPress.',
            'image'  =>  plugins_url( '/ignitiondeck/images/multifondo.png' ),
            'demo'  =>  'https://theme500.com/multifondo/',
            'doc'  =>  'https://docs.ignitiondeck.com/article/43-multifondo',
            //'url'  =>  'https://www.ignitiondeck.com/crowdfunding/product/multifondo-theme/',
            'url'  =>  'https://members.ignitiondeck.com/my-account/view-licenses/',
            'locked'  =>  true,
            'required-500'  =>  false,
        );
        
        $themes[] = array (
            'name'  =>  'Fundify',
            'slug'  =>  'fundify',
            'description'  =>  'Fundify is a WordPress theme and relies on the crowdfunding functionality built into the IgnitionDeck plugins.',
            'image'  =>  plugins_url( '/ignitiondeck/images/fundify.png' ),
            'demo'  =>  'https://theme500.com/fundify/',
            'doc'  =>  'https://docs.ignitiondeck.com/article/41-fundify',
            //'url'  =>  'https://www.ignitiondeck.com/crowdfunding/product/fundify-theme/',
            'url'  =>  'https://members.ignitiondeck.com/my-account/view-licenses/',
            'locked'  =>  true,
            'required-500'  =>  false,
        );
        
        foreach($themes as $theme) {
            $disabled = '';
            if(get_option('is_idc_licensed')) {
                $theme['locked'] = false;
                $status = 'Download from IgnitionDeck';
            } else {
                $status = $theme['locked']?'Upgrade to Unlock':'Download from IgnitionDeck';
            }
            if($current_theme->name == $theme['name']) {
                $disabled = 'disabled="disabled"';
                $status = 'Installed and Activated';
            } elseif(!empty( $all_themes[$theme['slug']] )) {
                $status = 'Activate';
                $theme['url'] = admin_url('themes.php?theme='.$theme['slug']);
            }
            ?>
           <div class="id-theme">
                <div class="theme-image <?php echo esc_attr($theme['locked'] ? 'locked' : ''); ?>">
                    <?php 
                    if (!empty($theme['image'])) {
                        echo '<img src="' . esc_url($theme['image']) . '" alt="' . esc_attr($theme['name']) . '">';
                    } 
                    ?>
                </div>
                <div class="theme-details">
                    <p>
                        <strong><?php echo esc_html($theme['name']); ?></strong> - <?php echo esc_html($theme['description']); ?>
                    </p>
                    <ul>
                        <li><a href="<?php echo esc_url($theme['demo']); ?>" target="_blank">View Demo</a></li>
                        <li><a href="<?php echo esc_url($theme['doc']); ?>" target="_blank">Read Documentation</a></li>
                        <?php
                        if (!empty($theme['required-500'])) {
                            echo '<li><b>' . 'Requirement: 500 Framework parent theme' . '</b></li>';
                        } else {
                            echo '<li style="list-style:none"><br></li>';
                        }
                        ?>
                    </ul>
                    <?php
                    if ($theme['locked'] || $theme['slug'] === 'fivehundred') {
                        ?>
                        <p>
                            <button 
                                data-slug="<?php echo esc_attr($theme['slug']); ?>" 
                                data-url="<?php echo esc_url($theme['url']); ?>" 
                                type="button" 
                                class="wiz-button <?php echo esc_attr($theme['locked'] ? 'locked' : ''); ?>" 
                                onclick="wizard_action('theme_install', this)" 
                                <?php echo isset($disabled) ? esc_attr($disabled) : ''; ?>>
                                <?php echo esc_html($status); ?>
                            </button>
                        </p>
                        <?php
                    } else {
                        ?>
                        <p>
                            <a 
                                href="<?php echo esc_url($theme['url']); ?>" 
                                class="wiz-button" 
                                <?php echo $status === 'Activate' ? '' : 'target="_blank"'; ?>>
                                <?php echo esc_html($status); ?>
                            </a>
                        </p>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <?php 
        }
        ?>
    </div>
    <div class="clearfix"></div>
    <p class="text-center">
        <button type="button" class="wiz-button" onclick="idWizardScreen('#wiz-configure')">Continue</button>
    </p>
</div>
