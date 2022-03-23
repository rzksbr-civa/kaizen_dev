<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<script>
	$(document).ready(function() {
		$('#trigger-add-image').on('click', function(e) {
			clear_add_image_form();
			$('#add-image-field-entity_name').val($(this).data('entity_name'));
			$('#add-image-field-data_id').val($(this).data('data_id'));
		});
		
		$('#form-add-image').on('submit', function(e) {
			e.preventDefault();
			var data = new FormData(this);
			
			$('#modal-please-wait').modal('show');
			
			$.ajax({
				url: $(this).attr('action'),
				type: $(this).attr('method'),
				cache: false,
				data: new FormData(this),
				success: function(result) {
					$('#modal-please-wait').modal('hide');
					
					if(result.success) {
						$('#modal-add-image').modal('hide');
						$('#no-image-message').hide();
						$('.images-area').append('<div class="image-area" id="image-area-'+result.image_id+'"><img class="image" src="'+result.image_path+'" image_id="'+result.image_id+'"></div>');
					}
					else {
						alert(result.error_message);
					}
				},
				dataType: 'json',
				processData: false,
				contentType: false
			});
		});
		
		$('body').on('click', '.image', function(e) {
			$('#detail-image').attr('src', $(this).attr('src'));
			$('#trigger-delete-image').attr('delete_image_id', $(this).attr('image_id'));
			$('#modal-view-image').modal('show');
		});
		
		$('body').on('click', '#trigger-delete-image', function(e) {
			var confirmed = confirm('<?php echo lang('message__are_you_sure_you_want_to_delete_this_image'); ?>');
			if(confirmed) {
				var image_id = $(this).attr('delete_image_id');
				$.post('<?php echo base_url('api/delete_image'); ?>', { 
					image_id: image_id}, function(result) {
						if(result.success) {
							$('#modal-view-image').modal('hide');
							$('#image-area-'+image_id).fadeOut('slow');
						}
						else {
							alert(result.error_message);
						}
				},'json');
			}
		});
	});
	
	function clear_add_image_form() {
		$('#add-image-field-image').val('');
	}
</script>

