<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- ApexCharts JS -->
<script src="<?php echo base_url('assets/apexcharts/apexcharts.min.v202005.js'); ?>"></script>

<script>
	var client_complexity_and_profitability_chart_options = {
		series: <?php echo json_encode($data); ?>,
		chart: {
			height: 'auto',
			type: 'scatter',
			zoom: {
				enabled: true,
				type: 'xy'
			},
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
		responsive: [{
			breakpoint: 1000,
			options: {
				chart: {
					height: '1200'
				}
			}
		}],
		annotations: {
			yaxis: [{
				y: <?php echo $max_profitability / 2; ?>,
				strokeDashArray: 0,
				borderColor: '#FFA500',
			}],
			xaxis: [{
				x: <?php echo $max_complexity / 2; ?>,
				strokeDashArray: 0,
				borderColor: '#FFA500'
			}]
		},
		colors: <?php echo json_encode($series_colors); ?>,
		legend: {
			show: true,
			labels: {
				colors: 'lightgrey',
				useSeriesColors: false
			},
		},
		xaxis: {
			max: <?php echo $max_complexity; ?>,
			tickAmount: 10,
			labels: {
				formatter: function(val) {
					return parseFloat(val).toFixed(1)
				},
				style: {
					colors: 'lightgrey'
				}
			},
			title: {
				text: 'Complexity',
				style: {
					color: 'lightgrey'
				}
			}
		},
		yaxis: {
			tickAmount: 5,
			forceNiceScale: true,
			title: {
				text: 'Profitability',
				style: {
					color: 'lightgrey'
				}
			},
			labels: {
				style: {
					colors: 'lightgrey'
				}
			},
			axisBorder: {
				show: true,
				color: 'lightgrey',
				offsetX: 0,
				offsetY: 0
			},
		},
		tooltip: {
			custom: function({series, seriesIndex, dataPointIndex, w}) {
				return '<div style="padding:10px;"><strong>'+w.globals.seriesNames[seriesIndex]+'</strong><br>Complexity: '+w.globals.seriesX[seriesIndex][0]+'<br>Profitability: Rank'+w.globals.series[seriesIndex][0]+'</div>';
			}
		},
		grid: {
			xaxis: {
				lines: {
					show: false
				}
			},   
			yaxis: {
				lines: {
					show: false
				}
			}, 
		}
	};
	
	var client_complexity_and_profitability_chart = new ApexCharts(document.querySelector("#client-complexity-and-profitability-chart"), client_complexity_and_profitability_chart_options);
	
	client_complexity_and_profitability_chart.render();
</script>