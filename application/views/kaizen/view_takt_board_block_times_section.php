<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<?php if($generate) : ?>

<div class="row">
	<?php for($shift=1; $shift<=4; $shift++) : ?>
		<div class="col-md-3">
			<div class="panel panel-default">
				<div class="panel-heading">
					<div class="panel-title takt-board-title">Block <?php echo $shift; ?></div>
				</div>
				<table class="table takt-board-info-table">
					<?php foreach($employee_assignment_count_by_assignment_type as $assignment_type_name => $employee_count) : ?>
						<tr>
							<td class="info-table-label"><?php echo $assignment_type_name ?></td>
							<td class="info-table-value"><?php echo $employee_count[$shift]; ?></td>
						</tr>
					<?php endforeach; ?>
				</table>
			</div>
		</div>
	<?php endfor; ?>
</div>

<?php endif; ?>