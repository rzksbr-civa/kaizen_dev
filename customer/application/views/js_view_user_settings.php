<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<script>
	$('body').on('click', '#btn-do-change-password', function() {
		resetFormErrorState();
		var hasError = false;
		var currentPassword = $('#input_current_password').val();
		var newPassword = $('#input_new_password').val();
		var confirmNewPassword = $('#input_confirm_new_password').val();
		
		var errorDetails = [];
		
		$('input.form-control').each(function(){
			if($(this).hasClass('input_required') && !$.trim($(this).val()).length) {
				hasError = true;
				errorDetails.push({'error_field':$(this).attr('name'), 'error_message':'<?php echo htmlspecialchars(lang('message__this_field_is_required')); ?>'});
			}
		});
		
		if(newPassword.length > 0 && newPassword.length < 8) {
			hasError = true;
			errorDetails.push({'error_field':'new_password', 'error_message':'<?php echo htmlspecialchars(lang('message__password_must_be_at_least_8_characters')); ?>'});
		}
		
		if(confirmNewPassword != newPassword) {
			hasError = true;
			errorDetails.push({'error_field':'confirm_new_password', 'error_message':'<?php echo htmlspecialchars(lang('message__password_does_not_match')); ?>'});
		}
		
		if(hasError) {
			showInlineErrorMessage(errorDetails);
		}
		else {

			$.post('<?php echo base_url('api/user/change_password'); ?>', { 
				current_password: currentPassword,
				new_password: newPassword,
				confirm_new_password: confirmNewPassword}, function(result) {
				if(result.success) {
					$('.form_control_change_password').val('');
					$('#feedback_change_password').html('<?php echo htmlspecialchars(lang('message__password_changed_successfully')); ?>');
					$('#feedback_change_password').removeClass('hidden').slideDown('fast');
				}
				else {
					showInlineErrorMessage(result.error_details);
				}
			}, "json" );
		}
	});
	
	$('body').on('click', '.show_hide_password', function() {
		var state = $(this).attr('state');
		var field = $(this).attr('field');
		
		if(state == 'open') {
			state = 'close';
			$(this).attr('state', state);
			$('#show_hide_'+field).removeClass('glyphicon-eye-open');
			$('#show_hide_'+field).addClass('glyphicon-eye-close');
			$('#input_'+field).attr('type', 'text');
		}
		else {
			state = 'open';
			$(this).attr('state', state);
			$('#show_hide_'+field).removeClass('glyphicon-eye-close');
			$('#show_hide_'+field).addClass('glyphicon-eye-open');
			$('#input_'+field).attr('type', 'password');
		}
	});
	
	function resetFormErrorState() {
		$('input.form-control,select.form-control').each(function(){
			$('.form_group_change_password').removeClass('has-error');
			$('.help_block_change_password').html('');
		});
		
		$('#feedback_change_password').html('');
		$('#feedback_change_password').hide();
	}
	
	function showInlineErrorMessage(errorDetails) {
		for(var i=0; i<errorDetails.length; i++) {
			var errorDetail = errorDetails[i];
			$('#form_group_'+errorDetail['error_field']).addClass('has-error');
			$('#help_block_'+errorDetail['error_field']).html(errorDetail['error_message']);
		}
	}
</script>