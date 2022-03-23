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
				<div class="panel-title takt-board-title">Completed Shipments<br><?php echo $date; ?></div>
			</div>
			<div class="panel-body">
				<div class="takt-board-chart-wrapper">
					<div class="takt-board-chart" id="hourly-completed-shipments-chart"></div>
				</div>
			</div>
		</div>
	</div>
</div>

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
							<?php $total_cols = 2; 
								$this_datetime = date('Y-m-d H:i:s', strtotime($start_datetime));
								while($this_datetime <= $end_datetime) :
									$total_cols++; ?>
									
									<th style="text-align:center;" <?php echo (date('G', strtotime($page_generated_time)) == date('G', strtotime($this_datetime))) ? ' class="td-current-time" ' : null; ?>><?php echo date('H:i', strtotime($this_datetime)); ?></th>
									
									<?php $this_datetime = date('Y-m-d H:i:s', strtotime('+1 hour '.$this_datetime));
								endwhile; ?>
							<th>Total</th>
						</thead>
						<tbody>
							<tr>
								<td>Completed Shipments Per Minute</td>
								
								<?php $this_datetime = date('Y-m-d H:i:s', strtotime($start_datetime));
								while($this_datetime <= $end_datetime) : ?>
									<td style="text-align:center; font-size:18px;" <?php echo (date('G', strtotime($page_generated_time)) == date('G', strtotime($this_datetime))) ? ' class="td-current-time" ' : null; ?>><?php echo isset($hourly_completed_shipments_count[$this_datetime]['value_per_minute']) ? $hourly_completed_shipments_count[$this_datetime]['value_per_minute'] : null; ?></td>
									
									<?php $this_datetime = date('Y-m-d H:i:s', strtotime('+1 hour '.$this_datetime));
								endwhile; ?>

								<td></td>
							</tr>
							<tr>
								<td>Shipments Per Minute</td>
								
								<?php $this_datetime = date('Y-m-d H:i:s', strtotime($start_datetime));
								while($this_datetime <= $end_datetime) : ?>
									<td style="text-align:center; font-size:18px;" <?php echo (date('G', strtotime($page_generated_time)) == date('G', strtotime($this_datetime))) ? ' class="td-current-time" ' : null; ?>><?php echo isset($hourly_shipments_count_per_minute[$this_datetime]) ? $hourly_shipments_count_per_minute[$this_datetime] : null; ?></td>
									
									<?php $this_datetime = date('Y-m-d H:i:s', strtotime('+1 hour '.$this_datetime));
								endwhile; ?>
								<td></td>
							</tr>
							<tr>
								<td colspan="<?php echo $total_cols; ?>">#Staffs</td>
							</tr>
							
							<?php $total_hourly_staffs = array(); ?>
							
							<?php foreach($hourly_staffs_status_list as $the_status): ?>
								<tr>
									<td><?php echo ucwords($the_status); ?></td>
									<?php 
									
									for($i=0; $i<$num_hours; $i++):
											if(!isset($hourly_staffs_count_by_status[$the_status.'-'.($i)]) && $i<=date('G', strtotime($page_generated_time))) {
												$hourly_staffs_count_by_status[$the_status.'-'.($i)] = 0;
											}
											$css_color = null;
											if(isset($hourly_staffs_count_by_status[$the_status.'-'.($i)]) && isset($hourly_staffs_count_by_status[$the_status.'-'.($i-1)])) {
												if($hourly_staffs_count_by_status[$the_status.'-'.($i)] < $hourly_staffs_count_by_status[$the_status.'-'.($i-1)]) {
													$css_color = 'color:red;';
												}
												else if($hourly_staffs_count_by_status[$the_status.'-'.($i)] == $hourly_staffs_count_by_status[$the_status.'-'.($i-1)]) {
													$css_color = 'color:green;';
												}
												else if($hourly_staffs_count_by_status[$the_status.'-'.($i)] > $hourly_staffs_count_by_status[$the_status.'-'.($i-1)]) {
													$css_color = 'color:yellow;';
												}
											}
									?>
										<td style="text-align:center; font-size:18px;<?php echo $css_color; ?>" <?php echo (floor((strtotime($page_generated_time)-strtotime($start_datetime))/3600) == $i) ? ' class="td-current-time" ' : null; ?>>
											<?php echo isset($hourly_staffs_count_by_status[$the_status.'-'.$i]) ? $hourly_staffs_count_by_status[$the_status.'-'.$i] : null; ?>
											<?php if(isset($past_hourly_staffs_count_by_status[$the_status.'-'.$i])) : ?>
												<br><span style="color:grey;">(<?php echo round($past_hourly_staffs_count_by_status[$the_status.'-'.$i]); ?>)</span>
											<?php endif; ?>
										</td>
									<?php endfor; ?>
									<td style="text-align:center; font-size:18px;">
										<?php echo isset($total_hourly_staffs_count_by_status[$the_status]) ? $total_hourly_staffs_count_by_status[$the_status] : null; ?>
										<?php if(isset($past_total_hourly_staffs_count_by_status[$the_status])) : ?>
											<br><span style="color:grey;">(<?php echo round($past_total_hourly_staffs_count_by_status[$the_status]); ?>)</span>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
						<tfoot>
							<td>Total</td>
							<?php for($i=0; $i<$num_hours; $i++): 
									$css_color = null;
									if(isset($hourly_staffs_count[$i]) && isset($hourly_staffs_count[$i-1])) {
										if($hourly_staffs_count[$i] < $hourly_staffs_count[$i-1]) {
											$css_color = 'color:red;';
										}
										else if($hourly_staffs_count[$i] == $hourly_staffs_count[$i-1]) {
											$css_color = 'color:green;';
										}
										else if($hourly_staffs_count[$i] > $hourly_staffs_count[$i-1]) {
											$css_color = 'color:yellow;';
										}
									}
							?>
								<td style="text-align:center; font-size:18px;<?php echo $css_color; ?>" <?php echo (floor((strtotime($page_generated_time)-strtotime($start_datetime))/3600) == $i) ? ' class="td-current-time" ' : null; ?>>
									<?php echo isset($hourly_staffs_count[$i]) ? $hourly_staffs_count[$i] : null; ?>
									<?php if(isset($past_hourly_staffs_count[$i])) : ?>
										<br><span style="color:grey;">(<?php echo round($past_hourly_staffs_count[$i]); ?>)</span>
									<?php endif; ?>
								</td>
							<?php endfor; ?>
							<td style="text-align:center; font-size:18px;">
								<?php echo isset($total_hourly_staffs_count_by_status['total']) ? $total_hourly_staffs_count_by_status['total'] : null; ?>
								<?php if(isset($past_total_hourly_staffs_count_by_status['total'])) : ?>
									<br><span style="color:grey;">(<?php echo round($past_total_hourly_staffs_count_by_status['total']); ?>)</span>
								<?php endif; ?>
							</td>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<div class="panel-title takt-board-title">Orders<br><?php echo $date; ?></div>
			</div>
			<div class="panel-body">
				<div class="takt-board-chart-wrapper">
					<div class="takt-board-chart" id="hourly-orders-chart"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php if(!empty($num_employees_scheduled) && !empty($operational_cost_per_package) && !empty($fte_cost_per_hour)) : ?>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title takt-board-title">Cost & Labor Hours Per Package<br><?php echo $date; ?></div>
			</div>
			<div class="panel-body">
				<div class="takt-board-chart-wrapper">
					<div class="takt-board-chart" id="hourly-cost-per-package-chart"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php else: ?>

<div class="alert alert-warning alert-dismissable" role="alert"><span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>&nbsp;&nbsp;
Please complete number of employees scheduled, operational cost per package, and FTE cost per hour to show Hourly Cost Per Package graph.</div>

<?php endif; ?>

<?php endif; ?>