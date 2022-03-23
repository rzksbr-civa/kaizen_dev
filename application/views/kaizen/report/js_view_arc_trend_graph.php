<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- ApexCharts JS -->
<script src="<?php echo base_url('assets/apexcharts/apexcharts.min.js'); ?>"></script>

<script>
	<?php if(!empty($graph)) : ?>
	
		<?php foreach($graph as $graph_name => $graph_data) : ?>
			var <?php echo $graph_name; ?>_options = {
				chart: {
					type: 'line',
					height: 300,
					fontFamily: 'Mukta'
				},
				plotOptions: {

				},
				dataLabels: {
					enabled: false
				},
				tooltip: {
					enabled: true,
					followCursor: false,
					custom: function({series, seriesIndex, dataPointIndex, w}) {
						return '<div style="padding:5px 10px;">'+w.config.series[0].data[dataPointIndex]+'</div>';
					},
				},
				series: [{
					type: 'line',
					name: '#Case',
					categories: <?php echo json_encode(array_keys($graph_data)); ?>,
					data: <?php echo json_encode(array_values($graph_data)); ?>,
				}],
				xaxis: {
					<?php if(($graph_name == 'weekly' && $num_days >= 20*7) || ($graph_name == 'monthly' && $num_days >= 20*30)) : ?>
					type: 'datetime',
					<?php endif; ?>
					categories: <?php echo json_encode(array_keys($graph_data)); ?>,
					tickPlacement: 'between',
					tickAmount: 'dataPoints',
					labels: {
						rotate: -45,
						rotateAlways: true,
						<?php if($graph_name == 'weekly' && $num_days < 20*7) : ?>
						formatter: function(value) {
							var date = new Date(value);
							return 'Week ' + ISO8601_week_no(date) + ' ('+date.getFullYear()+')';
						},
						<?php elseif($graph_name == 'monthly') : ?>
						format: 'MMM-yyyy',
						<?php endif; ?>
						style: {
							colors: 'lightgrey'
						}
					},
					tooltip: {
						<?php if($graph_name == 'weekly' && $num_days > 20*7) : ?>
						formatter: function(value) {
							var date = new Date(value);
							return 'Week ' + ISO8601_week_no(date) + ' ('+date.getFullYear()+')';
						}
						<?php elseif($graph_name == 'monthly') : ?>
						formatter: function(value) {
							var date = new Date(value);
							return monthNames[date.getMonth()] + '-' + date.getFullYear();
						}
						<?php endif; ?>
					}
				},
				yaxis: {
					min: 0,
					title: {
						text: '#Case',
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
					size: 5
				}
			}

			var <?php echo $graph_name; ?>_chart = new ApexCharts(document.querySelector("#trend-graph-<?php echo $graph_name; ?>"), <?php echo $graph_name; ?>_options);
			<?php echo $graph_name; ?>_chart.render();
		<?php endforeach; ?>
	<?php endif; ?>

const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

function ISO8601_week_no(dt) {
	var tdt = new Date(dt.valueOf());
	var dayn = (dt.getDay() + 6) % 7;
	tdt.setDate(tdt.getDate() - dayn + 3);
	var firstThursday = tdt.valueOf();
	tdt.setMonth(0, 1);
	if (tdt.getDay() !== 4) {
		tdt.setMonth(0, 1 + ((4 - tdt.getDay()) + 7) % 7);
	}
	return 1 + Math.ceil((firstThursday - tdt) / 604800000);
}
</script>