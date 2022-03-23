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
	
	var average_weight_chart_options = {
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
			<?php foreach($assignment_types as $assignment_type_id => $current_assignment_type): ?>
			{
				name: '<?php echo $current_assignment_type['assignment_type_name']; ?>',
				type: 'line',
				data: <?php echo json_encode(array_values($utilization_metrics_report_graph_data[$assignment_type_id]['average_weight_data'])); ?>,
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
			max: <?php echo ceil($max_average_weight * 1.1 / 10) * 10; ?>,
			title: {
				text: 'Average Weight',
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
	
	var average_weight_chart = new ApexCharts(document.querySelector("#average-weight-chart"), average_weight_chart_options);
	
	average_weight_chart.render();
	
	var average_cubic_ft_chart_options = {
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
			<?php foreach($assignment_types as $assignment_type_id => $current_assignment_type): ?>
			{
				name: '<?php echo $current_assignment_type['assignment_type_name']; ?>',
				type: 'line',
				data: <?php echo json_encode(array_values($utilization_metrics_report_graph_data[$assignment_type_id]['average_cubic_ft_data'])); ?>,
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
			max: <?php echo ceil($max_average_cubic_ft * 1.1); ?>,
			title: {
				text: 'Average Cubic Ft',
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
	
	var average_cubic_ft_chart = new ApexCharts(document.querySelector("#average-cubic-ft-chart"), average_cubic_ft_chart_options);
	
	average_cubic_ft_chart.render();
</script>