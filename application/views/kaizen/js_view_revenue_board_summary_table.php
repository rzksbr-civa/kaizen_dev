<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- ApexCharts JS -->
<script src="<?php echo base_url('assets/apexcharts/apexcharts.min.js'); ?>"></script>

<?php if($generate) : ?>

<script>
	var outbound_revenue_chart_options = {
		series: [{
          data: [<?php echo $revenue_summary['total_package_revenue'] . ',' . 
							$revenue_summary['wages_with_overhead']['outbound'] . ',' .
							$revenue_summary['outbound_profit']; ?>]
        }],
          chart: {
          type: 'bar',
          height: 300,
		  fontFamily: 'Mukta'
        },
        plotOptions: {
          bar: {
            barHeight: '100%',
            distributed: true,
            horizontal: true,
            dataLabels: {
              position: 'bottom'
            },
          }
        },
        colors: ['#2a9fd6', '#ff8800', '<?php echo $revenue_summary['outbound_profit'] >= 0 ? '#77b300' : '#c21807'; ?>'
        ],
        dataLabels: {
          enabled: true,
          textAnchor: 'start',
          style: {
			  fontSize: '18px',
            colors: ['#fff']
          },
          formatter: function (val, opt) {
            return opt.w.globals.labels[opt.dataPointIndex] + ":  $" + (Math.round(val * 100) / 100).toLocaleString('en')
          },
          offsetX: 0,
		  offsetY: -10,
          dropShadow: {
            enabled: true
          }
        },
        stroke: {
          width: 1,
          colors: ['#fff']
        },
        xaxis: {
          categories: ['Package Revenue','Outbound Wages','Outbound Profit'
          ],
		  labels: {
			  style: {
				  colors: ['#cccccc']
			  }
		  }
        },
        yaxis: {
          labels: {
            show: false
          }
        },
        tooltip: {
          theme: 'light',
          x: {
            show: false
          },
          y: {
            title: {
              formatter: function () {
                return ''
              }
            }
          }
        }
	};
	
	var outbound_revenue_chart = new ApexCharts(document.querySelector("#outbound-revenue-chart"), outbound_revenue_chart_options);
	
	outbound_revenue_chart.render();
	
	
	var inbound_revenue_chart_options = {
		series: [{
          data: [<?php echo $revenue_summary['inbound_revenue'] . ',' .
							$revenue_summary['wages_with_overhead']['inbound'] . ',' .
							($revenue_summary['inbound_revenue']-$revenue_summary['wages_with_overhead']['inbound']); ?>]
        }],
          chart: {
          type: 'bar',
          height: 300,
		  fontFamily: 'Mukta'
        },
        plotOptions: {
          bar: {
            barHeight: '100%',
            distributed: true,
            horizontal: true,
            dataLabels: {
              position: 'bottom'
            },
          }
        },
        colors: ['#2a9fd6', '#ff8800', '<?php echo ($revenue_summary['wages_with_overhead']['inbound'] <= $revenue_summary['inbound_revenue']) ? '#77b300' : '#c21807'; ?>'
        ],
        dataLabels: {
          enabled: true,
          textAnchor: 'start',
          style: {
			  fontSize: '18px',
            colors: ['#fff']
          },
          formatter: function (val, opt) {
            return opt.w.globals.labels[opt.dataPointIndex] + ":  $" + (Math.round(val * 100) / 100).toLocaleString('en')
          },
          offsetX: 0,
		  offsetY: -10,
          dropShadow: {
            enabled: true
          }
        },
        stroke: {
          width: 1,
          colors: ['#fff']
        },
        xaxis: {
          categories: ['Inbound Revenue','Inbound Wages','Inbound Profit'
          ],
		  labels: {
			  style: {
				  colors: ['#cccccc']
			  }
		  }
        },
        yaxis: {
          labels: {
            show: false
          }
        },
        tooltip: {
          theme: 'light',
          x: {
            show: false
          },
          y: {
            title: {
              formatter: function () {
                return ''
              }
            }
          }
        }
	};
	
	var inbound_revenue_chart = new ApexCharts(document.querySelector("#inbound-revenue-chart"), inbound_revenue_chart_options);
	
	inbound_revenue_chart.render();
	
	<?php foreach( array('inventory','kitting','leads','ltl','material') as $dept_code ): ?>
	
	var <?php echo $dept_code; ?>_revenue_chart_options = {
		series: [{
          data: [<?php echo $revenue_summary['wages_with_overhead'][$dept_code]; ?>]
        }],
          chart: {
          type: 'bar',
          height: 150,
		  fontFamily: 'Mukta'
        },
        plotOptions: {
          bar: {
            barHeight: '100%',
            distributed: true,
            horizontal: true,
            dataLabels: {
              position: 'bottom'
            },
          }
        },
        colors: ['#ff8800'
        ],
        dataLabels: {
          enabled: true,
          textAnchor: 'start',
          style: {
			  fontSize: '18px',
            colors: ['#fff']
          },
          formatter: function (val, opt) {
            return opt.w.globals.labels[opt.dataPointIndex] + ":  $" + (Math.round(val * 100) / 100).toLocaleString('en')
          },
          offsetX: 0,
		  offsetY: -10,
          dropShadow: {
            enabled: true
          }
        },
        stroke: {
          width: 1,
          colors: ['#fff']
        },
        xaxis: {
          categories: ['<?php echo ucwords($dept_code); ?> Wages'
          ],
		  labels: {
			  style: {
				  colors: ['#cccccc']
			  }
		  }
        },
        yaxis: {
          labels: {
            show: false
          }
        },
        tooltip: {
          theme: 'light',
          x: {
            show: false
          },
          y: {
            title: {
              formatter: function () {
                return ''
              }
            }
          }
        }
	};
	
	var <?php echo $dept_code; ?>_revenue_chart = new ApexCharts(document.querySelector("#<?php echo $dept_code; ?>-revenue-chart"), <?php echo $dept_code; ?>_revenue_chart_options);
	
	<?php echo $dept_code; ?>_revenue_chart.render();
	
	<?php endforeach; ?>
</script>

<?php endif; ?>