<?php 
/**
 * ERP CONNECTOR RemoteAction Helper
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

function erpc_ra_getCID() {
	global $current_user;
	return get_user_meta($current_user->ID, 'erpc_cid', true);
}

function erpc_ra_init(&$error, $check_cid = true) {
	global $erpcOptions;
	$error = FALSE;
	
	$cid = erpc_ra_getCID();
	$server = $erpcOptions['server'];
	$username = $erpcOptions['username'];
	$password = $erpcOptions['password'];
	$authkey = $erpcOptions['authkey'];
	
	if ( empty($server)
			|| empty($username)
			|| empty($password)
			|| empty($authkey) ) {
				$error = __("Incomplete option settings", "erpc");
				return;
	}
	
	if ( $check_cid === TRUE 
	     && empty($cid) ) {
		$error = __("Unknown Contractor id", "erpc");
		return;
	}
	
	return new erpc_RemoteAction($server, $username, $password, $authkey, "ERPC_WORDPRESS");

}

function erpc_ra_hello($ra, &$error) {
	$hello = $ra->Hello();
	$error = FALSE;
	
	if ( $hello->status->success ) {
		if ( $hello->ver_major < 3
			 || (  $hello->ver_major == 3 
			 	   && $hello->ver_minor < 6 ) ) {
			$error = sprintf(__("Incomplete server version. Installed: %d.%d Required: %s", "erpc"), $hello->ver_major, $hello->ver_minor, "3.6");
		}
	} else {
		$error = $hello->status->message;
	}
	
	return $hello;
}

function erpc_ra_invoices($ra, $cid, $from_date, &$error) {
	
	$error = FALSE;
	erpc_ra_hello($ra, $error);
	if ( $error !== FALSE ) {
		return null;
	}
	
	$inv = $ra->Invoices($cid, $from_date);
	if ( $inv->status->success !== TRUE ) {
		$error = $inv->status->message; 
		return null;
	}
	
	return $inv;
}

?>