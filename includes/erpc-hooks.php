<?php 
/**
 * ERP CONNECTOR Hooks
 *
 *
 * @version		1.0
 * @package		ERPC/Hooks
 * @category	Hooks
 * @author 		AC SOFTWARE SP. Z O.O. (p.zygmunt@acsoftware.pl)
 */


add_action( 'init', 'erpc_init' );
function erpc_init() {
	
	load_plugin_textdomain( 'erpc', false, erpc_plugin_dir() . '/lang' ); 

	$args = array(
			'label'              => 'Invoice',
			'public'             => false,
			'publicly_queryable' => true,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'query_var'          => true,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'custom-fields', 'page-attributes', 'editor' )
	);

	$result = register_post_type( 'erpc_invoice', $args );

	add_shortcode('erpc_invoice_list', 'erpc_invoice_list');
}

add_action( 'wp_enqueue_scripts', 'erpc_enqueue_scripts' );
function erpc_enqueue_scripts() {
	wp_enqueue_style( 'erpc', erpc_plugin_url().'assets/css/erpc.css');
	wp_enqueue_script( 'erpc-script', erpc_plugin_url().'assets/js/erpc.js', array(), '1.0.0', true );
}

add_filter('template_include', 'erpc_invoice_template');
function erpc_invoice_template( $template ) { 

    if ( get_query_var('post_type') == 'erpc_invoice' ) { 
       $template = $single_template = dirname( __FILE__ ) . '/erpc-invoice-template.php';
    } 

    return $template; 
} 

function erpc_invoice_list() {
	return wp_erpc_Invoice::getHTMLList();
}

?>