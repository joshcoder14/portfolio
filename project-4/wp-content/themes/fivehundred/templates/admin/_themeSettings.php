<div class="icon32"></div><h2 class="title"><?php _e('500 Theme Settings', 'ignitiondeck'); ?></h2>
	<div class="help">
		<a href="http://forums.ignitiondeck.com" alt="IgnitionDeck Support" title="IgnitionDeck Support" target="_blank"><button class="button button-large"><?php _e('Support', 'ignitiondeck') ?></button></a>
		<a href="http://docs.ignitiondeck.com" alt="IgnitionDeck Documentation" title="IgnitionDeck Documentation" target="_blank"><button class="button button-large"><?php _e('Documentation', 'ignitiondeck')?></button></a>
	</div>
<br style="clear: both;"/>
<div class="wrap">
	<div class="postbox-container" style="width:95%; margin-right: 5%">
		<div class="metabox-holder">
			<div class="postbox"><!-- 
				<h3 class="hndle"><span><?php _e('500 Theme', 'fivehundred'); ?></span></h3> -->
					<form method="POST" action="" id="fh_theme_settings">
						<table class="form-table">
							<tbody>
								<?php if (has_action('fh_logo_setting')) { ?>
								<tr class="fd_logo">
									<td>
									<label for="logo-input"><h2><?php _e('Replace Site Name with Your Logo', 'fivehundred'); ?></h2></label>
									<p class="logo-text"><?php _e('Maximum image dimensions are 300 pixels wide and/or 50 pixels high.', 'fivehundred'); ?><br/><span style="font-size: .85em;"><?php _e('Note: If you upload an image larger than this, it will automatically resize the image to fit, however we highly recommend you control the image dimensions prior to upload.', 'fivehundred'); ?></span></p>
										<div class="uploader">
			  								<input type="text" name="logo-input" id="logo-input" value="<?php echo ($logo ? $logo : ''); ?>"/>
			  								<input type="button" class="button" name="logo-upload" id="logo-upload" value="Upload" /><br/>
			  								<span id="logo-preview"><?php echo ($logo ? '<img src="'.$logo.'"/>' : ''); ?></span>
										</div>
										
									</td>
								</tr>
								<?php } ?>
								<?php if (has_action('fh_home_project_layout_setting')) { ?>
								<tr class="fd_home_project">
									<td>
										<label for="home-project" class="home-project"><?php _e('Home Page Layout', 'fivehundred'); ?></label><br/>
										<?php echo $levels; ?>
									</td>
								</tr>
								<?php } ?>
								<?php if (has_action('fh_home_project_count_setting')) { ?>
								<tr class="fd_home_projects">
									<td>
										<label for="home-projects"><?php _e('Number of Projects to Display on Home Page', 'fivehundred'); ?></label><br/>
										<input type="number" id="home-projects" name="home-projects" class="regular-text" value="<?php echo ($home_projects ? $home_projects : ""); ?>"/>
									</td>
								</tr>
								<?php } ?>
								<?php if (has_action('fh_featured_project_setting')) { ?>
								<tr class="fd_featured">
									<td>
										<label for="choose-featured" class="choose-featured"><?php _e('Featured Project on Home Page', 'fivehundred'); ?></label><br/>
										<select id="choose-featured" name="choose-featured">
											<option><?php _e('No Feature', 'fivehundred'); ?></option>
											<?php 
											foreach ($projects as $project) {
												$a_project = new ID_Project($project->id);
												$post_id = $a_project->get_project_postid();
												$post = get_post($post_id);
												if (!empty($post)) {
													$selected = null;
													if (isset($project_id) && $project_id == $project->id) {
														$selected = 'selected="selected"';
													} ?>
													<option value="<?php echo $project->id; ?>" <?php echo (isset($selected) ? $selected : ''); ?>><?php echo get_the_title($post_id); ?></option>
												<?php }
											} ?>
										</select>
								</tr>
								<?php } ?>
								<?php if (has_action('fh_blog_page_setting')) { ?>
								<tr class="fd_blog_page">
									<td>
										<label for="blog_page"><?php echo __('Blog Page', 'fivehundred'); ?></label><br/>
										<select name="blog_page" id="blog_page">
										<?php 
											$pages = get_pages();
										    foreach ($pages as $page) {
										       echo '<option value="'.$page->ID.'" '.(isset($settings['blog_page']) && $settings['blog_page'] == $page->ID ? 'selected="selected"' : '').'>'.$page->post_title.'</option>';
										    }
										?>
										</select>
									</td>
								</tr>
								<?php } ?>
								<tr>
									<td>&nbsp;</td>
								</tr>
								<?php if (has_action('fh_about_setting')) { ?>
								<tr class="fd_about_us">
									<td>
										<label for="about-us"><h2><?php _e('About Us Text', 'fivehundred'); ?></h2></label>
										<p><?php _e('This will appear below your featured projects on the home page.', 'fivehundred'); ?></p>
										<?php echo wp_editor(($about ? $about : ""), 'about-us', array(
											'media_buttons' => true,
											'textarea_rows' => 10,
											'tinymce' => true)); 
										?>
									</td>
								</tr>
								<?php } ?>
								<?php if (has_action('fh_custom_css_setting')) { ?>
								<tr class="fd_custom_css">
									<td>
										<label for="custom_css"><h2><?php _e('Custom CSS', 'fivehundred'); ?></h2></label>
										<p><?php _e('Enter custom CSS here.', 'fivehundred'); ?></p>
										<textarea name="custom_css" id="custom_css"><?php echo (isset($custom_css) ? $custom_css : ''); ?></textarea>
									</td>
								</tr>
								<?php } ?>
								<?php do_action('fivehundred_extra_fields'); ?>
								<tr>
									<td>
										<input type="submit" id="submit-theme-settings" name="submit-theme-settings" class="btn button" value="<?php _e('Save Settings', 'fivehundred'); ?>"/>
									</td>
								</tr>
							</tbody>
						</table>
					</form>
			</div>
		</div>
	</div>
</div>