
</div>
</div>
<div class="page-loader">
    <div class="loader">Loading...</div>
</div>
<footer class="footer parallax">
    <div class="footer_container">
        <h1 class="footer_title">
            <?php the_field('footer_title','options');?>
        </h1>  
        <div class="footer_content">
            <div class="content_right">
                <?php if( have_rows('contact_information','options') ): ?>
                    <?php while( have_rows('contact_information','options') ): the_row(); ?>
                        <div class="list_item">
                            <span>
                                <?php the_sub_field('contact_text','options'); ?>
                            </span>
                        </div>

                        <div class="list_item">
                            <a href="mailto:<?php the_sub_field('email_address','options'); ?>">
                                <?php the_sub_field('email_address','options'); ?>
                            </a>
                        </div>

                        <div class="list_item">
                            <a href="tel:<?php the_sub_field('contact_number','options'); ?>">
                                <?php the_sub_field('contact_number','options'); ?>
                            </a>
                        </div>

                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
            <div class="content_left">
                <?php if( have_rows('contact_address','options') ): ?>
                    <?php while( have_rows('contact_address','options') ): the_row(); ?>
                        <div class="list_item">
                            <div class="text">
                                <?php the_sub_field('content_text','options'); ?>
                            </div>
                        </div>
                        <div class="list_item">
                            <div class="address">
                                <?php the_sub_field('address_text','options'); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="bussiness_identity">
            <div class="registered_number">
                <?php the_field('registration_number','options');?>
            </div>
            <div class="account_identifier">
                <?php the_field('account_identifier','options');?>
            </div>
        </div>
    </div>
    
	<div class="footer_copyright" id="copyright">
        Copyright &copy; <?php echo wp_date('Y'); ?> 
        <span><?php bloginfo('name'); ?></span>
	</div>
	<div class="clear"></div>
</footer>

<?php wp_footer(); ?>

<a href="#" class="top fadeInUp">
    <img src="<?php echo get_stylesheet_directory_uri()?>/assets/images/arrow-up.svg" alt="">
</a>
</body>
</html>