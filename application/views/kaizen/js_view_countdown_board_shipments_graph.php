<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<script>
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
					for($i=0; $i<24; $i++) :
						if($date == date('Y-m-d') && $i > intval(date('G'))) continue; 
						
						$time = $date . ' ' . sprintf('%02d:00:00', $i);
						
						if(!isset($hourly_completed_shipments_count[$time])) continue;
						
						if($hourly_completed_shipments_count[$time]['value_per_minute'] > $takt_value_per_minute) :
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
			enabledOnSeries: [1,2]
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
				data: <?php echo json_encode(array_column($hourly_completed_shipments_count, 'value_per_minute')); ?>,
			},
			{
				name: '#Past Completed Shipments Per Minute',
				data: <?php echo json_encode(array_column($past_hourly_completed_shipments_count, 'value_per_minute')); ?>,
			},
			{
				name: 'Takt',
				data: <?php echo json_encode(array_values($hourly_takt_value_per_minute)); ?>,
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
		},
		markers: {
			size: [5, 5, 0],
		},
		stroke: {
			width: [5, 5, 1]
		},
		colors: ['#2A9DF4', '#545454', '#FFA836']
	};
	
	var hourly_completed_shipments_per_minute_chart = new ApexCharts(document.querySelector("#hourly-completed-shipments-per-minute-chart"), hourly_completed_shipments_per_minute_chart_options);
	
	hourly_completed_shipments_per_minute_chart.render();
</script>

<?php endif; ?>