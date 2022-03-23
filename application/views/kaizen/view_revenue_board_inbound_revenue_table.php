<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<div class="table-area">
	<table class="table datatabled-entity revenue-table" id="inbound-revenue-table">
		<thead>
			<th>Facility</th>
			<th>Delivery #</th>
			<th>Container</th>
			<th>Total SKUs</th>
			<th>Completed At</th>
			<th>Pallet/Parcel Fee</th>
			<th>Additional SKU Fee</th>
		</thead>
		<tbody>
			<?php foreach($inbound_revenue_data as $current_data) : ?>
				<tr>
					<td><?php echo $current_data['facility_name']; ?></td>
					<td><?php echo $current_data['delivery_no']; ?></td>
					<td><?php echo $current_data['container']; ?></td>
					<td><?php echo number_format($current_data['total_skus'],0); ?></td>
					<td><?php echo $current_data['completed_at']; ?></td>
					<td><?php echo '$' . number_format($current_data['pallet_or_parcel_fee'],2); ?></td>
					<td><?php echo '$' . number_format($current_data['additional_sku_fee'],2); ?></td>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<?php endif; ?>