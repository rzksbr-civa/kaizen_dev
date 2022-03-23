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
			],
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
		colors: ['#2A9DF4', '#545454']
	};
	
	var hourly_completed_shipments_chart = new ApexCharts(document.querySelector("#hourly-completed-shipments-chart"), hourly_completed_shipments_chart_options);
	
	hourly_completed_shipments_chart.render();
	
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
</script>

<?php endif; ?>