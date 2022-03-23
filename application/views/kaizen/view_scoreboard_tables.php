<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php foreach($scoreboard as $action => $action_scoreboard) : ?>
<div class="panel scoreboard-panel">
	<div class="panel-title">
		<div class="panel-heading">
			<h3 class="scoreboard-title"><?php echo $action; ?></h3>
		</div>
	</div>
	<table class="table scoreboard-table">
		<thead>
			<th style="text-align:center; width:50px;">#</th>
			<th style="width:80px;"></th>
			<th style="width:140px;">Name</th>
			<th style="text-align:center; width:90px;">Assignment</th>
			<th style="text-align:center; width:70px;">Qty</th>
			<th style="text-align:center; width:95px;">Total Time</th>
			<th style="text-align:center; width:95px;">Average</th>
		</thead>
		<tbody>
			<?php $rank = 1;
				foreach($action_scoreboard as $user => $user_data) : 
					$tr_class = 'row-data ';
					
					if(in_array($action, array('Picking', 'Packing'))) {
						$threshold_values = array();
						switch($action) {
							case 'Picking':
								$threshold_values['below'] = 50;
								$threshold_values['minimum'] = 60;
								$threshold_values['production_goal'] = 70;
								$threshold_values['great'] = 80;
								break;
							case 'Packing';
								$threshold_values['below'] = 25;
								$threshold_values['minimum'] = 30;
								$threshold_values['production_goal'] = 40;
								$threshold_values['great'] = 50;
								break;
						}
						
						switch(true) {
							case $user_data['average'] < $threshold_values['below']:
								$tr_class .= 'row-below';
								break;
							case $user_data['average'] < $threshold_values['minimum']:
								$tr_class .= 'row-minimum';
								break;
							case $user_data['average'] < $threshold_values['production_goal']:
								$tr_class .= 'row-production-goal';
								break;
							case $user_data['average'] < $threshold_values['great']:
								$tr_class .= 'row-great';
								break;
							case $user_data['average'] >= $threshold_values['great']:
								$tr_class .= 'row-outstanding';
								break;
						}
					}
			?>
			<tr class="scoreboard-row" data-employee_name="<?php echo $user; ?>">
				<td style="text-align:center;"><?php echo $rank++; ?></td>
				<td style="text-align:center; padding:15px;"><img src="<?php echo file_exists(str_replace('application','assets',APPPATH).'data/kaizen/app/users/thumbnails/'.$user.'.jpg') ? base_url('assets/data/kaizen/app/users/thumbnails/'.$user.'.jpg') : base_url('assets/data/kaizen/app/users/thumbnails/no-photo.jpg'); ?>"></td>
				<td class="scoreboard-username"><?php echo $user; ?></td>
				
				<?php if(isset($auto_employee_assignments[$user][$action])) : ?>
					<td style="font-style:italic;"><?php echo $auto_employee_assignments[$user][$action]; ?></td>
				<?php else: ?>
					<td class="scoreboard-employee-assignment scoreboard-assignment-<?php echo str_replace(array(" ", "'"), array("-", ""), $user); ?>"><?php echo $user_data['assignment']; ?></td>
				<?php endif; ?>
				
				<td class="scoreboard-value"><?php echo $user_data['qty']; ?></td>
				<td class="scoreboard-value"><?php echo $user_data['formatted_sum_of_time']; ?></td>
				<td class="scoreboard-value <?php echo $tr_class; ?>"><?php echo $user_data['formatted_average']; ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
<?php endforeach; ?>