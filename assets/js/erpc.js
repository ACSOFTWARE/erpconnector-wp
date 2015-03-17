(function($) {
	$(document).ready(function() {
	
		if ( $( "#erpc_invoices" ).length ) {
			
            var data = {
                    action: 'erpc_download_invoices'
            };
          
            $.ajax( {type: "POST", url: ajax_object.ajax_url, data: data } )
            .done(function( response ) {
            	$('.erpc_load2').remove();
            	
            	if ( response.success ) {
                	if ( response.result['message'] == 'OK'
                		 && response.result['table'] != undefined ) {
                		$('.erpc_invoice_list').replaceWith(response.result['table']);
                		$('.erpc_invoice_pdf').on('click',download_item);
                	} 
                	
            	}

            });
		}
		
		$('.erpc_invoice_pdf').on('click',download_item);
	   
	});
	
	

	function download_item(e){
		
		var l = $(this).data('locked');
		
		if ( l == undefined || l  == false  ) {
			
			var btn = $(this);
			btn.data('locked', true);
			btn.removeClass("erpc_invoice_pdf");
			btn.addClass("erpc_load1");

            var data = {
                    action: 'erpc_download_invoice_pdf',
                    post_id: btn.data('id')
               };
          
               $.ajax( {type: "POST", url: ajax_object.ajax_url, data: data } )
               .done(function( response ) {
            	   
            	   btn.removeClass("erpc_load1");
            	   btn.addClass("erpc_invoice_pdf");
            	   btn.data('locked', false);
            	   
            	   if (response.success && response.result == 'OK' ) {
            		   window.location.href = '/?erpc_invoice='+btn.data('name');
            	   }
               })
			
			
		}

	}
	
})(jQuery);