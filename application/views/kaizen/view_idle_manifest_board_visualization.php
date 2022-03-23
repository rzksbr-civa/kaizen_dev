<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<div class="col-lg-8 col-md-12">
	<table class="table">
		<thead>
			<th>Package ID</th>
			<th>Shipment#</th>
			<th>Order#</th>
			<th>Ship To Name</th>
			<th>Carrier</th>
			<th style="width:80px;">Length</th>
			<th style="width:80px;">Width</th>
			<th style="width:80px;">Height</th>
			<th>Packed</th>
		</thead>
		<tbody>
		<?php foreach($idle_manifest_board_data as $current_data) : ?>
			<tr>
				<td><?php echo $current_data['package_id']; ?></td>
				<td><?php echo $current_data['shipment_number']; ?></td>
				<td><?php echo $current_data['order_number']; ?></td>
				<td><?php echo $current_data['shipping_name']; ?></td>
				<td><?php echo $current_data['carrier_code']; ?></td>
				<td><?php echo $current_data['length']; ?></td>
				<td><?php echo $current_data['width']; ?></td>
				<td><?php echo $current_data['height']; ?></td>
				<td><?php 
					$hours = floor($current_data['packed_elapsed_secs']/3600);
					$mins = floor( ($current_data['packed_elapsed_secs'] % 3600) / 60 );
					
					if($hours > 0) {
						echo $hours . ' hr ';
					}
					if($mins > 0) {
						echo $mins . ' ' . (($mins==1) ? 'min' : 'mins');
					}
					
					echo ' ago';
					?>	
				</td>
			</tr>
		<?php endforeach; ?>
		<?php if(empty($idle_manifest_board_data)) : ?>
			<tr>
				<td colspan="9" style="font-size:20px; background-color:green; color:white; text-align:center;">
					No idle manifest found.
				</td>
		<?php endif; ?>
		</tbody>
	</table>
</div>

<?php endif; ?>