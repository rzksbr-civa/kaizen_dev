<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<style type="text/css">
	.card-title {
		text-align: center;
		font-size: 20px;
	}
	
	.card-value {
		text-align: center;
		font-size: 40px;
		font-weight: bold;
	}
</style>

<div class="row">
	<div class="col-md-4">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<div class="panel-title card-title">Total Package</div>
			</div>
			<div class="panel-body card-value">
				<?php echo number_format($total_package, 0); ?>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<div class="panel-title card-title">Total Cubic Ft</div>
			</div>
			<div class="panel-body card-value">
				<?php echo number_format($total_cubic_ft,2); ?>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<div class="panel-title card-title">Total Weight</div>
			</div>
			<div class="panel-body card-value">
				<?php echo number_format($total_weight, 2); ?>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-info">
			<div class="panel-heading">
				<div class="panel-title card-title">Forecasted Trailer (Based on Dimension)</div>
			</div>
			<div class="panel-body card-value">
				<?php echo $forecasted_trailer_based_on_dimension; ?>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="panel panel-info">
			<div class="panel-heading">
				<div class="panel-title card-title">Forecasted Trailer (Based on Weight)</div>
			</div>
			<div class="panel-body card-value">
				<?php echo $forecasted_trailer_based_on_weight; ?>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<?php if(empty($trailer_forecast_data)): ?>
		<h2>No Data Found</h2>
	<?php else: ?>
		<div class="table-area">
			<table class="table table-bordered" id="trailer-utilization-forecast-board-table" style="width:800px;">
				<thead>
					<th style="width: 100px;">Carrier</th>
					<th style="width: 80px;">Package Count</th>
					<th style="width: 100px;">Total Cubic Ft</th>
					<th style="width: 100px;">Total Weight</th>
					<th style="width: 100px;">Forecasted Trailer Based on Dimension</th>
					<th style="width: 100px;">Forecasted Trailer Based on Weight</th>
				</thead>
				<tbody>
					<?php foreach($trailer_forecast_data as $current_data): ?>
					<tr>
						<td><?php echo $current_data['carrier_code']; ?></td>
						<td><?php echo $current_data['package_qty']; ?></td>
						<td><?php echo number_format($current_data['total_cubic_ft'],2); ?></td>
						<td><?php echo number_format($current_data['total_weight'],2); ?></td>
						<td><?php echo number_format($current_data['forecasted_trailer_based_on_dimension'],2); ?></td>
						<td><?php echo number_format($current_data['forecasted_trailer_based_on_weight'],2); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>

<?php endif; ?>