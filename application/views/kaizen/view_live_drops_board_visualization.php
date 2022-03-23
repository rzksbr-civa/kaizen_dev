<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<style type="text/css">
	.total-row {
		background-color: #2A9FD6;
		font-weight: bold;
	}
</style>

<div class="row">
		<div class="table-area">
			<table class="table table-bordered datatabled-entity inventory-board-table" id="live-drops-table" style="width:860px;">
				<thead>
					<th style="width: 200px;">Merchant</th>
					<th style="width: 150px;">SKU</th>
					<th style="width: 40px;">SKU Tier</th>
					<th style="width: 80px;">Qty Allocated</th>
					<th style="width: 80px;">Qty Pickable</th>
					<th style="width: 80px;">Qty Required</th>
					<th style="width: 150px;">Location</th>
					<th style="width: 80px;">Stock Needed for Service Level</th>
				</thead>
				<tbody>
					<?php foreach($live_drops_data as $current_data): ?>
					<tr class="row-<?php echo $current_data['row_color']; ?>">
						<td><?php echo $current_data['merchant']; ?></td>
						<td><?php echo $current_data['sku']; ?></td>
						<td><?php echo $current_data['sku_tier']; ?></td>
						<td style="text-align:right;"><?php echo $current_data['qty_allocated']; ?></td>
						<td style="text-align:right;"><?php echo $current_data['qty_pickable']; ?></td>
						<td style="text-align:right;"><?php echo $current_data['qty_required']; ?></td>
						<td><?php echo $current_data['location']; ?></td>
						<td><?php echo $current_data['stock_needed_for_service_level']; ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<?php endif; ?>