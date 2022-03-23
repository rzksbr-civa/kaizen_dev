<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<style type="text/css">

</style>

	<?php if(!empty($error_message)): ?>
		<div class="alert alert-danger" role="alert">
			<span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span>
			<?php echo $error_message; ?>
		</div>
	<?php else: ?>
		<div class="row">
			<div class="col-md-9">
				<div class="table-area">
					<table class="table table-bordered datatabled-entity" id="sla-board-table" style="width:360px;">
						<thead>
							<th style="width: 200px;">Customer</th>
							<th style="width: 80px; text-align:right;">Count of Tracking Number</th>
							<th style="width: 80px; text-align:right;">Remaining</th>
							<th style="width: 80px; text-align:right;">SLA</th>
						</thead>
						<tbody>
							<?php foreach($sla_board_data as $customer_name => $current_data): ?>
							<tr>
								<td><?php echo $customer_name; ?></td>
								<td style="text-align:right;"><?php echo $current_data['todays_total_orders_count']; ?></td>
								<td style="text-align:right;"><?php echo $current_data['todays_remaining_orders_count']; ?></td>
								<td style="text-align:right;"><?php echo round($current_data['sla']); ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
						<tfoot>
							<tr>
								<td>Total</td>
								<td style="text-align:right;"><?php echo $total['todays_total_orders_count']; ?></td>
								<td style="text-align:right;"><?php echo $total['todays_remaining_orders_count']; ?></td>
								<td style="text-align:right;"><?php echo round($total['sla']); ?></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
			<div class="col-md-3">
				<div class="panel panel-primary">
					<div class="panel-heading">
						<div class="panel-title sla-board-title">SLA Cap: <?php echo $sla_cap; ?></div>
					</div>
					<table class="table">
						<thead>
							<th>SLA</th>
							<th style="text-align:right;">#Customers</th>
							<th style="text-align:right;">%</th>
						</thead>
						<tbody>
							<?php foreach($sla_board_summary_data as $sla => $current_data) : ?>
								<tr>
									<td><?php echo $sla; ?></td>
									<td style="text-align:right;"><?php echo ucwords($current_data['customers_count']); ?></td>
									<td style="text-align:right;"><?php echo $current_data['percentage']; ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	<?php endif; ?>

<?php endif; ?>