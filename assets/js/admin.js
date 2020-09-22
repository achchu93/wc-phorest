(function($){

	$(function(){

		$('.single-import').on('click', function(e){
			e.preventDefault();

			var row = $(this).parents('tr').data('row');

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
					console.log(response);
				}
			});
		});

	});

})(jQuery)