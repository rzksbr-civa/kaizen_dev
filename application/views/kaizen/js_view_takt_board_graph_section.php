<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<script>
	var hourly_completed_shipments_chart_options = {
		chart: {
			type: 'line',
			height: 300,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
		annotations: {
			yaxis: [
				{
					y: 200,
					borderColor: '#B22222',
					label: {
						borderColor: '#B22222',
						style: {
							color: '#fff',
							background: '#B22222',
						},
					text: 'Min',
					}
				},
				{
					y: 500,
					borderColor: '#009933',
					label: {
						borderColor: '#009933',
						style: {
							color: '#fff',
							background: '#009933',
						},
					text: 'Max',
					}
				},
			],
			xaxis: [
				{
					x: new Date('<?php echo $page_generated_time; ?>').getTime(),
					strokeDashArray: 0,
					borderColor: '#00E396',
					label: {
						borderColor: '#00E396',
						style: {
							color: '#fff',
							background: '#00E396',
						},
						text: 'Current time',
					}
				},
				<?php
					if(isset($takt_value)) :

					for($i=0; $i<$num_hours; $i++) :
						$time = date('Y-m-d H:i:s', strtotime('+'.$i.' hour '.$start_datetime));
						$now = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hour'));

						if(strtotime($time) > strtotime($page_generated_time)) {
							continue;
						}
						
						if(!isset($hourly_completed_shipments_count[$time])) continue;
						
						if($hourly_completed_shipments_count[$time]['value'] == 0) continue;
						
						if($hourly_completed_shipments_count[$time]['completed_shipments_diff_compared_to_takt_value'] > 0) :
				?>
				{
					x: new Date('<?php echo $time; ?>').getTime(),
					borderColor: '#66CC66',
					label: {
						borderColor: '#66CC66',
						style: {
							color: '#fff',
							background: '#66CC66',
							fontSize: '16px'
						},
						orientation: 'horizontal',
						text: '<?php echo '+' . $hourly_completed_shipments_count[$time]['completed_shipments_diff_compared_to_takt_value']; ?>'
					}
				},
				<?php elseif($hourly_completed_shipments_count[$time]['completed_shipments_diff_compared_to_takt_value'] < 0) : ?>
				{
					x: new Date('<?php echo $time; ?>').getTime(),
					borderColor: '#B22222',
					label: {
						borderColor: '#B22222',
						style: {
							color: '#fff',
							background: '#B22222',
							fontSize: '16px'
						},
						orientation: 'horizontal',
						text: '<?php echo $hourly_completed_shipments_count[$time]['completed_shipments_diff_compared_to_takt_value']; ?>'
					}
				},
				<?php endif; endfor; endif; ?>
			],
			points: [
				<?php 
					for($i=0; $i<$num_hours; $i++) :
						$time = date('Y-m-d H:i:s', strtotime('+'.$i.' hour '.$start_datetime));
						
						if(strtotime($time) > strtotime('+'.$timezone.' hour')) continue;
						
						if(!isset($hourly_completed_shipments_count[$time])) continue;
						
						if($hourly_completed_shipments_count[$time]['value'] > $takt_value) :
				?>
				{
					x: new Date('<?php echo $time; ?>').getTime(),
					y: <?php echo $hourly_completed_shipments_count[$time]['value']; ?>,
					marker: {
						size: 5,
						fillColor: '#66CC66',
						strokeColor: '#66CC66',
						radius: 2,
						cssClass: 'apexcharts-custom-class'
					}
				},
				<?php elseif($hourly_completed_shipments_count[$time]['value'] < $takt_value) : ?>
				{
					x: new Date('<?php echo $time; ?>').getTime(),
					y: <?php echo $hourly_completed_shipments_count[$time]['value']; ?>,
					marker: {
						size: 5,
						fillColor: '#B22222',
						strokeColor: '#B22222',
						radius: 2,
						cssClass: 'apexcharts-custom-class'
					}
				},
				<?php endif; endfor; ?>
			]
		},
		dataLabels: {
			enabled: true
		},
		grid: {
			borderColor: 'grey',
			row: {
				colors: 'grey'
			}
		},
		tooltip: {
			enabled: true,
			followCursor: true,
			x: {
				show: true,
				format: 'HH:mm'
			}
		},
		series: [
			{
				name: '#Completed Shipments',
				data: <?php echo json_encode(array_column($hourly_completed_shipments_count, 'value')); ?>,
			},
			{
				name: '#Past Completed Shipments',
				data: <?php echo json_encode(array_column($past_hourly_completed_shipments_count, 'value')); ?>,
			},
			<?php if(isset($graph_takt_value)): ?>
			{
				name: 'Takt Value',
				data: <?php echo json_encode(array_values($graph_takt_value)); ?>,
			}
			<?php endif; ?>
		],
		legend: {
			labels: {
				colors: 'lightgrey',
				useSeriesColors: false
			},
		},
		xaxis: {
			type: 'datetime',
			categories: <?php echo json_encode(array_keys($hourly_completed_shipments_count)); ?>,
			tickAmount: 'dataPoints',
			labels: {
				style: {
					colors: 'lightgrey'
				}
			},
			tooltip: {
				formatter: function(value) {
					var date = new Date(value);
					return date.getHours()+':00';
				}
			},
			title: {
				text: 'Hour',
				style: {
					color: 'lightgrey'
				}
			}
		},
		yaxis: {
			min: 0,
			max: <?php echo $hourly_completed_shipments_chart_max_scale; ?>,
			title: {
				text: '#Completed Shipments',
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
		colors: ['#2A9DF4', '#545454', '#ffa836']
	};
	
	var hourly_completed_shipments_chart = new ApexCharts(document.querySelector("#hourly-completed-shipments-chart"), hourly_completed_shipments_chart_options);
	
	hourly_completed_shipments_chart.render();
	
	var hourly_completed_shipments_per_minute_chart_options = {
		chart: {
			type: 'line',
			height: 300,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
		annotations: {
			xaxis: [
				{
					x: new Date('<?php echo $page_generated_time; ?>').getTime(),
					strokeDashArray: 0,
					borderColor: '#00E396',
					label: {
						borderColor: '#00E396',
						style: {
							color: '#fff',
							background: '#00E396',
						},
						text: 'Current time',
					}
				}
			],
			points: [
				<?php 
					for($i=0; $i<$num_hours; $i++) :
						$time = date('Y-m-d H:i:s', strtotime('+'.$i.' hour '.$start_datetime));
						
						if(!isset($hourly_completed_shipments_count[$time])) continue;
						
						if($hourly_completed_shipments_count[$time]['value_per_minute'] > $graph_takt_value_per_minute[$time]) :
				?>
				{
					x: new Date('<?php echo $time; ?>').getTime(),
					y: <?php echo $hourly_completed_shipments_count[$time]['value_per_minute']; ?>,
					marker: {
						size: 5,
						fillColor: '#66CC66',
						strokeColor: '#66CC66',
						radius: 2,
						cssClass: 'apexcharts-custom-class'
					}
				},
				<?php elseif($hourly_completed_shipments_count[$time]['value_per_minute'] < $takt_value_per_minute) : ?>
				{
					x: new Date('<?php echo $time; ?>').getTime(),
					y: <?php echo $hourly_completed_shipments_count[$time]['value_per_minute']; ?>,
					marker: {
						size: 5,
						fillColor: '#B22222',
						strokeColor: '#B22222',
						radius: 2,
						cssClass: 'apexcharts-custom-class'
					}
				},
				<?php endif; endfor; ?>
			]
		},
		dataLabels: {
			enabled: true,
			style: {
				colors: ['#EEE','#EEE','#EEE']
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
			followCursor: true,
			x: {
				show: true,
				format: 'HH:mm'
			}
		},
		series: [
			{
				name: '#Completed Shipments Per Minute',
				type: 'line',
				data: <?php echo json_encode(array_column($hourly_completed_shipments_count, 'value_per_minute')); ?>,
			},
			{
				name: '#Past Completed Shipments Per Minute',
				type: 'line',
				data: <?php echo json_encode(array_column($past_hourly_completed_shipments_count, 'value_per_minute')); ?>,
			},
			{
				name: '#Shipments Per Minute',
				type: 'bar',
				data: <?php echo json_encode(array_values($hourly_shipments_count_per_minute)); ?>,
			},
			<?php if(isset($graph_takt_value)): ?>
			{
				name: 'Takt Value',
				data: <?php echo json_encode(array_values($graph_takt_value_per_minute)); ?>,
			}
			<?php endif; ?>
		],
		legend: {
			labels: {
				colors: 'lightgrey',
				useSeriesColors: false
			},
		},
		xaxis: {
			type: 'datetime',
			categories: <?php echo json_encode(array_keys($hourly_completed_shipments_count)); ?>,
			tickAmount: 'dataPoints',
			labels: {
				style: {
					colors: 'lightgrey'
				}
			},
			tooltip: {
				formatter: function(value) {
					var date = new Date(value);
					return date.getHours()+':00';
				}
			},
			title: {
				text: 'Hour',
				style: {
					color: 'lightgrey'
				}
			}
		},
		yaxis: {
			min: 0,
			max: <?php echo $hourly_completed_shipments_per_minute_chart_max_scale; ?>,
			title: {
				text: '#Completed Shipments Per Minute',
				style: {
					color: 'lightgrey'
				}
			},
			labels: {
				style: {
					color: 'lightgrey'
				}
			},
			labels: {
				style: {
					color: 'lightgrey'
				}
			}
		},
		markers: {
			size: 5,
		},
		colors: ['#2A9DF4', '#545454', '#2149EE', '#ffa836']
	};
	
	var hourly_completed_shipments_per_minute_chart = new ApexCharts(document.querySelector("#hourly-completed-shipments-per-minute-chart"), hourly_completed_shipments_per_minute_chart_options);
	
	hourly_completed_shipments_per_minute_chart.render();
	
	var hourly_orders_chart_options = {
		chart: {
			type: 'line',
			height: 300,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
		annotations: {
			xaxis: [
				{
					x: new Date('<?php echo $page_generated_time; ?>').getTime(),
					strokeDashArray: 0,
					borderColor: '#00E396',
					label: {
						borderColor: '#00E396',
						style: {
							color: '#fff',
							background: '#00E396',
						},
						text: 'Current time',
					}
				}
			],
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
		tooltip: {
			enabled: true,
			followCursor: true,
			x: {
				show: true,
				format: 'HH:mm'
			}
		},
		series: [
			{
				name: '#Past Orders',
				type: 'line',
				data: <?php echo json_encode(array_values($past_hourly_orders_count)); ?>,
			},
			{
				name: '#Orders',
				type: 'bar',
				data: <?php echo json_encode(array_values($hourly_orders_count)); ?>,
			},
			{
				name: '#Completed Orders',
				data: <?php echo json_encode(array_column($hourly_completed_orders_count, 'value')); ?>,
			}
		],
		legend: {
			labels: {
				colors: 'lightgrey',
				useSeriesColors: false
			},
		},
		xaxis: {
			type: 'datetime',
			categories: <?php echo json_encode(array_keys($hourly_orders_count)); ?>,
			tickAmount: 'dataPoints',
			labels: {
				style: {
					colors: 'lightgrey'
				}
			},
			tooltip: {
				formatter: function(value) {
					var date = new Date(value);
					return date.getHours()+':00';
				}
			},
			title: {
				text: 'Hour',
				style: {
					color: 'lightgrey'
				}
			}
		},
		yaxis: {
			min: 0,
			max: <?php echo $hourly_orders_chart_max_scale; ?>,
			title: {
				text: '#Orders',
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
		colors: ['#545454', '#2149EE', '#2A9DF4']
	};
	
	var hourly_orders_chart = new ApexCharts(document.querySelector("#hourly-orders-chart"), hourly_orders_chart_options);
	
	hourly_orders_chart.render();
	
	<?php if(!empty($num_employees_scheduled) && !empty($operational_cost_per_package) && !empty($fte_cost_per_hour)) : ?>
	
	var hourly_cost_per_package_chart_options = {
		chart: {
			type: 'line',
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
		annotations: {
			yaxis: [
				<?php if(isset($cost_per_package_target)) : ?>
				{
					y: <?php echo $cost_per_package_target; ?>,
					strokeDashArray: 0,
					borderColor: '#ffa836',
					label: {
						borderColor: '#ffa836',
						style: {
							color: '#fff',
							background: '#ffa836',
						},
					text: 'Target',
					}
				},
				<?php endif; ?>
			],
			xaxis: [
				{
					x: new Date('<?php echo $page_generated_time; ?>').getTime(),
					strokeDashArray: 0,
					borderColor: '#00E396',
					label: {
						borderColor: '#00E396',
						style: {
							color: '#fff',
							background: '#00E396',
						},
						text: 'Current time',
						orientation: 'horizontal',
					}
				}
			],
			points: [
				<?php 
					for($i=0; $i<$num_hours; $i++) :
						$time = date('Y-m-d H:i:s', strtotime('+'.$i.' hour '.$start_datetime));
						
						if(strtotime($time) > strtotime('+'.$timezone.' hour')) continue;
						
						if(!isset($hourly_completed_shipments_count[$time])) continue;
						if(isset($cost_per_package_target) && $hourly_completed_shipments_count[$time]['cost_per_package'] > $cost_per_package_target) :
				?>
				{
					x: new Date('<?php echo $time; ?>').getTime(),
					y: <?php echo $hourly_completed_shipments_count[$time]['cost_per_package']; ?>,
					marker: {
						size: 5,
						fillColor: '#B22222',
						strokeColor: '#B22222',
						radius: 2,
						cssClass: 'apexcharts-custom-class'
					}
				},
				<?php elseif(isset($cost_per_package_target) && $hourly_completed_shipments_count[$time]['cost_per_package'] < $cost_per_package_target) : ?>
				{
					x: new Date('<?php echo $time; ?>').getTime(),
					y: <?php echo $hourly_completed_shipments_count[$time]['cost_per_package']; ?>,
					marker: {
						size: 5,
						fillColor: '#66CC66',
						strokeColor: '#66CC66',
						radius: 2,
						cssClass: 'apexcharts-custom-class'
					}
				},
				<?php endif; endfor; ?>
			]
		},
		dataLabels: {
			enabled: true,
			formatter: function (val, opts) {
				if(opts.seriesIndex == 0) {
					return '$' + val;
				}
				
				return val;
			},
		},
		grid: {
			borderColor: 'grey',
			row: {
				colors: 'grey'
			}
		},
		tooltip: {
			enabled: true,
			followCursor: true,
			x: {
				show: true,
				format: 'HH:mm'
			},
			y: {
				formatter: function (val, opts) {
					if(opts.seriesIndex == 0) {
						return '$' + val;
					}
					
					return val;
				},
			}
		},
		series: [
			{
				name: 'Cost Per Package',
				type: 'bar',
				data: <?php echo json_encode(array_column($hourly_completed_shipments_count, 'cost_per_package')); ?>,
			},
			{
				name: 'Labor Hours Per Package',
				type: 'line',
				data: <?php echo json_encode(array_column($hourly_completed_shipments_count, 'hour_per_package')); ?>,
			},
		],
		stroke: {
			width: [0, 4]
		},
		legend: {
			labels: {
				colors: 'lightgrey',
				useSeriesColors: false
			},
		},
		xaxis: {
			type: 'datetime',
			categories: <?php echo json_encode(array_keys($hourly_completed_shipments_count)); ?>,
			tickAmount: 'dataPoints',
			labels: {
				style: {
					colors: 'lightgrey'
				}
			},
			tooltip: {
				formatter: function(value) {
					var date = new Date(value);
					return date.getHours()+':00';
				}
			},
			title: {
				text: 'Hour',
				style: {
					color: 'lightgrey'
				}
			}
		},
		yaxis: [
			{
				min: 0,
				max: <?php echo $hourly_cost_per_package_chart_max_scale; ?>,
				title: {
					text: 'Cost',
					style: {
						color: 'lightgrey'
					}
				},
				labels: {
					style: {
						color: 'lightgrey'
					},
					formatter: function (val) {
						return '$' + val;
					},
				},
			},
			{
				min: 0,
				max: <?php echo $hourly_hour_per_package_chart_max_scale; ?>,
				opposite: true,
				title: {
					text: 'Hours',
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
		],
		markers: {
			size: 5,
		},
		colors: ['#00b32c', '#b399dd']
	};
	
	var hourly_cost_per_package_chart = new ApexCharts(document.querySelector("#hourly-cost-per-package-chart"), hourly_cost_per_package_chart_options);
	
	hourly_cost_per_package_chart.render();	
	
	<?php endif; ?>
</script>

<?php endif; ?>