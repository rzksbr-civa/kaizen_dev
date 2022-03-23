<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>
	
	<h2>Average Weight</h2>
	
	<div class="row">
		<div class="col-md-12">
			<table class="table datatabled datatabled-entity" style="width:<?php echo 400 + count($assignment_types)*100; ?>px;">
				<thead>
					<tr>
						<th style="width:200px;">Period</th>
						<th style="width:100px;">Count of Packages</th>
						<?php foreach($assignment_types as $assignment_type): ?>
							<th style="width:100px;"><?php echo $assignment_type['assignment_type_name']; ?></th>
						<?php endforeach; ?>
						<th style="width:100px;">Total</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($utilization_metrics_report_data['period'] as $period_name => $current_period_data) : ?>
					
					<tr>
						<td><?php echo $period_name; ?></td>
						<td><?php echo $utilization_metrics_report_data['total'][$period_name]['total_count']; ?></td>
						<?php foreach($current_period_data as $assignment_type_id => $current_data): ?>
							<td><?php echo $current_data['total_count'] > 0 ? number_format($current_data['total_weight']/$current_data['total_count'],2) : 0; ?></td>
						<?php endforeach; ?>
						<td><?php echo $utilization_metrics_report_data['total'][$period_name]['total_count'] > 0 ? number_format($utilization_metrics_report_data['total'][$period_name]['total_weight']/$utilization_metrics_report_data['total'][$period_name]['total_count'],2) : 0; ?></td>
					</tr>
					
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
	
	<h2>Average Cubic Ft</h2>
	
	<div class="row">
		<div class="col-md-12">
			<table class="table datatabled datatabled-entity" style="width:<?php echo 400 + count($assignment_types)*100; ?>px;">
				<thead>
					<tr>
						<th style="width:200px;">Period</th>
						<th style="width:100px;">Count of Packages</th>
						<?php foreach($assignment_types as $assignment_type): ?>
							<th style="width:100px;"><?php echo $assignment_type['assignment_type_name']; ?></th>
						<?php endforeach; ?>
						<th style="width:100px;">Total</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($utilization_metrics_report_data['period'] as $period_name => $current_period_data) : ?>
					
					<tr>
						<td><?php echo $period_name; ?></td>
						<td><?php echo $utilization_metrics_report_data['total'][$period_name]['total_count']; ?></td>
						<?php foreach($current_period_data as $assignment_type_id => $current_data): ?>
							<td><?php echo $current_data['total_count'] > 0 ? number_format($current_data['total_cubic_ft']/$current_data['total_count'],2) : 0; ?></td>
						<?php endforeach; ?>
						<td><?php echo $utilization_metrics_report_data['total'][$period_name]['total_count'] > 0 ? number_format($utilization_metrics_report_data['total'][$period_name]['total_cubic_ft']/$utilization_metrics_report_data['total'][$period_name]['total_count'],2) : 0; ?></td>
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
					<div class="panel-title chart-box-title">Average Weight</div>
				</div>
				<div class="panel-body">
					<div class="chart-wrapper">
						<div class="utilization-report-chart" id="average-weight-chart"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-info">
				<div class="panel-heading">
					<div class="panel-title chart-box-title">Average Cubic Ft</div>
				</div>
				<div class="panel-body">
					<div class="chart-wrapper">
						<div class="utilization-report-chart" id="average-cubic-ft-chart"></div>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php endif; ?>
