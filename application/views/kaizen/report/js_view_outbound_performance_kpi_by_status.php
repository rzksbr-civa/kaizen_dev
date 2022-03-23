<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- ApexCharts JS -->
<script src="<?php echo base_url('assets/apexcharts/apexcharts.min.js'); ?>"></script>

<script>
	<?php if(!empty($graph_data)) : ?>
		var chart_options = <?php echo json_encode($graph_data['chart_options']); ?>;

		var kpi_performance_chart = new ApexCharts(document.querySelector("#kpi-outbound-performance-by-status"), chart_options);
		kpi_performance_chart.render();
	<?php endif; ?>
</script>