<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<style type="text/css">
	.total-row {
		background-color: #2A9FD6;
		font-weight: bold;
	}
</style>

<div class="row">
	<div class="col-md-3">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<div class="panel-title inventory-board-title" style="text-align: center;"># of Drops</div>
			</div>
			<div class="panel-body" style="text-align: center; font-weight: bold; font-size: 36px;">
				<?php echo $live_drops_data_count; ?>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<div class="panel-title inventory-board-title">Total Drops Per Hour</div>
			</div>
			<div class="panel-body">
				<div class="total-drops-per-hour-chart-wrapper">
					<div class="inventory-board-chart" id="total-drops-per-hour-chart"></div>
				</div>
			</div>
		</div>
		
		<?php 
			if(!empty($total_drops_per_hour_data['user_breakdown'])) :
				$i = 0;
				foreach($total_drops_per_hour_data['user_breakdown'] as $name => $value): $i++;
		?>
		
		<div class="panel panel-default">
			<div class="panel-heading">
				<div class="panel-title inventory-board-title"><?php echo $name; ?></div>
			</div>
			<div class="panel-body">
				<div class="total-drops-per-hour-chart-wrapper">
					<div class="inventory-board-chart" id="user-<?php echo $i; ?>-drops-per-hour-chart"></div>
				</div>
			</div>
		</div>
		
		<?php endforeach; endif; ?>
		
		<h2>Data</h2>
		
		<div class="table-area">
			<table class="table table-bordered inventory-board-table" id="inventory-board-table" style="width:<?php echo 150+50*count($hours); ?>px;">
				<thead>
					<th style="width:150px;">Name</th>
					<?php foreach($hours as $hour): ?>
					<th style="width:50px;"><?php echo date('g:00A', strtotime($hour)); ?></th>
					<?php endforeach; ?>
				</thead>
				<tbody>
					<tr class="total-row">
						<td>Total</td>
						<?php foreach($total_drops_per_hour_data['total'] as $num_drop): ?>
						<td style="text-align:center;"><?php echo $num_drop; ?></td>
						<?php endforeach; ?>
					</tr>
					<?php foreach($total_drops_per_hour_data['user_breakdown'] as $name => $this_user_data): ?>
					<tr>
						<td><?php echo $name; ?></td>
						<?php foreach($this_user_data as $num_drop): ?>
						<td style="text-align:center;"><?php echo $num_drop; ?>
						<?php endforeach; ?>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<?php endif; ?>