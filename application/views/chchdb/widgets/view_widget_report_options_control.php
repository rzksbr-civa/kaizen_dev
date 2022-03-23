<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="panel panel-info" style="margin-top:40px">
	<div class="panel-heading">
		<div class="panel-title widget-panel-title"><?php echo lang('title__report_options'); ?></div>
	</div>
	<div class="panel-body">
		<form name="form_report_options" id="form_report_options">
		<?php
			if($show_report_type_options) :
		?>
				<div class="form-group">
					<label for="input_report_options_report_type" class="control-label"><?php echo lang('label__report_type'); ?></label>
					<select class="form-control selectized" id="input_report_options_report_type" name="report_type">
						<option value=""></option>
						<?php
							foreach($report_types as $report_type_name => $report_type_info) {
								$selected = ($report_type_name == $report_type) ? ' selected' : '';
								echo '<option value="'.$report_type_name.'" '.$selected.'>'.$report_type_info['title'].'</option>';
							}
						?>
					</select>
				</div>
		<?php
			endif;
		?>
		
			<div id="report-options-parameters-area">
				<?php
					echo $options_parameter['render'];
				?>
			</div>
		
			<button type="button" class="btn btn-primary btn-show-report">Show</button>
		</form>
	</div>
</div>