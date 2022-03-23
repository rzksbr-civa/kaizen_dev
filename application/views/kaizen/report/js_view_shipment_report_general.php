<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- ApexCharts JS -->
<script src="<?php echo base_url('assets/apexcharts/apexcharts.min.js'); ?>"></script>

<script>
	$('.multiple-selectized').selectize({
		maxItems: null
	});
</script>

<script>
	$('body').on('click', '#btn-export', function() {
		var arr = $('#form-shipment-report-filter').serialize();
		window.location = '<?php echo base_url('kaizen/tools/export/excel/shipment_report/?'); ?>' + arr;
	});
</script>
