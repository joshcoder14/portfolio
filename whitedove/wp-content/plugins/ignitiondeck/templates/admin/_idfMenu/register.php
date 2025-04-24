<h2>Register with IgnitionDeck.com</h2>
<p>Why register?</p>
<ul>
    <li>Premium support</li>
    <li>Product updates and feature announcements</li>
    <li>Access to downloadable modules</li>
</ul>
<p>
    Email
    <input type="text" 
           class="register-email <?php echo esc_attr(get_option('idf_registered_email') ? 'registered' : ''); ?>" 
           placeholder="Your best email address" 
           value="<?php echo esc_attr(get_option('idf_registered_email')); ?>" />
</p>
<input type="hidden" name="idf_activate_plugins_nonce" value="<?php echo esc_attr(wp_create_nonce('idf-activate-plugins-nonce')); ?>"/>
<p>
  <button type="button" class="wiz-button" onclick="wizard_action('register_email')" <?php echo get_option('idf_registered_email') ? 'disabled=""' : ''; ?>>
    <?php echo get_option('idf_registered_email') ? 'Registered' : 'Register'; ?>
  </button>
</p>

<a class="skip" href="#wiz-install" onclick="idWizardScreen('#wiz-install')">Skip this step</a>