<?php
/**
 * Get the list of active plugins.
 *
 * This function retrieves the list of active plugins based on whether the WordPress
 * installation is a multisite or single site. If it's a multisite, it retrieves the
 * active plugins from the site option 'active_sitewide_plugins'. Otherwise, it retrieves
 * the active plugins from the option 'active_plugins'.
 *
 * @return array The list of active plugins.
 */
function idf_active_plugins() {
	if (is_multisite()) {
		$active_plugins = get_site_option('active_sitewide_plugins');
	} else {
		$active_plugins = get_option('active_plugins');
	}
	return $active_plugins;
}

global $active_plugins;
$active_plugins = idf_active_plugins();
?>