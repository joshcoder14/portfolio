<?php
class IDF_Cache {
	/**
	 * Constructor for IDF_Cache class.
	 * Sets the filters for caching objects and events.
	 *
	 * @param string $class The class name.
	 * @param string $method The method name.
	 */
	function __construct($class = '', $method = '') {
		$this->set_filters();
	}

	/**
	 * Set filters for caching objects and events.
	 *
	 * Adds action hooks for caching objects and events.
	 */
	private function set_filters() {
		//add_action('idf_activation', array($this, 'idf_flush_cache'));
		add_action('idf_cache_object', array($this, 'idf_cache_object'), 10, 3);
		add_action('idf_cache_object_event', array($this, 'idf_cache_object_event'), 10, 3);
	}

	/**
	 * Flush Cache
	 *
	 * Deletes all transients stored in the 'idf_transient_cache' group.
	 */
	function idf_flush_cache() {
		$transients = get_transient('idf_transient_cache');
		if (!empty($transients)) {
			foreach ($transients as $k=>$v) {
				delete_transient($k);
			}
		}
	}

	/**
	 * Cache Object
	 *
	 * Caches an object with a specified transient key and expiration time.
	 *
	 * @param string $transient The transient key.
	 * @param mixed $object The object to be cached.
	 * @param int $exp The expiration time in seconds. Default is 3600 seconds.
	 */
	function idf_cache_object($transient = '', $object = null, $exp = 3600) {
		set_transient($transient, $object, $exp);
	}

	/**
	 * Cache Object Event
	 *
	 * Caches an event object with a specified transient key and expiration time.
	 *
	 * @param string $transient The transient key.
	 * @param mixed $object The event object to be cached.
	 * @param int $exp The expiration time in seconds.
	 */
	function idf_cache_object_event($transient, $object, $exp) {
		set_transient($transient, $object, $exp);
	}

	/**
	 * Get Object
	 *
	 * Retrieves the object from the specified transient key.
	 *
	 * @param string $transient The transient key.
	 * @return mixed The object retrieved from the transient.
	 */
	function idf_get_object($transient) {
		return get_transient($transient);
	}

	/**
	 * Flush Object
	 *
	 * Deletes a transient object from the cache using the specified transient key.
	 *
	 * @param string $transient The transient key to delete.
	 */
	function idf_flush_object($transient) {
		delete_transient($transient);
	}
}
$cache = new IDF_Cache(); 
?>