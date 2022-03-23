<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<div class="row">
	<div class="col-md-4">
		<div class="page-header">
		  <h1>
			<?php echo ucwords(lang('title__change_password')); ?>
		  </h1>
		</div>
		<div class="alert alert-success hidden" id="feedback_change_password" role="alert"></div>
		<form>
			<div class="form-group form_group_change_password" id="form_group_current_password">
				<label class="control-label" for="input_current_password">
					<?php echo ucwords(lang('label__current_password')); ?>
					<?php echo get_rendered_required_field_sign(); ?>
				</label>
				<div class="input-group">
					<input type="password" class="form-control form_control_change_password input_required" id="input_current_password" name="current_password">
					<span class="input-group-addon show_hide_password" field="current_password" state="open"><span class="glyphicon glyphicon-eye-open" id="show_hide_current_password" aria-hidden="true"></span></span>
				</div>
				<span id="help_block_current_password" class="help-block help_block_change_password">
			</div>
			<div class="form-group form_group_change_password" id="form_group_new_password">
				<label class="control-label" for="input_new_password">
					<?php echo ucwords(lang('label__new_password')); ?>
					<?php echo get_rendered_tooltip('message__password_must_be_at_least_8_characters'); ?>
					<?php echo get_rendered_required_field_sign(); ?>
				</label>
				<div class="input-group">
					<input type="password" class="form-control form_control_change_password input_required" id="input_new_password" name="new_password">
					<span class="input-group-addon show_hide_password" field="new_password" state="open"><span class="glyphicon glyphicon-eye-open" id="show_hide_new_password" aria-hidden="true"></span></span>
				</div>
				<span id="help_block_new_password" class="help-block help_block_change_password">
			</div>
			<div class="form-group form_group_change_password" id="form_group_confirm_new_password">
				<label class="control-label" for="input_confirm_new_password">
					<?php echo ucwords(lang('label__confirm_new_password')); ?>
					<?php echo get_rendered_tooltip('message__input_your_new_password_again'); ?>
					<?php echo get_rendered_required_field_sign(); ?>
				</label>
				<div class="input-group">
					<input type="password" class="form-control form_control_change_password input_required" id="input_confirm_new_password" name="confirm_new_password">
					<span class="input-group-addon show_hide_password" field="confirm_new_password" state="open"><span class="glyphicon glyphicon-eye-open" id="show_hide_confirm_new_password" aria-hidden="true"></span></span>
				</div>
				<span id="help_block_confirm_new_password" class="help-block help_block_change_password">
			</div>
			<button type="button" class="btn btn-primary" id="btn-do-change-password"><?php echo ucwords(lang('title__change_password')); ?></button>
		</form>
	</div>
</div>