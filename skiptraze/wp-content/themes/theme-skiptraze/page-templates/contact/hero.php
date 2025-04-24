<div class="contact">
    <div class="container">
        <div class="image">
            <img src="<?php echo get_template_directory_uri(); ?>/images/bg-white.svg" class="image-1" alt="image">
            <img src="<?php echo get_template_directory_uri(); ?>/images/bg-blue.svg" class="image-2" alt="image">
            <img src="<?php echo get_template_directory_uri(); ?>/images/bg-black.svg" class="image-3" alt="image">
        </div>
        <img src="<?php echo get_template_directory_uri(); ?>/images/bg-white.svg" class="image-1" alt="image">
        <div class="container-2">
            <div class="content-2">
                <div class="contact-title">
                    <?php the_field('contact_title');?>
                </div>
                <div class="subtext">
                    <?php the_field('contact_subtext');?>
                </div>
            </div>
            <form id="contact-form" action="#">
                <div class="form">
                    <div class="row">
                        <div class="col">
                            <div class="form-group field-ContactForm-first_name required">
                                <label class="control-label" for="ContactForm-first_name"><?php the_field('first_name_label');?></label>
                                <input type="text" id="ContactForm-first_name" class="form-control text-only" name="ContactForm[first_name]" placeholder="<?php echo the_field('first_name_placeholder');?>" aria-required="true" >
                
                                <div class="help-block" id="error_first_name"></div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-group field-ContactForm-last_name required">
                                <label class="control-label" for="ContactForm-last_name"><?php the_field('last_name_label');?></label>
                                <input type="text" id="ContactForm-last_name" class="form-control text-only" name="ContactForm[last_name]" placeholder="<?php echo the_field('last_name_placeholder');?>"  aria-required="true">
                                <div class="help-block" id="error_last_name"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col company">
                            <div class="form-group field-ContactForm-company required">
                                <label class="control-label" for="ContactForm-company_name"><?php the_field('company_label');?></label>
                                <input type="text" id="ContactForm-company" class="form-control" name="ContactForm[company]" placeholder="<?php echo the_field('company_placeholder');?>"  aria-required="true">
                                <span class="text-right"><?php the_field('company_option');?></span>
                                <div class="help-block" id="error_company"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row input-box-2">
                        <div class="col">
                            <div class="form-group field-ContactForm-message required">
                                <label class="control-label" for="ContactForm-message"><?php the_field('message_label');?></label>
                                <textarea name="ContactForm[message]" id="ContactForm-message" class="form-control"  placeholder="<?php echo the_field('message_placeholder');?>" aria-required="true"></textarea>
                                <span class="text-counter">300/300</span>
                                <div class="help-block" id="error_message"></div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="button submit_button">
                        <div class="loading"></div>
                        <div class="label-4"><?php the_field('submit_button_label');?></div>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>