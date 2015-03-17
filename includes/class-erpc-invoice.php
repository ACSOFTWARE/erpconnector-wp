<?php
/**
 * ERP CONNECTOR wp_erpc_Invoice
 *
 *
 * @class 		wp_erpc_Invoice
 * @version		1.0
 * @package		ERPC/Classes
 * @category	Class
 * @author 		AC SOFTWARE SP. Z O.O. [p.zygmunt@acsoftware.pl]
 */

class wp_erpc_Invoice {
	
	var $dateofissue;
	var $number;
	var $paid;
	var $paymentmethod;
	var $remaining;
	var $shortcut;
	var $termdate;
	var $totalgross;
	var $totalnet;
	var $uptodate;
	var $visible;
	var $externalordernumber;
	var $pdf_hash;
	
	
	function assign($src) {
		if ( is_a($src, 'erpc_Invoice') ) {
			$this->dateofissue = $src->dateofissue;
			$this->number = $src->number;
			$this->paid = $src->paid;
			$this->paymentmethod = $src->paymentmethod;
			$this->remaining = $src->remaining;
	    	$this->shortcut = $src->shortcut;
	    	$this->termdate = $src->termdate;
	    	$this->totalgross = $src->totalgross;
	    	$this->totalnet = $src->totalnet;
	    	$this->uptodate = $src->uptodate;
	    	$this->visible = $src->visible;
	    	$this->externalordernumber = $src->externalordernumber;
		}
	}
	
	
	public static function getInvoice($post) {
		if ( $post != null
			 && is_a($post, 'WP_Post') ) {
		  $inv = unserialize($post->post_content);
		  if ( $inv != null
		  	   && is_a($inv, 'wp_erpc_Invoice') ) {
		   	return $inv;
		  }
		}
		
		return null;
	}
	
	public static function getList() {
		global $current_user;
		
		$args = array(
				'author'    =>  $current_user->ID,
				'orderby'          => 'post_date',
				'order'            => 'DESC',
				'post_type'        => 'erpc_invoice',
				'post_status'      => 'private'
		);
		
		$posts = get_posts($args); 

		foreach($posts as $key=>$post) {
			$inv = wp_erpc_Invoice::getInvoice($post);
			if ( $inv ) {
				$posts[$key] = array('invoice' => $inv, 'post' => $posts[$key]);
			} else {
				unset($posts[$key]);
			}
		}
		
		return apply_filters('erpc_invoice_list', $posts);
	}
	
	public static function getHTMLList($include_header = true) {
		
		$invoices = wp_erpc_Invoice::getList();
		setlocale(LC_MONETARY, get_locale());

		$html = '';
		if ( $include_header ) {
			$html .= '<div class="erpc_invoice_header"><h2>'.__('Invoices', 'erpc').'</h2><div class="erpc_load2"></div></div>';
		}
		
		$html .= '<table class="erpc_invoice_list" id="erpc_invoices"><thead><tr>';
		$html .= '<th class="invoice-number"><span>'.__('Number', 'erpc').'</span></th>';
		$html .= '<th class="invoice-date"><span>'.__('Date', 'erpc').'</span></th>';
		$html .= '<th class="invoice-pm"><span>'.__('Payment method', 'erpc').'</span></th>';
		$html .= '<th class="invoice-td"><span>'.__('Term date', 'erpc').'</span></th>';
		$html .= '<th class="invoice-tg"><span>'.__('Total gross', 'erpc').'</span></th>';
		$html .= '<th class="invoice-remain"><span>'.__('Remain', 'erpc').'</span></th>';
		$html .= '<th class="invoice-pdf"><span>PDF</span></th>';
		$html .= '</tr><tbody>';
		
		foreach($invoices as $invoice) {
			
			$post = $invoice['post'];
			$invoice = $invoice['invoice'];
			
			$ihtml = '<tr class="invoice">';
			$ihtml .= '<td class="invoice-number"><span>'.$invoice->number.'</span></td>';
			$ihtml .= '<td class="invoice-date"><span>'.date('Y-m-d', $invoice->dateofissue).'</span></td>';
			$ihtml .= '<td class="invoice-pm"><span>'.$invoice->paymentmethod.'</span></td>';
			$ihtml .= '<td class="invoice-td"><span>'.date('Y-m-d', $invoice->termdate).'</span></td>';
			$ihtml .= '<td class="invoice-tg"><span>'.money_format('%.2n', $invoice->totalgross).' zł</span></td>';
			$ihtml .= '<td class="invoice-remain"><span>'.money_format('%.2n', $invoice->remain).' zł</span></td>';
			$ihtml .= '<td class="invoice-pdf"><div data-id="'.$post->ID.'" data-name="'.$post->post_name.'" class="erpc_invoice_pdf"></div></div></td>';
			
			$ihtml .= '</tr>';
			$html .= $ihtml;
		}
		
		$html .= '</tbody></table>';
		
		return apply_filters('erpc_invoice_html_list', $html, $invoices, $include_header);
	}
	
	function file_path() {
		return empty($this->pdf_hash) ? null : erpc_upload_dir().'/'.$this->pdf_hash.'.pdf';
	}
	

}
?>