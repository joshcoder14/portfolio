<?php
global $post;
global $withcomments;
if ($post->comment_status == 'open') {
	$withcomments = 1;
}
$options = get_option('fivehundred_theme_settings');
if (isset($options['home'])) {
	$project_id = $options['home'];
	if (function_exists('getPostbyProductID')) {
		$id = getPostbyProductID($project_id);
	}
	$content = the_project_content($id);
}
?>
<div id="site-description">
	<div class="project-mini-info">
		<h1><?php echo $content->name; ?></h1>
    	<p class="project-tag-single">
			<?php
                  $terms = wp_get_post_terms( $post->ID, 'project_category');
                  if(!empty($terms)) {
                  $site_url = home_url();
                  $cat_name = "";
                   foreach($terms as $term){
                      if($term->count > 0){
                           $cat_name .= "<a href='".esc_url( $site_url )."/project-category/".$term->slug."'>".$term->name."</a>";
                           break;
                      }
                   }
                  if($term->count > 0){ echo $cat_name; }
                  }
               ?>
     	</p>
    </div>
    <div class="project-short-description">
		<h2><?php echo $content->short_description; ?></h2> 
   </div>
</div>
<?php get_template_part( 'project', 'hDeck-home' ); ?>
<div id="ign-project-content" class="ign-project-content">
<div class="entry-content content_tab_container">
		<?php get_template_part('nav', 'above-project'); ?>
		<div class="ign-content-long content_tab description_tab active">
			<?php do_action('id_before_content_description', $project_id, $id); ?>
			<?php echo apply_filters('the_content', $content->long_description); ?>
		</div>
		<div id="updateslink" class="content_tab updates_tab">
			<?php echo apply_filters('fivehundred_updates', do_shortcode( '[project_updates product="'.$project_id.'"]')); ?>
		</div>
					
		<div id="faqlink" class="content_tab faq_tab">
			<?php echo apply_filters('fivehundred_faq', do_shortcode( '[project_faq product="'.$project_id.'"]')); ?>
		</div>
		<div class="content_tab comments_tab">
			<?php comments_template('/comments.php', true); ?>
		</div>
		<?php do_action('fh_below_project', $project_id, $id); ?>
		<?php if (dynamic_sidebar('home-content-widget-area')) : ?>
		<?php endif; ?>
	</div>
	<?php get_template_part( 'project', 'sidebar-home' ); ?>
	<div class="clear"></div>
</div>