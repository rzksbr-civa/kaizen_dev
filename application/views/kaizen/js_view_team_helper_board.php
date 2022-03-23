<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<script>
	$('.multiple-selectized').selectize({
		maxItems: null
	});
	
	$('body').on('click', '#btn-export', function() {
		var arr = $('#form-team-helper-board-filter').serialize();
		window.location = '<?php echo base_url('kaizen/tools/export/excel/team_helper/?'); ?>' + arr;
	});
</script>

<?php if($generate) : ?>

<!-- Datatables JS -->
<script src="<?php echo base_url('assets/datatables/1.10.18/datatables.min.js'); ?>"></script>

<!-- ColResizable JS -->
<script src="<?php echo base_url('assets/colresize/dataTables.colResize.js'); ?>"></script>

<script>
	$(function () {
		$('[data-toggle="tooltip"]').tooltip()
	})
	
	window.setInterval('refresh()', 60000);

    function refresh() {
		$.post('<?php echo base_url(PROJECT_CODE.'/api/get_team_helper_board_data'); ?>', { 
			data: $('#form-team-helper-board-filter').serializeArray()}, function(result) {
			if(result.success && result.page_version > $('#input-page-version').val()) {
				$('#page-last-updated-text').html(result.page_last_updated);
				$('#staff-time-log-table-area').html(result.team_helper_staff_time_log_html);
			}
		}, "json" );
    }
	
	$(document).ready(function() {
		/*var table = $('#staff-time-log-table').DataTable({
			dom: 'ZiB',
			paging: false,
			buttons: [
				{
					extend: 'copy',
					text: 'Copy',
					exportOptions: {columns: ':visible'}
				},
				{
					extend: 'excel',
					text: 'Excel',
					exportOptions: {columns: ':visible'}
				},
				{
					extend: 'print',
					text: 'Print',
					exportOptions: {columns: ':visible'}
				}
			],
		});*/
		
		// Show the table after the javascript is loaded
		$('.table-area').removeClass('invisible');
	});
</script>

<?php endif; ?>