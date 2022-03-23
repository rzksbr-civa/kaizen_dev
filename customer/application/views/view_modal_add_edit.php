<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="modal fade" id="modal-add-edit" tabindex="-1" role="dialog" entity="" parent_entity="" data_id="" parent_data_id="" action_mode="" widget_id="" aria-labelledby="modal-add-edit-label" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header" id="modal-add-edit-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo ucwords(lang('word__close')); ?>"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal-add-edit-label"><?php echo ucwords(lang('word__edit')); ?></h4>
			</div>
			<div class="modal-body" id="modal-add-edit-body">

			</div>
			<div class="modal-footer" id="modal-add-edit-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo ucwords(lang('word__cancel')); ?></button>
				<button type="button" class="btn btn-primary add-edit-action-button" id="modal-add-edit-action-button" entity="" parent_entity="" data_id="" action_mode="" form_source=""><?php echo ucwords(lang('word__save')); ?></button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal-please-wait" tabindex="-1" role="dialog" aria-labelledby="modal-please-wait" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-body" id="modal-add-edit-body">
				<div style="text-align:center; font-size:32px; font-weight:bold;"><?php echo lang('message__please_wait'); ?></div>
			</div>
		</div>
	</div>
</div>