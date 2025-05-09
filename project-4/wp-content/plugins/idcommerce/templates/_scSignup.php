<li class="md-box half">
	<div class="md-profile stripe-settings">
		<h3><?php _e('Stripe', 'memberdeck') ?></h3>
		<?php do_action('ide_above_sc_signup'); ?>
		<p><?php _e('Sign up with the Stripe payment gateway to get paid directly to your own account. The process is simple, and there are no setup or monthly fees. Click Connect with Stripe to get started.', 'memberdeck') ?></p>
		<a class="stripe-connect" <?php echo (empty($check_creds) ? 'href="'.$redirect_url.'"' : ''); ?> class="<?php echo (isset($button_style) ? $button_style : ''); ?>">
			<span><?php echo (!empty($check_creds) ? '<i class="fa fa-check"></i> '.__('Connected!', 'memberdeck') : __('Connect with Stripe', 'memberdeck')); ?></span>
		</a>
		<?php do_action('ide_below_sc_signup'); ?>
	</div>
</li>