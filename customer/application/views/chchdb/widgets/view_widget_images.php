<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<style type="text/css">
	.image-area img {
		max-width: 100%;
		cursor: pointer;
	}
	
	.image-area {
		margin-bottom: 20px;
	}
	
	.detail-image-area img {
		max-width: 100%;
	}
	
	.modal-menu-item {
		cursor: pointer;
	}
</style>

<div class="col-md-6">
	<div class="panel panel-info">
		<div class="panel-heading">
			<div class="panel-title widget-panel-title">
				<?php echo $widget_title; ?>
				<span class="pull-right">
					<span class="btn btn-default" id="trigger-add-image" role="button" title="<?php echo ucwords(lang('title__add_image')); ?>" data-toggle="modal" data-target="#modal-add-image" data-entity_name="<?php echo $entity_name; ?>" data-data_id="<?php echo $data_id; ?>">
						<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
					</span>
				</span>
			</div>
		</div>
		<div class="panel-body">
			<div class="images-area">
				<?php if(empty($images)) : ?>
					<div id="no-image-message"><?php echo lang('message__no_image'); ?></div>
				<?php else : 
						foreach($images as $image) : ?>
						
						<div class="image-area" id="image-area-<?php echo $image['id']; ?>"><img src="<?php echo base_url('assets/data/'.PROJECT_CODE.'/'.$image['file_path']); ?>" class="image" image_id="<?php echo $image['id']; ?>"></div>
					
				<?php endforeach; 
					endif; ?>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal-add-image" tabindex="-1" role="dialog" aria-labelledby="modal-add-edit-image-label" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header" id="modal-add-edit-image-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo ucwords(lang('word__close')); ?>"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal-add-edit-image-label"><?php echo ucwords(lang('title__add_image')); ?></h4>
			</div>
			<div class="modal-body" id="modal-add-edit-image-body">
				<form id="form-add-image" action="<?php echo base_url('api/add_image'); ?>" method="post" enctype="multipart/form-data">
				<input type="hidden" id="add-image-field-entity_name" name="entity_name" value="">
				<input type="hidden" id="add-image-field-data_id" name="data_id" value="">
				<div class="form-group">
					<input type="file" id="add-image-field-image" name="image" class="form-control">
				</div>
			</div>
			<div class="modal-footer" id="modal-add-edit-image-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo ucwords(lang('word__cancel')); ?></button>
				<button type="submit" class="btn btn-primary add-image-action-button" id="modal-add-image-action-button" entity="" data_id="" action_mode="" form_source="modal"><?php echo ucwords(lang('word__save')); ?></button>
				</form>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal-view-image" tabindex="-1" role="dialog" aria-labelledby="modal-view-image-label" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header" id="modal-view-image-header">
				<div class="btn-group">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Options">
						<span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
					</button>
					<ul class="dropdown-menu">
						<li>
							<a class="modal-menu-item" id="trigger-delete-image" delete_image_id=""><span class="glyphicon glyphicon-trash" aria-hidden="true"></span>&nbsp;&nbsp;&nbsp;<?php echo ucwords(lang('title__delete_image')); ?></a>
						</li>
					</ul>
				</div>
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo ucwords(lang('word__close')); ?>"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body" id="modal-view-image-body">
				<div class="detail-image-area">
					<img id="detail-image" src="">
				</div>
			</div>
		</div>
	</div>
</div>

