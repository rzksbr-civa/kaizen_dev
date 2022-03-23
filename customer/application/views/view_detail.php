<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<div class="page-header" id="view-detail-header" entity_name="<?php echo $entity_name; ?>" data_id="<?php echo $id; ?>">
	<h1>
		<div class="view-detail-info-status-area">
			<a href="<?php echo base_url('db/view/' . $entity_name); ?>" class="label label-info label-view-detail-info" style="background-color:white; color:#333; border:1px solid lightgrey;"><?php echo strtoupper($entity_data['label_singular']); ?> </a>&nbsp;
			
			<?php foreach($info_status as $status_field => $current_info_status) : ?>
				<span class="label label-<?php echo $current_info_status['color']; ?> label-view-detail-info" id="label-status-<?php echo $status_field; ?>" data-toggle="tooltip" data-placement="top" title="" data-original-title="<?php echo $current_info_status['type']; ?>"><?php echo strtoupper($current_info_status['label']); ?></span>
			<?php endforeach; ?>
		</div>
		
		<?php echo $page_header_title; ?>
		
		<span class="pull-right">
			<?php
				$primary_action_buttons = '';
				$other_action_buttons = '';

				foreach($action_options as $action_option) {
					if($action_option['type'] == 'primary') {
						$primary_action_buttons .= get_rendered_action_option($entity_name, $id, $action_option);
					}
					else if($action_option['type'] == 'other') {
						$other_action_buttons .= get_rendered_action_option($entity_name, $id, $action_option);
					}
				}
				
				echo $primary_action_buttons;
				
				if($other_action_buttons <> '') {
					echo '
						<div class="btn-group">
							<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="'.ucwords(lang('word__more_options')).'">
								<span class="glyphicon glyphicon-menu-down" aria-hidden="true"></span>
							</button>
							<ul class="dropdown-menu dropdown-menu-right">'.$other_action_buttons.'</ul></div>';
				}
			
			?>
		</span>
	</h1>
</div>

<div role="tabpanel">
	<ul class="nav nav-tabs" role="tablist">
		<?php
			if($tab == '') $tab = 'info';
			// Show tab by order
			foreach($tabs_by_order as $tab_header) {
				echo '
					<li role="presentation" '.(($tab === $tab_header['tab_name']) ? 'class="active"' : '').'>
						<a href="#'.$tab_header['tab_name'].'" aria-controls="'.$tab_header['tab_name'].'" role="tab" data-toggle="tab">' . ucwords($tab_header['tab_title']) . '</a>
					</li>';
			}
		?>
	</ul>

	<div class="tab-content">
		<?php
			if($tab === '' || $tab === 'info') :
		?>
			<div role="tabpanel" class="tab-pane fade <?php echo ($tab == '' || $tab == 'info') ? 'in active' : ''; ?>" id="info">
				<div class="row">
					<div class="col-md-6">
						<h2><?php echo ucwords(sprintf(lang('title__x_info'),$entity_data['label_singular'])); ?></h2>
					
						<table class="table table-striped table-responsive table-bordered table-info">
							<?php
								foreach($info_data as $info) {
									echo '<tr>';
									echo '<td class="info-label">'.$info['label'].'</td>';
									echo '<td width="info-content">'.$info['value'].'</td>';
									echo '</tr>';
								}
							?>
						</table>
					</div>
					
					<?php
						if(!empty($info_tab_widget)) {
							echo '<div class="col-md-6" style="margin-top:30px;">'.$info_tab_widget.'</div>';
						}
					?>
				</div>
			</div>
		<?php endif; ?>
		
		<?php
			// Related entities
			foreach($related_entities_data as $related_entity) {
				if($tab === $related_entity['tab_name']) {
					echo '<div role="tabpanel" class="tab-pane fade ' . ($tab == $related_entity['tab_name'] ? 'in active' : '') . '" id="'.$related_entity['tab_name'].'">';

					$related_entity_data = get_entity($related_entity['entity_name']);

					if($related_entity['user_can_add']) {
						$add_item_button = '
							<span class="pull-right">
								<span class="btn btn-primary trigger-manipulate-data" role="button" entity="'.$related_entity['entity_name'].'" parent_entity="'.$entity_name.'" data_id="0" parent_data_id="'.$id.'" action_mode="add" form_source="modal" title="'.addslashes(ucfirst(sprintf(lang('word__add_new_x'),$related_entity['label_singular']))).'">
								<span class="glyphicon glyphicon-plus" aria-hidden="true"></span> '.ucwords($related_entity['label_singular']).'</span>
						</span>';
					}
					else {
						$add_item_button = '';
					}
					
					echo '<h2>'.ucwords($related_entity['tab_title']).$add_item_button.'</h2>';
					
					echo isset($rendered_widgets_above_related_entity_table) ? '<div class="row">'.$rendered_widgets_above_related_entity_table.'</div>' : '';
					
					echo '<div class="row">' . $related_entity['datatable'] . '</div>';
					
					echo isset($rendered_widgets_below_related_entity_table) ? '<div class="row">'.$rendered_widgets_below_related_entity_table.'</div>' : '';
					
					echo '</div>';
				}
			}
		?>
		
		<?php
			if(!empty($entity_data['tabs_widgets'])) {
				foreach($entity_data['tabs_widgets'] as $custom_tab) {
					if($tab === $custom_tab['tab_name'] && $custom_tab['tab_name'] !== 'info' && !in_array($custom_tab['tab_name'], $related_entity_tab_name_list)) {
						echo '<div role="tabpanel" class="tab-pane fade ' . ($tab == $custom_tab['tab_name'] ? 'in active' : '') . '" id="'.$custom_tab['tab_name'].'">'.$custom_tab_content[$custom_tab['tab_name']].'</div>';
					}
				}
			}
		?>
	</div>
</div>