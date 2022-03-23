<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<script>
	$(document).ready(function() {		
		// Manipulate trigger (add/edit/delete)
		$('body').on('click', '.trigger-manipulate-data', function() {
			prepareModalAddEdit($(this));
		});
	});
	
	function prepareModalAddEdit(triggeredButton) {
		var currentEntity = $('#modal-add-edit').attr('entity');
		var currentParentEntity = $('#modal-add-edit').attr('parent_entity');
		var currentDataID = $('#modal-add-edit').attr('data_id');
		var currentActionMode = $('#modal-add-edit').attr('action_mode');
		var currentWidgetID = $('#modal-add-edit').attr('widget_id');
		
		var thisEntity = triggeredButton.attr('entity');
		var thisParentEntity = triggeredButton.attr('parent_entity');
		var thisDataID = triggeredButton.attr('data_id');
		var thisParentDataID = triggeredButton.attr('parent_data_id');
		var thisActionMode = triggeredButton.attr('action_mode');
		var thisFormSource = triggeredButton.attr('form_source');
		var thisWidgetID = triggeredButton.attr('widget_id');
		
		if(currentEntity == thisEntity && currentParentEntity == thisParentEntity && currentDataID == thisDataID && currentActionMode == thisActionMode && currentWidgetID == thisWidgetID) {
			$('#modal-add-edit').modal('show');
			return;
		}
		else {
			$('#modal-add-edit').attr('entity', thisEntity);
			$('#modal-add-edit').attr('parent_entity', thisParentEntity);
			$('#modal-add-edit').attr('data_id', thisDataID);
			$('#modal-add-edit').attr('action_mode', thisActionMode);
			
			$('#modal-add-edit .add-edit-action-button').attr('entity', thisEntity);
			$('#modal-add-edit .add-edit-action-button').attr('parent_entity', thisParentEntity);
			$('#modal-add-edit .add-edit-action-button').attr('data_id', thisDataID);
			$('#modal-add-edit .add-edit-action-button').attr('action_mode', thisActionMode);
			$('#modal-add-edit .add-edit-action-button').attr('form_source', thisFormSource);
		}
		
		if(typeof thisWidgetID === typeof undefined || thisWidgetID === false) {
			thisWidgetID = null;
		}
		
		showModalAddEditLoadingScreen();
		
		$.post('<?php echo base_url('api/prepare_modal_add_edit'); ?>', { 
			entity_name: thisEntity,
			parent_entity: thisParentEntity,
			data_id: thisDataID,
			parent_data_id: thisParentDataID,
			action_mode: thisActionMode,
			widget_id: thisWidgetID}, function(result) {
			if(result.success) {
				$('#modal-add-edit-label').html(result.modal_header_label);
				
				$('#modal-add-edit-body').html(result.modal_body);
				$('#modal-add-edit-body').css('display', 'block');
				
				if(thisActionMode == 'delete') {
					$('#modal-add-edit-action-button').html('<?php echo addslashes(ucwords(lang('word__delete'))); ?>');
					$('#modal-add-edit-action-button').removeClass('btn-primary');
					$('#modal-add-edit-action-button').addClass('btn-danger');
				}
				else {
					$('#modal-add-edit-action-button').html('<?php echo addslashes(ucwords(lang('word__save'))); ?>');
					$('#modal-add-edit-action-button').removeClass('btn-danger');
					$('#modal-add-edit-action-button').addClass('btn-primary');
				}
				
				$('#modal-add-edit-footer').css('display', 'block');
				
				$('[data-toggle="tooltip"]').tooltip();
				$('#modal-add-edit .selectized').selectize();
				$('#modal-add-edit').modal('show');
			}
			else {
				alert(result.error_message);
			}
		}, "json" );
	}
	
	function showModalAddEditLoadingScreen() {
		$('#modal-add-edit-label').html('<?php echo addslashes(ucwords(lang('message__loading'))); ?>');
		$('#modal-add-edit-body').css('display', 'none');
		$('#modal-add-edit-footer').css('display', 'none');
	}
</script>