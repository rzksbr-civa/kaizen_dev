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
	var drops_count_board_table = $('#drops-count-board-table').DataTable( {
		dom: 'Z<"col-md-6"l><"col-md-6"f>r<"table-container"t><"col-md-12"i><"col-md-6"B><"col-md-6"p>',
		buttons: [
			{
				extend: 'excel',
				title: 'Drops Count Board - <?php echo $period_from . ' - ' . $period_to; ?>',
				text: 'Download',
				exportOptions: {columns: ':visible'}
			}
		],
	} );

	var drops_count_chart_options = {
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
				<?php if($periodicity == 'hourly'): ?>
					format: "dd MMM h:mmTT"
				<?php elseif($periodicity == 'monthly'): ?>
					format: "MMM yyyy"
				<?php elseif($periodicity == 'yearly'): ?>
					format: "yyyy"
				<?php endif; ?>
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
				name: 'New Drops',
				data: <?php echo json_encode($drops_count_data_for_chart); ?>,
			}
		],
		xaxis: {
			type: 'datetime',
			tickPlacement: 'on',
			tickAmount: 'dataPoints',
			labels: {
				minHeight: 55,
				showDuplicates: false,
				style: {
					colors: 'lightgrey'
				},
				datetimeFormatter: {
					year: 'yyyy',
					month: 'MMM \'yy',
					day: 'dd MMM',
					hour: 'HH:mm'
				}
			},
			title: {
				text: 'Datetime',
				style: {
					color: 'lightgrey'
				}
			}
		},
		yaxis: [
			{
				seriesName: 'Total New Drops',
				forceNiceScale: true,
				decimalsInFloat: 0,
				min: 0,
				max: <?php echo ceil(
					max(array_column($drops_count_data, 'drops_count')) * 1.1 / 10) * 10; ?>,
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
			size: <?php echo count($drops_count_data) < 10 ? 5 : 0; ?>,
		},
		stroke: {
			width: <?php echo count($drops_count_data) < 10 ? 5 : 1; ?>,
		},
		colors: ['#2A9DF4', '#B22222']
	};
	
	var drops_count_chart = new ApexCharts(document.querySelector("#drops-count-chart"), drops_count_chart_options);
	
	drops_count_chart.render();
</script>

<?php endif; ?>