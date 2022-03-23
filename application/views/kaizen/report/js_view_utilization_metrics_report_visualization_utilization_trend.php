<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Datatables JS -->
<script src="<?php echo base_url('assets/datatables/1.10.18/datatables.min.js'); ?>"></script>

<!-- ColResizable JS -->
<script src="<?php echo base_url('assets/colresize/dataTables.colResize.js'); ?>"></script>

<!-- ApexCharts JS -->
<script src="<?php echo base_url('assets/apexcharts/apexcharts.min.js'); ?>"></script>

<script>
	var table = $('.datatabled').DataTable( {
			dom: 'Z<"col-md-6"l><"col-md-6"f>r<"table-container"t><"col-md-12"i><"col-md-6"B><"col-md-6"p>',
			buttons: [
				{
					extend: 'excel',
					text: 'Download',
					exportOptions: {columns: ':visible'}
				}
			],
		} );
	
	var weight_percentage_chart_options = {
		chart: {
			type: 'line',
			height: 300,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
		dataLabels: {
			enabled: false,
			offsetY: -5,
			style: {
				colors: ['lightgrey', 'lightgrey']
			}
		},
		grid: {
			borderColor: 'grey',
			row: {
				colors: 'grey'
			}
		},
		series: [
			<?php foreach($carriers as $carrier_code): ?>
			{
				name: '<?php echo $carrier_code; ?>',
				type: 'line',
				data: <?php echo json_encode(array_values($utilization_metrics_trend_graph_data[$carrier_code]['weight_percentage'])); ?>,
			},
			<?php endforeach; ?>
		],
		legend: {
			labels: {
				colors: 'lightgrey',
				useSeriesColors: false
			},
		},
		xaxis: {
			type: 'category',
			categories: <?php echo json_encode($period_labels); ?>,
			tickAmount: 'dataPoints',
			labels: {
				style: {
					colors: 'lightgrey'
				}
			},
			title: {
				text: 'Period',
				style: {
					color: 'lightgrey'
				}
			}
		},
		yaxis: {
			min: 0,
			max: 100,
			title: {
				text: 'Weight Percentage',
				style: {
					color: 'lightgrey'
				}
			},
			labels: {
				style: {
					color: 'lightgrey'
				}
			},
		},
		markers: {
			size: 5,
		}
	};
	
	var weight_percentage_chart = new ApexCharts(document.querySelector("#weight-percentage-chart"), weight_percentage_chart_options);
	
	weight_percentage_chart.render();
	
	var cubic_ft_percentage_chart_options = {
		chart: {
			type: 'line',
			height: 300,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
		dataLabels: {
			enabled: false,
			offsetY: -5,
			style: {
				colors: ['lightgrey', 'lightgrey']
			}
		},
		grid: {
			borderColor: 'grey',
			row: {
				colors: 'grey'
			}
		},
		series: [
			<?php foreach($carriers as $carrier_code): ?>
			{
				name: '<?php echo $carrier_code; ?>',
				type: 'line',
				data: <?php echo json_encode(array_values($utilization_metrics_trend_graph_data[$carrier_code]['cubic_ft_percentage'])); ?>,
			},
			<?php endforeach; ?>
		],
		legend: {
			labels: {
				colors: 'lightgrey',
				useSeriesColors: false
			},
		},
		xaxis: {
			type: 'category',
			categories: <?php echo json_encode($period_labels); ?>,
			tickAmount: 'dataPoints',
			labels: {
				style: {
					colors: 'lightgrey'
				}
			},
			title: {
				text: 'Period',
				style: {
					color: 'lightgrey'
				}
			}
		},
		yaxis: {
			min: 0,
			max: 100,
			title: {
				text: 'Cubic Ft Percentage',
				style: {
					color: 'lightgrey'
				}
			},
			labels: {
				style: {
					color: 'lightgrey'
				}
			},
		},
		markers: {
			size: 5,
		}
	};
	
	var cubic_ft_percentage_chart = new ApexCharts(document.querySelector("#cubic-ft-percentage-chart"), cubic_ft_percentage_chart_options);
	
	cubic_ft_percentage_chart.render();
</script>