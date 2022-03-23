<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<script>
	$('.multiple-selectized').selectize({
		maxItems: null
	});
</script>

<?php if($generate) : ?>

<!-- Datatables JS -->
<script src="<?php echo base_url('assets/datatables/1.10.18/datatables.min.js'); ?>"></script>

<!-- ColResizable JS -->
<script src="<?php echo base_url('assets/colresize/dataTables.colResize.js'); ?>"></script>

<script>
	window.setInterval('refresh()', 60000);

    function refresh() {
		$.post('<?php echo base_url(PROJECT_CODE.'/api/get_inbound_idle_time_board_data'); ?>', { 
			data: $('#form-inbound-idle-time-board-filter').serializeArray()}, function(result) {
			if(result.success && result.page_version > $('#input-page-version').val()) {
				$('#page-last-updated-text').html(result.page_last_updated);
				$('#inbound-idle-time-waiting-asns-area').html(result.inbound_idle_time_waiting_asns_html);
			}
		}, "json" );
    }

	var waiting_asns_table = $('#waiting-asns-table').DataTable( {
		dom: 'Z<"col-md-6"l><"col-md-6"f>r<"table-container"t><"col-md-12"i><"col-md-6"B><"col-md-6"p>',
		buttons: [
			{
				extend: 'excel',
				title: 'Inbound Idle Time Board',
				text: 'Download',
				exportOptions: {columns: ':visible'}
			}
		],
		aaSorting: [[2,'desc']],
		columnDefs: [
			{targets: [2], visible: false}
		]
	} );
</script>

<?php endif; ?>