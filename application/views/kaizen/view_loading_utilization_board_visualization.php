<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<style type="text/css">

</style>

<div class="row">
		<div class="table-area">
			<?php if($breakdown == 'no_breakdown'): ?>
				<table class="table table-bordered datatabled-entity" id="loading-utilization-board-table" style="width:800px;">
					<thead>
						<th style="width: 100px;">Manifest</th>
						<th style="width: 80px;">Customer Count</th>
						<th style="width: 100px;">Start</th>
						<th style="width: 100px;">End</th>
						<th style="width: 100px;">Carrier</th>
						<th style="width: 80px;">Total Packages</th>
						<th style="width: 80px;">Total Weight</th>
						<th style="width: 80px;">Total Cu.Ft</th>
						<th style="width: 80px;">% Weight</th>
						<th style="width: 80px;">% Cu.Ft</th>
					</thead>
					<tbody>
						<?php foreach($manifest_data as $current_data): ?>
						<tr>
							<td><?php echo $current_data['manifest_no']; ?></td>
							<td><?php echo $current_data['customer_count']; ?></td>
							<td><?php echo $current_data['start']; ?></td>
							<td><?php echo $current_data['end']; ?></td>
							<td><?php echo $current_data['carrier_code']; ?></td>
							<td style="text-align:right;"><?php echo $current_data['total_packages']; ?></td>
							<td style="text-align:right;"><?php echo number_format($current_data['total_weight'], 2); ?></td>
							<td style="text-align:right;"><?php echo number_format($current_data['total_cubic_ft'], 2); ?></td>
							<td style="text-align:right;" class="row-<?php echo $current_data['weight_utilization_color']; ?>"><?php echo number_format($current_data['weight_percentage'], 1); ?>%</td>
							<td style="text-align:right;" class="row-<?php echo $current_data['cubic_ft_utilization_color']; ?>"><?php echo number_format($current_data['cubic_ft_percentage'], 1); ?>%</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php elseif($breakdown == 'customer'): ?>
				<table class="table table-bordered datatabled-entity" id="loading-utilization-board-table" style="width:1060px;">
					<thead>
						<th style="width: 100px;">Manifest</th>
						<th style="width: 80px;">Customer Count</th>
						<th style="width: 80px;">% Weight (Manifest)</th>
						<th style="width: 80px;">% Cu.Ft (Manifest)</th>
						<th style="width: 100px;">Customer</th>
						<th style="width: 100px;">Start</th>
						<th style="width: 100px;">End</th>
						<th style="width: 100px;">Carrier</th>
						<th style="width: 80px;">Total Packages</th>
						<th style="width: 80px;">Total Weight</th>
						<th style="width: 80px;">Total Cu.Ft</th>
						<th style="width: 80px;">% Weight</th>
						<th style="width: 80px;">% Cu.Ft</th>	
					</thead>
					<tbody>
						<?php foreach($manifest_by_customer_data as $key => $current_data): ?>
						<tr>
							<td><?php echo $current_data['manifest_no']; ?></td>
							<td><?php echo $manifest_data_by_manifest_no[$current_data['manifest_no']]['customer_count']; ?></td>
							<td style="text-align:right;" class="row-<?php echo $manifest_data_by_manifest_no[$current_data['manifest_no']]['weight_utilization_color']; ?>"><?php echo number_format($manifest_data_by_manifest_no[$current_data['manifest_no']]['weight_percentage'], 1); ?>%</td>
							<td style="text-align:right;" class="row-<?php echo $manifest_data_by_manifest_no[$current_data['manifest_no']]['cubic_ft_utilization_color']; ?>"><?php echo number_format($manifest_data_by_manifest_no[$current_data['manifest_no']]['cubic_ft_percentage'], 1); ?>%</td>
							<td><?php echo $current_data['customer_name']; ?></td>
							<td><?php echo $current_data['start']; ?></td>
							<td><?php echo $current_data['end']; ?></td>
							<td><?php echo $current_data['carrier_code']; ?></td>
							<td style="text-align:right;"><?php echo $current_data['total_packages']; ?></td>
							<td style="text-align:right;"><?php echo number_format($current_data['total_weight'], 2); ?></td>
							<td style="text-align:right;"><?php echo number_format($current_data['total_cubic_ft'], 2); ?></td>
							<td style="text-align:right;" class="row-<?php echo $current_data['weight_utilization_color']; ?>"><?php echo number_format($current_data['weight_percentage'], 1); ?>%</td>
							<td style="text-align:right;" class="row-<?php echo $current_data['cubic_ft_utilization_color']; ?>"><?php echo number_format($current_data['cubic_ft_percentage'], 1); ?>%</td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php endif; ?>