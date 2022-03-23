<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Datatables JS -->
<script src="<?php echo base_url('assets/datatables/1.10.18/datatables.min.js'); ?>"></script>

<!-- ColResizable JS -->
<script src="<?php echo base_url('assets/colresize/dataTables.colResize.js'); ?>"></script>

<script>
	$(document).ready(function() {
		var table = $('#monthly-ac-data-table').DataTable({
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
		});
		
		// Show the table after the javascript is loaded
		$('.table-area').removeClass('invisible');
	});
</script>