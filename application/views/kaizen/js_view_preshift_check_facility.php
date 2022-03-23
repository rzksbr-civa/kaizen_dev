<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<script>
	var employees = [];
	
	<?php foreach($employees as $employee): ?>
		employees.push( {
			'id': <?php echo $employee['id']; ?>,
			'name': '<?php echo addslashes($employee['employee_name']); ?>',
			'is_checked_in': <?php echo isset($checked_in_employees[$employee['id']]) ? 'true' : 'false'; ?>,
			'is_flagged': <?php echo isset($checked_in_employees[$employee['id']]) && $checked_in_employees[$employee['id']]['is_flagged'] ? 'true' : 'false'; ?>
		} );
	<?php endforeach; ?>

	$('body').on('click', '.the-employee', function() {
		clearPreshiftCheckForm();
		$('#pc-employee-id').val($(this).attr('eid'));
		$('#preshift-check-modal').modal('show');
	});
	
	$('body').on('click', '.yes-no-button', function() {
		var question_id = $(this).attr('qid');
		var response = $(this).attr('response');
		
		$('#pc-resp-'+question_id).val(response);
		
		if(response == 'Yes') {
			$('#yes-button-'+question_id).addClass('yes-button-selected');
			$('#no-button-'+question_id).removeClass('no-button-selected');
		}
		else if(response == 'No') {
			$('#yes-button-'+question_id).removeClass('yes-button-selected');
			$('#no-button-'+question_id).addClass('no-button-selected');
		}
	});
	
	$('body').on('click', '#checkin-button', function() {
		var resp = $('#preshift-check-form').serializeArray();
		var employee_id = $('#pc-employee-id').val();
		var complete = true;
		
		for(var i=0; i<resp.length; i++) {
			if(resp[i]['value'].length == 0) {
				complete = false;
			}
		}
		
		if(complete) {
			$('#preshift-check-modal').modal('hide');
			$('#checking-in-modal').modal('show');
			$.post('<?php echo base_url(PROJECT_CODE.'/api/submit_preshift_check'); ?>', { 
				data: resp,
				date: new Date().toISOString().slice(0,10),
				time: new Date().toLocaleTimeString(),
				facility_id: <?php echo $facility_data['id']; ?>}, function(result) {
				if(result.success) {
					$('#checking-in-modal').modal('hide');
					$('#employee-'+employee_id).addClass('employee-checked-in');
					if(result.is_flagged) {
						$('#employee-'+employee_id).addClass('employee-flagged');
						$('#negative-feedback-modal').modal('show');
					}
					else {
						$('#positive-feedback-modal').modal('show');
					}
				}
				else {
					alert('Error. Please try again.');
				}
			}, "json" );
		}
		else {
			alert("Please respond to all the questions.");
		}
	});
	
	refreshEmployeeList();
	
	function refreshEmployeeList() {
		var key = $('#input-employee-name').val().toLowerCase();
		var employeeListView = '';
		
		var addedClasses = '';
		
		for(var i=0; i<employees.length; i++) {
			addedClasses = '';
			if(employees[i]['is_checked_in']) {
				addedClasses += ' employee-checked-in';
			}
			if(employees[i]['is_flagged']) {
				addedClasses += ' employee-flagged';
			}
			
			if(key.length > 0 && employees[i]['name'].toLowerCase().startsWith(key)) {
				employeeListView = '<a href="#" class="list-group-item the-employee'+addedClasses+'" id="employee-'+employees[i]['id']+'" eid="'+employees[i]['id']+'">'+employees[i]['name']+'</a>' + employeeListView;
			}
			else if(employees[i]['name'].toLowerCase().includes(key)) {
				employeeListView += '<a href="#" class="list-group-item the-employee'+addedClasses+'" id="employee-'+employees[i]['id']+'" eid="'+employees[i]['id']+'">'+employees[i]['name']+'</a>';
			}
		}
		
		$('.employee-list').html(employeeListView);
	}
	
	function clearPreshiftCheckForm() {
		$('.pc-resp').val('');
		$('.yes-button').removeClass('yes-button-selected');
		$('.no-button').removeClass('no-button-selected');
	}
	
	$('#input-employee-name').keyup(function() {
		refreshEmployeeList();
	});
</script>