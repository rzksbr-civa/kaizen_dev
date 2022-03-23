<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DB extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
    }
	
	public function index() {
		$this->_show_404_page();
	}
	
	// View List if id = 0,
	// View Detail if id > 0
	public function view($entity_name = '', $id = 0, $tab = '') {
		if(!entity_exists($entity_name)) {
			$this->_show_404_page();
			return;
		}
		
		// View List
		if($id === 0) {
			if(!$this->model_db_crud->user_can('view', $entity_name)) {
				$this->_show_access_denied_page();
				return;
			}
			
			$header_data = array();
			$body_data = array();
			$footer_data = array();
			$js_data = array();
			
			// Set page title
			$header_data['page_title'] = generate_page_title(ucwords(sprintf(lang('title__x_list'), get_entity_label($entity_name))));

			$body_data['entity_name'] = $entity_name;
			
			$entity_data = get_entity($entity_name);
			$body_data['entity_data'] = $entity_data;
			
			$data_filters = isset($entity_data['data_filters']) ? $entity_data['data_filters'] : array();
			
			$body_data['page_header_title'] = ucwords(sprintf(lang('title__x_list'), get_entity_label($entity_name)));
			$body_data['user_can_add'] = $this->model_db_crud->user_can('add', $entity_name) && $this->model_db_crud->user_can('direct_add', $entity_name);
			
			$datatable_config_args = array('data_filters' => $data_filters);
			if(!empty($entity_data['default_filter']) && isset($entity_data['filtered_views'][$entity_data['default_filter']]['criteria'])) {
				$datatable_filters = $entity_data['filtered_views'][$entity_data['default_filter']]['criteria'];
				$datatable_config_args['datatable_filters'] = $datatable_filters;
			}
			
			$datatable_config = get_datatable_config('body', $entity_name, $datatable_config_args);
			$body_data['datatable'] = $this->load->view('view_datatable', $datatable_config, true);
			
			$js_datatable_config = get_datatable_config('js', $entity_name, $datatable_config_args);
			
			// the "TRUE" argument tells it to return the content, rather than display it immediately
			// This is to ensure JS code runs after jquery and other basic js code is loaded in the footer
			$footer_data['js'] = $this->load->view('js_view_list', $js_data, true);
			$footer_data['js'] .= $this->load->view('js_view_datatable', $js_datatable_config, true);
			$footer_data['js'] .= $this->load->view('js_view_modal_add_edit', array(), true);
			$footer_data['js'] .= $this->load->view('js_add_edit', array(), true);
			$footer_data['js'] .= $this->load->view('js_action_option', array(), true);
			
			$this->load->view('view_header', $header_data);
			$this->load->view('view_list', $body_data);
			$this->load->view('view_modal_add_edit');
			$this->load->view('view_footer', $footer_data);
		}
		
		// View detail
		else {
			if(!is_numeric($id)) {
				$this->_show_404_page();
				return;
			}
			
			if(!$this->model_db_crud->user_can('view', $entity_name, $id)) {
				$this->_show_access_denied_page();
				return;
			}
			
			$header_data = array();
			$body_data = array();
			$footer_data = array();
			$js_data = array();
			$js_add_edit_data = array('data_id'=>$id);
			
			$entity_data = get_entity($entity_name);
			$tab = ($tab == '') ? 'info' : $tab;
			
			// Redirect to the custom page if redirect_base_url is set
			if(!empty($entity_data['redirect_base_url'])) {
				redirect(PROJECT_CODE . '/' . $entity_data['redirect_base_url'] . '/' . $id);
				return;
			}
			
			$info_data = $this->model_db_crud->get_view_detail_info_data($entity_name, $id);
			
			// If data is not found (e.g. has been deleted)...
			if(empty($info_data)) {
				$this->_show_404_page();
				return;
			}

			$item_name = $info_data['rendered'][$entity_data['table_name'] . '__' . $entity_data['name_field']]['value'];
			
			$header_data['page_title'] = generate_page_title($item_name . ' - ' . ucwords($entity_data['label_singular']));
			
			$body_data['page_header_title'] = $item_name;
			$body_data['entity_name'] = $entity_name;
			$body_data['id'] = $id;
			$body_data['tab'] = $tab;
			
			$body_data['entity_data'] = $entity_data;
			$body_data['show_add_new_button'] = config_item('show_add_new_button_in_view_detail');
			$body_data['info_data'] = $info_data['rendered'];
			
			if(isset($entity_data['status_field'])) {
				if(is_array($entity_data['status_field'])) {
					foreach($entity_data['status_field'] as $status_field) {
						$body_data['info_status'][$status_field] = array(
							'label' => $info_data['rendered'][$entity_data['table_name'] . '__' . $status_field]['value'],
							'color' => get_status_color($entity_data['fields'][$status_field]['options_type'], $info_data['original'][$status_field]),
							'type' => $entity_data['fields'][$status_field]['field_label']
						);
					}
				}
				else {
					$body_data['info_status'][] = array(
						'label' => $info_data['rendered'][$entity_data['table_name'] . '__' . $entity_data['status_field']]['value'],
						'color' => get_status_color($entity_data['fields'][$entity_data['status_field']]['options_type'], $info_data['original'][$entity_data['status_field']]),
						'type' => $entity_data['fields'][$entity_data['status_field']]['field_label']
					);
				}
			}
			else {
				$body_data['info_status'] = array();
			}
						
			$body_data['user_can_add'] = $this->model_db_crud->user_can('add', $entity_name) ? true : false;
			$body_data['user_can_edit'] = $this->model_db_crud->user_can('edit', $entity_name, $id) ? true : false;
			$body_data['user_can_delete'] = $this->model_db_crud->user_can('delete', $entity_name, $id) ? true : false;
			
			$body_data['action_options'] = $this->model_db_crud->get_action_options($entity_name, $id);
			
			// Related entity
			$related_entities_data = array();
			
			$tab_found = false;
			if($tab == '' || $tab == 'info') $tab_found = true;
			
			$tabs_by_order = array( array('tab_name' => 'info', 'tab_title' => 'Info') );
			
			$related_entity_tab_name_list = array();
			if(!empty($entity_data['related_entities'])) {
				foreach($entity_data['related_entities'] as $related_entity_info) {
					$this_related_entity_data = array();
					$related_entity_name = $related_entity_info['entity_name'];
					$related_entity_related_field = $related_entity_info['related_field'];

					$related_entity_tab_name = isset($related_entity_info['tab_name']) ? $related_entity_info['tab_name'] : $related_entity_name;
					$related_entity_tab_name_list[] = $related_entity_tab_name;
					
					if(!$this->model_db_crud->user_can('view', $related_entity_name)) {
						continue;
					}
			
					$related_entity = get_entity($related_entity_name);
					$this_related_entity_data['entity_name'] = $related_entity_name;
					$this_related_entity_data['tab_name'] = $related_entity_name;
					$this_related_entity_data['tab_title'] = isset($related_entity_info['tab_title']) ? $related_entity_info['tab_title'] : $related_entity['label_plural'];
					$this_related_entity_data['label_singular'] = $related_entity['label_singular'];
					$this_related_entity_data['data_filters'] = isset($related_entity['data_filters']) ? $related_entity['data_filters'] : array();

					$tabs_by_order[] = array('tab_name' => $related_entity_name, 'tab_title' => $this_related_entity_data['tab_title']);
					
					$this_related_entity_data['user_can_add'] = $this->model_db_crud->user_can('add', $related_entity_name, 0, array('parent_entity_name' => $entity_name, 'parent_data_id' => $id)) ? true : false;
					
					if(isset($related_entity_info['hide_add_button'])) {
						$this_related_entity_data['user_can_add'] = false;
					}
					
					if($tab == $this_related_entity_data['tab_name']) $tab_found = true;

					$datatable_config = get_datatable_config('body', $related_entity_name, array('data_filters' => $this_related_entity_data['data_filters']));

					$this_related_entity_data['datatable'] = $this->load->view('view_datatable', $datatable_config, true);
					
					if($related_entity_name === $tab) {
						$parent_entity = array(
							'entity_name' => $entity_name,
							'related_field' => $related_entity_related_field,
							'parent_id' => $id
						);
						
						$datatable_config_args = array(
							'data_filters' => $this_related_entity_data['data_filters'], 
							'parent_entity' => $parent_entity
						);

						$datatable_config_args['related_entity_info'] = $related_entity_info;
						
						if(!empty($related_entity['default_filter']) && isset($related_entity['filtered_views'][$related_entity['default_filter']]['criteria'])) {
							$datatable_config_args['datatable_filters'] = $related_entity['filtered_views'][$related_entity['default_filter']]['criteria'];
						}
						
						$js_datatable_config = get_datatable_config('js', $related_entity_name, $datatable_config_args);
						$js_datatable_config['parent_entity_name'] = $parent_entity['entity_name'];
						$js_datatable_config['tab_name'] = $related_entity_tab_name;
						$js_datatable_config['entity_name'] = $related_entity_name;
						$js_datatable_config['data_id'] = $id;
						
						if(!empty($related_entity_info['add_edit_callback'])) {
							if(is_array($related_entity_info['add_edit_callback'])) {
								$js_add_edit_data['add_edit_callback'] = $related_entity_info['add_edit_callback'];
							}
							else {
								$js_add_edit_data['add_edit_callback'] = array($related_entity_info['add_edit_callback']);
							}
						}
					}
					
					$related_entities_data[] = $this_related_entity_data;
				}
			}
			$body_data['related_entities_data'] = $related_entities_data;
			$body_data['related_entity_tab_name_list'] = $related_entity_tab_name_list;

			// Widgets
			if(!empty($entity_data['tabs_widgets'])) {
				foreach($entity_data['tabs_widgets'] as $tab_widgets) {
					if(isset($tab_widgets['show_before_tab'])) {
						$insert_tab_index = array_search($tab_widgets['show_before_tab'], array_column($tabs_by_order, 'tab_name'));
					}
					else {
						$insert_tab_index = count($tabs_by_order);
					}
					
					if($tab_widgets['tab_name'] <> 'info' && isset($tab_widgets['tab_title'])) {	
						$tabs_by_order = array_merge(
							array_slice($tabs_by_order, 0, $insert_tab_index, true),
							array(array('tab_name' => $tab_widgets['tab_name'], 'tab_title' => $tab_widgets['tab_title'])),
							array_slice($tabs_by_order, $insert_tab_index, count($tabs_by_order)-$insert_tab_index, true));
					}
					
					// Skip if we don't currently open this tab
					if($tab <> $tab_widgets['tab_name']) {
						continue;
					}
					
					// Render widgets only if this tab is loaded...
					$rendered_widgets = '';
					$rendered_widgets_above_related_entity_table = '';
					$rendered_widgets_below_related_entity_table = '';
					$rendered_widgets_js = '';
					$js_datatable_config['after_table_widgets_exists'] = false;
					$js_view_load_queue = array();
					$common_view_load_queue = array();
					foreach($tab_widgets['widgets'] as $widget) {
						$widget_type = isset($widget['widget_type']) ? $widget['widget_type'] : 'core';
						$widget_name = $widget['widget_name'];
						
						$project_code = ($widget_type === 'core') ? 'chchdb' : PROJECT_CODE;
						$this->load->library($project_code.'/widgets/'.$widget_name);
						
						$widget_specs = array(
							'entity_name' => $entity_name,
							'data_id' => $id
						);
						
						if(isset($widget['widget_specs'])) {
							$widget_specs = array_merge($widget_specs, $widget['widget_specs']);
						}
						
						$this->$widget_name->set_widget_specs($widget_specs);
			
						if(in_array($tab_widgets['tab_name'], $related_entity_tab_name_list)) {
							if(isset($widget['widget_position']) && $widget['widget_position'] == 'before') {
								$rendered_widgets_above_related_entity_table .= $this->$widget_name->get_rendered_widget();
							}
							else if(isset($widget['widget_position']) && $widget['widget_position'] == 'after_table') {
								$js_datatable_config['after_table_widgets_exists'] = true;
							}
							else {
								$rendered_widgets_below_related_entity_table .= $this->$widget_name->get_rendered_widget();
							}
						}
						else {
							$rendered_widgets .= $this->$widget_name->get_rendered_widget();
						}
						
						$rendered_widgets_js .= $this->$widget_name->get_rendered_widget_js();
					}
					
					$tab_name = $tab_widgets['tab_name'];
					
					if($tab == $tab_name) $tab_found = true;
					
					if($tab_name === 'info') {
						$body_data['info_tab_widget'] = $rendered_widgets;
					}
					else if(!in_array($tab_name, $related_entity_tab_name_list)) {
						$tab_title = $tab_widgets['tab_title'];
						$body_data['custom_tab_content'][$tab_name] = $rendered_widgets;
					}
					
					$body_data['rendered_widgets_above_related_entity_table'] = $rendered_widgets_above_related_entity_table;
										
					$body_data['rendered_widgets_below_related_entity_table'] = $rendered_widgets_below_related_entity_table;
				}
			}
			
			if(!$tab_found) {
				$this->_show_404_page();
				return;
			}
			
			$body_data['tabs_by_order'] = $tabs_by_order;

			// Page URL without the tab
			$js_data['page_url'] = base_url('db/view/'.$entity_name.'/'.$id);
			
			$footer_data['js'] = $this->load->view('js_view_detail', $js_data, true);
			
			if(isset($js_datatable_config)) {
				$footer_data['js'] .= $this->load->view('js_view_datatable', $js_datatable_config, true);
			}
			
			$footer_data['js'] .= $this->load->view('js_view_modal_add_edit', array(), true);
			$footer_data['js'] .= $this->load->view('js_add_edit', $js_add_edit_data, true);
			$footer_data['js'] .= $this->load->view('js_action_option', array(), true);
			
			if(file_exists(APPPATH.'views/'.PROJECT_CODE.'/js_add_edit_callback.php')) {
				$footer_data['js'] .= $this->load->view(PROJECT_CODE.'/js_add_edit_callback', $js_add_edit_data, true);
			}
			
			if(isset($rendered_widgets_js)) {
				$footer_data['js'] .= $rendered_widgets_js;
			}
			
			$this->load->view('view_header', $header_data);
			$this->load->view('view_detail', $body_data);
			$this->load->view('view_modal_add_edit');
			$this->load->view('view_custom_modal');
			$this->load->view('view_footer', $footer_data);
		}
	}
	
	public function add($entity_name = '') {
		if(!entity_exists($entity_name)) {
			$this->_show_404_page();
			return;
		}
		
		if(!$this->model_db_crud->user_can('add', $entity_name) || !$this->model_db_crud->user_can('direct_add', $entity_name)) {
			$this->_show_access_denied_page();
			return;
		}
		
		$header_data = array();
		$body_data = array();
		$footer_data = array();
		
		$header_data['page_title'] = generate_page_title(ucwords(sprintf(lang('title__new_x'), get_entity_label($entity_name))));
		
		$body_data['entity_name'] = $entity_name;
		
		$entity_data = get_entity($entity_name);
		$body_data['entity_data'] = $entity_data;
		
		// Check if this entity is set to 'view_only'. If yes, show 404 page.
		if(!empty($entity_data['view_only'])) {
			$this->_show_404_page();
			return;
		}
		
		$body_data['page_header_title'] = ucwords(sprintf(lang('title__new_x'), get_entity_label($entity_name)));

		$this->load->library('chchdb/chchdb_form');
		$this->chchdb_form->set('entity_name', $entity_name);
		$this->chchdb_form->set('action_mode', 'add');
		$this->chchdb_form->set('form_source', 'page');
		$body_data['form'] = $this->chchdb_form->get_rendered_form();

		$footer_data['js'] = $this->load->view('js_add_edit', array(), true);
		
		$this->load->view('view_header', $header_data);
		$this->load->view('view_add_edit', $body_data);
		$this->load->view('view_footer', $footer_data);
	}
	
	public function edit($entity_name = '', $id = 0) {
		if(!entity_exists($entity_name)) {
			$this->_show_404_page();
			return;
		}
		
		if(!$this->model_db_crud->user_can('edit', $entity_name, $id)) {
			$this->_show_access_denied_page();
			return;
		}
		
		$current_data = $this->model_db_crud->get_specific_data($entity_name, $id);
		if($current_data == null) {
			// Data not found
			$this->_show_404_page();
			return;
		}
		
		$header_data = array();
		$body_data = array();
		$footer_data = array();
		
		$header_data['page_title'] = generate_page_title(ucwords(sprintf(lang('title__edit_x'), get_entity_label($entity_name))));
		
		$body_data['entity_name'] = $entity_name;
		
		$entity_data = get_entity($entity_name);
		$body_data['entity_data'] = $entity_data;
		
		// Check if this entity is set to 'view_only'. If yes, show 404 page.
		if(!empty($entity_data['view_only'])) {
			$this->_show_404_page();
			return;
		}
		
		$body_data['page_header_title'] = ucwords(sprintf(lang('title__edit_x'), get_entity_label($entity_name)));

		$this->load->library('chchdb/chchdb_form');
		$this->chchdb_form->set('entity_name', $entity_name);
		$this->chchdb_form->set('action_mode', 'edit');
		$this->chchdb_form->set('data_id', $id);
		$this->chchdb_form->set('data', $current_data);
		$this->chchdb_form->set('form_source', 'page');
		$body_data['form'] = $this->chchdb_form->get_rendered_form();

		$footer_data['js'] = $this->load->view('js_add_edit', array(), true);
		
		$this->load->view('view_header', $header_data);
		$this->load->view('view_add_edit', $body_data);
		$this->load->view('view_footer', $footer_data);
	}
}
