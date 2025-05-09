<?php

class ID_Dev_Tools {
	var $dev_mode;
	
	/**
	 * Constructor for ID_Dev_Tools class.
	 * Sets the dev mode based on the defined ID_DEV_MODE constant.
	 */
	function __construct() {
		if (defined('ID_DEV_MODE')) {
			$this->dev_mode = ID_DEV_MODE;
		}
		else {
			$this->dev_mode = false;
		}
	}

	/**
	 * Get the current dev mode setting.
	 *
	 * @return bool The current dev mode setting.
	 */
	function dev_mode() {
		return $this->dev_mode;
	}
}
?>