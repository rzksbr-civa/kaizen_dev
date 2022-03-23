<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<!-- Datatables CSS -->
<link href="<?php echo base_url('assets/datatables/1.10.18/datatables.min.css'); ?>" rel="stylesheet">

<style type="text/css">

</style>

<div class="page-header">
	<h3 style="text-align:center; color: #C52428;">
		<img src="<?php echo base_url('assets/data/kaizen/app/redstag-logo.png'); ?>" width="200"><br>
		LONG TERM INVENTORY COUNTS BOARD
	</h3>
</div>

<div class="panel panel-default">
	<div class="panel-body">
		<form id="form-long-term-inventory-counts-board-board-filter">
			<input type="hidden" class="form-control" id="input-generate" name="generate" value=1>
			<input type="hidden" class="form-control" id="input-page-version" name="page_version" value=<?php echo $page_version; ?>>
			<div class="row">
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-month">Month</label>
						<select class="form-control selectized" id="input-month" name="month">
							<?php
								foreach($month_list as $month_code => $month_name) {
									$selected = ($month == $month_code) ? ' selected' : '';
									echo '<option value="'.$month_code.'"'.$selected.'>'.$month_name.'</option>';
								}
							?>
						</select>
					</div>
				</div>
				
				<div class="col-md-2">
					<div class="form-group">
						<label for="input-milestone">Milestone</label>
						<select class="form-control selectized" id="input-milestone" name="milestone">
							<?php
								foreach($milestone_list as $milestone_code => $milestone_name) {
									$selected = ($milestone == $milestone_code) ? ' selected' : '';
									echo '<option value="'.$milestone_code.'"'.$selected.'>'.$milestone_name.'</option>';
								}
							?>
						</select>
					</div>
				</div>
			
				<div class="col-md-2">
					<div class="form-group">
						<label>&nbsp;</label>
						<button type="submit" class="form-control btn btn-primary">Download</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>