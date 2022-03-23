<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<div class="col-lg-8 col-md-12">
	<table class="table">
		<thead>
			<th style="width:40px;"></th>
			<th>Name</th>
			<th>Status</th>
			<th>Department</th>
			<th>Idle Time</th>
		</thead>
		<tbody>
		<?php foreach($idle_time_data as $current_data) : ?>
			<tr class="row-color-<?php echo $current_data['color']; ?>">
				<td style="text-align:center; padding:5px;"><img style="width:30px;" src="<?php echo file_exists(str_replace('application','assets',APPPATH).'data/kaizen/app/users/thumbnails/'.$current_data['name'].'.jpg') ? base_url('assets/data/kaizen/app/users/thumbnails/'.$current_data['name'].'.jpg') : base_url('assets/data/kaizen/app/users/thumbnails/no-photo.jpg'); ?>"></td>
				<td style="font-size:20px; vertical-align:middle;"><?php echo $current_data['name']; ?></td>
				<td style="font-size:20px; vertical-align:middle;"><?php echo ucwords($current_data['status']); ?></td>
				<td style="font-size:20px; vertical-align:middle;"><?php echo $current_data['department']; ?></td>
				<td style="font-size:20px; vertical-align:middle;"><?php echo sprintf('%02d:%02d:%02d', floor($current_data['idle_time'] / 3600), floor($current_data['idle_time'] / 60 % 60), floor($current_data['idle_time'] % 60)); ?></td>
			</tr>
		<?php endforeach; ?>
		<?php if(empty($idle_time_data)) : ?>
			<tr>
				<td colspan="5" style="font-size:20px; background-color:green; color:white; text-align:center;">
					No idle status found.
				</td>
		<?php endif; ?>
		</tbody>
	</table>
</div>

<?php endif; ?>