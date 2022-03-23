<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<div class="row">
	<div class="col-md-2">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<div class="panel-title takt-board-title">Total Orders</div>
			</div>
			<div class="panel-body takt-board-value">
				<?php echo $total_orders_count; ?>
			</div>
		</div>
	</div>
	<div class="col-md-2">
		<div class="panel panel-info">
			<div class="panel-heading">
				<div class="panel-title takt-board-title">Total Shipments</div>
			</div>
			<div class="panel-body takt-board-value">
				<?php echo $total_shipments_count; ?>
			</div>
			<table class="table">
				<?php foreach($total_shipments_by_shipping_method as $shipping_method => $shipments_count) : 
						if($shipments_count > 0): ?>
					<tr>
						<td style="text-align:right;"><?php echo $shipping_method; ?></td>
						<td><?php echo $shipments_count; ?></td>
					</tr>
				<?php endif; endforeach; ?>
			</table>
		</div>
	</div>
	<div class="col-md-2">
		<div class="panel panel-info">
			<div class="panel-heading">
				<div class="panel-title takt-board-title">New Shipments</div>
			</div>
			<div class="panel-body takt-board-value">
				<?php echo $new_shipments_count; ?>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="panel panel-warning">
			<div class="panel-heading">
				<div class="panel-title takt-board-title">In Processing Shipments</div>
			</div>
			<div class="panel-body takt-board-value">
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
				<div class="panel-title takt-board-title">Completed Shipments</div>
			</div>
			<div class="panel-body">
				<div class="takt-board-value">
					<?php echo $completed_shipments_count; ?>
				</div>
				<?php if($completed_shipments_count_difference_to_time <> 0) : ?>
					<div class="takt-board-subvalue <?php echo ($completed_shipments_count_difference_to_time >= 0) ? 'green-text' : 'red-text'; ?>">
						<?php echo ($completed_shipments_count_difference_to_time >= 0 ? '+' : '') . $completed_shipments_count_difference_to_time; ?>
						<br><span style="font-size:14px;">(4wk Trend)</span>
					</div>
				<?php endif; ?>
			</div>
		</div>
		
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title takt-board-title">Completed Orders</div>
			</div>
			<div class="panel-body">
				<div class="takt-board-value">
					<?php echo $completed_orders_count; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<?php endif; ?>