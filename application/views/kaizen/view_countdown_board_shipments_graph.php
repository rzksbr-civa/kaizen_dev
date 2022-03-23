<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<style type="text/css">
	.td-current-time {
		border: 3px solid #00E396 !important;
	}
</style>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title takt-board-title">Completed Shipments Per Minute<br><?php echo $date; ?></div>
			</div>
			<div class="panel-body">
				<div class="takt-board-chart-wrapper">
					<div class="takt-board-chart" id="hourly-completed-shipments-per-minute-chart"></div>
					
					<table class="table table-bordered" style="width:100%; min-width: 1200px; background-image: linear-gradient(to bottom, #000000, #00003F);">
						<thead>
							<th style="width:150px;"></th>
							<?php $total_cols = 1; 
								for($i=date('G', strtotime($start_time)); $i<=23; $i++): $total_cols++; ?>
								<th style="text-align:center;" <?php echo (date('G', strtotime($page_generated_time)) == $i) ? ' class="td-current-time" ' : null; ?>><?php echo sprintf('%02d:00', $i); ?></th>
							<?php endfor; ?>
						</thead>
						<tbody>
							<tr>
								<td>Completed Shipments Per Minute</td>
							<?php for($i=date('G', strtotime($start_time)); $i<=23; $i++): ?>
								<td style="text-align:center; font-size:18px;" <?php echo (date('G', strtotime($page_generated_time)) == $i) ? ' class="td-current-time" ' : null; ?>><?php echo isset($hourly_completed_shipments_count[$date.' '.sprintf('%02d:00:00', $i)]['value_per_minute']) ? $hourly_completed_shipments_count[$date.' '.sprintf('%02d:00:00', $i)]['value_per_minute'] : null; ?></td>
							<?php endfor; ?>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<?php endif; ?>