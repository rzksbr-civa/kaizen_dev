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
	<div class="col-md-12">
		<div class="panel panel-primary">
			<div class="panel-heading">
				<div class="panel-title inventory-board-title">Drops Count</div>
			</div>
			<div class="panel-body">
				<div class="drops-count-chart-wrapper">
					<div id="drops-count-chart"></div>
				</div>
			</div>
		</div>

		
		<h2>Data</h2>
		
		<div class="table-area">
			<table class="table table-bordered drops-count-board-table" id="drops-count-board-table">
				<thead>
					<th style="width:150px;">Date</th>
					<th>New Drops Count</th>
				</thead>
				<tbody>
					<?php foreach($drops_count_data as $date => $current_data): ?>
					<tr>
						<td><?php echo $current_data['label']; ?></td>
						<td><?php echo $current_data['drops_count']; ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<?php endif; ?>