<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate && !empty($carrier_list)) : ?>

<div class="col-lg-12">
	<table class="table datatabled-entity" id="packages-by-date-location-carrier-table" style="width:<?php echo count($carrier_list) * count($stock_ids) * 100 + 200; ?>px;">
		<thead>
			<tr>
				<th>Date</th>
				<?php foreach($stock_ids as $stock_id): ?>
					<?php foreach($carrier_list as $current_carrier): ?>
						<th style="text-align:center;"><?php echo $stock_warehouse_name_by_id[$stock_id] . '<br>' . (!empty($current_carrier) ? '('.$current_carrier.')' : '(Not Set)'); ?></th>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<?php if(empty($packages_by_date_location_carrier_data)): ?>
				<tr>
					<td colspan="<?php echo count($stock_ids) + 1; ?>" style="font-size:20px; text-align:center;">
						No data found.
					</td>
				</tr>
			<?php else: ?>
				<?php foreach($packages_by_date_location_carrier_data as $the_date => $current_data): ?>
					<tr>
						<td><?php echo $the_date; ?></td>
						<?php foreach($stock_ids as $stock_id): ?>
							<?php foreach($carrier_list as $current_carrier): ?>
								<td style="text-align:center;"><?php echo !empty($current_data[$stock_id.'-'.$current_carrier]) ? $current_data[$stock_id.'-'.$current_carrier] : 0; ?></td>
							<?php endforeach; ?>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
	</table>
</div>

<?php endif; ?>