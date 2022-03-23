<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<script>
	$('body').on('click', '#btn-export', function() {
		var arr = $('#form-inventory-report-filter').serialize();
		window.location = '<?php echo base_url('kaizen/tools/export/excel/inventory_report/?'); ?>' + arr;
	});
</script>

<!-- Datatables JS -->
<script src="<?php echo base_url('assets/datatables/1.10.18/datatables.min.js'); ?>"></script>

<!-- ColResizable JS -->
<script src="<?php echo base_url('assets/colresize/dataTables.colResize.js'); ?>"></script>

