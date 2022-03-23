<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- ApexCharts JS -->
<script src="<?php echo base_url('assets/apexcharts/apexcharts.min.js'); ?>"></script>

<script>
	<?php if(!empty($graph)) : ?>
	
		<?php foreach($graph as $graph_name => $graph_data) : ?>
			<?php if(!empty($graph[$graph_name])) : ?>
			var <?php echo $graph_name; ?>_options = {
				chart: {
					type: 'bar',
					height: 300,
					fontFamily: 'Mukta'
				},
				plotOptions: {
					bar: {
						dataLabels: {
							position: 'top'
						},
					}
				},
				dataLabels: {
					offsetY: -30,
					style: {
						colors: ['lightgrey']
					}
				},
				grid: {
					borderColor: 'grey',
					row: {
						colors: 'grey'
					}
				},
				tooltip: {
					enabled: true,
					followCursor: true
				},
				series: [{
					name: '#Case',
					data: <?php echo json_encode(array_values($graph_data)); ?>,
				}],
				xaxis: {
					categories: <?php echo json_encode(array_keys($graph_data)); ?>,
					labels: {
						style: {
							colors: 'lightgrey'
						}
					},
					title: {
						style: {
							colors: 'lightgrey'
						}
					}
				},
				yaxis: {
					min: 0,
					max: <?php echo ceil(max(array_values($graph_data)) * 1.1 / 10) * 10; ?>,
					title: {
						text: '#Case',
						style: {
							color: 'lightgrey'
						}
					},
					labels: {
						style: {
							color: 'lightgrey'
						}
					},
				}
			}

			var <?php echo $graph_name; ?>_chart = new ApexCharts(document.querySelector("#kpi-graph-<?php echo $graph_name; ?>"), <?php echo $graph_name; ?>_options);
			<?php echo $graph_name; ?>_chart.render();
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>
</script>