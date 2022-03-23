<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<script>
	var edited_employee_name = null;
	
	$('.multiple-selectized').selectize({
		maxItems: null
	});
	
	$('body').on('click', '#btn-auto-refresh', function() {
		toggleAutoRefreshButton();
	});
	
	function toggleAutoRefreshButton() {
		var current_state = $('#btn-auto-refresh').data('state');
		
		if(current_state == 'on') {
			turnOffAutoRefresh();
		}
		else {
			turnOnAutoRefresh();
		}
	}
	
	function turnOffAutoRefresh() {
		$('#btn-auto-refresh').data('state', 'off');
		$('#btn-auto-refresh').removeClass('btn-success').addClass('btn-default');
		$('#btn-auto-refresh').text('Auto Refresh Off');
	}
	
	function turnOnAutoRefresh() {
		$('#btn-auto-refresh').data('state', 'on');
		$('#btn-auto-refresh').removeClass('btn-default').addClass('btn-success');
		$('#btn-auto-refresh').text('Auto Refresh On');
	}
	
	<?php if($generate) : ?>
	
	window.setInterval('refresh()', 30000);

    function refresh() {
		if($('#btn-auto-refresh').data('state') == 'on') {
			$.post('<?php echo base_url(PROJECT_CODE.'/api/get_scoreboard_data'); ?>', { 
				data: $('#form-scoreboard-filter').serializeArray()}, function(result) {
				if(result.success && result.page_version > $('#input-page-version').val()) {
					$('#page-last-updated-text').html(result.page_last_updated);
					$('#scoreboard-tables-area').html(result.scoreboard_tables_html);
				}
				else {
					alert('Error');
				}
			}, "json" );
		}
    }
	
	$('body').on('click', '.scoreboard-row', function() {
		turnOffAutoRefresh();
		var employee_name = $(this).data('employee_name');
		show_edit_assignment_dialog(employee_name);
	});
	
	$('body').on('click', '#btn-do-edit-assignment', function() {
		do_edit_assignment();
	});
	
	function show_edit_assignment_dialog(employee_name) {
		clear_edit_assignment_dialog();
	
		edited_employee_name = employee_name;
		$('#modal-edit-assignment-title').html('Edit '+employee_name+'\'s Assignment');
		
		$.post('<?php echo base_url(PROJECT_CODE.'/api/get_current_employee_assignment'); ?>', {
			employee_name: employee_name,
			date: '<?php echo $date; ?>'}, function(result) {
			if(result.success) {
				$('#input-edit-assignment-employee-name').val(employee_name);
				
				for (const [block, assignment] of Object.entries(result.assignments)) {
					$('#input-edit-assignment-'+block).selectize()[0].selectize.setValue(assignment.assignment_type);
				}
				
				$('#modal-edit-assignment').modal('show');
			}
			else {
				alert('Error');
			}
		}, "json" );
	}
	
	function clear_edit_assignment_dialog() {
		$('#modal-edit-assignment-title').html('Edit Assignment');
		$('#input-employee-id').val('');
		$('.input-edit-assignment').each(function() {
			if(this.id.length > 0) {
				$('#'+this.id).selectize()[0].selectize.clear();
			}
		});
	}
	
	function do_edit_assignment() {
		$.post('<?php echo base_url(PROJECT_CODE.'/api/do_edit_employee_assignment'); ?>', { 
			data: $('#form-edit-assignment').serializeArray(),
			date: '<?php echo $date; ?>'}, function(result) {
			if(result.success) {
				$('#modal-edit-assignment').modal('hide');
				clear_edit_assignment_dialog();
				$('.scoreboard-assignment-'+edited_employee_name.split(' ').join('-').split("'").join('')).html(result.current_assignments_text);
			}
			else {
				alert('Error');
			}
		}, "json" );
	}
	
	<?php endif; ?>
</script>