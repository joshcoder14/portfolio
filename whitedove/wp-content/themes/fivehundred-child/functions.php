<?php

/**
 * Include all php files in functions folder
 */
foreach (glob(dirname(__FILE__) . "/functions/*.php") as $filename) {
    require_once($filename);
}
