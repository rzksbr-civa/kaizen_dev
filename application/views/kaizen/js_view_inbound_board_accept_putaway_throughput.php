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
	var accept_putaway_throughput_table = $('#accept-putaway-throughput-table').DataTable( {
		dom: 'Z<"col-md-6"l><"col-md-6"f>r<"table-container"t><"col-md-12"i><"col-md-6"B><"col-md-6"p>',
		buttons: [
			{
				extend: 'excel',
				text: 'Download',
				exportOptions: {columns: ':visible'}
			}
		],
	} );

	var accept_putaway_throughput_chart_options = {
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
				format: "<?php echo $datetime_formatter; ?>"
			}
		},
		legend: {
			labels: {
				colors: 'lightgrey'
			}
		},
		series: [
			{
				type: 'bar',
				name: '# of Pallets Processed',
				data: <?php echo json_encode(array_column($accept_putaway_throughput_data, 'num_pallets_processed')); ?>,
			},
			{
				type: 'bar',
				name: '# of SKUs Processed',
				data: <?php echo json_encode(array_column($accept_putaway_throughput_data, 'num_skus_processed')); ?>,
			},
			{
				type: 'line',
				name: '# of ASNn Processed',
				data: <?php echo json_encode(array_column($accept_putaway_throughput_data, 'asn_count')); ?>,
			},
			{
				type: 'line',
				name: 'Average Accept End - Putaway Throughput (Hrs)',
				data: <?php echo json_encode(array_column($accept_putaway_throughput_data, 'average_accept_putaway_duration')); ?>,
			},
		],
		xaxis: {
			type: '<?php echo count($accept_putaway_throughput_data) > 10 ? 'datetime' : 'category'; ?>',
			categories: <?php echo json_encode(array_column($accept_putaway_throughput_data, 'period')); ?>,
			tickPlacement: 'on',
			tickAmount: 'dataPoints',
			labels: {
				minHeight: 55,
				showDuplicates: false,
				style: {
					colors: 'lightgrey'
				},
				datetimeFormatter: {
					day: "<?php echo $datetime_formatter; ?>"
				}
			}
		},
		yaxis: [
			{
				seriesName: '# of Pallets Processed',
				forceNiceScale: true,
				decimalsInFloat: 0,
				min: 0,
				max: <?php echo ceil(max(
					max(array_column($accept_putaway_throughput_data, 'num_pallets_processed')),
					max(array_column($accept_putaway_throughput_data, 'num_skus_processed'))) * 1.1 / 100) * 100; ?>,
				title: {
					text: '# Processed',
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
			{
				seriesName: '# of SKUs Processed',
				show: false,
				forceNiceScale: true,
				decimalsInFloat: 0,
				min: 0,
				max: <?php echo ceil(max(
					max(array_column($accept_putaway_throughput_data, 'num_pallets_processed')),
					max(array_column($accept_putaway_throughput_data, 'num_skus_processed'))) * 1.1 / 100) * 100; ?>,
				title: {
					text: '# Processed',
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
			{
				seriesName: '# of ASNs Processed',
				show: false,
				forceNiceScale: true,
				decimalsInFloat: 0,
				min: 0,
				max: <?php echo ceil(max(
					max(array_column($accept_putaway_throughput_data, 'asn_count')),
					max(array_column($accept_putaway_throughput_data, 'asn_count'))) * 1.1 / 100) * 100; ?>,
				title: {
					text: '# Processed',
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
			{
				seriesName: 'Average Accept End - Putaway Throughput (Hrs)',
				min: 0,
				max: <?php echo ceil(max(array_column($accept_putaway_throughput_data, 'average_accept_putaway_duration')) * 1.1 / 10) * 10; ?>,
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
				}
			}
		],
		<?php if(count($accept_putaway_throughput_data) == 1) : ?>
		markers: {
			size: 5,
		},
		<?php else : ?>
		markers: {
			size: 1,
		},
		<?php endif; ?>
	};
	
	var accept_putaway_throughput_chart = new ApexCharts(document.querySelector("#accept-putaway-throughput-chart"), accept_putaway_throughput_chart_options);
	
	accept_putaway_throughput_chart.render();
</script>

<?php endif; ?>