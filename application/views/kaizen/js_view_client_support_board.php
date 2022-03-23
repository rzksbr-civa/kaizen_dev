<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<!-- ApexCharts JS -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<!-- Datatables JS -->
<script src="<?php echo base_url('assets/datatables/1.10.18/datatables.min.js'); ?>"></script>

<!-- ColResizable JS -->
<script src="<?php echo base_url('assets/colresize/dataTables.colResize.js'); ?>"></script>

<?php if($periodicity == 'hourly'): ?>
<script>
	window.setInterval('refresh()', 60000);

    function refresh() {
		location.reload();
	}
</script>
<?php endif; ?>

<script>
	var total_open_tickets_per_day_chart_options = {
		chart: {
			type: 'line',
			height: 300,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
		annotations: {
			<?php if($periodicity == 'hourly' && strtotime($period_from) <= strtotime($page_generated_time) && strtotime('+1 day ' . $period_to) >= strtotime($page_generated_time)) : ?>
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
			<?php endif; ?>
		},
		plotOptions: {

		},
		dataLabels: {
			enabled: true,
			enabledOnSeries: [1,2,3],
			offsetY: -5
		},
		tooltip: {
			enabled: true,
			followCursor: false,
			x: {
				show: false,
				format: "<?php echo $chart_options['datetime_formatter']; ?>"
			}
		},
		grid: {
			borderColor: '#333'
		},
		legend: {
			labels: {
				colors: 'lightgrey'
			}
		},
		series: [
			{
				type: 'line',
				name: '#Open Tickets',
				categories: <?php echo json_encode(array_keys($open_tickets_count_by_date)); ?>,
				data: <?php echo json_encode(array_values($open_tickets_count_by_date)); ?>,
			},
			{
				type: 'bar',
				name: '#New Tickets',
				categories: <?php echo json_encode(array_keys($new_tickets_count_by_date)); ?>,
				data: <?php echo json_encode(array_values($new_tickets_count_by_date)); ?>,
			},
			{
				type: 'bar',
				name: '#Closed Tickets',
				categories: <?php echo json_encode(array_keys($closed_tickets_count_by_date)); ?>,
				data: <?php echo json_encode(array_values($closed_tickets_count_by_date)); ?>,
			},
			{
				type: 'line',
				name: 'Efficiency',
				categories: <?php echo json_encode(array_keys($ticket_efficiency)); ?>,
				data: <?php echo json_encode(array_values($ticket_efficiency)); ?>,
			},
		],
		xaxis: {
			type: 'datetime',
			categories: <?php echo json_encode(array_keys($open_tickets_count_by_date)); ?>,
			tickPlacement: 'on',
			tickAmount: 'dataPoints',
			labels: {
				rotate: -45,
				rotateAlways: true,
				minHeight: 55,
				showDuplicates: false,
				style: {
					colors: 'lightgrey'
				},
				datetimeFormatter: {
					day: "<?php echo $chart_options['datetime_formatter']; ?>"
				}
			},
			tooltip: {
				enabled: false
			}
		},
		yaxis: [
			<?php $max_tickets = ceil(max(
					max(array_values($open_tickets_count_by_date)),
					max(array_values($new_tickets_count_by_date)),
					max(array_values($closed_tickets_count_by_date))
					) * 1.1 / 100) * 100; ?>
			{
				seriesName: '#Open Tickets',
				min: 0,
				max: <?php echo $max_tickets; ?>,
				decimalsInFloat: 0,
				title: {
					text: '#Tickets',
					style: {
						color: 'lightgrey'
					}
				},
				labels: {
					style: {
						color: 'lightgrey',
						colors: 'lightgrey'
					}
				}
			},
			{
				seriesName: '#New Tickets',
				min: 0,
				max: <?php echo $max_tickets; ?>,
				decimalsInFloat: 0,
				show: false
			},
			{
				seriesName: '#Open Tickets',
				min: 0,
				max: <?php echo $max_tickets; ?>,
				decimalsInFloat: 0,
				show: false
			},
			{
				seriesName: 'Efficiency',
				min: <?php echo min(50, floor(min(array_values($ticket_efficiency)) * 0.9 / 10) * 10); ?>,
				max: <?php echo ceil(max(array_values($ticket_efficiency)) * 1.1 / 10) * 10; ?>,
				forceNiceScale: true,
				opposite: true,
				decimalsInFloat: 0,
				title: {
					text: 'Efficiency',
					style: {
						color: 'lightgrey'
					}
				},
				labels: {
					style: {
						color: 'lightgrey'
					},
					formatter: function(val, index) {
						return val + '%';
					}
				}
			}
		],
		<?php if($periodicity == 'hourly' || count(array_keys($open_tickets_count_by_date)) == 1) : ?>
		markers: {
			size: 5,
		},
		<?php endif; ?>
	};
	
	var total_open_tickets_per_day_chart = new ApexCharts(document.querySelector("#total-open-tickets-per-day-chart"), total_open_tickets_per_day_chart_options);
	
	total_open_tickets_per_day_chart.render();
	
	
	var total_open_tickets_by_type_per_day_chart_options = {
		chart: {
			type: 'line',
			height: 500,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
		annotations: {
			<?php if($periodicity == 'hourly' && strtotime($period_from) <= strtotime($page_generated_time) && strtotime('+1 day ' . $period_to) >= strtotime($page_generated_time)) : ?>
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
			<?php endif; ?>
		},
		plotOptions: {

		},
		dataLabels: {
			enabled: false
		},
		grid: {
			borderColor: '#333'
		},
		legend: {
			labels: {
				colors: 'lightgrey'
			}
		},
		series: [
			<?php $data_points = array(); foreach($ticket_types as $ticket_type_name) : ?>
			{
				type: 'line',
				name: '<?php echo $ticket_type_name; ?>',
				categories: <?php echo json_encode(array_keys($open_tickets_count_by_type_and_date[$ticket_type_name])); ?>,
				data: <?php echo json_encode(array_values($open_tickets_count_by_type_and_date[$ticket_type_name])); ?>,
			},
			
			<?php $data_points = array_keys($open_tickets_count_by_type_and_date[$ticket_type_name]); ?>
			
			<?php endforeach; ?>
		],
		tooltip: {
			enabled: true,
			followCursor: false,
			<?php if(count($data_points) < 30): ?>
			shared: false,
			<?php endif; ?>
			x: {
				show: false,
				format: "<?php echo $chart_options['datetime_formatter']; ?>"
			}
		},
		xaxis: {
			type: 'datetime',
			categories: <?php echo json_encode($data_points); ?>,
			tickPlacement: 'on',
			tickAmount: 'dataPoints',
			labels: {
				rotate: -45,
				rotateAlways: true,
				minHeight: 55,
				showDuplicates: false,
				style: {
					colors: 'lightgrey'
				},
				datetimeFormatter: {
					day: "<?php echo $chart_options['datetime_formatter']; ?>"
				}
			},
			tooltip: {
				enabled: false
			}
		},
		yaxis: {
			min: 0,
			title: {
				text: '#Open Tickets',
				style: {
					color: 'lightgrey'
				}
			},
			labels: {
				style: {
					color: 'lightgrey',
					colors: 'lightgrey'
				}
			}
		},
		stroke: {
			width: 1
		},
		<?php if(count($data_points) < 30) : ?>
		markers: {
			size: 5,
		},
		<?php endif; ?>
	};
	
	var total_open_tickets_by_type_per_day_chart = new ApexCharts(document.querySelector("#total-open-tickets-by-type-per-day-chart"), total_open_tickets_by_type_per_day_chart_options);
	
	total_open_tickets_by_type_per_day_chart.render();
	
	
	var average_age_of_closed_tickets_chart_options = {
		chart: {
			type: 'line',
			height: 500,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
		annotations: {
			<?php if($periodicity == 'hourly' && strtotime($period_from) <= strtotime($page_generated_time) && strtotime('+1 day ' . $period_to) >= strtotime($page_generated_time)) : ?>
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
			<?php endif; ?>
		},
		plotOptions: {

		},
		dataLabels: {
			enabled: false
		},
		grid: {
			borderColor: '#333'
		},
		legend: {
			labels: {
				colors: 'lightgrey'
			}
		},
		series: [
			<?php foreach(array_merge(array('All'),$ticket_types) as $ticket_type_name) : 
					if(!empty($average_tickets_age_by_type_and_date_for_chart[$ticket_type_name])) : ?>
			{
				type: 'line',
				name: '<?php echo $ticket_type_name; ?>',
				data: <?php echo json_encode($average_tickets_age_by_type_and_date_for_chart[$ticket_type_name]); ?>,
			},
			
			<?php $data_points = array_keys($average_tickets_age_by_type_and_date_for_chart[$ticket_type_name]); ?>
			
			<?php endif; endforeach; ?>
		],
		tooltip: {
			enabled: true,
			followCursor: false,
			<?php if(count($data_points) < 30): ?>
			shared: false,
			<?php endif; ?>
			x: {
				show: false,
				format: "<?php echo $chart_options['datetime_formatter']; ?>"
			}
		},
		xaxis: {
			type: 'datetime',
			categories: <?php echo json_encode($data_points); ?>,
			tickPlacement: 'on',
			tickAmount: 'dataPoints',
			labels: {
				rotate: -45,
				rotateAlways: true,
				minHeight: 55,
				showDuplicates: false,
				style: {
					colors: 'lightgrey'
				},
				datetimeFormatter: {
					day: "<?php echo $chart_options['datetime_formatter']; ?>"
				}
			},
			tooltip: {
				enabled: false
			}
		},
		yaxis: {
			min: 0,
			forceNiceScale: true,
			decimalsInFloat: 2,
			title: {
				text: 'Days',
				style: {
					color: 'lightgrey'
				}
			},
			labels: {
				style: {
					color: 'lightgrey',
					colors: 'lightgrey'
				}
			}
		},
		stroke: {
			width: 1
		},
		<?php if(count($data_points) < 30) : ?>
		markers: {
			size: 5,
		},
		<?php endif; ?>
	};
	
	
	var average_age_of_closed_tickets_chart = new ApexCharts(document.querySelector("#average-age-of-closed-tickets-chart"), average_age_of_closed_tickets_chart_options);
	
	average_age_of_closed_tickets_chart.render();
	
	<?php if(!empty($ticket_statuses)): ?>
	
	var total_open_tickets_by_status_per_day_chart_options = {
		chart: {
			type: 'line',
			height: 500,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
		annotations: {
			<?php if($periodicity == 'hourly' && strtotime($period_from) <= strtotime($page_generated_time) && strtotime('+1 day ' . $period_to) >= strtotime($page_generated_time)) : ?>
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
			<?php endif; ?>
		},
		plotOptions: {

		},
		dataLabels: {
			enabled: false
		},
		grid: {
			borderColor: '#333'
		},
		legend: {
			labels: {
				colors: 'lightgrey'
			}
		},
		series: [
			<?php foreach($ticket_statuses as $ticket_status) : ?>
			{
				type: 'line',
				name: '<?php echo $ticket_status; ?>',
				categories: <?php echo json_encode(array_keys($open_tickets_count_by_status_and_date[$ticket_status])); ?>,
				data: <?php echo json_encode(array_values($open_tickets_count_by_status_and_date[$ticket_status])); ?>,
			},
			
			<?php $data_points = array_keys($open_tickets_count_by_status_and_date[$ticket_status]); ?>
			
			<?php endforeach; ?>
		],
		tooltip: {
			enabled: true,
			followCursor: false,
			<?php if(count($data_points) < 30): ?>
			shared: false,
			<?php endif; ?>
			x: {
				show: false,
				format: "<?php echo $chart_options['datetime_formatter']; ?>"
			}
		},
		xaxis: {
			type: 'datetime',
			categories: <?php echo json_encode($data_points); ?>,
			tickPlacement: 'on',
			tickAmount: 'dataPoints',
			labels: {
				rotate: -45,
				rotateAlways: true,
				minHeight: 55,
				showDuplicates: false,
				style: {
					colors: 'lightgrey'
				},
				datetimeFormatter: {
					day: "<?php echo $chart_options['datetime_formatter']; ?>"
				}
			},
			tooltip: {
				enabled: false
			}
		},
		yaxis: {
			min: 0,
			title: {
				text: '#Open Tickets',
				style: {
					color: 'lightgrey'
				}
			},
			labels: {
				style: {
					color: 'lightgrey',
					colors: 'lightgrey'
				}
			}
		},
		stroke: {
			width: 1
		},
		<?php if(count($data_points) < 30) : ?>
		markers: {
			size: 5,
		},
		<?php endif; ?>
	};
	
	<?php if(empty($customer)): ?>
		var total_open_tickets_by_status_per_day_chart = new ApexCharts(document.querySelector("#total-open-tickets-by-status-per-day-chart"), total_open_tickets_by_status_per_day_chart_options);
		
		total_open_tickets_by_status_per_day_chart.render();
	<?php endif; ?>
	
	<?php endif; ?>
	
	
	
	var average_age_of_closed_tickets_by_group_chart_options = {
		chart: {
			type: 'line',
			height: 500,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
		annotations: {
			<?php if($periodicity == 'hourly' && strtotime($period_from) <= strtotime($page_generated_time) && strtotime('+1 day ' . $period_to) >= strtotime($page_generated_time)) : ?>
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
			<?php endif; ?>
		},
		plotOptions: {

		},
		dataLabels: {
			enabled: false
		},
		grid: {
			borderColor: '#333'
		},
		legend: {
			labels: {
				colors: 'lightgrey'
			}
		},
		series: [
			<?php foreach(array_keys($average_tickets_age_by_group_and_date_for_chart) as $group_name) : 
					if(!empty($average_tickets_age_by_group_and_date_for_chart[$group_name])) : ?>
			{
				type: 'line',
				name: '<?php echo $group_name; ?>',
				data: <?php echo json_encode($average_tickets_age_by_group_and_date_for_chart[$group_name]); ?>,
			},
			
			<?php $data_points = array_keys($average_tickets_age_by_group_and_date_for_chart[$group_name]); ?>
			
			<?php endif; endforeach; ?>
		],
		tooltip: {
			enabled: true,
			followCursor: false,
			<?php if(count($data_points) < 30): ?>
			shared: false,
			<?php endif; ?>
			x: {
				show: false,
				format: "<?php echo $chart_options['datetime_formatter']; ?>"
			}
		},
		xaxis: {
			type: 'datetime',
			categories: <?php echo json_encode($data_points); ?>,
			tickPlacement: 'on',
			tickAmount: 'dataPoints',
			labels: {
				rotate: -45,
				rotateAlways: true,
				minHeight: 55,
				showDuplicates: false,
				style: {
					colors: 'lightgrey'
				},
				datetimeFormatter: {
					day: "<?php echo $chart_options['datetime_formatter']; ?>"
				}
			},
			tooltip: {
				enabled: false
			}
		},
		yaxis: {
			min: 0,
			forceNiceScale: true,
			decimalsInFloat: 2,
			title: {
				text: 'Days',
				style: {
					color: 'lightgrey'
				}
			},
			labels: {
				style: {
					color: 'lightgrey',
					colors: 'lightgrey'
				}
			}
		},
		stroke: {
			width: 1
		},
		<?php if(count($data_points) < 30) : ?>
		markers: {
			size: 5,
		},
		<?php endif; ?>
	};
	
	
	var average_age_of_closed_tickets_by_group_chart = new ApexCharts(document.querySelector("#average-age-of-closed-tickets-by-group-chart"), average_age_of_closed_tickets_by_group_chart_options);
	
	average_age_of_closed_tickets_by_group_chart.render();

	
	var total_open_tickets_by_group_per_day_chart_options = {
		chart: {
			type: 'line',
			height: 500,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
		annotations: {
			<?php if($periodicity == 'hourly' && strtotime($period_from) <= strtotime($page_generated_time) && strtotime('+1 day ' . $period_to) >= strtotime($page_generated_time)) : ?>
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
			<?php endif; ?>
		},
		plotOptions: {

		},
		dataLabels: {
			enabled: false
		},
		grid: {
			borderColor: '#333'
		},
		legend: {
			labels: {
				colors: 'lightgrey'
			}
		},
		series: [
			<?php foreach(array_keys($open_tickets_count_by_group_and_date) as $group_name) : ?>
			{
				type: 'line',
				name: '<?php echo $group_name; ?>',
				categories: <?php echo json_encode(array_keys($open_tickets_count_by_group_and_date[$group_name])); ?>,
				data: <?php echo json_encode(array_values($open_tickets_count_by_group_and_date[$group_name])); ?>,
			},
			
			<?php $data_points = array_keys($open_tickets_count_by_group_and_date[$group_name]); ?>
			
			<?php endforeach; ?>
		],
		tooltip: {
			enabled: true,
			followCursor: false,
			<?php if(count($data_points) < 30): ?>
			shared: false,
			<?php endif; ?>
			x: {
				show: false,
				format: "<?php echo $chart_options['datetime_formatter']; ?>"
			}
		},
		xaxis: {
			type: 'datetime',
			categories: <?php echo json_encode($data_points); ?>,
			tickPlacement: 'on',
			tickAmount: 'dataPoints',
			labels: {
				rotate: -45,
				rotateAlways: true,
				minHeight: 55,
				showDuplicates: false,
				style: {
					colors: 'lightgrey'
				},
				datetimeFormatter: {
					day: "<?php echo $chart_options['datetime_formatter']; ?>"
				}
			},
			tooltip: {
				enabled: false
			}
		},
		yaxis: {
			min: 0,
			title: {
				text: '#Open Tickets',
				style: {
					color: 'lightgrey'
				}
			},
			labels: {
				style: {
					color: 'lightgrey',
					colors: 'lightgrey'
				}
			}
		},
		stroke: {
			width: 1
		},
		<?php if(count($data_points) < 30) : ?>
		markers: {
			size: 5,
		},
		<?php endif; ?>
	};
	
	var total_open_tickets_by_group_per_day_chart = new ApexCharts(document.querySelector("#total-open-tickets-by-group-per-day-chart"), total_open_tickets_by_group_per_day_chart_options);
	
	total_open_tickets_by_group_per_day_chart.render();
	
	
	var average_initial_response_time_chart_options = {
		chart: {
			type: 'line',
			height: 500,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
		annotations: {
			<?php if($periodicity == 'hourly' && strtotime($period_from) <= strtotime($page_generated_time) && strtotime('+1 day ' . $period_to) >= strtotime($page_generated_time)) : ?>
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
			<?php endif; ?>
		},
		plotOptions: {

		},
		dataLabels: {
			enabled: false
		},
		grid: {
			borderColor: '#333'
		},
		legend: {
			labels: {
				colors: 'lightgrey'
			}
		},
		series: [
			{
				type: 'line',
				name: 'Average Initial Response Time',
				data: <?php echo json_encode($average_initial_response_time_by_date_for_chart); ?>,
			},
			
			<?php $data_points = array_keys($open_tickets_count_by_status_and_date[$ticket_status]); ?>
		],
		tooltip: {
			enabled: true,
			followCursor: false,
			<?php if(count($data_points) < 30): ?>
			shared: false,
			<?php endif; ?>
			x: {
				show: false,
				format: "<?php echo $chart_options['datetime_formatter']; ?>"
			}
		},
		xaxis: {
			type: 'datetime',
			categories: <?php echo json_encode($data_points); ?>,
			tickPlacement: 'on',
			tickAmount: 'dataPoints',
			labels: {
				rotate: -45,
				rotateAlways: true,
				minHeight: 55,
				showDuplicates: false,
				style: {
					colors: 'lightgrey'
				},
				datetimeFormatter: {
					day: "<?php echo $chart_options['datetime_formatter']; ?>"
				}
			},
			tooltip: {
				enabled: false
			}
		},
		yaxis: {
			min: 0,
			forceNiceScale: true,
			decimalsInFloat: 2,
			title: {
				text: 'Minutes',
				style: {
					color: 'lightgrey'
				}
			},
			labels: {
				style: {
					color: 'lightgrey',
					colors: 'lightgrey'
				}
			}
		},
		<?php if(count($data_points) < 30) : ?>
		markers: {
			size: 5,
		},
		<?php endif; ?>
	};
	
	var average_initial_response_time_chart = new ApexCharts(document.querySelector("#average-initial-response-time-chart"), average_initial_response_time_chart_options);
	
	average_initial_response_time_chart.render();
	
	
	var days_opened_by_group_chart_options = {
		chart: {
			type: 'bar',
			height: 500,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
		dataLabels: {
			enabled: true
		},
		grid: {
			borderColor: '#333'
		},
		legend: {
			labels: {
				colors: 'lightgrey'
			}
		},
		series: [
			{
				type: 'bar',
				name: 'Days Opened by Group',
				data: <?php echo json_encode(array_values($days_opened_by_group_for_chart)); ?>,
			},
		],
		xaxis: {
			type: 'category',
			categories: <?php echo json_encode(array_keys($days_opened_by_group_for_chart)); ?>,
			labels: {
				rotate: -45,
				rotateAlways: true,
				minHeight: 55,
				showDuplicates: false,
				style: {
					colors: 'lightgrey'
				}
			},
			tooltip: {
				enabled: false
			}
		},
		yaxis: {
			min: 0,
			forceNiceScale: true,
			decimalsInFloat: 2,
			title: {
				text: 'Days',
				style: {
					color: 'lightgrey'
				}
			},
			labels: {
				style: {
					color: 'lightgrey',
					colors: 'lightgrey'
				}
			}
		}
	};
	
	var days_opened_by_group_chart = new ApexCharts(document.querySelector("#days-opened-by-group-chart"), days_opened_by_group_chart_options);
	
	days_opened_by_group_chart.render();
	
	
	var total_tickets_opened_by_group_chart_options = {
		chart: {
			type: 'bar',
			height: 500,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
		dataLabels: {
			enabled: true
		},
		grid: {
			borderColor: '#333'
		},
		legend: {
			labels: {
				colors: 'lightgrey'
			}
		},
		series: [
			{
				type: 'bar',
				name: 'Total Tickets Opened by Group',
				data: <?php echo json_encode(array_values($total_tickets_opened_by_group_for_chart)); ?>,
			},
		],
		xaxis: {
			type: 'category',
			categories: <?php echo json_encode(array_keys($total_tickets_opened_by_group_for_chart)); ?>,
			labels: {
				rotate: -45,
				rotateAlways: true,
				minHeight: 55,
				showDuplicates: false,
				style: {
					colors: 'lightgrey'
				}
			},
			tooltip: {
				enabled: false
			}
		},
		yaxis: {
			min: 0,
			forceNiceScale: true,
			decimalsInFloat: 2,
			title: {
				text: 'Tickets',
				style: {
					color: 'lightgrey'
				}
			},
			labels: {
				style: {
					color: 'lightgrey',
					colors: 'lightgrey'
				}
			}
		}
	};
	
	var total_tickets_opened_by_group_chart = new ApexCharts(document.querySelector("#total-tickets-opened-by-group-chart"), total_tickets_opened_by_group_chart_options);
	
	total_tickets_opened_by_group_chart.render();
	
	$('#new-open-tickets-count-table').DataTable( {
		dom: 'Z<"col-md-6"l><"col-md-6"f>r<"table-container"t><"col-md-12"i><"col-md-6"B><"col-md-6"p>',
		buttons: [
			{
				extend: 'excel',
				text: 'Download',
				exportOptions: {columns: ':visible'},
				filename: 'New And Open Tickets Count'
			}
		],
	} );
	
	$('#open-tickets-by-type-table').DataTable( {
		dom: 'Z<"col-md-6"l><"col-md-6"f>r<"table-container"t><"col-md-12"i><"col-md-6"B><"col-md-6"p>',
		buttons: [
			{
				extend: 'excel',
				text: 'Download',
				exportOptions: {columns: ':visible'},
				filename: 'Open Tickets By Type'
			}
		],
	} );
	
	$('#average-tickets-age-by-type-table').DataTable( {
		dom: 'Z<"col-md-6"l><"col-md-6"f>r<"table-container"t><"col-md-12"i><"col-md-6"B><"col-md-6"p>',
		buttons: [
			{
				extend: 'excel',
				text: 'Download',
				exportOptions: {columns: ':visible'},
				filename: 'Average Tickets Age By Type'
			}
		],
	} );
	
	$('#open-tickets-by-status-table').DataTable( {
		dom: 'Z<"col-md-6"l><"col-md-6"f>r<"table-container"t><"col-md-12"i><"col-md-6"B><"col-md-6"p>',
		buttons: [
			{
				extend: 'excel',
				text: 'Download',
				exportOptions: {columns: ':visible'},
				filename: 'Open Tickets By Status'
			}
		],
	} );
	
	$('#average-initial-response-time').DataTable( {
		dom: 'Z<"col-md-6"l><"col-md-6"f>r<"table-container"t><"col-md-12"i><"col-md-6"B><"col-md-6"p>',
		buttons: [
			{
				extend: 'excel',
				text: 'Download',
				exportOptions: {columns: ':visible'},
				filename: 'Average Initial Response Time'
			}
		],
	} );
	
	$('#top-users-with-assigned-open-tickets').DataTable( {
		dom: 'Z<"col-md-6"l><"col-md-6"f>r<"table-container"t><"col-md-12"i><"col-md-6"B><"col-md-6"p>',
		buttons: [
			{
				extend: 'excel',
				text: 'Download',
				exportOptions: {columns: ':visible'},
				filename: 'Top Users with Assigned Open Ticket'
			}
		],
		aaSorting: [[1,'desc']]
	} );
</script>

<?php endif; ?>