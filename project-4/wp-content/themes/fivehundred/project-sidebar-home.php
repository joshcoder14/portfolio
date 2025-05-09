<?php
$options = get_option('fivehundred_theme_settings');
if (isset($options['home'])) {
	$project_id = $options['home'];
	if (class_exists('ID_Project')) {
		$project = new ID_Project($project_id);
		$id = $project->get_project_postid();
		$settings = getSettings();
	}
}
$new_hdeck = new Deck($project_id);
$hDeck = $new_hdeck->hDeck();
$prefix = idf_get_querystring_prefix();
?>
<aside id="sidebar">
<h3 id="ign-levels-headline"><?php the_title(); ?> <?php _e('Support Levels', 'fivehundred'); ?></h3>
<div id="ign-product-levels" data-projectid="<?php echo $project_id; ?>">
	<?php do_action('id_before_levels', $project_id); ?>
	<?php get_template_part('loop', 'levels-home'); ?>
	<?php do_action('id_after_levels', $project_id); ?>
</div>
<div class="ign-supportnow mobile">
	<?php if ($hDeck->end_type == 'open' || $hDeck->days_left > 0) { ?>
 		<a href="<?php the_permalink().$prefix; ?>purchaseform=500&amp;prodid=<?php echo $project_id; ?>"><?php _e('Support Now', 'fivehundred'); ?></a>
 	<?php } ?>
</div>
<?php if (isset($settings) && $settings->id_widget_logo_on == 1) {
	echo '<div id="poweredbyID"><span><a href="http://www.ignitiondeck.com" title="Crowdfunding Wordpress Theme by IgnitionDeck"></a></span></div>';
} ?>
<?php if ( is_active_sidebar('projects-widget-area') ) : ?>
<div id="primary" class="widget-area">
	<ul class="sid">
		<?php dynamic_sidebar('projects-widget-area'); ?>
	</ul>
</div>
<?php endif; ?>
</aside>