<?php
add_action('idf_transfer_key', 'idf_transfer_key');

/**
 * Update the option for the IDF transfer key.
 *
 * This function updates the option for the IDF transfer key to 1.
 */
function idf_transfer_key() {
	update_option('idf_transfer_key', 1);
}
?>