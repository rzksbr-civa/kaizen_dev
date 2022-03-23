<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<div class="col-lg-12">
	<table class="table" id="packages-by-month-table">
		<thead>
			<th>Month</th>
			<?php foreach($stock_ids as $stock_id): ?>
				<th><?php echo $stock_warehouse_name_by_id[$stock_id]; ?></th>
			<?php endforeach; ?>
		</thead>
		<tbody>
			<?php if(empty($packages_by_month_data)): ?>
				<tr>
					<td colspan="<?php echo count($stock_ids) + 1; ?>" style="font-size:20px; text-align:center;">
						No data found.
					</td>
				</tr>
			<?php else: ?>
				<?php foreach($packages_by_month_data as $the_date => $current_data): ?>
					<tr>
						<td><?php echo date('Y-m (M Y)', strtotime($the_date)); ?></td>
						<?php foreach($stock_ids as $stock_id): ?>
							<td><?php echo $current_data[$stock_id]; ?></td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>

<?php endif; ?>