<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if(!empty($metrics_board_note)) : ?>
<div class="metrics-board-note">
	<?php echo nl2br($metrics_board_note); ?>
</div>
<?php endif; ?>

<div class="table-area invisible" id="metrics-board-table-area">
	<table class="table table-bordered metrics-board-table" style="border:3px solid white;">
		<thead>
			<tr>
				<th rowspan="2" style="text-align:center; width:50px;">Rank#</th>
				<th rowspan="2" style="width:80px;"></th>
				<th rowspan="2" style="text-align:center; width:80px;">Name</th>
				<th rowspan="2" style="text-align:center; width:70px;">Daily Evolution Points</th>
				<th rowspan="2" style="text-align:center; width:80px;">Lifetime Evolution Points</th>
				<th rowspan="2" style="text-align:center; width:70px;">Total Atten dance Issues</th>
				<th rowspan="2" style="text-align:center; width:70px;">Total Rewards</th>
				
				<?php foreach($block_time_list as $block_time) : ?>
					<th colspan="7" style="text-align:center; border-left:3px solid white;"><?php echo str_replace(' ', '<br>', $block_time['block_time_name']); ?></th>
				<?php endforeach; ?>
				
				<th class="total-cell" colspan="6" style="text-align:center; border-left:3px solid white;">Total</th>
			</tr>
			<tr>
				<?php foreach($block_time_list as $block_time) : ?>	
					<th style="text-align:center; width:80px; border-left:3px solid white;">Pick Qty</th>
					<th style="text-align:center; width:80px;">Pick Avg</th>
					<th style="text-align:center; width:80px;">Pack Qty</th>
					<th style="text-align:center; width:80px;">Pack Avg</th>
					<th style="text-align:center; width:80px;">Load Qty</th>
					<th style="text-align:center; width:80px;">Load Avg</th>
					<th style="text-align:center; width:200px;">Assign</th>
				<?php endforeach; ?>
				
				<th class="total-cell" style="text-align:center; width:80px; border-left:3px solid white;">Pick Qty</th>
				<th class="total-cell" style="text-align:center; width:80px;">Pick Avg</th>
				<th class="total-cell" style="text-align:center; width:80px;">Pack Qty</th>
				<th class="total-cell" style="text-align:center; width:80px;">Pack Avg</th>
				<th class="total-cell" style="text-align:center; width:80px;">Load Qty</th>
				<th class="total-cell" style="text-align:center; width:80px;">Load Avg</th>
			</tr>
		</thead>
		<tbody>
			<?php $rank = 1;
				foreach($evolution_points_data as $item) : 
			?>
			<tr class="metrics-board-row" data-employee_name="<?php echo $item['employee_name']; ?>">
				<td class="<?php echo $item['evolution_status']; ?>-bg" style="text-align:center;"><?php echo $rank++; ?></td>
				<td style="text-align:center; padding:15px;"><img class="<?php echo $item['evolution_status']; ?>-frame" src="<?php echo file_exists(str_replace('application','assets',APPPATH).'data/kaizen/app/users/thumbnails/'.$item['employee_name'].'.jpg') ? base_url('assets/data/kaizen/app/users/thumbnails/'.$item['employee_name'].'.jpg') : base_url('assets/data/kaizen/app/users/thumbnails/no-photo.jpg'); ?>"></td>
				<td class="metrics-board-username"><a href="<?php echo base_url('db/view/employee/'.$item['employee_id']); ?>" target="_blank"><?php echo $item['employee_name']; ?></a></td>
				
				<td class="metrics-board-value"><?php echo round($item['daily_evolution_points']); ?></td>
				<td class="metrics-board-value evolution-points-area"><?php echo round($item['lifetime_evolution_points']); ?></td>
				<td class="metrics-board-value"><?php echo isset($total_attendance[$item['employee_id']]) ? $total_attendance[$item['employee_id']] : 0; ?></td>
				<td class="metrics-board-value"><?php echo isset($total_rewards[$item['employee_id']]) ? $total_rewards[$item['employee_id']] : 0; ?></td>
				
				<?php foreach($block_time_list as $block_time) : ?>
				
					<?php
						$picking_avg_td_class = '';
						
						if(isset($scoreboard[$block_time['id']]['Picking'][$item['employee_name']]['average'])) {
							$threshold_values['below'] = 50;
							$threshold_values['minimum'] = 60;
							$threshold_values['production_goal'] = 70;
							$threshold_values['great'] = 80;
							
							switch(true) {
								case $scoreboard[$block_time['id']]['Picking'][$item['employee_name']]['average'] < $threshold_values['below']:
									$picking_avg_td_class .= 'row-below';
									break;
								case $scoreboard[$block_time['id']]['Picking'][$item['employee_name']]['average'] < $threshold_values['minimum']:
									$picking_avg_td_class .= 'row-minimum';
									break;
								case $scoreboard[$block_time['id']]['Picking'][$item['employee_name']]['average'] < $threshold_values['production_goal']:
									$picking_avg_td_class .= 'row-production-goal';
									break;
								case $scoreboard[$block_time['id']]['Picking'][$item['employee_name']]['average'] < $threshold_values['great']:
									$picking_avg_td_class .= 'row-great';
									break;
								case $scoreboard[$block_time['id']]['Picking'][$item['employee_name']]['average'] >= $threshold_values['great']:
									$picking_avg_td_class .= 'row-outstanding';
									break;
							}
						}
						
						$packing_avg_td_class = '';
						
						if(isset($scoreboard[$block_time['id']]['Packing'][$item['employee_name']]['average'])) {
							$threshold_values['below'] = 25;
							$threshold_values['minimum'] = 30;
							$threshold_values['production_goal'] = 40;
							$threshold_values['great'] = 50;
							
							switch(true) {
								case $scoreboard[$block_time['id']]['Packing'][$item['employee_name']]['average'] < $threshold_values['below']:
									$packing_avg_td_class .= 'row-below';
									break;
								case $scoreboard[$block_time['id']]['Packing'][$item['employee_name']]['average'] < $threshold_values['minimum']:
									$packing_avg_td_class .= 'row-minimum';
									break;
								case $scoreboard[$block_time['id']]['Packing'][$item['employee_name']]['average'] < $threshold_values['production_goal']:
									$packing_avg_td_class .= 'row-production-goal';
									break;
								case $scoreboard[$block_time['id']]['Packing'][$item['employee_name']]['average'] < $threshold_values['great']:
									$packing_avg_td_class .= 'row-great';
									break;
								case $scoreboard[$block_time['id']]['Packing'][$item['employee_name']]['average'] >= $threshold_values['great']:
									$packing_avg_td_class .= 'row-outstanding';
									break;
							}
						}
					?>
				
					<td class="metrics-board-value" style="border-left:3px solid white;"><?php echo isset($scoreboard[$block_time['id']]['Picking'][$item['employee_name']]['qty']) ? $scoreboard[$block_time['id']]['Picking'][$item['employee_name']]['qty'] : ''; ?></td>
					<td class="metrics-board-value <?php echo $picking_avg_td_class; ?>"><?php echo isset($scoreboard[$block_time['id']]['Picking'][$item['employee_name']]['formatted_average']) ? $scoreboard[$block_time['id']]['Picking'][$item['employee_name']]['formatted_average'] : ''; ?></td>

					<td class="metrics-board-value"><?php echo isset($scoreboard[$block_time['id']]['Packing'][$item['employee_name']]['qty']) ? $scoreboard[$block_time['id']]['Packing'][$item['employee_name']]['qty'] : ''; ?></td>
					<td class="metrics-board-value <?php echo $packing_avg_td_class; ?>"><?php echo isset($scoreboard[$block_time['id']]['Packing'][$item['employee_name']]['formatted_average']) ? $scoreboard[$block_time['id']]['Packing'][$item['employee_name']]['formatted_average'] : ''; ?></td>

					<td class="metrics-board-value"><?php echo isset($scoreboard[$block_time['id']]['Load'][$item['employee_name']]['qty']) ? $scoreboard[$block_time['id']]['Load'][$item['employee_name']]['qty'] : ''; ?></td>
					<td class="metrics-board-value"><?php echo isset($scoreboard[$block_time['id']]['Load'][$item['employee_name']]['formatted_average']) ? $scoreboard[$block_time['id']]['Load'][$item['employee_name']]['formatted_average'] : ''; ?></td>
					
					<td class="metrics-board-employee-assignment" data-employee_id="<?php echo $item['employee_id']; ?>" data-employee_name="<?php echo $item['employee_name']; ?>">
						<?php
							if(isset($scoreboard[$block_time['id']]['Picking'][$item['employee_name']]['assignment'])) {
								echo $scoreboard[$block_time['id']]['Picking'][$item['employee_name']]['assignment'];
							}
							else if(isset($scoreboard[$block_time['id']]['Packing'][$item['employee_name']]['assignment'])) {
								echo $scoreboard[$block_time['id']]['Packing'][$item['employee_name']]['assignment'];
							}
							else if(isset($scoreboard[$block_time['id']]['Load'][$item['employee_name']]['assignment'])) {
								echo $scoreboard[$block_time['id']]['Load'][$item['employee_name']]['assignment'];
							}
						?>
					</td>
				<?php endforeach; ?>
				
				<?php
					$picking_avg_td_class = '';
					
					if(isset($scoreboard['total']['Picking'][$item['employee_name']]['average'])) {
						$threshold_values['below'] = 50;
						$threshold_values['minimum'] = 60;
						$threshold_values['production_goal'] = 70;
						$threshold_values['great'] = 80;
						
						switch(true) {
							case $scoreboard['total']['Picking'][$item['employee_name']]['average'] < $threshold_values['below']:
								$picking_avg_td_class .= 'row-below';
								break;
							case $scoreboard['total']['Picking'][$item['employee_name']]['average'] < $threshold_values['minimum']:
								$picking_avg_td_class .= 'row-minimum';
								break;
							case $scoreboard['total']['Picking'][$item['employee_name']]['average'] < $threshold_values['production_goal']:
								$picking_avg_td_class .= 'row-production-goal';
								break;
							case $scoreboard['total']['Picking'][$item['employee_name']]['average'] < $threshold_values['great']:
								$picking_avg_td_class .= 'row-great';
								break;
							case $scoreboard['total']['Picking'][$item['employee_name']]['average'] >= $threshold_values['great']:
								$picking_avg_td_class .= 'row-outstanding';
								break;
						}
					}
					
					$packing_avg_td_class = '';
					
					if(isset($scoreboard['total']['Packing'][$item['employee_name']]['average'])) {
						$threshold_values['below'] = 25;
						$threshold_values['minimum'] = 30;
						$threshold_values['production_goal'] = 40;
						$threshold_values['great'] = 50;
						
						switch(true) {
							case $scoreboard['total']['Packing'][$item['employee_name']]['average'] < $threshold_values['below']:
								$packing_avg_td_class .= 'row-below';
								break;
							case $scoreboard['total']['Packing'][$item['employee_name']]['average'] < $threshold_values['minimum']:
								$packing_avg_td_class .= 'row-minimum';
								break;
							case $scoreboard['total']['Packing'][$item['employee_name']]['average'] < $threshold_values['production_goal']:
								$packing_avg_td_class .= 'row-production-goal';
								break;
							case $scoreboard['total']['Packing'][$item['employee_name']]['average'] < $threshold_values['great']:
								$packing_avg_td_class .= 'row-great';
								break;
							case $scoreboard['total']['Packing'][$item['employee_name']]['average'] >= $threshold_values['great']:
								$packing_avg_td_class .= 'row-outstanding';
								break;
						}
					}
				?>
				
				<td class="metrics-board-value total-cell" style="border-left:3px solid white;"><?php echo isset($scoreboard['total']['Picking'][$item['employee_name']]['qty']) ? $scoreboard['total']['Picking'][$item['employee_name']]['qty'] : ''; ?></td>
				<td class="metrics-board-value total-cell <?php echo $picking_avg_td_class; ?>"><?php echo isset($scoreboard['total']['Picking'][$item['employee_name']]['formatted_average']) ? $scoreboard['total']['Picking'][$item['employee_name']]['formatted_average'] : ''; ?></td>
				
				<td class="metrics-board-value total-cell"><?php echo isset($scoreboard['total']['Packing'][$item['employee_name']]['qty']) ? $scoreboard['total']['Packing'][$item['employee_name']]['qty'] : ''; ?></td>
				<td class="metrics-board-value total-cell <?php echo $packing_avg_td_class; ?>"><?php echo isset($scoreboard['total']['Packing'][$item['employee_name']]['formatted_average']) ? $scoreboard['total']['Packing'][$item['employee_name']]['formatted_average'] : ''; ?></td>
				
				<td class="metrics-board-value total-cell"><?php echo isset($scoreboard['total']['Load'][$item['employee_name']]['qty']) ? $scoreboard['total']['Load'][$item['employee_name']]['qty'] : ''; ?></td>
				<td class="metrics-board-value total-cell"><?php echo isset($scoreboard['total']['Load'][$item['employee_name']]['formatted_average']) ? $scoreboard['total']['Load'][$item['employee_name']]['formatted_average'] : ''; ?></td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>