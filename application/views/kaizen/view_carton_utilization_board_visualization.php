<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<div class="table-area">
	<table class="table datatabled-entity carton-utilization-table" id="carton-utilization-table">
		<thead>
			<th>Carton SKU</th>
			<th style="text-align:center;">Daily Usage</th>
			<th style="text-align:center;">Weekly Usage</th>
			<th style="text-align:center;">Monthly Usage</th>
		</thead>
		<tbody>
			<?php foreach($carton_usage as $carton_sku => $current_data) : ?>
				<tr>
					<td><?php echo $carton_sku; ?></td>
					<td style="text-align:center;"><?php echo number_format($current_data['daily'],2); ?></td>
					<td style="text-align:center;"><?php echo number_format($current_data['weekly'],2); ?></td>
					<td style="text-align:center;"><?php echo number_format($current_data['monthly'],2); ?></td>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<?php endif; ?>