<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<div class="table-area">
	<table class="table datatabled-entity revenue-table" id="inbound-pivot-table">
		<thead>
			<th>Period</th>
			<th># of Containers</th>
			<th>Sum of Pallet/Parcel Fee</th>
			<th>Sum of Additional SKU Fee</th>
			<th>Total Inbound Revenue</th>
		</thead>
		<tbody>
			<?php foreach($inbound_pivot_data as $period => $current_data) : ?>
				<tr>
					<td><?php echo $current_data['label']; ?></td>
					<td><?php echo number_format($current_data['num_containers'],0); ?></td>
					<td><?php echo '$' . number_format($current_data['sum_of_pallet_or_parcel_fee'],2); ?></td>
					<td><?php echo '$' . number_format($current_data['sum_of_additional_sku_fee'],2); ?></td>
					<td><?php echo '$' . number_format($current_data['total_inbound_revenue'],2); ?></td>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<td>Total</td>
			<td><?php echo number_format($total_inbound_pivot_data['num_containers'],0); ?></td>
			<td><?php echo '$' . number_format($total_inbound_pivot_data['sum_of_pallet_or_parcel_fee'],2); ?></td>
			<td><?php echo '$' . number_format($total_inbound_pivot_data['sum_of_additional_sku_fee'],2); ?></td>
			<td><?php echo '$' . number_format($total_inbound_pivot_data['total_inbound_revenue'],2); ?></td>
		</tfoot>
	</table>
</div>

<?php endif; ?>