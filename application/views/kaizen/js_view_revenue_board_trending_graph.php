<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- ApexCharts JS -->
<script src="<?php echo base_url('assets/apexcharts/apexcharts.min.js'); ?>"></script>

<?php if($generate) : ?>

<script>
	<?php foreach($department_list as $department_code => $department_name) : ?>
	var <?php echo $department_code; ?>_chart_options = {
		series: [
			<?php if(isset($trending_graph[$department_name]['revenue'])) : ?>
				{
					name: 'Revenue',
					data: <?php echo json_encode(array_values($trending_graph[$department_name]['revenue'])); ?>
				},
			<?php endif; ?>
			{
				name: 'Wages with overhead',
				data: <?php echo json_encode(array_values($trending_graph[$department_name]['wages_with_overhead'])); ?>
			}
			<?php if(isset($trending_graph[$department_name]['profit'])) : ?>
				, {
					name: 'Profit',
					data: <?php echo json_encode(array_values($trending_graph[$department_name]['profit'])); ?>
				},
			<?php endif; ?>
		],
		chart: {
			type: 'line',
			height: 350,
			fontFamily: 'Mukta'
		},
		colors: ['#8AC8FF', '#F42185', '#5DEECD'],
		plotOptions: {
			bar: {
				horizontal: false,
				columnWidth: '55%',
				endingShape: 'rounded'
			},
		},
		dataLabels: {
			enabled: false
		},
		tooltip: {
			enabled: true,
			followCursor: false,
			custom: function({series, seriesIndex, dataPointIndex, w}) {
				var theTooltip = '<div style="padding:5px 10px;">';
				
				if(typeof w.config.series[0].data[dataPointIndex] !== 'undefined') {
					theTooltip += 'Revenue: $'+(Math.round(w.config.series[0].data[dataPointIndex]*100)/100)+'<br>';
				}
				if(typeof w.config.series[1].data[dataPointIndex] !== 'undefined') {
					theTooltip += 'Wages with overhead: $'+(Math.round(w.config.series[1].data[dataPointIndex]*100)/100)+'<br>';
				}
				if(typeof w.config.series[2].data[dataPointIndex] !== 'undefined') {
					theTooltip += 'Profit: $'+(Math.round(w.config.series[2].data[dataPointIndex]*100)/100)+'<br>';
				}
				
				theTooltip += '</div>';
				
				return theTooltip;
			},
			x: {
				<?php if($periodicity == 'weekly' && count($trending_graph_label) >= 15) : ?>
				formatter: function(value) {
					var date = new Date(value);
					return 'Week ' + date.toISOString().split('T')[0];
				},
				<?php elseif($periodicity == 'monthly') : ?>
				format: 'MMM-yyyy',
				<?php endif; ?>
			}
		},
		legend: {
			show: true,
			labels: {
				colors: 'lightgrey',
				useSeriesColors: false
			}
		},
		grid: {
			borderColor: '#3E5493'
		},
		stroke: {
			width: 2
		},
		xaxis: {
			<?php if(count($trending_graph_label) < 15) : ?>
			categories: <?php echo json_encode(array_values($trending_graph_label)); ?>,
			<?php else: ?>
			type: 'datetime',
			categories: <?php echo json_encode(array_values($trending_graph_label_date)); ?>,
			<?php endif; ?>
			labels: {
				rotate: -45,
				rotateAlways: true,
				<?php if(count($trending_graph_label) >= 15) : ?>
				formatter: function(value) {
					var date = new Date(value);
					return date.toISOString().split('T')[0];
				},
				<?php endif; ?>
				style: {
					colors: 'lightgrey'
				}
			}
		},
		yaxis: {
			title: {
				text: '$',
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
		<?php //if(count($trending_graph_label) < 2) : ?>
		markers: {
			size: 2,
		},
		<?php //endif; ?>
	};

	var <?php echo $department_code; ?>_chart = new ApexCharts(document.querySelector("#<?php echo $department_code; ?>-trending-chart"), <?php echo $department_code; ?>_chart_options);
	<?php echo $department_code; ?>_chart.render();
	
	<?php endforeach; ?>
</script>

<?php endif; ?>