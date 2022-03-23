<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
	
<?php if($generate) : ?>

<!-- Datatables JS -->
<script src="<?php echo base_url('assets/datatables/1.10.18/datatables.min.js'); ?>"></script>

<!-- ColResizable JS -->
<script src="<?php echo base_url('assets/colresize/dataTables.colResize.js'); ?>"></script>


<script>
	var table = $('#shipment-report-table').DataTable( {
			dom: 'Z<"col-md-6"l><"col-md-6"f>r<"table-container"t><"col-md-12"i><"col-md-6"B><"col-md-6"p>',
			buttons: [
				{
					extend: 'excel',
					text: 'Download',
					exportOptions: {columns: ':visible'}
				}
			],
			aaSorting: []
		} );
	
	<?php if($generate) : ?>
	
	var demand_chart_options = {
		chart: {
			type: 'bar',
			height: 300,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
		plotOptions: {
			bar: {
				dataLabels: {
					position: 'top'
				},
			}
		},
		dataLabels: {
			enabled: true,
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
			{
				name: 'Demand',
				type: 'bar',
				data: <?php echo json_encode(array_column(array_values($table_data), 'demand')); ?>,
			}
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
			max: <?php echo ceil(max(array_column(array_values($table_data), 'demand')) * 1.1 / 1000) * 1000; ?>,
			title: {
				text: 'Demand',
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
		},
		colors: ['#2149EE']
	};
	
	var demand_chart = new ApexCharts(document.querySelector("#demand-chart"), demand_chart_options);
	
	demand_chart.render();
	
	var num_employees_worked_chart_options = {
		chart: {
			type: 'line',
			height: 300,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
		dataLabels: {
			enabled: true,
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
			{
				name: 'Num Employees Worked',
				type: 'line',
				data: <?php echo json_encode(array_column(array_values($table_data), 'num_employees')); ?>,
			}
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
			max: <?php echo ceil(max(array_column(array_values($table_data), 'num_employees')) * 1.1 / 10) * 10; ?>,
			title: {
				text: 'Num Employees',
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
		},
		colors: ['#2149EE']
	};
	
	var num_employees_worked_chart = new ApexCharts(document.querySelector("#num-employees-worked-chart"), num_employees_worked_chart_options);
	
	num_employees_worked_chart.render();
	
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
			{
				name: 'Labor Hours Worked',
				type: 'line',
				data: <?php echo json_encode(array_column(array_values($table_data), 'labor_hours_worked')); ?>,
			}
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
			max: <?php echo ceil(max(array_column(array_values($table_data), 'labor_hours_worked')) * 1.1 / 100) * 100; ?>,
			title: {
				text: 'Labor Hours',
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
		},
		colors: ['#2149EE']
	};
	
	var labor_hours_worked_chart = new ApexCharts(document.querySelector("#labor-hours-worked-chart"), labor_hours_worked_chart_options);
	
	labor_hours_worked_chart.render();
	
	var labor_hours_per_package_chart_options = {
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
			{
				name: 'Labor Hours Worked',
				type: 'line',
				data: <?php echo json_encode(array_column(array_values($table_data), 'labor_hours_per_package')); ?>,
			}
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
			max: <?php echo ceil(max(array_column(array_values($table_data), 'labor_hours_per_package')) * 1.1 / 0.1) * 0.1; ?>,
			title: {
				text: 'Labor Hours',
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
		},
		colors: ['#2149EE']
	};
	
	var labor_hours_per_package_chart = new ApexCharts(document.querySelector("#labor-hours-per-package-chart"), labor_hours_per_package_chart_options);
	
	labor_hours_per_package_chart.render();
	
	
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
			{
				name: 'Cost',
				type: 'line',
				data: <?php echo json_encode(array_column(array_values($table_data), 'cost')); ?>,
			}
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
			max: <?php echo ceil(max(array_column(array_values($table_data), 'cost')) * 1.1); ?>,
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
		},
		colors: ['#2149EE']
	};
	
	var cost_chart = new ApexCharts(document.querySelector("#cost-chart"), cost_chart_options);
	
	cost_chart.render();
	
	
	var cost_per_package_chart_options = {
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
			{
				name: 'Cost Per Package',
				type: 'line',
				data: <?php echo json_encode(array_column(array_values($table_data), 'cost_per_package')); ?>,
			}
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
			max: <?php echo ceil(max(array_column(array_values($table_data), 'cost_per_package')) * 1.1); ?>,
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
		},
		colors: ['#2149EE']
	};
	
	var cost_per_package_chart = new ApexCharts(document.querySelector("#cost-per-package-chart"), cost_per_package_chart_options);
	
	cost_per_package_chart.render();
	
	<?php endif; ?>
</script>

<?php endif; ?>