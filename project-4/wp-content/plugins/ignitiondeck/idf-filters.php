<?php
//add_filter('id_modules', 'idf_modules_require');
/**
 * Require registered modules.
 *
 * This function checks if the user is registered and returns the modules if so.
 *
 * @param array $modules The array of modules to be checked.
 * @return array|null Returns the modules if the user is registered, null otherwise.
 */
function idf_modules_require($modules) {
	if (!idf_registered()) {
		return null;
	}
	return $modules;
}
?>