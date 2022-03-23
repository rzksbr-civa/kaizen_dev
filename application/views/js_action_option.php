<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<script>
	$('body').on('click', '.action-option', function() {
		if(!$(this).hasClass('disabled') && !$(this).hasAttr('disabled')) {
			var confirmed = true;
			if($(this).hasAttr('confirmation_message')) {
				confirmed = confirm($(this).attr('confirmation_message'));
			}
			
			if(confirmed) {
				$(this).attr('disabled', true);
				$('#modal-please-wait').modal('show');
				
				var command = $(this).attr('command');

				if(command === 'call_chchdb_api' || command === 'call_project_api') {
					$.post('<?php echo base_url('api/do_action'); ?>', {
						command: command,
						action_name: $(this).attr('action_name'),
						entity_name: $(this).attr('entity'),
						data_id: $(this).attr('data_id')}, function(result) {
						if(result.success) {
							if(result.callback_action == 'reload') {
								location.reload();
							}
							else if(result.callback_action == 'redirect') {
								window.location = result.redirect_url;
							}
						}
						else {
							if(result.error_message != null) {
								alert(result.error_message);
							}
							else {
								alert('Error');
							}
						}
						
						$(this).removeAttr('disabled');
						$('#modal-please-wait').modal('hide');
					}, "json" );
				}
				else if(command === 'show_custom_modal') {
					$('#custom-modal').modal('show');
					$.post('<?php echo base_url('api/get_rendered_custom_modal'); ?>', {
						modal_name: $(this).attr('modal_name'),
						entity_name: $(this).attr('entity'),
						data_id: $(this).attr('data_id')}, function(result) {
							$('#custom-modal-title').html(result.modal_title);
							$('#custom-modal-body').html(result.modal_body);
							$('#custom-modal-footer').html(result.modal_footer);
							$('[data-toggle="tooltip"]').tooltip();
							
							$(this).prop('disabled', false);
							$('#modal-please-wait').modal('hide');
					}, "json" );
				}
			}
		}
		else {
			if($(this).hasAttr('disabled_reason')) {
				alert($(this).attr('disabled_reason'));
			}
		}
	});
</script>