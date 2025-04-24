    <footer class="footer grid place-items-center text-white">
        <div class="footer_container overflow-hidden p-px relative">
            <div class="glow inset-0 w-100 h-100 absolute rotate-45"></div>
            <div class="footer_wrapper space-y-2 bg-theme z-10 relative">
                <div class="footer_logo">
                    <a href="<?php echo home_url(); ?>">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo/logo.svg" alt="">
                    </a>  
                </div>
                <div class="contact">
                    <div class="company_name">
                        <?php the_field('company_name','options'); ?>
                    </div>
                    <div class="registration">
                        <?php the_field('registered_number','options'); ?>
                    </div>
                    <div class="address">
                        <?php the_field('address','options'); ?>
                    </div>
                    <a href="mailto:<?php the_field('email_address','options'); ?>" class="email_add">
                        <?php the_field('email_address','options'); ?>
                    </a>
                </div>
                <div class="terms_and_policies">
                    <?php
                        if ( has_nav_menu( 'policy-menu' ) ) {
                            wp_nav_menu( array(
                                'theme_location'  => 'policy-menu'
                            ) );
                        }
                    ?>     
                </div>
            </div>
        </div>
    </footer>
    <?php wp_footer(); ?>
</body>
</html>