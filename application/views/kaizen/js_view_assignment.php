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
	$('body').on('mouseover', '.asg', function() {
		$(this).tooltip('destroy');
		showTooltip(this);
	});
	
	$('body').on('mouseout', '.asg', function() {
		$('.asg').removeClass('green-cell').removeClass('red-cell');
	});

	$('body').on('click', '.asg', function() {
		var employee_id = $(this).data('employee');
		var shift = $(this).data('shift');
		
		var bulk_assignment_type = $('#input-bulk-assign-assignment-type').val();
		var bulk_assignment_type_name = $('#input-bulk-assign-assignment-type option:selected').text();
		if(bulk_assignment_type > 0) {
			assign(employee_id, '<?php echo $date; ?>', shift, bulk_assignment_type, bulk_assignment_type_name);
		}
		else if(bulk_assignment_type == -1) {
			assign(employee_id, '<?php echo $date; ?>', shift, null, null);
		}
	});
	
	function assign(employee_id, date, shift, assignment_type, assignment_type_name) {
		$('#asg-'+employee_id+'-'+shift).html('<img src="<?php echo base_url('assets/chchdb/loading.gif'); ?>" width="20">');
		$.post('<?php echo base_url(PROJECT_CODE.'/api/assign_employee'); ?>', {
			employee_id: employee_id,
			date: date,
			shift: shift,
			assignment_type: assignment_type}, function(result) {
			if(result.success) {
				$('#asg-'+employee_id+'-'+shift).html(result.current_assignments_text);
			}
			else {
				alert('Error');
			}
			
			$('.tooltip').hide();
		}, "json" );
	}
	
	function showTooltip(element) {
		var current_assignments_text = $(element).text().trim();
		var bulk_assignment_type = $('#input-bulk-assign-assignment-type option:selected').val();
		var bulk_assignment_type_name = $('#input-bulk-assign-assignment-type option:selected').text();

		if(bulk_assignment_type > 0 || bulk_assignment_type == -1) {
			var employee_name = $(element).data('employee_name');
			
			$('.asg').removeClass('green-cell').removeClass('red-cell');
			
			if(current_assignments_text.includes(bulk_assignment_type_name) || (bulk_assignment_type == -1 && current_assignments_text.length > 0)) {
				$(element).addClass('red-cell');
				$(element).tooltip(
					{
						animation: false,
						container: 'body',
						title: 'Unassign from ' + current_assignments_text,
						template: '<div class="tooltip tooltip-red" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
					}
				);
				
				$(element).attr('title', 'Unassign ' + employee_name + ' from ' + current_assignments_text).tooltip('fixTitle').tooltip('show');
			}
			else if(bulk_assignment_type > 0) {
				$(element).addClass('green-cell');
				$(element).tooltip(
					{
						animation: false,
						container: 'body',
						title: 'Assign to ' + bulk_assignment_type_name,
						template: '<div class="tooltip tooltip-green" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
					}
				);
				
				$(element).attr('title', 'Assign ' + employee_name + ' to ' + bulk_assignment_type_name).tooltip('fixTitle').tooltip('show');
			}
		}
	}
</script>

<?php endif; ?>