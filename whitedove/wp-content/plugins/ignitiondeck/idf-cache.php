<?php

/**
 * Cache an object with a specified transient expiration time.
 *
 * This function caches an object with a specified transient expiration time. It triggers
 * the 'idf_cache_object' action hook before caching the object.
 *
 * @param string $transient The name of the transient.
 * @param mixed $object The object to be cached.
 * @param int $exp The expiration time in seconds. Default is 3600 seconds.
 * @return void
 */
function idf_cache_object($transient = '', $object = null, $exp = 3600) {
	do_action('idf_cache_object', $transient, $object, $exp);
}

/**
 * Retrieve an object from the cache.
 *
 * This function retrieves an object from the cache using the specified transient name.
 *
 * @param string $transient The name of the transient.
 * @return mixed|null The cached object if found, null otherwise.
 */
function idf_get_object($transient) {
	$cache = new IDF_Cache;
	return $cache->idf_get_object($transient);
}

/**
 * Flush an object from the cache.
 *
 * This function flushes an object from the cache using the specified transient name.
 *
 * @param string $transient The name of the transient to be flushed.
 * @return void
 */
function idf_flush_object($transient) {
	$cache = new IDF_Cache;
	return $cache->idf_flush_object($transient);
}

/**
 * Handle AJAX request to flush an object from the cache.
 *
 * This function handles the AJAX request to flush an object from the cache. It sanitizes
 * the input transient and then calls the idf_flush_object function to perform the
 * flushing.
 *
 * @return void
 */
function idf_flush_object_ajax() {
	// Check if the user has the required capability
	if (!current_user_can('manage_options')) {
		wp_send_json_error(__('You do not have sufficient permissions to perform this action.', 'memberdeck'));
		exit;
	}

	if (isset($_GET['wp_id_nonce'])) {
		check_admin_referer('wp_id_nonce', 'wp_id_nonce');
	}
	
	if (isset($_POST['object'])) {
		$transient = sanitize_text_field($_POST['object']);
		idf_flush_object($transient);
	}
	exit;
}

add_action('wp_ajax_idf_flush_object', 'idf_flush_object_ajax');
?>