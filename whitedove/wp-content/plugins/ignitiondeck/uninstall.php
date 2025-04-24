/**
 * Uninstall script for removing IgnitionDeck plugins.
 *
 * This script checks for active IgnitionDeck plugins and prevents uninstallation if any are active.
 * It retrieves the list of active plugins and checks for specific IgnitionDeck plugins. If any are found,
 * it displays a message to the user and prevents the uninstallation process.
 *
 * @package IgnitionDeck
 */

<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

$active_plugins = get_option('active_plugins');

$all_plugins = get_plugins();

$flag = false;

$names = '';

if(in_array('idcommerce/idcommerce.php',$active_plugins) || array_key_exists('idcommerce/idcommerce.php',$all_plugins)) {
    $names .= 'IgnitionDeck Commerce, ';
    $flag = true;
}
if(in_array('ignitiondeck-crowdfunding/ignitiondeck.php',$active_plugins) || array_key_exists('ignitiondeck-crowdfunding/ignitiondeck.php',$all_plugins)) {
    $names .= 'IgnitionDeck Crowdfunding, ';
    $flag = true;
}

$names = rtrim ($names , ",");

if($flag) {
    // Translators: %s: Comma-separated list of plugin names
    $message = __('It looks like you are deleting the IgnitionDeck plugins. Please delete %s first to proceed with removing IgnitionDeck', 'ignitiondeck');
    wp_die(sprintf(esc_html($message), esc_html($names)));
}