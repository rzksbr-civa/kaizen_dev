<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Datatables JS -->
<script src="<?php echo base_url('assets/datatables/1.10.18/datatables.min.js'); ?>"></script>

<!-- ColResizable JS -->
<script src="<?php echo base_url('assets/colresize/dataTables.colResize.js'); ?>"></script>

<script>
	$(document).ready(function() {
		var table = $('#outbound-performance-kpi-by-user-table').DataTable({
			dom: 'Zi',
			paging: false,
			order: [[ <?php echo count($data['date_label'])+1; ?>, "desc" ]],
			columnDefs: [
				{
					targets: [0],
					width: 20
				},
				{
					targets: [1],
					width: 200
				},
				{
					targets: 'ac-data-col',
					width: 100
				},
				{
					targets: 'ac-data-col-total',
					width: 100
				}
			]
		});
		
		// Show the table after the javascript is loaded
		$('.table-area').removeClass('invisible');
	});
</script>