<?php 
/**
 * ERP CONNECTOR Database Functions
 *
 *
 * @version		1.0
 * @package		ERPC/Functions
 * @category	Database
 * @author 		AC SOFTWARE SP. Z O.O. (p.zygmunt@acsoftware.pl)
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function erpc_db_do_lock($name, $timeout_sec, $user_id) {
	global $wpdb;
	return $wpdb->get_var($wpdb->prepare( "SELECT ".$wpdb->prefix."erpc_do_lock (%s, %d, %d) AS `result`", $name, $timeout_sec, $user_id));
}

function erpc_db_unlock($name) {
	global $wpdb;
	$wpdb->delete($wpdb->prefix."erpc_locks", array( 'lock_name' => $name ), array( '%s' ) );
}

function erpc_db_get_timestamp($name) {
	global $wpdb;
	return $wpdb->get_var($wpdb->prepare( "SELECT UNIX_TIMESTAMP(ts) FROM ".$wpdb->prefix."erpc_ts WHERE action = %s", $name));	
}

function erpc_db_set_current_timestamp($name) {
	global $wpdb;
	$wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix."erpc_ts (action) VALUES (%s) ON DUPLICATE KEY UPDATE ts=CURRENT_TIMESTAMP", $name));
}


?>