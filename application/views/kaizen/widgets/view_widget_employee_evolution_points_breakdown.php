<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<style type="text/css">
	.lifetime-evolution-points-row {
		background-color: green;
		font-weight: bold;
		font-size: 16px;
	}
</style>

<div class="col-lg-6">
	<div class="panel panel-primary">
		<div class="panel-heading">Evolution Points</div>
		<table class="table table-bordered employee-evolution-points-breakdown-table">
			<thead>
				<th>Assignment/ Description</th>
				<th>Evolution Points</th>
			</thead>
			<tbody>
				<?php if(!empty($employee_evolution_points_breakdown_data['positive_evolution_points'])) :
					foreach($employee_evolution_points_breakdown_data['positive_evolution_points'] as $row_data) : ?>
					
					<tr>
						<td><?php echo !empty($row_data['assignment_type_name']) ? $row_data['assignment_type_name'] : 
						'(No Assignment)'; ?></td>
						<td><?php echo $row_data['evolution_points']; ?></td>
					</tr>
				
				<?php endforeach; endif; ?>
				<tr>
					<td>Devolution Points</td>
					<td><?php echo $employee_evolution_points_breakdown_data['negative_evolution_points']; ?></td>
				</tr>
				<tr class="lifetime-evolution-points-row">
					<td>Lifetime Evolution Points</td>
					<td><?php echo $employee_evolution_points_breakdown_data['lifetime_evolution_points']; ?></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>