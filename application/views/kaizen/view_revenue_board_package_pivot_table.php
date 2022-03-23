<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<div class="table-area">
	<table class="table datatabled-entity revenue-table" id="package-pivot-table">
		<thead>
			<th>Period</th>
			<th># of Packages</th>
			<th>Fulfillment Revenue</th>
			<th>Packaging Revenue</th>
			<th>Additional Item Revenue</th>
			<th>Total Package Revenue</th>
		</thead>
		<tbody>
			<?php foreach($package_pivot_data as $period => $current_data) : ?>
				<tr>
					<td><?php echo $current_data['label']; ?></td>
					<td><?php echo number_format($current_data['num_packages'],0); ?></td>
					<td><?php echo '$' . number_format($current_data['fulfillment_revenue'],2); ?></td>
					<td><?php echo '$' . number_format($current_data['packaging_revenue'],2); ?></td>
					<td><?php echo '$' . number_format($current_data['additional_item_revenue'],2); ?></td>
					<td><?php echo '$' . number_format($current_data['total_package_revenue'],2); ?></td>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<td>Total</td>
			<td><?php echo number_format($total_package_pivot_data['num_packages'],0); ?></td>
			<td><?php echo '$' . number_format($total_package_pivot_data['fulfillment_revenue'],2); ?></td>
			<td><?php echo '$' . number_format($total_package_pivot_data['packaging_revenue'],2); ?></td>
			<td><?php echo '$' . number_format($total_package_pivot_data['additional_item_revenue'],2); ?></td>
			<td><?php echo '$' . number_format($total_package_pivot_data['total_package_revenue'],2); ?></td>
		</tfoot>
	</table>
</div>

<?php endif; ?>