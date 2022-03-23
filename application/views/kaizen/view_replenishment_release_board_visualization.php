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

<div class="table-area">
	<table class="table datatabled-entity table-bordered replenishment-release-board-table" id="replenishment-release-board-table" style="width:1430px;">
		<thead>
			<th style="width:150px;">SKU</th>
			<th style="width:80px;">#Of Orders with SKU</th>
			<th style="width:80px;">Average of Item Count Per Order</th>
			<th style="width:80px;">Stand Dev</th>
			<th style="width:80px;">Sum of Items</th>
			<th style="width:80px;">Average Daily Demand</th>
			<th style="width:80px;">Cumulative</th>
			<th style="width:80px;">Cumulative %</th>
			<th style="width:80px;">SKU Tier</th>
			<th style="width:80px;">Replenish Freq (Days)</th>
			<th style="width:80px;">Safety Stock</th>
			<th style="width:80px;">Reorder Point (ROP)</th>
			<th style="width:80px;">Current Pickable Stock</th>
			<th style="width:80px;">Current Non Pickable</th>
			<th style="width:80px;">Total Stock On Hand</th>
			<th style="width:80px;">Stock Neeed for Service Level</th>
			<th style="width:80px;">Need Restock?</th>
		</thead>
		<tbody>
			<tr>
				<td colspan="15">Loading...</td>
			</tr>
		</tbody>
	</table>
</div>

<?php endif; ?>