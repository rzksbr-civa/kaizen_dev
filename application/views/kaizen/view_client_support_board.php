<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Datatables CSS -->
<link href="<?php echo base_url('assets/datatables/1.10.18/datatables.min.css'); ?>" rel="stylesheet">

<style>
	table.datatabled th,
	table.datatabled td,
	table.datatabled th,
	table.datatabled td {
		white-space: initial;
	}
</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		CLIENT SUPPORT BOARD
	</h3>	
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-team-helper-board-filter">
			<input type="hidden" class="form-control" id="input-generate" name="generate" value=1>
			<div class="row">
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-customer">Customer</label>
						<select class="form-control selectized" id="input-customer" name="customer">
							<option value=''>All Customers</option>
							<?php
								foreach($customer_list as $item) {
									$selected = ($customer == $item['id']) ? ' selected' : '';
									echo '<option value="'.$item['id'].'"'.$selected.'>'.$item['customer_name'].'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-periodicity">Periodicity</label>
						<select class="form-control selectized" id="input-periodicity" name="periodicity">
							<?php
								foreach($periodicity_list as $item) {
									$selected = ($periodicity == $item) ? ' selected' : '';
									echo '<option value="'.$item.'"'.$selected.'>'.ucwords($item).'</option>';
								}
							?>
						</select>
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-period-from">Period From</label>
						<input type="date" class="form-control" id="input-period-from" name="period_from" value="<?php echo $period_from; ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-period-to">Period To</label>
						<input type="date" class="form-control" id="input-period-to" name="period_to" value="<?php echo $period_to; ?>">
					</div>
				</div>
				<div class="col-md-2">
					<div class="form-group">
						<label>&nbsp;</label>
						<button type="submit" class="form-control btn btn-primary">Show</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>

<?php if($generate) : ?>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title">Ticket Efficiency</div>
			</div>
			<div class="panel-body">
				<div class="client-support-board-chart-wrapper">
					<div class="client-support-board-chart" id="total-open-tickets-per-day-chart"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title">Total Open Tickets By Type Per Day</div>
			</div>
			<div class="panel-body">
				<div class="client-support-board-chart-wrapper">
					<div class="client-support-board-chart" id="total-open-tickets-by-type-per-day-chart"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title">Average Closed Tickets Age by Type</div>
			</div>
			<div class="panel-body">
				<div class="client-support-board-chart-wrapper">
					<div class="client-support-board-chart" id="average-age-of-closed-tickets-chart"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php if(!empty($ticket_statuses) && empty($customer)): ?>
<div class="row">
	<div class="col-md-12">
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title">Total Open Tickets By Status Per Day</div>
			</div>
			<div class="panel-body">
				<div class="client-support-board-chart-wrapper">
					<div class="client-support-board-chart" id="total-open-tickets-by-status-per-day-chart"></div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php endif; ?>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title">Average Initial Response Time</div>
			</div>
			<div class="panel-body">
				<div class="client-support-board-chart-wrapper">
					<div class="client-support-board-chart" id="average-initial-response-time-chart"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title">Total Open Tickets By Group Per Day</div>
			</div>
			<div class="panel-body">
				<div class="client-support-board-chart-wrapper">
					<div class="client-support-board-chart" id="total-open-tickets-by-group-per-day-chart"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title">Average Closed Tickets Age by Group</div>
			</div>
			<div class="panel-body">
				<div class="client-support-board-chart-wrapper">
					<div class="client-support-board-chart" id="average-age-of-closed-tickets-by-group-chart"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-6">
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title">Current "Days Opened" By Group</div>
			</div>
			<div class="panel-body">
				<div class="client-support-board-chart-wrapper">
					<div class="client-support-board-chart" id="days-opened-by-group-chart"></div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="col-md-6">
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title">Current Total Tickets Opened By Group</div>
			</div>
			<div class="panel-body">
				<div class="client-support-board-chart-wrapper">
					<div class="client-support-board-chart" id="total-tickets-opened-by-group-chart"></div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="col-md-6">
		<div class="panel panel-success">
			<div class="panel-heading">
				<div class="panel-title">Top Users with Assigned Open Tickets</div>
			</div>
			<div class="panel-body">
				<table class="table datatabled" id="top-users-with-assigned-open-tickets">
					<thead>
						<th>User Name</th>
						<th>#Open Tickets</th>
						<th>#Yesterday Actions</th>
					</thead>
					<tbody>
						<?php if(!empty($user_with_assigned_open_tickets)): 
								foreach($user_with_assigned_open_tickets as $current_data): ?>
							<tr>
								<td><?php echo $current_data['user_name']; ?></td>
								<td><?php echo $current_data['total']; ?></td>
								<td><?php echo $current_data['yesterday_total_actions']; ?></td>
							</tr>
						<?php endforeach; endif; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<h2>Ticket Efficiency</h2>

<table class="table datatabled" id="new-open-tickets-count-table">
	<thead>
		<th>Date</th>
		<th>New Tickets</th>
		<th>Closed Tickets</th>
		<th>Open Tickets</th>
		<th>Efficiency</th>
	</thead>
	<tbody>
		<?php foreach($new_tickets_count_by_date as $date => $new_tickets_count) : ?>
		<tr>
			<td><?php echo $date; ?></td>
			<td><?php echo $new_tickets_count; ?></td>
			<td><?php echo isset($closed_tickets_count_by_date[$date]) ? $closed_tickets_count_by_date[$date] : ''; ?></td>
			<td><?php echo isset($open_tickets_count_by_date[$date]) ? $open_tickets_count_by_date[$date] : ''; ?></td>
			<td><?php echo isset($ticket_efficiency[$date]) ? number_format($ticket_efficiency[$date],2) . '%' : ''; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<h2>Open Tickets by Type</h2>

<table class="table datatabled" id="open-tickets-by-type-table" style="width:3000px;">
	<thead>
		<th>Date</th>
		<?php foreach($ticket_types as $ticket_type_name) : ?>
		
		<th><?php echo $ticket_type_name; ?></th>
		
		<?php endforeach; ?>
	</thead>
	<tbody>
		<?php foreach($new_tickets_count_by_date as $date => $new_tickets_count) : ?>
		<tr>
			<td><?php echo $date; ?></td>
			
			<?php foreach($ticket_types as $ticket_type_name) : ?>
			
			<td><?php echo isset($open_tickets_count_by_type_and_date[$ticket_type_name][$date]) ? $open_tickets_count_by_type_and_date[$ticket_type_name][$date] : ''; ?></td>
			
			<?php endforeach; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<h2>Average Closed Tickets Age by Type</h2>

<table class="table datatabled" id="average-tickets-age-by-type-table" style="width:3000px;">
	<thead>
		<th>Date</th>
		<?php foreach(array_merge(array('All'), $ticket_types) as $ticket_type_name) : ?>
		
		<th><?php echo $ticket_type_name; ?></th>
		
		<?php endforeach; ?>
	</thead>
	<tbody>
		<?php foreach($new_tickets_count_by_date as $date => $new_tickets_count) : ?>
		<tr>
			<td><?php echo $date; ?></td>
			
			<?php foreach(array_merge(array('All'), $ticket_types) as $ticket_type_name) : ?>
			
			<td><?php echo isset($average_tickets_age_by_type_and_date[$ticket_type_name][$date]) ? number_format($average_tickets_age_by_type_and_date[$ticket_type_name][$date],2) : ''; ?></td>
			
			<?php endforeach; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php if(!empty($ticket_statuses) && empty($customer)): ?>

<h2>Open Tickets by Status</h2>

<table class="table datatabled" id="open-tickets-by-status-table" style="width:3000px;">
	<thead>
		<th>Date</th>
		<?php foreach($ticket_statuses as $ticket_status) : ?>
		
		<th><?php echo ucwords($ticket_status); ?></th>
		
		<?php endforeach; ?>
	</thead>
	<tbody>
		<?php foreach($new_tickets_count_by_date as $date => $new_tickets_count) : ?>
		<tr>
			<td><?php echo $date; ?></td>
			
			<?php foreach($ticket_statuses as $ticket_status) : ?>
			
			<td><?php echo isset($open_tickets_count_by_status_and_date[$ticket_status][$date]) ? $open_tickets_count_by_status_and_date[$ticket_status][$date] : ''; ?></td>
			
			<?php endforeach; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php endif; ?>

<h2>Average Initial Response Time</h2>

<table class="table datatabled" id="average-initial-response-time-table">
	<thead>
		<th>Date</th>
		<th>Average Initial Response Time (Mins)</th>
	</thead>
	<tbody>
		<?php foreach($average_initial_response_time_by_date as $date => $average_initial_response_time) : ?>
		<tr>
			<td><?php echo $date; ?></td>
			<td><?php echo !empty($average_initial_response_time) ? number_format($average_initial_response_time, 2) : ''; ?></td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php endif; ?>