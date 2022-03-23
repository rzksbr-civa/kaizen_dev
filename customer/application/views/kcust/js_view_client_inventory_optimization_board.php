<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<style type="text/css">
	.apexcharts-yaxis-annotations rect {
		opacity: 0.5;
	}
	
	.align-left {
		text-align: left !important;
	}
	
	div.dataTables_wrapper div.dataTables_filter {
		text-align: left;
	}
	
	#client-inventory-optimization-board-table_wrapper .download-btn-group {
		text-align: right;
	}
</style>

<!-- Datatables JS -->
<script src="<?php echo base_url('assets/datatables/1.10.18/datatables.min.js'); ?>"></script>

<!-- ColResizable JS -->
<script src="<?php echo base_url('assets/colresize/dataTables.colResize.js'); ?>"></script>

<!-- ApexCharts JS -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.jsdelivr.net/npm/moment"></script>

<script>
	const getAverageOfArray = arr => arr.reduce((a,b) => a + b, 0) / arr.length;

	$.fn.dataTableExt.oSort['custom-num-fmt-desc'] = function(x,y) {
		if(x=="-") {
			return 1;
		}
		else if(y=="-") {
			return -1;
		}
		else {
			return parseFloat(y)-parseFloat(x);
		}
	};
	$.fn.dataTableExt.oSort['custom-num-fmt-asc'] = function(x,y) {
		if(x=="-") {
			return 1;
		}
		else if(y=="-") {
			return -1;
		}
		else {
			return parseFloat(x)-parseFloat(y);
		}
	};
	
	var client_inventory_optimization_board_table = $('.client-inventory-optimization-board-table').DataTable( {
		dom: 'Z<"col-md-6"f><"col-md-6 download-btn-group"B>r<"table-container"t><"col-md-6"l><"col-md-12"i><"col-md-12"p>',
		buttons: [
			{
				extend: 'excel',
				text: 'Download',
				exportOptions: {columns: ':visible'}
			},
			'colvis'
		],
		data: <?php echo json_encode($client_inventory_optimization_board_2d_data); ?>,
		deferRender: true,
		columnDefs: [
			{ targets: [4,5,6,7,8,9,10,11,12,13,14,15], className:'dt-right', type:'custom-num-fmt' },
			{ targets: [1,16], className:'dt-center' },
			{ targets: [11,12], visible:false },
			{ 
				targets: 0,
				data: 2,
				render: function(data, type, row, meta) {
					return '<a class="glyphicon glyphicon-stats generate-sku-demand-graph" aria-hidden="true" sku="'+data+'" style="cursor:pointer;"></a>';
				},
				className: 'dt-center'
			}
		],
		aaSorting: [[4,'<?php echo ($sort_order == 'lowest_to_highest') ? 'asc' : 'desc'; ?>']],
	} );
	
	var inventory_snapshot_chart_options = {
		chart: {
			type: 'donut',
			height: 300,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
        responsive: [{
			breakpoint: 480,
			options: {
				chart: {
					width: 200
				},
				legend: {
					position: 'bottom'
				}
			}
		}],
		dataLabels: {
			enabled: true,
			style: {
				colors: ['#FFF','#000','#FFF']
			}
		},
		legend: {
			labels: {
				colors: ['#CCC']
			}
		},
		series: [<?php echo $inventory_snapshot_chart_data['out_of_stock'] . ',' . $inventory_snapshot_chart_data['running_out'] . ',' . $inventory_snapshot_chart_data['ok']; ?>],
		labels: ['Out of Stock', 'Running Out', 'OK'],
		colors: ['#C52428', '#FFFF00', '#028A0F'],
		tooltip: {
			fillSeriesColor: false,
			y: {
				formatter: function(value) {
					return Math.round(value);
				}
			}
		}
	};
	
	var inventory_snapshot_chart = new ApexCharts(document.querySelector("#inventory-snapshot-chart"), inventory_snapshot_chart_options);
	
	inventory_snapshot_chart.render();
	
	
	var warehouse_inventory_distribution_chart_options = {
		chart: {
			type: 'bar',
			stacked: true,
			height: 300,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			}
		},
        responsive: [{
			breakpoint: 480,
			options: {
				chart: {
					width: 200
				},
				legend: {
					position: 'bottom'
				}
			}
		}],
		dataLabels: {
			enabled: true,
			formatter: function(value) {
				return numberWithCommas(value);
			},
			style: {
				colors: ['#FFF','#000','#FFF']
			}
		},
		legend: {
			labels: {
				colors: ['#CCC']
			}
		},
		plotOptions: {
          bar: {
            horizontal: false,
          },
        },
		series: [
			{
				name: 'On Hand',
				data: <?php echo json_encode(array_column($warehouse_inventory_distribution_chart_data,'On Hand')); ?>
			},
			{
				name: 'Inbound',
				data: <?php echo json_encode(array_column($warehouse_inventory_distribution_chart_data,'Inbound')); ?>
			},
			{
				name: 'Backorder',
				data: <?php echo json_encode(array_column($warehouse_inventory_distribution_chart_data,'Backorder')); ?>
			}
		],
		xaxis: {
			type: 'category',
			categories: <?php echo json_encode(array_keys($warehouse_inventory_distribution_chart_data)); ?>,
			labels: {
				style: {
					colors: '#CCC'
				}
			}
		},
		yaxis: {
			show: true,
			labels: {
				style: {
					colors: ['#CCC']
				},
				formatter: function(value) {
					return numberWithCommas(value);
				}
			},
			title: {
				text: 'Inventory Units',
				style: {
					color: '#CCC'
				}
			}
		},
		grid: {
			borderColor: '#333'
		},
		colors: ['#008FFB', '#FFFF00', '#C52428']
	};
	
	var warehouse_inventory_distribution_chart = new ApexCharts(document.querySelector("#warehouse-inventory-distribution-chart"), warehouse_inventory_distribution_chart_options);
	
	warehouse_inventory_distribution_chart.render();
	
	function numberWithCommas(x) {
		return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}
	
	var sku_historical_demand_graph_options = {
		chart: {
			type: 'line',
			height: 350,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			},
			stacked: false,
			zoom: {
				autoScaleYaxis: true
			}
		},
		dataLabels: {
			enabled: false
		},
		markers: {
			size: 0
		},
		fill: {
			type:'solid',
          opacity: [0.35, 1],
		},
		series: [{
          name: 'Historical Demand',
		  type: 'area',
		  data: []
        },
		/* {
			name: 'Forecasted Demand Trend',
			type: 'line',
			data: [
			]
		}*/],
		xaxis: {
			type: 'datetime',
			min: 1361919600000,
			showDuplicates: false,
			labels: {
				rotate: -15,
				rotateAlways: true,
				style: {
					colors: '#CCC'
				},
				formatter: function(val, timestamp) {
					return moment(new Date(timestamp)).format("MMM YYYY")
				}
			}
		},
		yaxis: {
			show: true,
			labels: {
				style: {
					colors: ['#CCC']
				},
				formatter: function(value) {
					return Math.round(value);
				}
			}
		},
		grid: {
			borderColor: '#333'
		},
		legend: {
			labels: {
				colors: ['#CCC']
			}
		},
		colors: ['#08CB0E', '#CC00FF'],
		stroke: {
			width: 3,
			curve: 'smooth',
			dashArray: [0, 2]
		}
	};
	
	var historical_inventory_levels_graph_options = {
		chart: {
			type: 'area',
			height: 350,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			},
			stacked: false,
			zoom: {
				autoScaleYaxis: true
			}
		},
		fill: {
			type:'solid',
          opacity: [0.35, 0.35, 0.35, 0.35],
		},
		dataLabels: {
			enabled: false
		},
		markers: {
			size: 0
		},
		series: [
		{
          name: 'On Hand Qty (End of the month snapshot)',
		  data: []
        },
		{
          name: 'Backorder Qty',
		  data: []
        },
		{
          name: 'Inbound Qty',
		  data: []
        },
		{
          name: 'On Hand Qty (Daily average)',
		  data: []
        }],
		xaxis: {
			type: 'datetime',
			min: 1361919600000,
			showDuplicates: false,
			labels: {
				rotate: -15,
				rotateAlways: true,
				style: {
					colors: '#CCC'
				},
				formatter: function(val, timestamp) {
					return moment(new Date(timestamp)).format("MMM YYYY")
				}
			}
		},
		yaxis: {
			show: true,
			labels: {
				style: {
					colors: ['#CCC']
				},
				formatter: function(value) {
					return Math.round(value);
				}
			}
		},
		grid: {
			borderColor: '#333'
		},
		legend: {
			labels: {
				colors: ['#CCC']
			}
		},
		colors: ['#008FFB', '#C52428', '#FFFF00', '#8B008B'],
		stroke: {
			width: 3,
			curve: 'smooth',
			dashArray: [0, 0, 0, 0]
		}
	};
	
	var yoy_order_volume_graph_options = {
		chart: {
			type: 'line',
			height: 350,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			},
			stacked: false,
			zoom: {
				autoScaleYaxis: true
			}
		},
		dataLabels: {
			enabled: false
		},
		markers: {
			size: 5
		},
		series: [
			<?php for($i=3; $i>0; $i--): ?>
				{
				  name: '<?php echo date('Y', strtotime('-'.$i.' year')); ?>',
				  type: 'line',
				  data: []
				},
			<?php endfor; ?>
				{
				  name: '<?php echo date('Y'); ?>',
				  type: 'line',
				  data: []
				},
				/*{
					name: 'Forecasted',
					type: 'line',
					data: []
				}*/
		],
		xaxis: {
			type: 'category',
			categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
			labels: {
				style: {
					colors: '#CCC'
				}
			}
		},
		yaxis: {
			show: true,
			labels: {
				style: {
					colors: ['#CCC']
				},
				formatter: function(value) {
					return Math.round(value);
				}
			}
		},
		grid: {
			borderColor: '#333'
		},
		legend: {
			labels: {
				colors: ['#CCC']
			}
		},
		colors: ['#FF9F00','#FF3F00','#CC00FF','#008FFB', '#008FFB'],
		stroke: {
			width: 3,
			curve: 'smooth',
			dashArray: [0, 0, 0, 0, 2]
		}
	};
	
	var projected_stock_out_graph_options = {
		chart: {
			type: 'line',
			height: 350,
			fontFamily: 'Mukta',
			animations: {
				enabled: false
			},
			stacked: false,
			zoom: {
				autoScaleYaxis: true
			}
		},
		annotations: {
			position: 'back',
			yaxis: [{
				y: 0,
				y2: -2000,
				fillColor: '#C52428',
				opacity: 0.5
			},
			{
				y: 0,
				y2: 10000,
				fillColor: '#028A0F',
				opacity: 0.5
			}]
		},
		/*fill: {
			type: 'gradient',
			gradient: {
				shadeIntensity: 1,
				type: 'vertical',
				opacityFrom: 0.7,
				opacityTo: 0.9,
				colorStops: [
					{
						offset: 0,
						color: "#95DA74",
						opacity: 1
					},
					{
						offset: 50,
						color: "#f5eea4",
						opacity: 1
					},
					{
						offset: 100,
						color: "#EB656F",
						opacity: 1
					}
				]
			}
		},*/
		dataLabels: {
			enabled: false
		},
		markers: {
			size: 0
		},
		series: [
			{
			  name: 'Projected On Hand Qty',
			  data: []
			}
		],
		xaxis: {
			type: 'datetime',
			showDuplicates: false,
			labels: {
				rotate: -15,
				rotateAlways: true,
				style: {
					colors: '#CCC'
				}
			}
		},
		yaxis: {
			show: true,
			labels: {
				style: {
					colors: ['#CCC']
				},
				formatter: function(value) {
					return Math.round(value);
				}
			}
		},
		grid: {
			borderColor: '#333'
		},
		legend: {
			labels: {
				colors: ['#CCC']
			}
		},
		colors: ['#008FFB'],
		stroke: {
			width: 3,
			dashArray: [0]
		}
	};
	
	$('body').on('click', '.generate-sku-demand-graph', function() {
		let sku = $(this).attr('sku');
		
		updateYoYOrderVolumeGraph(sku);
		updateHistoricalInventoryLevelsGraph(sku);
	});
	
	function updateHistoricalInventoryLevelsGraph(sku) {
		$('#historical-inventory-levels-graph').html('');
		$('#projected-stock-out-graph').html('');
		if(sku == "") {
			$('#historical-inventory-levels-graph-title').html('');
			$('#sku-historical-inventory-levels-default-message').show();
			$('#historical-inventory-levels-graph').hide();
			$('#historical-inventory-levels-graph-footer').hide();
			
			$('#projected-stock-out-graph-title').html('');
			$('#projected-stock-out-graph-default-message').show();
			$('#projected-stock-out-graph').hide();
			return;
		}
		else {
			$('#historical-inventory-levels-graph-title').html('Loading...');
			$('#sku-historical-inventory-levels-default-message').hide();
			$('#historical-inventory-levels-graph').show();
			$('#historical-inventory-levels-graph-footer').show();
			
			$('#projected-stock-out-graph-title').html('Loading...');
			$('#projected-stock-out-graph-default-message').hide();
			$('#projected-stock-out-graph').show();
		}
		
		$.post(
			'<?php echo base_url(PROJECT_CODE.'/api/get_historical_inventory_levels_graph_data'); ?>', 
			{ 
				sku: sku,
				customer: <?php echo !empty($customer) ? $customer : 'null'; ?>
			},
			function(result) {
				$('#historical-inventory-levels-graph-title').html(sku);
				$('#projected-stock-out-graph-title').html(sku);
				
				historical_inventory_levels_graph_options['series'][0]['data'] = result.historical_on_hand_qty;
				historical_inventory_levels_graph_options['series'][1]['data'] = result.historical_backorder_volume;
				historical_inventory_levels_graph_options['series'][2]['data'] = result.historical_inbound_qty;
				historical_inventory_levels_graph_options['series'][3]['data'] = result.historical_average_on_hand_qty;

				historical_inventory_levels_graph_options['xaxis']['min'] = result.historical_inventory_levels_min_date;
				
				//historical_inventory_levels_graph_options['annotations']['yaxis'][0]['y'] = getAverageOfArray(arrayColumn(result.historical_order_volume,1));

				//historical_inventory_levels_graph_options['annotations']['yaxis'][1]['y'] = getAverageOfArray(arrayColumn(result.historical_on_hand_qty,1));
				
				var historical_inventory_levels_graph = new ApexCharts(document.querySelector("#historical-inventory-levels-graph"), historical_inventory_levels_graph_options);
				historical_inventory_levels_graph.render();
				
				$('#historical-inventory-levels-graph-footer').show();
				
				projected_stock_out_graph_options['series'][0]['data'] = result.projected_stock_out_graph_data;
				
				/*if(result.projected_stock_out_graph_data_min >= 0) {
					projected_stock_out_graph_options['fill']['gradient']['colorStops'] = [
						{
							offset: 0,
							color: "#95DA74",
							opacity: 1
						},
						{
							offset: 50,
							color: "#95DA74",
							opacity: 1
						},
						{
							offset: 100,
							color: "#95DA74",
							opacity: 1
						}
					]
				}
				else {
					projected_stock_out_graph_options['fill']['gradient']['colorStops'] = [
						{
							offset: 0,
							color: "#95DA74",
							opacity: 1
						},
						{
							offset: result.projected_stock_out_graph_data_max / (result.projected_stock_out_graph_data_max - result.projected_stock_out_graph_data_min) * 100,
							color: "#f5eea4",
							opacity: 1
						},
						{
							offset: 100,
							color: "#EB656F",
							opacity: 1
						}
					]
				}*/
				
				var projected_stock_out_graph = new ApexCharts(document.querySelector("#projected-stock-out-graph"), projected_stock_out_graph_options);
				projected_stock_out_graph.render();
			},
			"json" 
		);
	}
	
	function updateYoYOrderVolumeGraph(sku) {
		$('#yoy-order-volume-graph-title').html('Loading...');
		$('#yoy-order-volume-graph').html('');
		
		$.post(
			'<?php echo base_url(PROJECT_CODE.'/api/get_sku_historical_demand_data'); ?>', 
			{ 
				sku: sku,
				customer: <?php echo !empty($customer) ? $customer : 'null'; ?>
			},
			function(result) {
				if(sku=='') {
					$('#yoy-order-volume-graph-title').html('All SKUs');
				}
				else {
					$('#yoy-order-volume-graph-title').html(sku);
				}
				
				let i = 0;
				let thisYear = <?php echo date('Y'); ?>;
				
				let historicalDemand = result.historical_demand;
				let forecastedDemand = result.forecasted_demand;
				
				for(i=0; i<4; i++) {
					yoy_order_volume_graph_options['series'][i]['data'] = [];
					for(j=0; j<12; j++) {
						if(i==3 && j >= <?php echo date('n'); ?>) {
							yoy_order_volume_graph_options['series'][i]['data'][j] = null;
						}
						else {
							yoy_order_volume_graph_options['series'][i]['data'][j] = 0;
						}
					}
				}
				for(i=0; i<historicalDemand.length; i++) {
					let currentDate = new Date(historicalDemand[i][0]);
					
					yoy_order_volume_graph_options['series'][3-(thisYear-currentDate.getFullYear())]['data'][currentDate.getMonth()] = historicalDemand[i][1];			
				}
				
				/*yoy_order_volume_graph_options['series'][4]['data'] = [];
				for(j=0; j<12; j++) {
					yoy_order_volume_graph_options['series'][4]['data'][j] = null;
				}
				let current_month = <?php echo date('n'); ?>;
				if(current_month > 1) {
					yoy_order_volume_graph_options['series'][4]['data'][current_month-2] = forecastedDemand[0][1];
				}
				for(i=0; i<4; i++) {
					yoy_order_volume_graph_options['series'][4]['data'][current_month-1+i] = forecastedDemand[(i+1)%12][1];
				}*/

				var yoy_order_volume_graph = new ApexCharts(document.querySelector('#yoy-order-volume-graph'), yoy_order_volume_graph_options);
				yoy_order_volume_graph.render();
			},
			"json" 
		);
	}
	
	function arrayColumn(array, columnName) {
		return array.map(function(value,index) {
			return parseFloat(value[columnName]);
		})
	}
	
	function getMaxOfArray(numArray) {
		return Math.max.apply(null, numArray);
	}
	
	function getMinOfArray(numArray) {
		return Math.min.apply(null, numArray);
	}

	updateYoYOrderVolumeGraph('');
</script>

<?php endif; ?>