<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<!-- Datatables JS -->
<script src="<?php echo base_url('assets/datatables/1.10.18/datatables.min.js'); ?>"></script>

<!-- ColResizable JS -->
<script src="<?php echo base_url('assets/colresize/dataTables.colResize.js'); ?>"></script>

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
		
	var action_chart_options = {
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
			<?php foreach($assignment_types as $assignment_type_id => $assignment_type) : 
					if($assignment_type['show']) : ?>
			{
				name: '<?php echo $assignment_type['assignment_type_name']; ?>',
				type: 'line',
				data: <?php echo json_encode(array_column(array_column(array_values($table_data), 'action'), $assignment_type_id)); ?>,
			},
			
			<?php endif; endforeach; ?>
		],
		legend: {
			labels: {
				colors: 'lightgrey',
				useSeriesColors: false
			},
		},
		xaxis: {
			type: 'category',
			categories: <?php echo json_encode(array_keys($table_data)); ?>,
			tickAmount: 'dataPoints',
			labels: {
				style: {
					colors: 'lightgrey'
				}
			},
			title: {
				text: 'Date',
				style: {
					color: 'lightgrey'
				}
			}
		},
		yaxis: {
			min: 0,
			max: <?php echo ceil(max(array_map('max', array_column(array_values($table_data), 'action'))) * 1.1 / 1000) * 1000; ?>,
			title: {
				text: 'Actions',
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
	
	var action_chart = new ApexCharts(document.querySelector("#product-family-action-chart"), action_chart_options);
	
	action_chart.render();
	
	var labor_hours_worked_chart_options = {
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
			<?php foreach($assignment_types as $assignment_type_id => $assignment_type) : 
					if($assignment_type['show']) : ?>
			{
				name: '<?php echo $assignment_type['assignment_type_name']; ?>',
				type: 'line',
				data: <?php echo json_encode(array_column(array_column(array_values($table_data), 'labor_hours_worked'), $assignment_type_id)); ?>,
			},
			
			<?php endif; endforeach; ?>
		],
		legend: {
			labels: {
				colors: 'lightgrey',
				useSeriesColors: false
			},
		},
		xaxis: {
			type: 'category',
			categories: <?php echo json_encode(array_keys($table_data)); ?>,
			tickAmount: 'dataPoints',
			labels: {
				style: {
					colors: 'lightgrey'
				}
			},
			title: {
				text: 'Date',
				style: {
					color: 'lightgrey'
				}
			}
		},
		yaxis: {
			min: 0,
			max: <?php echo ceil(max(array_map('max', array_column(array_values($table_data), 'labor_hours_worked'))) * 1.1 / 100) * 100; ?>,
			title: {
				text: 'Labor Hours Worked',
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
	
	var labor_hours_worked_chart = new ApexCharts(document.querySelector("#product-family-labor-hours-worked-chart"), labor_hours_worked_chart_options);
	
	labor_hours_worked_chart.render();
	
	
	var labor_hours_per_assignment_chart_options = {
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
			<?php foreach($assignment_types as $assignment_type_id => $assignment_type) : 
					if($assignment_type['show']) : ?>
			{
				name: '<?php echo $assignment_type['assignment_type_name']; ?>',
				type: 'line',
				data: <?php echo json_encode(array_column(array_column(array_values($table_data), 'labor_hours_per_assignment'), $assignment_type_id)); ?>,
			},
			
			<?php endif; endforeach; ?>
		],
		legend: {
			labels: {
				colors: 'lightgrey',
				useSeriesColors: false
			},
		},
		xaxis: {
			type: 'category',
			categories: <?php echo json_encode(array_keys($table_data)); ?>,
			tickAmount: 'dataPoints',
			labels: {
				style: {
					colors: 'lightgrey'
				}
			},
			title: {
				text: 'Date',
				style: {
					color: 'lightgrey'
				}
			}
		},
		yaxis: {
			min: 0,
			max: <?php echo ceil(max(array_map('max', array_column(array_values($table_data), 'labor_hours_per_assignment'))) * 11) / 10; ?>,
			title: {
				text: 'Labor Hours Per Assignment',
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
	
	var labor_hours_per_assignment_chart = new ApexCharts(document.querySelector("#product-family-labor-hours-per-assignment-chart"), labor_hours_per_assignment_chart_options);
	
	labor_hours_per_assignment_chart.render();
	
	
	var cost_per_assignment_chart_options = {
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
			<?php foreach($assignment_types as $assignment_type_id => $assignment_type) : 
					if($assignment_type['show']) : ?>
			{
				name: '<?php echo $assignment_type['assignment_type_name']; ?>',
				type: 'line',
				data: <?php echo json_encode(array_column(array_column(array_values($table_data), 'cost_per_assignment'), $assignment_type_id)); ?>,
			},
			
			<?php endif; endforeach; ?>
		],
		legend: {
			labels: {
				colors: 'lightgrey',
				useSeriesColors: false
			},
		},
		xaxis: {
			type: 'category',
			categories: <?php echo json_encode(array_keys($table_data)); ?>,
			tickAmount: 'dataPoints',
			labels: {
				style: {
					colors: 'lightgrey'
				}
			},
			title: {
				text: 'Date',
				style: {
					color: 'lightgrey'
				}
			}
		},
		yaxis: {
			min: 0,
			max: <?php echo ceil(max(array_map('max', array_column(array_values($table_data), 'cost_per_assignment'))) * 1.1); ?>,
			title: {
				text: 'Cost Per Assignment',
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
	
	var cost_per_assignment_chart = new ApexCharts(document.querySelector("#product-family-cost-per-assignment-chart"), cost_per_assignment_chart_options);
	
	cost_per_assignment_chart.render();
	
	
	var cost_chart_options = {
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
			<?php foreach($assignment_types as $assignment_type_id => $assignment_type) : 
					if($assignment_type['show']) : ?>
			{
				name: '<?php echo $assignment_type['assignment_type_name']; ?>',
				type: 'line',
				data: <?php echo json_encode(array_column(array_column(array_values($table_data), 'cost'), $assignment_type_id)); ?>,
			},
			
			<?php endif; endforeach; ?>
		],
		legend: {
			labels: {
				colors: 'lightgrey',
				useSeriesColors: false
			},
		},
		xaxis: {
			type: 'category',
			categories: <?php echo json_encode(array_keys($table_data)); ?>,
			tickAmount: 'dataPoints',
			labels: {
				style: {
					colors: 'lightgrey'
				}
			},
			title: {
				text: 'Date',
				style: {
					color: 'lightgrey'
				}
			}
		},
		yaxis: {
			min: 0,
			max: <?php echo ceil(max(array_map('max', array_column(array_values($table_data), 'cost'))) * 1.1); ?>,
			title: {
				text: 'Cost',
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
	
	var cost_chart = new ApexCharts(document.querySelector("#product-family-cost-chart"), cost_chart_options);
	
	cost_chart.render();
</script>

<?php endif; ?>