(function($){

	$(function(){

		$('.single-import').on('click', function(e){
			e.preventDefault();

			var form  = $('#import-phorest-products'),
				rowEl = $(this).parents('tr')
				row   = rowEl.data('row');

			form.block({message: null});

			$.ajax({
				url : ajaxurl,
				method: 'POST',
				data: {
					action: 'wcph_single_import',
					barcode: row.barcode,
					nonce: wcph_admin_args.nonce,
					branch_id: $('#ph_branch_selection_top').val()
				},
				success: function(response){
					console.log($(response.data.product));
					rowEl.replaceWith( $(response.data.product) );
				},
				complete: function(){
					form.unblock();
				}
			});
		});

	});

})(jQuery)