<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<style type="text/css">
	.total-row {
		background-color: #2A9FD6;
		font-weight: bold;
	}
	
	.help-btn {
		cursor: pointer;
	}
</style>

<div class="row">
	<div class="col-md-6">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title">Inventory Snapshot
					<span data-toggle="modal" data-target="#modal_help_inventory_snapshot"><span class="glyphicon glyphicon-question-sign pull-right help-btn" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="About Inventory Snapshot"></span></span>
				</h3>
			</div>
			<div class="panel-body">
				<div id="inventory-snapshot-chart"></div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<h3 class="panel-title">Warehouse Inventory Distribution
					<span data-toggle="modal" data-target="#modal_help_warehouse_inventory_distribution"><span class="glyphicon glyphicon-question-sign pull-right help-btn" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="About Warehouse Inventory Distribution"></span></span>
				</h3>
			</div>
			<div class="panel-body">
				<div style="margin-left:70px; margin-right:0px;">
					<table class="table">
						<thead>
							<td colspan="4" style="text-align:center; font-size:16px; font-weight:bold;">Total On Hand + Inbound</td>
						</thead>
						<thead>
							<?php $total_stock = 0; foreach($warehouse_inventory_distribution_chart_data as $warehouse_name => $current_data): 
									if($warehouse_name <> 'Total Backorder'): $total_stock+= ($current_data['On Hand'] + $current_data['Inbound']); ?>
								<th style="vertical-align:middle; text-align:center; width:25%;"><?php echo $warehouse_name; ?></th>
							<?php endif; endforeach; ?>
								<th style="vertical-align:middle; text-align:center; width:25%;"></th>
						</thead>
						<tr>
							<?php foreach($warehouse_inventory_distribution_chart_data as $warehouse_name => $current_data): 
									if($warehouse_name <> 'Total Backorder'): ?>
								<td style="vertical-align:middle; text-align:center;"><span style="font-size:18px;"><?php echo number_format($current_data['On Hand'] + $current_data['Inbound'],0); ?></span><br>
								(<?php echo number_format(( $total_stock > 0 ? (($current_data['On Hand'] + $current_data['Inbound'])/$total_stock*100) : 0),2); ?>%)</td>
							<?php endif; endforeach; ?>
								<td style="vertical-align:middle; text-align:center;"></td>
						</tr>
					</table>
				</div>
			
				<div id="warehouse-inventory-distribution-chart"></div>
				
				<div style="text-align:center;">Click/hover on the legend to toggle on hand, inbound, and backorders.</div>
			</div>
		</div>
	</div>

	<div class="col-md-12">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">YoY Order Volume
					<span data-toggle="modal" data-target="#modal_help_yoy_order_volume"><span class="glyphicon glyphicon-question-sign pull-right help-btn" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="About YoY Order Volume"></span></span>
				</h3>
			</div>
			<div class="panel-body">
				<h3 id="yoy-order-volume-graph-title">Loading...</h3>
				<div id="yoy-order-volume-graph">
				</div>
				
				<div style="text-align:center;">Click/hover on the legend to toggle years.</div>
			</div>
		</div>
	</div>

	<br><br>

	<br><br>

	<div class="col-md-6" style="display:none;">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Order Volume
					<span data-toggle="modal" data-target="#modal_help_order_volume"><span class="glyphicon glyphicon-question-sign pull-right help-btn" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="About Order Volume"></span></span>
				</h3>
			</div>
			<div class="panel-body">
				<h3 id="sku-historical-demand-graph-title"></h3>
				<div id="sku-historical-demand-graph-wrapper">
					<div id="sku-historical-demand-default-message">Please select a SKU by clicking on the &nbsp;&nbsp;<span class="glyphicon glyphicon-stats" aria-hidden="true"></span>&nbsp;&nbsp; icon in the below table.</div>
					<div id="sku-historical-demand-graph">
					
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Historical Inventory Levels
					<span data-toggle="modal" data-target="#modal_help_historical_inventory_levels"><span class="glyphicon glyphicon-question-sign pull-right help-btn" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="About Historical Inventory Levels"></span></span>
				</h3>
			</div>
			<div class="panel-body">
				<h3 id="historical-inventory-levels-graph-title"></h3>
				<div id="historical-inventory-levels-graph-wrapper">
					<div id="sku-historical-inventory-levels-default-message">
						Please select a SKU by clicking on the &nbsp;&nbsp;<span class="glyphicon glyphicon-stats" aria-hidden="true"></span>&nbsp;&nbsp; icon in the below table.
					</div>
					<div id="historical-inventory-levels-graph">
					
					</div>
				</div>
				
				<div style="text-align:center; display:none;" id="historical-inventory-levels-graph-footer">Click/hover on the legend to toggle on hand, inbound, and backorders.</div>
			</div>
		</div>
	</div>
	
	<div class="col-md-6">
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">
					Projected Stock Out
				</h3>
			</div>
			<div class="panel-body">
				<h3 id="projected-stock-out-graph-title"></h3>
				<div id="projected-stock-out-graph-wrapper">
					<div id="projected-stock-out-graph-default-message">
						Please select a SKU by clicking on the &nbsp;&nbsp;<span class="glyphicon glyphicon-stats" aria-hidden="true"></span>&nbsp;&nbsp; icon in the below table.
					</div>
					<div id="projected-stock-out-graph">
					
					</div>
				</div>
			</div>
		</div>
	</div>

</div>
<div class="row">

	<br><br>

	<div class="col-md-12">	
		<div class="panel panel-default">
			<div class="panel-heading">
				<h3 class="panel-title">Projected Stock Out Dates
					<span data-toggle="modal" data-target="#modal_help_projected_stock_out_dates"><span class="glyphicon glyphicon-question-sign pull-right help-btn" aria-hidden="true" data-toggle="tooltip" data-placement="top" title="About Projected Stock Out Dates"></span></span>
				</h3>
			</div>
			<div class="panel-body">
				<div class="table-area">
					<table class="table datatabled-entity table-bordered client-inventory-optimization-board-table" id="client-inventory-optimization-board-table" style="width:1530px;">
						<thead>
							<th style="width:50px; text-align:center;"><a class="generate-sku-demand-graph" sku="" style="cursor:pointer;"><span class="glyphicon glyphicon-stats" aria-hidden="true"></span><br>Clear</a></th>
							<th style="width:80px;">SKU Status</th>
							<th style="width:150px;">SKU</th>
							<th style="width:150px;">Product Name</th>
							<th style="width:80px;">Last 90 Day Sales</th>
							<th style="width:80px;">#90 Day Average</th>
							<th style="width:80px;">Share of Order Volume</th>
							<th style="width:80px;">Current Inventory</th>
							<th style="width:80px;">Backorders</th>
							<th style="width:80px;">Incoming Deliveries</th>
							<th style="width:80px;">On-Time Incoming Deliveries</th>
							<th style="width:80px;">Cumulative</th>
							<th style="width:80px;">Cumulative Share of Order Volume</th>
							<th style="width:80px;">Safety Stock</th>
							<th style="width:80px;">Inventory After Deliveries</th>
							<th style="width:80px;">Projected Days of On Hand Inventory</th>
							<th style="width:80px;">Projected Stock Out Date</th>
						</thead>
						<tbody>
							<tr>
								<td colspan="15">Loading...</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_help_inventory_snapshot" tabindex="-1" role="dialog" aria-labelledby="modal_help_inventory_snapshot_label">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_help_inventory_snapshot_label">About Inventory Snapshot</h4>
			</div>
			<div class="modal-body">
				<p>The chart illustrates the available quantity of all SKU with at least one order in the past 90 days. These are referred to as "Ordered SKU".</p>
				
				<h4 style="color:#C52428"><span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span> Out of Stock</h4>
				Ordered SKU with no availability inventory.
				
				<h4 style="color:#FFFF00"><span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span> Running Out</h4>
				Ordered SKU with available inventory greater than zero, but less than the safety stock.
				
				<h4 style="color:#028A0F"><span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span> OK</h4>
				Ordered SKU with an available inventory greater than the safety stock.
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_help_warehouse_inventory_distribution" tabindex="-1" role="dialog" aria-labelledby="modal_help_warehouse_inventory_distribution_label">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_help_warehouse_inventory_distribution_label">About Warehouse Inventory Distribution</h4>
			</div>
			<div class="modal-body">
				<h4 style="color:#008FFB"><span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span> On Hand</h4>
				Available inventory of SKU ordered in the last 90 days by warehouse location.
				
				<h4 style="color:#FFFF00"><span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span> Inbound</h4>
				Incoming inventory, as determined by newly input ASN's, by warehouse location.
				
				<h4 style="color:#C52428"><span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span> Backorder</h4>
				Total quantity of SKU classified as backorder within the last 90 days.
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_help_yoy_order_volume" tabindex="-1" role="dialog" aria-labelledby="modal_help_yoy_order_volume_label">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_help_yoy_order_volume_label">About YoY Order Volume</h4>
			</div>
			<div class="modal-body">
				<p>When the page is first loaded, the graph illustrates total historical orders.</p>
				<p>When a SKU in the Reorder Quantity Guidance table is selected, the graph reflects order volume for the specific SKU.</p>
				
				<!--<h4 style="color:#008FFB"><span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>Forecasted Demand Trend</h4>
				<p>The current month's forecasted demand trend is calculated by taking a rolling average of order volume since the beginning of the month and multiplying it by the number of days in the month.</p>
				
				<p>The following three month's forecasted demand trend is determined by the average order volume growth over the past 6 months. We limit the monthly growth range from -30% to +30%.</p> -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_help_historical_inventory_levels" tabindex="-1" role="dialog" aria-labelledby="modal_help_historical_inventory_levels_label">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_help_historical_inventory_levels_label">About Historical Inventory Levels</h4>
			</div>
			<div class="modal-body">
				<h4 style="color:#008FFB"><span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span>On Hand Qty</h4>
				Historical available inventory quantity.
				
				<h4 style="color:#C52428"><span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span> Backorder Qty</h4>
				Monthly order volume of the selected SKU that is presently in the backorder status.

				<h4 style="color:#FFFF00"><span class="glyphicon glyphicon-asterisk" aria-hidden="true"></span> Inbound Qty</h4>
				Monthly quantity of the selected SKU that is projected for arrival as indicated by ASN's.
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_help_projected_stock_out_dates" tabindex="-1" role="dialog" aria-labelledby="modal_help_projected_stock_out_dates_label">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_help_projected_stock_out_dates_label">About Projected Stock Out Dates</h4>
			</div>
			<div class="modal-body">
				<h5>SKU Status</h5>
				Current Inventory = 0 → Out of stock<br>
				0 < Current Inventory < Safety Stock → Running out<br>
				Current Inventory >= Safety Stock → OK<br><br>

				<h5>Last 90 Day Sales</h5>
				Number of quantities the SKU was ordered in the last 90 days<br><br>

				<h5>#90 Day Average</h5>
				Last 90 Day Sales / 90<br><br>

				<h5>Current Inventory</h5>
				Qty available for that SKU<br><br>

				<h5>Backorders</h5>
				Total qty_backordered of that SKU in the orders with "backordered" status created in the last 90 days<br><br>

				<h5>Incoming Deliveries</h5>
				Total qty_expected of that SKU in delivery item which type=ASN and delivery_status=new<br><br>

				<h5>Cumulative</h5>
				Cumulative total of Last 90 Day Sales<br><br>

				<h5>Cumulative %</h5>
				Cumulative / Total Last 90 Day Sales for all SKU * 100%<br><br>

				<h5>Safety Stock</h5>
				Z Score * #90 Day Average<br>
				Z Score = Normsinv(Service Level Percentage)<br><br>

				<h5>Inventory After Deliveries</h5>
				Incoming Deliveries - Backorders<br>

				<h5>Projected Days of On Hand Inventory</h5>
				Inventory After Deliveries / #90 Day Average<br>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
	
<?php endif; ?>