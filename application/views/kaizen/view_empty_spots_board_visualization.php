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
			<table class="table table-bordered inventory-board-table" id="empty-spots-table" style="width:300px;">
				<thead>
					<th style="width: 200px;">Label</th>
					<th style="width: 100px;">Locations</th>
				</thead>
				<tbody>
					<?php if(!empty($empty_spots_data)): ?>
						<?php foreach($empty_spots_data as $current_data): ?>
						<tr>
							<td><?php echo $current_data['label']; ?></td>
							<td><?php echo $current_data['count_of_building']; ?></td>
						</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
				<tfoot class="total-row">
					<td>Total</td>
					<td><?php echo $total_empty_spots; ?></td>
				</tfoot>
			</table>
		</div>
	</div>
</div>

<?php endif; ?>