<?php 
/**
 * ERP CONNECTOR Lock Functions
 *
 *
 * @version		1.0
 * @package		ERPC/Functions
 * @category	Functions
 * @author 		AC SOFTWARE SP. Z O.O. (p.zygmunt@acsoftware.pl)
 */

if ( ! defined( 'ABSPATH' ) ) {
        exit;
}

$erpcOptions = get_option("erpc_options");

if ( empty($erpcOptions["authkey"]) ) {


	$erpcOptions["authkey"] = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
			mt_rand(0, 65535),
			mt_rand(0, 65535),
			mt_rand(0, 65535),
			mt_rand(16384, 20479),
			mt_rand(32768, 49151),
			mt_rand(0, 65535),
			mt_rand(0, 65535),
			mt_rand(0, 65535));

	update_option("erpc_options", $erpcOptions);

}

function erpc_lock($name, $timeout_sec = 90) {
	global $current_user;	
	return erpc_db_do_lock($name, $timeout_sec, $current_user->ID) == '1';
}

function erpc_lock_wait($name, $wait = true, $timeout_sec = 90) {
	
  $result = FALSE;
  $timeout = $wait === TRUE ? intval($timeout_sec, 0)+1 : -1;
	
  do {
  	
  	$result = erpc_lock($name, $timeout_sec);
  	$timeout--;
  	
  	if ( $result === FALSE )
  		sleep(1);
  	
  } while($result === FALSE && $timeout >= 0);
  
  return $result;
} 

function erpc_unlock($name) {
	erpc_db_unlock($name);
}

function erpc_get_timestamp($name) {
	return intval(erpc_db_get_timestamp($name), 0);
}

function erpc_set_current_timestamp($name) {
	erpc_db_set_current_timestamp($name);
}

function erpc_upload_url() {
	return apply_filters('erpc_upload_url', wp_upload_dir()['baseurl'].'/erpc');	
}



?>