<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<div class="col-lg-8 col-md-12">
	<table class="table">
		<thead>
			<th>Order #</th>
			<th>Status</th>
			<th>Idle Duration</th>
			<th>Last Action By</th>
			<th></th>
		</thead>
		<tbody>
		<?php foreach($idle_shipments as $current_data) : ?>
			<tr class="row-color-<?php echo $current_data['color']; ?>">
				<td style="font-size:20px; vertical-align:middle;"><?php echo $current_data['order_no']; ?></td>
				<td style="font-size:20px; vertical-align:middle;"><?php echo ucwords($current_data['status']); ?></td>
				<td style="font-size:20px; vertical-align:middle;"><?php echo sprintf('%02d:%02d:%02d', floor($current_data['idle_duration'] / 3600), floor($current_data['idle_duration'] / 60 % 60), floor($current_data['idle_duration'] % 60)); ?></td>
				<td style="font-size:20px; vertical-align:middle;"><?php echo ucwords($current_data['last_action_by']); ?></td>
				<?php if($current_data['is_removed']): ?>
					<td class="td-action-<?php echo $current_data['order_no']; ?>"><a class="btn btn-default btn-change-idle-order-state" action="unremove" order_no="<?php echo $current_data['order_no']; ?>">Unremove</a></td>
				<?php else: ?>
					<td class="td-action-<?php echo $current_data['order_no']; ?>"><a class="btn btn-default btn-change-idle-order-state" action="remove" order_no="<?php echo $current_data['order_no']; ?>">Remove</a></td>
				<?php endif; ?>
			</tr>
		<?php endforeach; ?>
		<?php if(empty($idle_shipments)) : ?>
			<tr>
				<td colspan="5" style="font-size:20px; background-color:green; color:white; text-align:center;">
					No idle ACs found.
				</td>
		<?php endif; ?>
		</tbody>
	</table>
</div>

<?php endif; ?>