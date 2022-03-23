<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<div class="row">
	<div class="col-md-2">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<div class="panel-title shipment-board-title">Total Orders</div>
			</div>
			<div class="panel-body shipment-board-value">
				<?php echo $total_orders_count; ?>
			</div>
		</div>
	</div>
	<div class="col-md-2">
		<div class="panel panel-info">
			<div class="panel-heading">
				<div class="panel-title shipment-board-title">Total Shipments</div>
			</div>
			<div class="panel-body shipment-board-value">
				<?php echo $total_shipments_count; ?>
			</div>
		</div>
	</div>
	<div class="col-md-2">
		<div class="panel panel-info">
			<div class="panel-heading">
				<div class="panel-title shipment-board-title">New Shipments</div>
			</div>
			<div class="panel-body shipment-board-value">
				<?php echo $new_shipments_count; ?>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="panel panel-warning">
			<div class="panel-heading">
				<div class="panel-title shipment-board-title">In Processing Shipments</div>
			</div>
			<div class="panel-body shipment-board-value">
				<?php echo $in_processing_shipments_count; ?>
			</div>
			<table class="table">
				<?php foreach($in_processing_shipments_by_status as $current_data) : ?>
					<tr>
						<td style="text-align:right;"><?php echo ucwords($current_data['status']); ?></td>
						<td><?php echo $current_data['shipments_count']; ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
	<div class="col-md-3">
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title shipment-board-title">Completed Shipments</div>
			</div>
			<div class="panel-body">
				<div class="shipment-board-value">
					<?php echo $completed_shipments_count; ?>
				</div>
			</div>
		</div>
		
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title shipment-board-title">Completed Orders</div>
			</div>
			<div class="panel-body">
				<div class="shipment-board-value">
					<?php echo $completed_orders_count; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title shipment-board-title">Completed Shipments<br><?php echo $date; ?></div>
			</div>
			<div class="panel-body">
				<div class="shipment-board-chart-wrapper">
					<div class="shipment-board-chart" id="hourly-completed-shipments-chart"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<div class="panel-title shipment-board-title">Orders<br><?php echo $date; ?></div>
			</div>
			<div class="panel-body">
				<div class="shipment-board-chart-wrapper">
					<div class="shipment-board-chart" id="hourly-orders-chart"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php endif; ?>