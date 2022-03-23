<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>
	
	<style type="text/css">
		.row-red {
			background-color: #C21807;
			color: white;
			transition: 1200ms ease;
		}
		
		.row-yellow {
			background-color: yellow;
			color: black;
		}
		
		.row-green {
			background-color: green;
			color: white;
		}
	</style>
	
	<h2>Utilization Trend</h2>
	
	<div class="row">
		<div class="col-md-12">
			<table class="table datatabled datatabled-entity" style="width:600px;">
				<thead>
					<tr>
						<th style="width:200px;">Period</th>
						<th style="width:200px;">Carrier</th>
						<th style="width:100px;">Weight Percentage</th>
						<th style="width:100px;">Cubic Ft Percentage</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($utilization_metrics_trend_data as $current_data) : 
							$weight_percentage_color = ($current_data['weight_percentage'] < 50) ? 'red' : ($current_data['weight_percentage'] < 70 ? 'yellow' : 'green');
							$cubic_ft_percentage_color = ($current_data['cubic_ft_percentage'] < 50) ? 'red' : ($current_data['cubic_ft_percentage'] < 70 ? 'yellow' : 'green');
					?>
					
					<tr>
						<td><?php echo $current_data['period_label']; ?></td>
						<td><?php echo $current_data['carrier_code']; ?></td>
						<td class="row-<?php echo $weight_percentage_color; ?>"><?php echo number_format($current_data['weight_percentage'],2); ?></td>
						<td class="row-<?php echo $cubic_ft_percentage_color; ?>"><?php echo number_format($current_data['cubic_ft_percentage'],2); ?></td>
					</tr>
					
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-info">
				<div class="panel-heading">
					<div class="panel-title chart-box-title">Weight Percentage</div>
				</div>
				<div class="panel-body">
					<div class="chart-wrapper">
						<div class="utilization-trend-chart" id="weight-percentage-chart"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-info">
				<div class="panel-heading">
					<div class="panel-title chart-box-title">Cubic Ft Percentage</div>
				</div>
				<div class="panel-body">
					<div class="chart-wrapper">
						<div class="utilization-trend-chart" id="cubic-ft-percentage-chart"></div>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php endif; ?>
