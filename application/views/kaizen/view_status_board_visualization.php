<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<div class="col-lg-8 col-md-12">
	<table class="table" style="width:360px;">
		<thead>
			<th style="width:150px; text-align:center;">Status</th>
			<th style="width:80px; text-align:center;">#Staffs</th>
			<th style="width:200px; text-align:center;">Staffs</th>
		</thead>
		<tbody>
		<?php foreach($active_staffs_data as $current_status => $current_data) : ?>
			<tr>
				<td style="font-size:20px; vertical-align:middle;"><?php echo ucwords(str_replace('_',' ',$current_status)); ?></td>
				<td style="font-size:20px; vertical-align:middle; text-align:center;"><?php echo count($current_data); ?></td>
				<td style="font-size:14px; vertical-align:middle;"><?php echo implode('<br>', $current_data); ?></td>
			</tr>
		<?php endforeach; ?>
		<?php if(empty($active_staffs_data)) : ?>
			<tr>
				<td colspan="3" style="font-size:20px; background-color:green; color:white; text-align:center;">
					No data found.
				</td>
		<?php endif; ?>
		</tbody>
		<tfoot style="background-color:#333;">
			<td style="font-size:20px; vertical-align:middle;">Total</td>
			<td style="font-size:20px; vertical-align:middle; text-align:center;"><?php echo count($all_staffs); ?></td>
			<td></td>
		</tfoot>
	</table>
</div>

<?php endif; ?>