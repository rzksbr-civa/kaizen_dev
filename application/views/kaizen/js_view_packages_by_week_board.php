<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<!-- Datatables JS -->
<script src="<?php echo base_url('assets/datatables/1.10.18/datatables.min.js'); ?>"></script>

<!-- ColResizable JS -->
<script src="<?php echo base_url('assets/colresize/dataTables.colResize.js'); ?>"></script>


<script>
	var packages_by_month_table = $('#packages-by-week-table').DataTable( {
		dom: 'Z<"col-md-6"l><"col-md-6"f>r<"table-container"t><"col-md-12"i><"col-md-6"B><"col-md-6"p>',
		buttons: [
			{
				extend: 'excel',
				text: 'Download',
				exportOptions: {columns: ':visible'}
			}
		],
		aaSorting: [[0,'desc']]
	} );
</script>

<?php endif; ?>