<?php
/**
 * ERP CONNECTOR ERPC_AJAX
 *
 * AJAX Event Handler
 *
 * @class 		ERPC_AJAX
 * @version		1.0
 * @package		ERPC/Classes
 * @category	Class
 * @author 		AC SOFTWARE SP. Z O.O. [p.zygmunt@acsoftware.pl]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class ERPC_AJAX {

	const PERIODIC_TIME = 600;
	const SECS_BACK = 31536000; // 365 days back
	
	public static function init() {
		add_action( 'wp_ajax_erpc_download_invoices', array(__CLASS__, 'download_invoices'));
		add_action( 'wp_ajax_erpc_download_invoice_pdf', array(__CLASS__, 'download_invoice_pdf'));
	}
	

	public static function download_invoice_pdf() {
		
		$post_id = intval(@$_POST['post_id'], 0);
		$inv = wp_erpc_Invoice::getInvoice(get_post($post_id));

		if ( $inv != null ) {

			$file = $inv->file_path();

			
			if ( $file && !file_exists($file) ) {
				$lck_name = __FUNCTION__.'_'.md5($file);
				
				$error = FALSE;
				$ra = erpc_ra_init($error, false);
				if ( $error !== FALSE ) {
					wp_send_json_error($error);
					return;
				}
				
				if ( !erpc_lock_wait($lck_name, true) ) {
					wp_send_json_error(__("Operation in progress","erpc"));
					return;
				}
				
				if ( !file_exists($file) ) {

					$result = $ra->InvoiceDOC($inv->shortcut);
					if ( $result->totalsize > 0
						 && strlen($result->data) === $result->totalsize ) {
								
						 file_put_contents($file, $result->data);
					}
					
				}
					
				erpc_unlock($lck_name);
			}
			

			if ( $file && file_exists($file) ) {
				wp_send_json(array('success' => true, 'result' => "OK"));
				return;
			}
			
		}
		
		wp_send_json_error(__("Document don't exists","erpc"));
				
	}
	
	public static function download_invoices() {
		global $current_user;
		$changed = FALSE;
		$wait = @$_POST['wait'] == '1';

		$error = FALSE;
		$ra = erpc_ra_init($error);
		if ( $error !== FALSE ) {
			wp_send_json_error($error);
			return;
		}

		$cid = erpc_ra_getCID();
		$lck_name = __FUNCTION__.'_'.md5($cid);
		$last_ts = erpc_get_timestamp($lck_name);
		$ts = time()-$last_ts;
		
		if ( $ts < ERPC_AJAX::PERIODIC_TIME ) {
	        $ts = ERPC_AJAX::PERIODIC_TIME - $ts;
			wp_send_json_error(sprintf(__("Operation possible for %d sec.","erpc"), $ts));
			return;
		}
		
		erpc_set_current_timestamp($lck_name);
					
		if ( !erpc_lock_wait($lck_name, $wait) ) {
			wp_send_json_error(__("Operation in progress","erpc"));
			return;
		}
		
		if ( $last_ts > ERPC_AJAX::SECS_BACK ) {
			$last_ts-=ERPC_AJAX::SECS_BACK;
		}
		
		$inv = erpc_ra_invoices($ra, $cid, $last_ts, $error);
		if ( $error !== FALSE 
			 || $inv == null ) {
			 wp_send_json_error($error);
		} else {
		

			$x=0;
			$rc = $inv->recordCount();
			for($a=0;$a<$rc;$a++) {
				$invoice = $inv->getRecord($a);
				if ( $invoice != null
					 && !empty($invoice->shortcut) ) {
					 	
					 	$my_invoice = new wp_erpc_Invoice();
					 	$my_invoice->assign($invoice);
					 	$my_invoice->pdf_hash = md5(strtolower($cid.$invoice->shortcut));
					 	
					 	$args=array(
					 			'post_type' => 'erpc_invoice',
					 			'post_status' => 'private',
					 			'numberposts' => 1,
					 			'author'    =>  $current_user->ID,
					 			'meta_query' => array(
					 					array(
					 							'key' => 'shortcut',
					 							'value' => $invoice->shortcut,
					 					)
					 			)
					 	);
					 	
					 	$post = get_posts($args);

					 	if ( is_array($post)
					 		 && count($post) > 0  ) {
					 		 
					 		$post = $post[0];
					 		$content = serialize($my_invoice);
					 		
					 		if ( $content != $post->post_content ) {
					 			
					 			$inv_post = array(
					 					'ID'        => $post->ID,
					 					'post_content'   => serialize($my_invoice));
					 				
					 			
					 			wp_update_post($inv_post);
					 			update_post_meta($post->ID, "eon", $invoice->externalordernumber);
					 			$changed = TRUE;
					 		}

					 	    
					 	} else {
					 		$inv_post = array(
					 				'post_content'   => serialize($my_invoice),
					 				'post_name'      => $invoice->shortcut,
					 				'post_title'     => $invoice->number,
					 				'post_status'    => 'private',
					 				'post_type'      => 'erpc_invoice',
					 				'ping_status'    => 'closed');
					 		
					 		if ( $invoice->dateofissue > 0 ) {
					 			$inv_post['post_date'] = date('Y-m-d H:i:s', $invoice->dateofissue);
					 		}
					 
					 		$post_id = wp_insert_post($inv_post);
					 		if ( $post_id > 0 ) {
					 			add_post_meta($post_id, "shortcut", $invoice->shortcut, true);
					 			add_post_meta($post_id, "eon", $invoice->externalordernumber, true);
					 		}
					 		$changed = TRUE;
				
					 	} 	
				}
				
			}
			
			
		}
		
		erpc_unlock($lck_name);
		$result['message'] = 'OK';

		if ( $changed ) {
			$result['table'] = wp_erpc_Invoice::getHTMLList(false);
		}
		
		wp_send_json(array('success' => true, 'result' => $result));
		
	}
}

ERPC_AJAX::init();
?>