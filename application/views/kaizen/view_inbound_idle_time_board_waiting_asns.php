<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<div class="col-lg-12">
	<div id="waiting-asns-table-wrapper">
		<table class="table" id="status-summary">
			<thead>
				<?php foreach($status_summary as $status_code => $status_info): ?>
					<th style="text-align:center;"><?php echo $status_info['status_name']; ?></th>
				<?php endforeach; ?>
			</thead>
			<tbody>
				<?php foreach($status_summary as $status_code => $status_info): ?>
					<td style="text-align:center; font-size:20px;"><?php echo $status_info['count']; ?></td>
				<?php endforeach; ?>
			</tbody>
		</table>
		
		<br><br>
	
		<table class="table datatabled-entity" id="waiting-asns-table">
			<thead>
				<th>Delivery #</th>
				<th>Status</th>
				<th>Secs since last action</th>
				<th>Merchant Name</th>
				<th>Carrier Name</th>
				<th>Total SKUs</th>
				<th>Containers</th>
				<th>Exceptions</th>
				<th>Progress</th>
				<th>Accepted At</th>
				<th>Completed Date</th>
				<th>Receive By</th>
				<th>Duration (Hrs)</th>
			</thead>
			<tbody>
				<?php if(empty($waiting_asns)) : ?>
					<tr>
						<td colspan="12" style="font-size:20px; background-color:green; color:white; text-align:center;">
							No waiting ASN found.
						</td>
				<?php else: ?>
					<?php foreach($waiting_asns as $current_data) : 
						if($current_data['status'] == 'complete') {
							$row_color = 'green';
						}
						else if($current_data['secs_since_last_action'] < 12 * 3600) {
							$row_color = 'blue';
						}
						else if($current_data['duration'] < -48) {
							$row_color = 'red';
						}
						else if($current_data['duration'] < -12) {
							$row_color = 'yellow';
						}
						else {
							$row_color = 'green';
						}
						
						$time_since_last_action_text = '';
						if($current_data['secs_since_last_action'] >= 86400) {
							$num = floor($current_data['secs_since_last_action'] / 86400);
							$time_since_last_action_text = '(' . $num . ' ' . ($num < 2 ? 'day' : 'days') . ' ago)';
						}
						else if($current_data['secs_since_last_action'] >= 3600) {
							$num = floor($current_data['secs_since_last_action'] / 3600);
							$time_since_last_action_text = '(' . $num . ' ' . ($num < 2 ? 'hour' : 'hours') . ' ago)';
						}
						else {
							$num = floor($current_data['secs_since_last_action'] / 60);
							$time_since_last_action_text = '(' . $num . ' ' . ($num < 2 ? 'min' : 'mins') . ' ago)';
						}
					?>
						<tr class="row-color-<?php echo $row_color; ?>">
							<td><?php echo $current_data['delivery_no']; ?></td>
							<td><?php echo ucwords(str_replace('_',' ',$current_data['status'])) . '<br>' . $time_since_last_action_text; ?></td>
							<td><?php echo $current_data['secs_since_last_action']; ?></td>
							<td><?php echo $current_data['merchant_name']; ?></td>
							<td><?php echo $current_data['carrier_name']; ?></td>
							<td><?php echo $current_data['total_skus']; ?></td>
							<td><?php echo $current_data['num_containers']; ?></td>
							<td><?php echo $current_data['num_exceptions']; ?></td>
							<td><?php echo $current_data['progress']; ?>%</td>
							<td><?php echo $current_data['accepted_at']; ?></td>
							<td><?php echo $current_data['completed_date']; ?></td>
							<td><?php echo substr($current_data['local_receive_by'],0,10); ?></td>
							<td><?php echo $current_data['duration']; ?></td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<?php endif; ?>