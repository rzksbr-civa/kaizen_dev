<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="container">
	<div class="col-md-6">
		<div class="alert alert-success" role="alert">
			<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
			&nbsp;&nbsp;Employees data have been updated.
		</div>

		<?php 
			foreach($updated_employees as $employee) {
				echo $employee['status'] . ': ' . $employee['employee_name'] . '<br>';
			}
		?>
	</div>
</div>