<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<div class="col-lg-12">
	<?php if(count($batching_helper_board_data) == 10000) : ?>
		<div class="alert alert-warning" role="alert">Your query returns too many data. We only show the latest 10000 data here. Try to use the filter to get the information you want.</div>
	<?php elseif(empty($batching_helper_board_data)): ?>
		<h2>No data found.</h2>
	<?php else: ?>
	
	<div id="batching-helper-board-table-wrapper">
		<table class="table datatabled-entity" id="batching-helper-board-table" style="width:2000px">
			<thead>
				<th style="width:100px;">Order No.</th>
				<th style="width:100px;">Order Ref No.</th>
				<th style="width:100px;">Shipment No.</th>
				<th style="width:100px;">Brand Name</th>
				<th style="width:100px;">Shipment Status</th>
				<th style="width:100px;">Order Status</th>
				<th style="width:100px;">Target Ship Date</th>
				<th style="width:100px;">Ready To Ship</th>
				<th style="width:100px;">Batch Tag</th>
				<th style="width:100px;">Carrier</th>
				<th style="width:100px;">Shipping Method</th>
				<th style="width:100px;">Packing Solution ID</th>
				<th style="width:100px;">Order Date</th>
				<th style="width:100px;">Total Qty</th>
				<th style="width:100px;">Total Weight</th>
				<th style="width:100px;">Shipment Last Updated On</th>
				<th style="width:100px;">Batch Created On</th>
				<th style="width:100px;">Order ID</th>
				<th style="width:100px;">Shipment ID</th>
			</thead>
			<tbody>
				<?php foreach($batching_helper_board_data as $current_data): ?>
					<tr>
						<td><?php echo $current_data['order_no']; ?></td>
						<td><?php echo $current_data['order_ref_no']; ?></td>
						<td><?php echo $current_data['shipment_no']; ?></td>
						<td><?php echo $current_data['brand_name']; ?></td>
						<td><?php echo ucwords($current_data['shipment_status']); ?></td>
						<td><?php echo ucwords($current_data['order_status']); ?></td>
						<td><?php echo $current_data['target_ship_date']; ?></td>
						<td><?php echo $current_data['ready_to_ship']; ?></td>
						<td><?php echo $current_data['batch_tag']; ?></td>
						<td><?php echo $current_data['carrier']; ?></td>
						<td><?php echo $current_data['shipping_method']; ?></td>
						<td><?php echo $current_data['packing_solution_id']; ?></td>
						<td><?php echo $current_data['order_date']; ?></td>
						<td><?php echo $current_data['total_qty']; ?></td>
						<td><?php echo $current_data['total_weight']; ?></td>
						<td><?php echo $current_data['shipment_last_updated_on']; ?></td>
						<td><?php echo $current_data['batch_created_on']; ?></td>
						<td><?php echo $current_data['order_id']; ?></td>
						<td><?php echo $current_data['shipment_id']; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
	
	<?php endif; ?>
</div>

<?php endif; ?>