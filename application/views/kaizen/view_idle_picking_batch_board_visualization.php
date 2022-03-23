<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<div class="col-lg-8 col-md-12">
	<table class="table">
		<thead>
			<th>Batch ID</th>
			<th>Status</th>
			<th>Progress</th>
			<th>User</th>
			<th>Created At</th>
			<th>Time Since Created</th>
			<th>Start</th>
			<th>Time Since Started</th>
		</thead>
		<tbody>
		<?php foreach($idle_batches_data as $current_data) : ?>
			<tr>
				<td><?php echo $current_data['batch_id']; ?></td>
				<td><?php echo ucwords($current_data['status']); ?></td>
				<td style="text-align:center;"><?php echo $current_data['progress']; ?>%</td>
				<td><?php echo $current_data['name']; ?></td>
				<td><?php echo $current_data['created_at_local']; ?></td>
				<td><?php echo isset($current_data['created_at']) ? sprintf('%02d:%02d:%02d', floor($current_data['time_since_created'] / 3600), floor($current_data['time_since_created'] / 60 % 60), floor($current_data['time_since_created'] % 60)) : ''; ?></td>
				<td><?php echo $current_data['started_at_local']; ?></td>
				<td><?php echo isset($current_data['started_at']) ? sprintf('%02d:%02d:%02d', floor($current_data['time_since_started'] / 3600), floor($current_data['time_since_started'] / 60 % 60), floor($current_data['time_since_started'] % 60)) : ''; ?></td>
			</tr>
		<?php endforeach; ?>
		<?php if(empty($idle_batches_data)) : ?>
			<tr>
				<td colspan="9" style="font-size:20px; background-color:green; color:white; text-align:center;">
					No idle picking batch found.
				</td>
		<?php endif; ?>
		</tbody>
	</table>
</div>

<?php endif; ?>