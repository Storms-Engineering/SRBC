<?php 
//Remove any databases and options that we setup
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
//In some circumstances I would want to delete these tables.  But probably during plugin maintenance I wouldn't want this to happen
//global $wpdb;
//$wpdb->query("DROP TABLE IF EXISTS srbc_camps,srbc_registration,srbc_campers");

?>