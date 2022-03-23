<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<!-- ApexCharts JS -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
	var kaizen_manager_pace_chart_options = {
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
				borderRadius: 4,
				horizontal: true,
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
		annotations: {
			xaxis: [
				{
					x: <?php echo $current_takt_value_per_minute; ?>,
					strokeDashArray: 0,
					borderColor: '#ED7D31',
					label: {
						borderColor: '#ED7D31',
						style: {
							color: '#fff',
							background: '#ED7D31',
						},
						text: 'Takt: <?php echo $current_takt_value_per_minute; ?>',
						orientation: 'horizontal',
					}
				}
			],
		},
		series: [
			{
				name: 'Total Actions Per Minute',
				data: [
					{
						x: 'Packing',
						y: <?php echo $action_pace['packing'] ?>
					},
					{
						x: 'Picking',
						y: <?php echo $action_pace['picking'] ?>
					},
					{
						x: 'Loading',
						y: <?php echo $action_pace['loading'] ?>
					}
				]
			}
		],
		xaxis: {
			categories: ['Packing','Picking','Loading'],
			labels: {
				style: {
					colors: 'lightgrey'
				}
			}
		},
		yaxis: {
			labels: {
				style: {
					color: 'lightgrey',
					colors: 'lightgrey'
				}
			},
		},
		colors: [function({ value, seriesIndex, w }) {
			if (value < <?php echo $current_takt_value_per_minute; ?> ) {
				return '#B22222'
			} else {
				return '#66CC66'
			}
		}]
	};
	
	var kaizen_manager_pace_chart = new ApexCharts(document.querySelector("#kaizen-manager-pace-chart"), kaizen_manager_pace_chart_options);
	kaizen_manager_pace_chart.render();


	var kaizen_manager_packing_chart_options = {
		chart: {
			type: 'line',
			height: 300,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
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
				name: 'Packing Action',
				data: <?php echo json_encode(array_values($packing_action_count)); ?>,
			},
			{
				name: 'Takt',
				data: <?php echo json_encode(array_values($takt_value)); ?>,
			}
		],
		annotations: {
			xaxis: [
				<?php
					foreach($packing_action_count as $the_time => $the_value) :
						if(strtotime($the_time) >= strtotime(date('H:00', strtotime('+1 hour')))) continue;
					
						$diff = $the_value - $takt_value[$the_time];
						if($diff >= 0) :
				?>
				{
					x: '<?php echo $the_time; ?>',
					borderColor: '#66CC66',
					label: {
						borderColor: '#66CC66',
						style: {
							color: '#fff',
							background: '#66CC66',
							fontSize: '16px'
						},
						orientation: 'horizontal',
						text: '<?php echo '+' . $diff; ?>'
					}
				},
				<?php else : ?>
				{
					x: '<?php echo $the_time; ?>',
					borderColor: '#B22222',
					label: {
						borderColor: '#B22222',
						style: {
							color: '#fff',
							background: '#B22222',
							fontSize: '16px'
						},
						orientation: 'horizontal',
						text: '<?php echo $diff; ?>'
					}
				},
				<?php endif; endforeach; ?>
			]
		},
		legend: {
			labels: {
				colors: 'lightgrey',
				useSeriesColors: false
			},
		},
		xaxis: {
			type: 'category',
			categories: <?php echo json_encode(array_keys($packing_action_count)); ?>,
			tickAmount: 'dataPoints',
			labels: {
				style: {
					colors: 'lightgrey'
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
			/*max: ???,*/
			title: {
				text: '#Action',
				style: {
					color: 'lightgrey'
				}
			},
			forceNiceScale: true,
			labels: {
				style: {
					color: 'lightgrey',
					colors: 'lightgrey'
				}
			},
		},
		markers: {
			size: 5,
		},
		colors: ['#2A9DF4', '#ED7D31']
	};
	
	var kaizen_manager_packing_chart = new ApexCharts(document.querySelector("#kaizen-manager-packing-chart"), kaizen_manager_packing_chart_options);
	kaizen_manager_packing_chart.render();
	
	
	var kaizen_manager_picking_chart_options = {
		chart: {
			type: 'line',
			height: 300,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
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
				name: 'Picking Action',
				data: <?php echo json_encode(array_values($picking_action_count)); ?>,
			},
			{
				name: 'Takt',
				data: <?php echo json_encode(array_values($takt_value)); ?>,
			}
		],
		annotations: {
			xaxis: [
				<?php
					foreach($picking_action_count as $the_time => $the_value) :
						if(strtotime($the_time) >= strtotime(date('H:00', strtotime('+1 hour')))) continue;
					
						$diff = $the_value - $takt_value[$the_time];
						if($diff >= 0) :
				?>
				{
					x: '<?php echo $the_time; ?>',
					borderColor: '#66CC66',
					label: {
						borderColor: '#66CC66',
						style: {
							color: '#fff',
							background: '#66CC66',
							fontSize: '16px'
						},
						orientation: 'horizontal',
						text: '<?php echo '+' . $diff; ?>'
					}
				},
				<?php else : ?>
				{
					x: '<?php echo $the_time; ?>',
					borderColor: '#B22222',
					label: {
						borderColor: '#B22222',
						style: {
							color: '#fff',
							background: '#B22222',
							fontSize: '16px'
						},
						orientation: 'horizontal',
						text: '<?php echo $diff; ?>'
					}
				},
				<?php endif; endforeach; ?>
			]
		},
		legend: {
			labels: {
				colors: 'lightgrey',
				useSeriesColors: false
			},
		},
		xaxis: {
			type: 'category',
			categories: <?php echo json_encode(array_keys($picking_action_count)); ?>,
			tickAmount: 'dataPoints',
			labels: {
				style: {
					colors: 'lightgrey'
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
			/*max: ???,*/
			title: {
				text: '#Action',
				style: {
					color: 'lightgrey'
				}
			},
			forceNiceScale: true,
			labels: {
				style: {
					color: 'lightgrey',
					colors: 'lightgrey'
				}
			},
		},
		markers: {
			size: 5,
		},
		colors: ['#2A9DF4', '#ED7D31']
	};
	
	var kaizen_manager_picking_chart = new ApexCharts(document.querySelector("#kaizen-manager-picking-chart"), kaizen_manager_picking_chart_options);
	kaizen_manager_picking_chart.render();
	
	
	var kaizen_manager_loading_chart_options = {
		chart: {
			type: 'line',
			height: 300,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
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
				name: 'Loading Action',
				data: <?php echo json_encode(array_values($loading_action_count)); ?>,
			},
			{
				name: 'Takt',
				data: <?php echo json_encode(array_values($takt_value)); ?>,
			}
		],
		annotations: {
			xaxis: [
				<?php
					foreach($loading_action_count as $the_time => $the_value) :
						if(strtotime($the_time) >= strtotime(date('H:00', strtotime('+1 hour')))) continue;
					
						$diff = $the_value - $takt_value[$the_time];
						if($diff >= 0) :
				?>
				{
					x: '<?php echo $the_time; ?>',
					borderColor: '#66CC66',
					label: {
						borderColor: '#66CC66',
						style: {
							color: '#fff',
							background: '#66CC66',
							fontSize: '16px'
						},
						orientation: 'horizontal',
						text: '<?php echo '+' . $diff; ?>'
					}
				},
				<?php else : ?>
				{
					x: '<?php echo $the_time; ?>',
					borderColor: '#B22222',
					label: {
						borderColor: '#B22222',
						style: {
							color: '#fff',
							background: '#B22222',
							fontSize: '16px'
						},
						orientation: 'horizontal',
						text: '<?php echo $diff; ?>'
					}
				},
				<?php endif; endforeach; ?>
			]
		},
		legend: {
			labels: {
				colors: 'lightgrey',
				useSeriesColors: false
			},
		},
		xaxis: {
			type: 'category',
			categories: <?php echo json_encode(array_keys($loading_action_count)); ?>,
			tickAmount: 'dataPoints',
			labels: {
				style: {
					colors: 'lightgrey'
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
			/*max: ???,*/
			title: {
				text: '#Action',
				style: {
					color: 'lightgrey'
				}
			},
			forceNiceScale: true,
			labels: {
				style: {
					color: 'lightgrey',
					colors: 'lightgrey'
				}
			},
		},
		markers: {
			size: 5,
		},
		colors: ['#2A9DF4', '#ED7D31']
	};
	
	var kaizen_manager_loading_chart = new ApexCharts(document.querySelector("#kaizen-manager-loading-chart"), kaizen_manager_loading_chart_options);
	kaizen_manager_loading_chart.render();
</script>

<?php endif; ?>