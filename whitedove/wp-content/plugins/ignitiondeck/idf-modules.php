<?php
/**
 * Get active modules.
 *
 * This function retrieves the active modules using the ID_Modules class and returns them.
 *
 * @return array An array containing the active modules.
 */
function idf_get_modules() {
	return ID_Modules::get_active_modules();
}
?>