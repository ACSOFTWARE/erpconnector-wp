<?php 
/**
 * ERP CONNECTOR Invoice Template
 *
 *
 * @version		1.0
 * @package		ERPC/Templates
 * @category	Templates
 * @author 		AC SOFTWARE SP. Z O.O. (p.zygmunt@acsoftware.pl)
 */

if ( $post->post_author == $current_user->ID ) {
	
	$inv = wp_erpc_Invoice::getInvoice($post);
	
	if ( $inv ) {
		$file = $inv->file_path();
		if ( file_exists($file) ) {
	
			header('Content-Description: File Transfer');
			header('Content-Type: application/pdf');
			header('Content-Disposition: attachment; filename='.$post->post_name.'.pdf');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			readfile($file);
			exit;
	
		}
	}
	
}


echo __("Invoice not found", "erpc");


?>