<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<div class="table-area">
	<table class="table datatabled-entity revenue-table" id="outbound-packages-table">
		<thead>
			<th>Facility</th>
			<th>Order #</th>
			<th>Shipment #</th>
			<th>Overbox Requested</th>
			<th>Total Qty</th>
			<th>Package Created At</th>
			<th>Additional Item</th>
			<th>Fulfillment Fee</th>
			<th>Packaging Fee</th>
			<th>Additional Item Fee</th>
		</thead>
		<tbody>
			<?php foreach($outbound_packages_data as $current_data) : ?>
				<tr>
					<td><?php echo $current_data['facility_name']; ?></td>
					<td><?php echo $current_data['order_no']; ?></td>
					<td><?php echo $current_data['shipment_no']; ?></td>
					<td><?php echo ($current_data['overbox'] == 1) ? 'Yes' : 'No'; ?></td>
					<td><?php echo number_format($current_data['total_qty'],0); ?></td>
					<td><?php echo $current_data['package_created_at']; ?></td>
					<td><?php echo number_format($current_data['additional_item'],0); ?></td>
					<td><?php echo '$' . number_format($current_data['fulfillment_fee'],2); ?></td>
					<td><?php echo '$' . number_format($current_data['packaging_fee'],2); ?></td>
					<td><?php echo '$' . number_format($current_data['additional_item_fee'],2); ?></td>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<?php endif; ?>