<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$rendered_add_edit_callback = '';
if(isset($add_edit_callback)) {
	foreach($add_edit_callback as $callback_function_name => $callback_args) {
		$rendered_add_edit_callback .= $callback_function_name . '();';
	}
}
?>
<script>
	$('body').on('click', '.add-edit-action-button', function() {
		resetFormErrorState();
		var hasError = false;
		var entityName = $(this).attr('entity');
		var parentEntity = $(this).attr('parent_entity');
		var dataID = $(this).attr('data_id');
		var actionMode = $(this).attr('action_mode');
		var formSource = $(this).attr('form_source');
		var errorDetails = [];
		
		if(hasError) {
			showInlineErrorMessage(errorDetails, formSource);
		}
		else {
			if(actionMode == 'add' || actionMode == 'edit') {
				$.post('<?php echo base_url('api/add_edit_item'); ?>', { 
					entity_name: entityName,
					parent_entity: parentEntity,
					action_mode: actionMode,
					data_id: dataID,
					data: $('#form_'+entityName).serializeArray()}, function(result) {
					if(result.success) {
						if(formSource == 'page') {
							if(actionMode == 'add') {
								window.location = '<?php echo base_url('db/view/'); ?>' + entityName + '/' + result.insert_id;
							}
							else if(actionMode == 'edit') {
								window.location = '<?php echo base_url('db/view/'); ?>' + entityName + '/' + dataID;
							}
						}
						else if(formSource == 'modal') {
							if(actionMode == 'add') {
								$('#modal-add-edit').modal('hide');
								$('#modal-add-edit').attr('action_mode','');

								var currentTable = $('#table-'+entityName).DataTable();
								console.log(result.added_data);
								currentTable.row.add(result.added_data).draw(false);
							}
							else if(actionMode == 'edit') {
								$('#modal-add-edit').modal('hide');
																
								var currentTable = $('#table-'+entityName).DataTable();
								currentTable.row($('tr#row-'+entityName+'-'+dataID)).data(result.updated_data).draw(false);
							}
							
							<?php echo $rendered_add_edit_callback; ?>
						}
						else {
							location.reload();
						}
					}
					else {
						console.log(result);
						showInlineErrorMessage(result.error, formSource);
					}
				}, "json" );
			}
			else if(actionMode == 'delete') {
				var confirmed = false;
				if(formSource == 'page') {
					confirmed = confirm('<?php echo addslashes(ucfirst(lang('message__are_you_sure_you_want_to_delete_this_item'))); ?>');
				}
				else if(formSource == 'modal') {
					confirmed = true;
				}
				
				if(confirmed) {
					$.post('<?php echo base_url('api/delete_item'); ?>', { 
						entity_name: entityName,
						data_id: dataID}, function(result) {
						if(result.success) {
							if(formSource == 'page') {
								window.location = '<?php echo base_url('db/view/'); ?>' + entityName;
							}
							if(formSource == 'modal') {
								$('#modal-add-edit').modal('hide');
								var currentTable = $('#table-'+entityName).DataTable();
								currentTable.row('#row-'+entityName+'-'+dataID).remove().draw(false);
								
								<?php echo $rendered_add_edit_callback; ?>
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
							
					}, "json" );
				}
			}
		}
	});
	
	$('input.form-control,select.form-control').change(function(){
		if($(this).hasClass('input_required') && !$.trim($(this).val()).length) {
			$('#form_group_'+$(this).attr('name')).addClass('has-error');
			$('#help_block_'+$(this).attr('name')).html('<?php echo addslashes(lang('message__this_field_is_required')); ?>');
		}
		else {
			$('#form_group_'+$(this).attr('name')).removeClass('has-error');
			$('#help_block_'+$(this).attr('name')).html('');
		}
	});
	
	$('body').on('keyup', 'input.input_number', function() {
		// skip for arrow keys
		if(event.which >= 37 && event.which <= 40) return;

		// format number
		var currentCaretPos = document.getElementById($(this).attr('id')).selectionEnd;
		var textLengthBefore = $(this).val().length;
		$(this).val(formatThousandSeparator($(this).val()));
		var textLengthAfter = $(this).val().length;
		setCaretPosition($(this).attr('id'),currentCaretPos - textLengthBefore + textLengthAfter);
	});
	
	$.fn.hasAttr = function(name) {  
		return this.attr(name) !== undefined;
	};
	
	function resetFormErrorState() {
		$('input.form-control,select.form-control').each(function(){
			$('#form_group_'+$(this).attr('name')).removeClass('has-error');
			$('#help_block_'+$(this).attr('name')).html('');
		});
		$('#help_block_general').addClass('hidden').html('');
	}
	
	function showInlineErrorMessage(errorDetails, formSource) {
		for(var i=0; i<errorDetails.length; i++) {
			var errorDetail = errorDetails[i];
			
			if(errorDetail['error_field'] == 'general') {
				alert(errorDetail['error_message']);
				continue;
			}
			
			$('#form_group_'+errorDetail['error_field']).addClass('has-error');
			$('#help_block_'+errorDetail['error_field']).html(errorDetail['error_message']);
			
			if(i==0) {
				if(formSource == 'page') {
					$([document.documentElement, document.body]).animate({
						scrollTop: $('#form_group_'+errorDetail['error_field']).offset().top - 60
					}, 500);
				}
				else if(formSource == 'modal') {
					$('#modal-add-edit').animate({
						scrollTop: $('#form_group_'+errorDetail['error_field']).position().top + 70
					}, 500);
				}
			}
		}
	}
	
	function formatThousandSeparator(value) {
		value += '';
		var decimals = value.split('<?php echo NUMBER_DECIMAL_POINT; ?>');
		var decimal = '';
		if(decimals.length > 1) {
			decimal = '<?php echo NUMBER_DECIMAL_POINT; ?>' + decimals[1];
		}
		value = decimals[0];
		
		var sign = '';
		if(value.length > 0 && value.charAt(0) == '-') {
			sign = '-';
			value = value.substring(1);
		}
		
		return sign + value
			.replace(/\D/g, "")
			.replace(/\B(?=(\d{3})+(?!\d))/g, "<?php echo NUMBER_THOUSAND_SEPARATOR; ?>") + decimal
		;
	}
	
	// Event Listener
	$('body').on('change', '.event_listened_on_change', function() {
		doEventListenerAction($(this).attr('on_change_event_listener_command'), $(this).attr('name'), $(this).val(), $('form.form_chchdb').attr('action_mode'));
	});
	
	function doEventListenerAction(command, fieldName, fieldValue, actionMode, elementID) {
		$.post('<?php echo base_url('api/get_event_listener_action'); ?>', {
			command: command,
			field_name: fieldName,
			field_value: fieldValue,
			action_mode: actionMode,
			fields: $('#form_'+$('form.form_chchdb').attr('entity')).serializeArray()}, function(result) {
			if(result.success) {
				var actions = result.actions;
				for(var i=0; i<actions.length; i++) {
					if(actions[i]['action_type'] == 'show_alert') {
						alert(actions[i]['alert_message']);
					}
					else if(actions[i]['action_type'] == 'change_field_value') {
						if($('#input_'+actions[i]['field_name']).hasClass('selectized')) {
							var $select = $('#input_'+actions[i]['field_name']).selectize();
							var selectize = $select[0].selectize;
							selectize.setValue(actions[i]['field_value']);
						}
						else {
							$('#input_'+actions[i]['field_name']).val(actions[i]['field_value']);
						}
					}
				}
			}
			else {
				alert(result.error_message);
			}
				
		}, "json" );
	}
	
	function setCaretPosition(elemId, caretPos) {
		var elem = document.getElementById(elemId);
		
		if(elem != null) {
			if(elem.createTextRange) {
				var range = elem.createTextRange();
				range.move('character', caretPos);
				range.select();
			}
			else {
				if(elem.selectionStart) {
					elem.focus();
					elem.setSelectionRange(caretPos, caretPos);
				}
				else {
					elem.focus();
				}
			}
		}
	}
	
	$('body').on('click', '.custom-form-action-button', function() {
		$.post('<?php echo base_url(PROJECT_CODE.'/api/'); ?>'+$(this).attr('data-callback'), { 
			args: $(this).data(),
			data: $('#chchdb_custom_form').serializeArray()}, function(result) {
			if(result.success) {
				if(result.callback_action == 'reload') {
					location.reload();
				}
				else if(result.callback_action == 'redirect') {
					window.location = result.redirect_url;
				}
			}
			else {
				alert(result.error_message);
			}
		}, "json" );
	});
</script>