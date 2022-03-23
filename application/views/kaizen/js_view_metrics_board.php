<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<script>
	$('.multiple-selectized').selectize({
		maxItems: null
	});
</script>

<?php if($generate) : ?>

<script>
	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	})
	
	window.setInterval('refresh()', 60000);

    function refresh() {
		$.post('<?php echo base_url(PROJECT_CODE.'/api/get_metrics_board_data'); ?>', { 
			data: $('#form-metrics-board-filter').serializeArray()}, function(result) {
			if(result.success && result.page_version > $('#input-page-version').val()) {
				$('#page-last-updated-text').html(result.page_last_updated);
				$('#metrics-board-table-area').html(result.evolution_points_leaderboard_html);
			}
		}, "json" );
    }
	
	$('body').on('click', '.metrics-board-note', function() {
		$('#modal-set-metrics-board-note').modal('show');
	});
	
	$('body').on('click', '.metrics-board-employee-assignment', function() {
		var employee_id = $(this).data('employee_id');
		var employee_name = $(this).data('employee_name');
		show_edit_assignment_dialog(employee_id, employee_name);
	});
	
	$('body').on('click', '#btn-do-edit-assignment', function() {
		do_edit_assignment();
	});
	
	function show_edit_assignment_dialog(employee_id, employee_name) {
		clear_edit_assignment_dialog();
	
		edited_employee_name = employee_name;
		$('#modal-edit-assignment-title').html('Edit '+employee_name+'\'s Assignment');
		
		$.post('<?php echo base_url(PROJECT_CODE.'/api/get_current_employee_assignment'); ?>', {
			employee_id: employee_id,
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
				$('#metrics-board-table-area').html('Refreshing data...');
				refresh();
			}
			else {
				alert('Error');
			}
		}, "json" );
	}
	
	$('body').on('click', '#btn-do-edit-assignment', function() {
		do_edit_assignment();
	});
	
	function do_set_metrics_board_note() {
		$.post('<?php echo base_url(PROJECT_CODE.'/api/do_set_metrics_board_note'); ?>', { 
			data: $('#form-set-metrics-board-note').serializeArray()
			}, function(result) {
			if(result.success) {
				$('#modal-set-metrics-board-note').modal('hide');
				$('#input-metrics-board-note-content').val('');
				$('.metrics-board-note').html(result.metrics_board_note);
			}
			else {
				alert('Error');
			}
		}, "json" );
	}
	
	$('body').on('click', '#btn-do-set-metrics-board-note', function() {
		do_set_metrics_board_note();
	});
</script>

<?php endif; ?>