<?php
if (!wp_next_scheduled('schedule_twicedaily_idf_cron')) {
	wp_schedule_event(time(), 'twicedaily', 'schedule_twicedaily_idf_cron');
}

/**
 * Schedule installation of a plugin.
 *
 * This function schedules the installation of a plugin by activating it using the
 * provided path. It requires the 'wp-admin/includes/plugin.php' file and uses the
 * 'activate_plugin' function to activate the plugin.
 *
 * @param string $path The file path of the plugin to be installed.
 * @return void
 */
function idf_schedule_install($path) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	activate_plugin($path);
}

add_action('idf_schedule_install', 'idf_schedule_install');
?>