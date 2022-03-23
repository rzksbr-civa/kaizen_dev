<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- ApexCharts JS -->
<script src="<?php echo base_url('assets/apexcharts/apexcharts.min.js'); ?>"></script>

<?php if($generate) : ?>

<script>
	$('.multiple-selectized').selectize({
		maxItems: null
	});
	
	window.setInterval('refresh()', 60000);

    function refresh() {
		var data = $('#form-takt-board-filter').serializeArray();
		console.log(data);
		$.post('<?php echo base_url(PROJECT_CODE.'/api/get_takt_board_data'); ?>', { 
			data: $('#form-takt-board-filter').serializeArray()}, function(result) {
			if(result.success && result.page_version > $('#input-page-version').val()) {
				$('#page-last-updated-text').html(result.page_last_updated);
				$('#cost-calculation-section').html(result.cost_calculation_section_html);
				$('#block-times-section').html(result.block_times_section_html);
				$('#completed-shipment-section').html(result.completed_shipment_section_html);
				$('#graph-section').html(result.graph_section_html);
				$('#js-graph-section').html(result.js_graph_section_html);
			}
		}, "json" );
    }
	
	$('body').on('click', '#btn-edit-takt-data', function() {
		show_edit_takt_data_dialog();
	});
	
	function show_edit_takt_data_dialog() {
		$('.input-edit-takt-data').val('');
		
		$.post('<?php echo base_url(PROJECT_CODE.'/api/get_takt_data'); ?>', {
			facility: '<?php echo $facility; ?>',
			date: '<?php echo $date; ?>'}, function(result) {
			if(result.success) {
				$('#input-edit-takt-data-projected_demand').val(result.takt_data.projected_demand);
				$('#input-edit-takt-data-hours_shift').val(result.takt_data.hours_shift);
				$('#input-edit-takt-data-break_time_per_shift_in_min').val(result.takt_data.break_time_per_shift_in_min);
				$('#input-edit-takt-data-lunch_time_per_shift_in_min').val(result.takt_data.lunch_time_per_shift_in_min);
				$('#input-edit-takt-data-number_of_employees_scheduled').val(result.takt_data.number_of_employees_scheduled);
				
				$('#modal-edit-takt-data').modal('show');
			}
			else {
				alert('Error');
			}
		}, "json" );
	}
	
	$('body').on('click', '#btn-do-edit-takt-data', function() {
		do_edit_takt_data();
	});
	
	function do_edit_takt_data() {
		var data = $('#form-edit-takt-data').serializeArray();
		
		$('#modal-edit-takt-data').modal('hide');
		$('.input-edit-takt-data').val('');
		$('#cost-calculation-section').html('<div class="alert alert-info" role="alert">Refreshing data. Please wait...</div>');
		
		$.post('<?php echo base_url(PROJECT_CODE.'/api/do_edit_takt_data'); ?>', { 
			data: data,
			facility: '<?php echo $facility; ?>',
			date: '<?php echo $date; ?>'}, function(result) {
			if(result.success) {
				refresh();
			}
			else {
				alert('Error');
				refresh();
			}
		}, "json" );
	}
</script>

<div id="js-graph-section">
	<?php echo $js_graph_section_html; ?>
</div>

<?php endif; ?>