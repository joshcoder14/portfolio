<div id="finaldescTrial" class="finaldesc" style="display:none;">
<?php
	if (isset($level) && is_object($level) && isset($level->trial_length) && isset($level->trial_type)) {
		echo apply_filters('idc_trial_description', __(sprintf('Charges will begin upon the completion of a %d %s trial period', $level->trial_length, $level->trial_type), 'memberdeck'));

    } else {
        echo __('Trial information is not available', 'memberdeck');
    }
	?>
</div>