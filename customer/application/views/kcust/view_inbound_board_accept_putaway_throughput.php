<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<table class="table datatabled-entity" id="accept-putaway-throughput-table">
	<thead>
		<th>Period</th>
		<th>Average Accept End - Putaway Duration (Hrs)</th>
		<th># of Pallets Processed</th>
		<th># of SKUs Processed</th>
		<th># of ASNs</th>
	</thead>
	<tbody>
		<?php if(!empty($accept_putaway_throughput_data)): ?>
			<?php foreach($accept_putaway_throughput_data as $current_data) : ?>
				<tr>
					<td><?php echo $current_data['period_label']; ?></td>
					<td><?php echo number_format($current_data['average_accept_putaway_duration'], 2); ?></td>
					<td><?php echo $current_data['num_pallets_processed']; ?></td>
					<td><?php echo $current_data['num_skus_processed']; ?></td>
					<td><?php echo $current_data['asn_count']; ?></td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="5" style="text-align:center;">No Data Available</td>
			</tr>
		<?php endif; ?>
	</tbody>
</table>

&nbsp;<br>

<?php if(!empty($accept_putaway_throughput_data)): ?>
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-success">
				<div class="panel-heading">
					<div class="panel-title inbound-board-title">Accept-Putaway Throughput</div>
				</div>
				<div class="panel-body">
					<div class="accept-putaway-throughput-chart-wrapper">
						<div class="inbound-board-chart" id="accept-putaway-throughput-chart"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>

<?php endif; ?>