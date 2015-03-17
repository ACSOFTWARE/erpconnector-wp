<?php
/**
 * Plugin Name: ERP CONNECTOR
 * Description: Access to ERP systems ( Wf-Mag, CDN Optima, CDN XL, SubiektGT, Sage Symfonia, Hermes SQL, Corax, Navireo, Enova, PCBiznes, Polpress.pl, Faktury Express, Elisoft Faktury, Raks SQL, ODL, PolkaSQL, Fin7 )
 * Version: 1.0
 * Author: AC SOFTWARE SP. Z O.O.
 * Author URI: http://www.acsoftware.pl
 * Author E-mail: p.zygmunt@acsoftware.pl
 * Requires at least: 4.0
 * Tested up to: 4.1
 * 
 * Text Domain: erpc
 * Domain Path: /lang
 * 
 * @package ERPC
 * @category Core
 * @author ACSOFTWARE SP. Z O.O. (p.zygmunt@acsoftware.pl)
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function erpc_plugin_url() {
	return plugin_dir_url( __FILE__ );
}

function erpc_plugin_dir() {
	return dirname( __FILE__ );
}

function erpc_upload_dir() {
	return apply_filters('erpc_upload_dir', wp_upload_dir()['basedir'].'/erpc');
}

register_activation_hook( __FILE__, array( 'ERPC_Install', 'install' ) );

include_once 'includes/erpc-functions.php';
include_once 'includes/erpc-hooks.php';
include_once 'includes/erpc-db.php';
include_once 'includes/erpc-remoteaction.php';
include_once 'includes/erpc-ra-helper.php';
include_once 'includes/class-erpc-invoice.php';
include_once 'includes/class-erpc-ajax.php';
include_once 'includes/class-erpc-install.php';
include_once 'includes/erpc-admin.php';
include_once 'includes/erpc-user.php';
include_once 'includes/erpc-woocommerce-extensions.php';

