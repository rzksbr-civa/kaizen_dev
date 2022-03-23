<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<div class="table-area">
	<table class="table datatabled-entity revenue-table" id="wages-table">
		<thead>
			<th>First Name</th>
			<th>Last Name</th>
			<th>Total Work Hours</th>
			<th>Total Cost</th>
			<th>Payrate</th>
			<th>Regular Hours</th>
			<th>Regular Cost</th>
			<th>PTO Hours</th>
			<th>PTO Cost</th>
			<th>Paid Break Hours</th>
			<th>Paid Break Cost</th>
			<th>1.5x Overtime Hours</th>
			<th>1.5x Overtime Payrate</th>
			<th>1.5x Overtime Cost</th>
			<th>2x Overtime Hours</th>
			<th>2x Overtime Payrate</th>
			<th>2x Overtime Cost</th>
			<th>Unpaid Break Hours</th>
		</thead>
		<tbody>
			<?php foreach($wages as $current_data) : ?>
				<tr>
					<td><?php echo $current_data['first_name']; ?></td>
					<td><?php echo $current_data['last_name']; ?></td>
					<td><?php echo number_format($current_data['total_work_hours'],2); ?></td>
					<td><?php echo '$' . number_format($current_data['total_cost'],2); ?></td>
					<td><?php echo '$' . number_format($current_data['pay_rate'],2); ?></td>
					<td><?php echo number_format($current_data['regular_hours'],2); ?></td>
					<td><?php echo '$' . number_format($current_data['regular_cost'],2); ?></td>
					<td><?php echo number_format($current_data['pto_hours'],2); ?></td>
					<td><?php echo '$' . number_format($current_data['pto_cost'],2); ?></td>
					<td><?php echo number_format($current_data['paid_break_hours'],2); ?></td>
					<td><?php echo '$' . number_format($current_data['paid_break_cost'],2); ?></td>
					<td><?php echo number_format($current_data['one_half_overtime_hours'],2); ?></td>
					<td><?php echo '$' . number_format($current_data['one_half_overtime_pay_rate'],2); ?></td>
					<td><?php echo '$' . number_format($current_data['one_half_overtime_cost'],2); ?></td>
					<td><?php echo number_format($current_data['double_overtime_hours'],2); ?></td>
					<td><?php echo '$' . number_format($current_data['double_overtime_pay_rate'],2); ?></td>
					<td><?php echo '$' . number_format($current_data['double_overtime_cost'],2); ?></td>
					<td><?php echo number_format($current_data['unpaid_break_hours'],2); ?></td>
			<?php endforeach; ?>
		</tbody>
		<tfoot>
			<td>Total</td>
			<td></td>
			<td><?php echo number_format($total_wages['total_work_hours'],2); ?></td>
			<td><?php echo '$' . number_format($total_wages['total_cost'],2); ?></td>
			<td></td>
			<td><?php echo number_format($total_wages['regular_hours'],2); ?></td>
			<td><?php echo '$' . number_format($total_wages['regular_cost'],2); ?></td>
			<td><?php echo number_format($total_wages['pto_hours'],2); ?></td>
			<td><?php echo '$' . number_format($total_wages['pto_cost'],2); ?></td>
			<td><?php echo number_format($total_wages['paid_break_hours'],2); ?></td>
			<td><?php echo '$' . number_format($total_wages['paid_break_cost'],2); ?></td>
			<td><?php echo number_format($total_wages['one_half_overtime_hours'],2); ?></td>
			<td></td>
			<td><?php echo '$' . number_format($total_wages['one_half_overtime_cost'],2); ?></td>
			<td><?php echo number_format($total_wages['double_overtime_hours'],2); ?></td>
			<td></td>
			<td><?php echo '$' . number_format($total_wages['double_overtime_cost'],2); ?></td>
			<td><?php echo number_format($total_wages['unpaid_break_hours'],2); ?></td>
		</tfoot>
	</table>
</div>

<?php endif; ?>