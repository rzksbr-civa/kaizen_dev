<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<!-- ApexCharts JS -->
<script src="<?php echo base_url('assets/apexcharts/apexcharts.min.js'); ?>"></script>

<!-- Datatables JS -->
<script src="<?php echo base_url('assets/datatables/1.10.18/datatables.min.js'); ?>"></script>

<!-- ColResizable JS -->
<script src="<?php echo base_url('assets/colresize/dataTables.colResize.js'); ?>"></script>

<script>
	var inventory_board_table = $('#inventory-board-table').DataTable( {
		dom: 'Z<"col-md-6"l><"col-md-6"f>r<"table-container"t><"col-md-12"i><"col-md-6"B><"col-md-6"p>',
		buttons: [
			{
				extend: 'excel',
				title: 'Inventory Board - <?php echo $date; ?>',
				text: 'Download',
				exportOptions: {columns: ':visible'}
			}
		],
	} );

	var total_drops_per_hour_chart_options = {
		chart: {
			type: 'line',
			height: 300,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
		grid: {
			borderColor: '#333'
		},
		tooltip: {
			enabled: true,
			followCursor: false,
			x: {
				format: "h:mmTT"
			}
		},
		legend: {
			labels: {
				colors: 'lightgrey'
			}
		},
		series: [
			{
				type: 'line',
				name: 'Total Drops Per Hour',
				data: <?php echo json_encode(array_values($total_drops_per_hour_data['total'])); ?>,
			},
			{
				type: 'bar',
				name: 'New Drops Per Hour',
				data: <?php echo json_encode(array_values($live_drops_per_hour_data)); ?>,
			}
		],
		xaxis: {
			type: 'datetime',
			categories: <?php echo json_encode($hours); ?>,
			tickPlacement: 'on',
			tickAmount: 'dataPoints',
			labels: {
				minHeight: 55,
				showDuplicates: false,
				style: {
					colors: 'lightgrey'
				},
				datetimeFormatter: {
					hour: "h:mmTT"
				}
			},
			title: {
				text: 'Hour of Day',
				style: {
					color: 'lightgrey'
				}
			}
		},
		yaxis: [
			{
				seriesName: 'Total Drops Per Hour',
				forceNiceScale: true,
				decimalsInFloat: 0,
				min: 0,
				max: <?php echo ceil(
					max(
						max(array_values($total_drops_per_hour_data['total'])),
						max(array_values($live_drops_per_hour_data)),
					) * 1.1 / 10) * 10; ?>,
				title: {
					text: 'Drops',
					style: {
						color: 'lightgrey'
					}
				},
				labels: {
					style: {
						color: 'lightgrey'
					}
				}
			}
		],
		markers: {
			size: 5,
		},
		colors: ['#2A9DF4', '#B22222']
	};
	
	var total_drops_per_hour_chart = new ApexCharts(document.querySelector("#total-drops-per-hour-chart"), total_drops_per_hour_chart_options);
	
	total_drops_per_hour_chart.render();
	
	<?php 
		if(!empty($total_drops_per_hour_data['user_breakdown'])) :
			$i = 0;
			foreach($total_drops_per_hour_data['user_breakdown'] as $name => $value): $i++;
	?>
	
	var user_<?php echo $i; ?>_drops_per_hour_chart_options = {
		chart: {
			type: 'line',
			height: 300,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
		grid: {
			borderColor: '#333'
		},
		tooltip: {
			enabled: true,
			followCursor: false,
			x: {
				format: "h:mmTT"
			}
		},
		legend: {
			labels: {
				colors: 'lightgrey'
			}
		},
		series: [
			{
				type: 'line',
				name: 'Total Drops Per Hour',
				data: <?php echo json_encode(array_values($total_drops_per_hour_data['user_breakdown'][$name])); ?>,
			},
		],
		xaxis: {
			type: 'datetime',
			categories: <?php echo json_encode($hours); ?>,
			tickPlacement: 'on',
			tickAmount: 'dataPoints',
			labels: {
				minHeight: 55,
				showDuplicates: false,
				style: {
					colors: 'lightgrey'
				},
				datetimeFormatter: {
					hour: "h:mmTT"
				}
			},
			title: {
				text: 'Hour of Day',
				style: {
					color: 'lightgrey'
				}
			}
		},
		yaxis: [
			{
				seriesName: 'Total Drops Per Hour',
				forceNiceScale: true,
				decimalsInFloat: 0,
				min: 0,
				max: <?php echo ceil(max(array_values($total_drops_per_hour_data['user_breakdown'][$name])) * 1.1 / 10) * 10; ?>,
				title: {
					text: 'Drops',
					style: {
						color: 'lightgrey'
					}
				},
				labels: {
					style: {
						color: 'lightgrey'
					}
				}
			}
		],
		markers: {
			size: 5,
		},
	};
	
	var user_<?php echo $i; ?>_drops_per_hour_chart = new ApexCharts(document.querySelector("#user-<?php echo $i; ?>-drops-per-hour-chart"), user_<?php echo $i; ?>_drops_per_hour_chart_options);
	
	user_<?php echo $i; ?>_drops_per_hour_chart.render();
	
	<?php endforeach; endif; ?>
</script>

<?php endif; ?>