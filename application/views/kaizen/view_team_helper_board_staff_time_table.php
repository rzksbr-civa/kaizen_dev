<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<!-- Datatables CSS -->
<link href="<?php echo base_url('assets/datatables/1.10.18/datatables.min.css'); ?>" rel="stylesheet">

<div id="staff-time-log-table-area">
	<table class="table table-bordered" id="staff-time-log-table">
		<thead>
			<th style="width:38px; text-align:center;"></th>
			<th style="width:150px;">Employee Name</th>
			<th style="width:100px;">Packing</th>
			<th style="width:100px;">Packing Qty</th>
			<th style="width:100px;">Packing Cost</th>
			<th style="width:100px;">Picking</th>
			<th style="width:100px;">Picking Qty</th>
			<th style="width:100px;">Picking Cost</th>
			<th style="width:100px;">Loading</th>
			<th style="width:100px;">Loading Qty</th>
			<th style="width:100px;">Loading Cost</th>
			<th style="width:100px;">Support</th>
			<th style="width:100px;">Support Cost</th>
			<th style="width:100px;">Training</th>
			<th style="width:100px;">Training Cost</th>
			<th style="width:100px;">Planned Time</th>
			<th style="width:100px;">Actual Time</th>
			<th style="width:100px;">Total (VA) Time</th>
			<th style="width:100px;">Total (NVA) Time</th>
			<th style="width:100px;">Total Qty</th>
			<th style="width:100px;">VA Cost</th>
			<th style="width:100px;">NVA Cost</th>
			<th style="width:100px;">Total Cost</th>
			<th style="width:100px;">Productivity Rate</th>
		</thead>
		<tbody>
			<?php foreach($staff_time_log_summary as $employee_name => $time_log_summary) : 
				$tr_class = '';
				if(isset($time_log_summary['productivity_rate'])) {
					if($time_log_summary['productivity_rate'] > 100) {
						$tr_class = 'orange-bg';
					}
					else if($time_log_summary['productivity_rate'] >= 85) {
						$tr_class = 'green-bg';
					}
					else if($time_log_summary['productivity_rate'] >= 75) {
						$tr_class = 'yellow-bg';
					}
					else {
						$tr_class = 'red-bg';
					}
				}
			
			?>
			
			<tr<?php echo !empty($tr_class) ? ' class="'.$tr_class.'"' : ''; ?>>
				<td style="padding:4px;"><img src="<?php echo file_exists(str_replace('application','assets',APPPATH).'data/kaizen/app/users/thumbnails/'.$employee_name.'.jpg') ? base_url('assets/data/kaizen/app/users/thumbnails/'.$employee_name.'.jpg') : base_url('assets/data/kaizen/app/users/thumbnails/no-photo.jpg'); ?>" width="30"></td>
				<td><?php echo $employee_name; ?></td>
				<td><?php echo isset($time_log_summary['sum_of_time_by_status']['Packing']) ? sprintf('%02d:%02d:%02d', floor($time_log_summary['sum_of_time_by_status']['Packing'] / 3600), floor(($time_log_summary['sum_of_time_by_status']['Packing'] % 3600) / 60), floor($time_log_summary['sum_of_time_by_status']['Packing'] % 60)) : '00:00:00'; ?></td>
				<td><?php echo isset($time_log_summary['qty_by_status']['Packing']) ? $time_log_summary['qty_by_status']['Packing'] : ''; ?></td>
				<td><?php echo isset($time_log_summary['cost_by_status']['Packing']) ? '$' . number_format($time_log_summary['cost_by_status']['Packing'], 2) : ''; ?></td>
				<td><?php echo isset($time_log_summary['sum_of_time_by_status']['Picking']) ? sprintf('%02d:%02d:%02d', floor($time_log_summary['sum_of_time_by_status']['Picking'] / 3600), floor(($time_log_summary['sum_of_time_by_status']['Picking'] % 3600) / 60), floor($time_log_summary['sum_of_time_by_status']['Picking'] % 60)) : '00:00:00'; ?></td>
				<td><?php echo isset($time_log_summary['qty_by_status']['Picking']) ? $time_log_summary['qty_by_status']['Picking'] : ''; ?></td>
				<td><?php echo isset($time_log_summary['cost_by_status']['Picking']) ? '$' . number_format($time_log_summary['cost_by_status']['Picking'], 2) : ''; ?></td>
				<td><?php echo isset($time_log_summary['sum_of_time_by_status']['Loading']) ? sprintf('%02d:%02d:%02d', floor($time_log_summary['sum_of_time_by_status']['Loading'] / 3600), floor(($time_log_summary['sum_of_time_by_status']['Loading'] % 3600) / 60), floor($time_log_summary['sum_of_time_by_status']['Loading'] % 60)) : '00:00:00'; ?></td>
				<td><?php echo isset($time_log_summary['qty_by_status']['Loading']) ? $time_log_summary['qty_by_status']['Loading'] : ''; ?></td>
				<td><?php echo isset($time_log_summary['cost_by_status']['Loading']) ? '$' . number_format($time_log_summary['cost_by_status']['Loading'], 2) : ''; ?></td>
				<td><?php echo isset($time_log_summary['sum_of_time_by_status']['Support']) ? sprintf('%02d:%02d:%02d', floor($time_log_summary['sum_of_time_by_status']['Support'] / 3600), floor(($time_log_summary['sum_of_time_by_status']['Support'] % 3600) / 60), floor($time_log_summary['sum_of_time_by_status']['Support'] % 60)) : '00:00:00'; ?></td>
				<td><?php echo isset($time_log_summary['cost_by_status']['Support']) ? '$' . number_format($time_log_summary['cost_by_status']['Support'], 2) : ''; ?></td>
				<td><?php echo isset($time_log_summary['sum_of_time_by_status']['Training']) ? sprintf('%02d:%02d:%02d', floor($time_log_summary['sum_of_time_by_status']['Training'] / 3600), floor(($time_log_summary['sum_of_time_by_status']['Training'] % 3600) / 60), floor($time_log_summary['sum_of_time_by_status']['Training'] % 60)) : '00:00:00'; ?></td>
				<td><?php echo isset($time_log_summary['cost_by_status']['Training']) ? '$' . number_format($time_log_summary['cost_by_status']['Training'], 2) : ''; ?></td>
				<td><?php echo sprintf('%02d:%02d:%02d', floor($time_log_summary['planned_time_in_seconds'] / 3600), floor(($time_log_summary['planned_time_in_seconds'] % 3600) / 60), floor($time_log_summary['planned_time_in_seconds'] % 60)); ?></td>
				<td><?php echo sprintf('%02d:%02d:%02d', floor($time_log_summary['actual_time_in_seconds'] / 3600), floor(($time_log_summary['actual_time_in_seconds'] % 3600) / 60), floor($time_log_summary['actual_time_in_seconds'] % 60)); ?></td>
				<td><?php echo isset($time_log_summary['sum_of_time']) ? sprintf('%02d:%02d:%02d', floor($time_log_summary['sum_of_time'] / 3600), floor(($time_log_summary['sum_of_time'] % 3600) / 60), floor($time_log_summary['sum_of_time'] % 60)) : '00:00:00'; ?></td>
				<td><?php echo sprintf('%02d:%02d:%02d', floor($time_log_summary['non_value_added_time_in_seconds'] / 3600), floor(($time_log_summary['non_value_added_time_in_seconds'] % 3600) / 60), floor($time_log_summary['non_value_added_time_in_seconds'] % 60)); ?></td>
				<td><?php echo isset($time_log_summary['total_qty']) ? $time_log_summary['total_qty'] : ''; ?></td>
				<td><?php echo isset($time_log_summary['value_added_cost']) ? '$' . number_format($time_log_summary['value_added_cost'], 2) : ''; ?></td>
				<td><?php echo isset($time_log_summary['non_value_added_cost']) ? '$' . number_format($time_log_summary['non_value_added_cost'], 2) : ''; ?></td>
				<td><?php echo isset($time_log_summary['total_cost']) ? '$' . number_format($time_log_summary['total_cost'], 2) : ''; ?></td>
				<td><?php echo !empty($time_log_summary['productivity_rate']) ? number_format($time_log_summary['productivity_rate']) : '0'; ?>%</td>
			</tr>
			
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<?php endif; ?>