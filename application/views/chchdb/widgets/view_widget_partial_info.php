<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>

<style type="css">
	.margin-top-zero {
		margin-top: 0 !important;
	}
</style>

<div class="widget-partial-info <?php echo $col_layout; ?>">
	<div class="panel panel-info">
		<?php if(isset($widget_title)) : ?>
			<div class="panel-heading">
				<div class="panel-title widget-panel-title">
					<?php echo $widget_title; ?>
					<span class="pull-right">
						<?php
							if($info_type == 'info' && $user_can_edit) {
								echo '
									<span class="btn btn-default trigger-manipulate-data" role="button" title="'.ucfirst(sprintf(lang('word__set_x'), strtolower($widget_title))).'" entity="'.$data_entity.'" data_id="'.$data_id.'" parent_entity="'.$entity_name.'" parent_data_id="'.$data_id.'" action_mode="edit" form_source="widget_partial_info" widget_id="'.$widget_id.'">
										<span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
									</span>
								';
							}
							else if($info_type == 'list' && $user_can_add) {
								echo '
									<span class="btn btn-default trigger-manipulate-data" role="button" title="'.ucfirst(sprintf(lang('word__set_x'), strtolower($widget_title))).'" entity="'.$data_entity.'" parent_entity="'.$entity_name.'" parent_data_id="'.$parent_data_id.'" action_mode="add" form_source="widget_partial_info" widget_id="'.$widget_id.'">
										<span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
									</span>
								';
							}
						?>
						<?php if($info_type == 'info' && $user_can_edit) : ?>
							
						<?php endif; ?>
					</span>
				</div>
			</div>
		<?php endif; ?>
		
		<?php
			if($info_type == 'info') {
				echo '<div class="panel-body">';

				foreach($fields_alias as $field_alias) {
					echo '
						<div class="field-info-area">
							<div class="field-label">'.$info['rendered'][$field_alias]['label'].'</div>
							<div class="field-value">'.(strlen($info['rendered'][$field_alias]['value']) > 0 ? $info['rendered'][$field_alias]['value'] : '-').'</div>
						</div>
					';
				}
				
				echo '</div>';
			}
			else if($info_type == 'list') {
				echo '
					<table class="table table-bordered">
						<thead><tr>';
				
				foreach($list['header'] as $header_label) {
					echo '<th>'.$header_label.'</th>';
				}
				
				echo '
					</tr></thead>
					<tbody>
				';
				
				foreach($list['content'] as $row) {
					echo '<tr>';
					foreach($displayed_fields as $field_name) {
						echo '<td>'.$row[str_replace('.', '__', $field_name)].'</td>';
					}
					echo '</tr>';
				}
				
				echo '</tbody></table>';
			}
		?>
	</div>
</div>

