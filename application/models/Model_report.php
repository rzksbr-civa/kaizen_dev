<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_report extends CI_Model {
	private $chart_default_options = array(
		'bar' => array(
			'chart' => array(
				'height' => 350,
				'type' => 'bar',
				'fontFamily' => 'Mukta',
				'toolbar' => array(
					'show' => true,
					'tools' => array(
						'download' => false
					)
				),
				'animations' => array(
					'enabled' => false
				)
			),
			'plotOptions' => array(
				'bar' => array(
					'dataLabels' => array(
						'position' => 'top'
					)
				)
			),
			'dataLabels' => array(
				'enabled' => true,
				'offsetY' => -30,
				'style' => array(
					'fontSize' => '12px',
					'colors' => array('#304758')
				)
			),
			'xaxis' => array(
				'position' => 'bottom',
				'labels' => array(
					'offsetY' => -5,
					'trim' => false,
					'maxHeight' => 500
				),
				'axisBorder' => array(
					'show' => false
				),
				'axisTicks' => array(
					'show' => true
				),
				'crosshairs' => array(
					'fill' => array(
						'type' => 'gradient',
						'gradient' => array(
							'colorFrom' => '#D8E3F0',
                            'colorTo' => '#BED1E6',
                            'stops' => array(0, 100),
                            'opacityFrom' => 0.4,
                            'opacityTo' => 0.5,
						)
					)
				),
				'tooltip' => array(
					'enabled' => false
				)
			),
			'fill' => array(
				'gradient' => array(
					'shade' => 'light',
                    'type' => 'horizontal',
                    'shadeIntensity' => 0.25,
                    'gradientToColors' => null,
                    'inverseColors' => true,
                    'opacityFrom' => 1,
                    'opacityTo' => 1,
                    'stops' => array(50, 0, 100, 100)
				)
			),
			'yaxis' => array(
				'axisBorder' => array(
					'show' => false
				),
				'axisTicks' => array(
					'show' => false
				),
				'labels' => array(
					'show' => true
				)
			)
		),
		
		'line' => array(
			'chart' => array(
				'height' => 350,
				'type' => 'line',
				'fontFamily' => 'Mukta',
				'toolbar' => array(
					'show' => true,
					'tools' => array(
						'selection' => true,
						'download' => false
					)
				),
				'animations' => array(
					'enabled' => false
				)
			),
			'plotOptions' => array(
				'bar' => array(
					'dataLabels' => array(
						'position' => 'top'
					)
				)
			),
			'dataLabels' => array(
				'enabled' => false,
				'offsetY' => -30,
				'style' => array(
					'fontSize' => '12px',
					'colors' => array('#304758')
				)
			),
			'stroke' => array(
				'curve' => 'straight'
			),
			'markers' => array(
				'size' => 4
			),
			'xaxis' => array(
				'position' => 'bottom',
				'labels' => array(
					'offsetY' => -5
				),
				'axisBorder' => array(
					'show' => false
				),
				'axisTicks' => array(
					'show' => true
				),
				'crosshairs' => array(
					'fill' => array(
						'type' => 'gradient',
						'gradient' => array(
							'colorFrom' => '#D8E3F0',
                            'colorTo' => '#BED1E6',
                            'stops' => array(0, 100),
                            'opacityFrom' => 0.4,
                            'opacityTo' => 0.5,
						)
					)
				),
				'tooltip' => array(
					'enabled' => false
				)
			),
			'fill' => array(
				'gradient' => array(
					'shade' => 'light',
                    'type' => 'horizontal',
                    'shadeIntensity' => 0.25,
                    'gradientToColors' => null,
                    'inverseColors' => true,
                    'opacityFrom' => 1,
                    'opacityTo' => 1,
                    'stops' => array(50, 0, 100, 100)
				)
			),
			'yaxis' => array(
				'axisBorder' => array(
					'show' => false
				),
				'axisTicks' => array(
					'show' => false
				),
				'labels' => array(
					'show' => true
				)
			)
		)
	);
	
	public function __construct() {
		$this->load->database();
		$this->load->model('model_db_crud');
		$this->load->model(PROJECT_CODE.'/model_report_config');
	}
	
	public function get_all_report_types() {
		return $this->model_report_config->report_types;
	}
	
	public function get_report_options_parameter($report_args) {
		$result = array('render'=>'','selectized_elements'=>array());
		
		if(!isset($report_args['report_type'])) {
			return $result;
		}
		
		$report_type = $report_args['report_type'];
		
		$report_types = $this->model_report_config->report_types;
		
		if(isset($report_types[$report_type]['parameters'])) {
			foreach($report_types[$report_type]['parameters'] as $parameter_name => $parameter_info) {
				if(isset($parameter_info['options_template'])) {
					$original_parameter_info = $parameter_info;
					$template_parameter_info = $this->model_report_config->options_template[$parameter_info['options_template']];
					$parameter_info = $template_parameter_info;
				}
				
				if(isset($original_parameter_info['option_default'])) {
					$parameter_info['option_default'] = $original_parameter_info['option_default'];
				}
				
				$element_id = 'input_report_options_' . $parameter_name;
				$element_classes = array('form-control', 'report-option-parameters');
				
				$result['render'] .= '<div class="form-group">';
				$result['render'] .= '<label for="'.$element_id.'">'.$parameter_info['label'].'</label>';
				
				if($parameter_info['input_type'] == 'select') {
					$element_classes[] = 'selectized';
					$result['selectized_elements'][] = $element_id;
					
					$result['render'] .= '<select class="'.implode(' ', $element_classes).'" id="'.$element_id.'" name="'.$parameter_name.'">';

					if($parameter_info['select_type'] == 'basic') {
						$option_default = isset($parameter_info['option_default']) ? $parameter_info['option_default'] : null;
						$result['render'] .= '<option value=""></option>';
						foreach($parameter_info['options'] as $option_name => $option_info) {
							$selected = '';
							if(isset($report_args[$parameter_name]) && $report_args[$parameter_name] == $option_name) {
								$selected = ' selected';
							}
							else if(isset($report_args['refresh_option_parameter']) && $report_args['refresh_option_parameter'] && isset($parameter_info['option_default']) && $parameter_info['option_default'] == $option_name) {
								$selected = ' selected';
							}
							
							$result['render'] .= '<option value="'.$option_name.'" '.$selected.'>'.$option_info['label'].'</option>';
						}
					}
					
					$result['render'] .= '</select>';
				}
				else if($parameter_info['input_type'] == 'date') {
					$value = isset($report_args[$parameter_name]) ? $report_args[$parameter_name] : null;
					
					$result['render'] .= '<input type="date" class="'.implode(' ', $element_classes).'" id="'.$element_id.'" name="'.$parameter_name.'" value="'.$value.'">';
				}
				
				$result['render'] .= '</div>';
			}
		}
		
		return $result;
	}
	
	public function get_report_result($report_specs, $data_for) {
		$report_args = $report_specs['report_args'];
		
		if(!isset($report_args['report_type'])) {
			return array();
		}
		
		$report_type = $report_args['report_type'];
		$report_result = array();
		
		foreach($report_args as $report_arg_key => $report_arg_value) {
			if(isset($this->model_report_config->options_template[$report_arg_key]['options'][$report_arg_value]['report_subtype'])) {
				$report_args['report_format'] = $this->model_report_config->report_types[$this->model_report_config->options_template[$report_arg_key]['options'][$report_arg_value]['report_subtype']]['report_format'];
				break;
			}
		}
		
		if(!isset($report_args['report_format'])) {
			$report_args['report_format'] = $this->model_report_config->report_types[$report_type]['report_format'];
		}
		
		return $data_for == 'body' ?
			$this->get_general_report_result($report_args, 'body') :
			$this->get_general_report_result($report_args, 'js');
	}
	
	public function get_general_report_result($report_args, $data_for) {
		$result = array();
		$js_data = array();
		
		$report_format = $report_args['report_format'];
		
		if($data_for == 'body') {
			$result['cards'] = array();
			
			if(isset($report_format['cards'])) {
				foreach($report_format['cards'] as $card_name => $card_info) {
					if(isset($card_info['use_template'])) {
						$card_info_template = $this->model_report_config->card_config_template[$card_name];
						foreach($card_info_template as $card_info_key => $card_info_value) {
							if(!isset($card_info[$card_info_key])) {
								$card_info[$card_info_key] = $card_info_value;
							}
						}
					}
					
					$result['cards'][$card_name] = $card_info['attributes'];
					
					if(!isset($card_info['query_specs']['select'])) {
						$card_info['query_specs']['select'] = array('COUNT(*) as result');
					}
					
					$from_table = $card_info['query_specs']['from'];
					unset($card_info['query_specs']['from']);
					
					if(isset($card_info['parameters'])) {
						foreach($card_info['parameters'] as $parameter_name => $parameter_info) {
							if(!empty($report_args[$parameter_name])) {
								$card_filter_query_specs = $this->get_filter_query_specs($parameter_info, $report_args[$parameter_name]);
								if(isset($card_info['query_specs']['where'])) {
									$card_info['query_specs']['where'] = array_merge($card_info['query_specs']['where'], $card_filter_query_specs);
								}
								else {
									$card_info['query_specs']['where'] = $card_filter_query_specs;
								}
							}
						}
					}
					
					$query = $this->model_db_crud->get_data($from_table, $card_info['query_specs']);
					$card_value = !empty($query[0]['result']) ? $query[0]['result'] : 0;
					
					if(isset($card_info['render'])) {
						$render_info = $card_info['render'];
						switch($render_info['render_type']) {
							case 'format_text':
								$card_value = format_text($card_value, $render_info['format_type'], $render_info['format_args']);
								break;
							default:
						}
					}
					
					$result['cards'][$card_name]['value'] = $card_value;
				}
			}
		}
		
		$result['tables'] = array();
		$js_data['tables'] = array();
		
		if(isset($report_format['tables'])) {
			foreach($report_format['tables'] as $table_name => $table_info) {
				$table_js_data = array(
					'table_name' => $table_name
				);
				$report_args['table_name'] = $table_name;
				$build_query_array = $report_args;
				unset($build_query_array['report_format']);
				$table_js_data['ajax_url'] = base_url('api/view_report/?') . http_build_query($build_query_array);
				unset($report_args['table_name']);
				
				for($i=0; $i<count($table_info['column_defs']); $i++) {
					$table_info['column_defs'][$i]['targets'] = $i;
				}
				$table_js_data['column_defs'] = $table_info['column_defs'];
				
				$table = array('table_headers' => array_column($table_js_data['column_defs'], 'header'));
				$table['table_name'] = $table_js_data['table_name'];
				if(isset($table_info['table_title'])) $table['table_title'] = $table_info['table_title'];
				$table['table_footers'] = $table['table_headers'];
				for($i=0; $i<count($table_js_data['column_defs']); $i++) {
					$table_js_data['column_defs'][$i] = json_encode($table_js_data['column_defs'][$i]);
				}
				
				$result['tables'][] = $table;
				$js_data['tables'][] = $table_js_data;
			}
		}
		
		$result['charts'] = array();
		$js_data['charts'] = array();
		
		if(isset($report_format['charts'])) {
			foreach($report_format['charts'] as $chart_name => $chart_info) {
				$chart_js_data = $chart = array(
					'chart_name' => $chart_name
				);
				
				$chart_type = $chart_info['chart_type'];
				$chart_options = $this->chart_default_options[$chart_type];
				
				$this_report_args = $report_args;
				$this_report_args['table_name'] = $chart_info['data_table'];
				$chart_table_data = $this->get_table_data($this_report_args);
				
				if(empty($chart_table_data)) {
					continue;
				}
				
				$xaxis_categories = array();
				$series_data = array();
				for($i=0; $i<count($chart_table_data); $i++) {
					$xaxis_categories[] = $chart_table_data[$i][$chart_info['xaxis']['field_name']];
					
					foreach($chart_info['series'] as $series_name => $series_info) {
						$series_data[$series_name][] = $chart_table_data[$i][$series_info['field_name']];
					}
					
					if(isset($chart_info['xaxis']['limit']) && $i >= $chart_info['xaxis']['limit']) {
						break;
					}
				}
				$chart_options['xaxis']['categories'] = $xaxis_categories;
				
				$chart_js_data['formatter'] = array();
				$chart_options['series'] = array();
				foreach($chart_info['series'] as $series_name => $series_info) {
					$chart_options['series'][] = array(
						'name' => $series_info['label'],
						'data' => $series_data[$series_name]
					);
					
					if(isset($series_info['formatter'])) {
						$chart_js_data['formatter'] = array(
							'series_name' => $series_name,
							'format_type' => $series_info['formatter']
						);
					}
				}
				
				if(!empty($chart_info['options'])) {
					foreach($chart_info['options'] as $option_key => $option_value) {
						foreach($option_value as $suboption_key => $suboption_value) {
							$chart_options[$option_key][$suboption_key] = $suboption_value;
						}
					}
				}
				
				$chart_js_data['chart_options'] = $chart_options;
				
				$result['charts'][] = $chart;
				$js_data['charts'][] = $chart_js_data;
			}
		}
				
		if($data_for == 'js') return $js_data;
		else return $result;
	}
	
	public function get_table_data($report_args) {
		$report_type = $report_args['report_type'];
		$table_name = $report_args['table_name'];
		
		$report_types = $this->model_report_config->report_types;
		
		foreach($report_args as $report_arg_key => $report_arg_value) {
			if(isset($this->model_report_config->options_template[$report_arg_key]['options'][$report_arg_value]['report_subtype'])) {
				$report_format = $this->model_report_config->report_types[$this->model_report_config->options_template[$report_arg_key]['options'][$report_arg_value]['report_subtype']]['report_format'];
				break;
			}
		}
		
		if(!isset($report_format)) {
			$report_format = $report_types[$report_type]['report_format'];
		}
		
		$table_info = $report_format['tables'][$table_name];
		
		$query_specs = $table_info['query_specs'];
		
		if(isset($table_info['parameters'])) {
			foreach($table_info['parameters'] as $parameter_name => $parameter_info) {
				if(!empty($report_args[$parameter_name])) {
					$filter_query_specs = $this->get_filter_query_specs($parameter_info, $report_args[$parameter_name]);

					if(isset($table_info['query_specs']['where'])) {						
						$query_specs['where'] = array_merge($query_specs['where'], $filter_query_specs);
					}
					else {
						$query_specs['where'] = $filter_query_specs;
					}
				}
			}
		}
		
		if(isset($table_info['query_specs']['query'])) {
			$query_str = $table_info['query_specs']['query'];
			$query_str = str_replace('{{USER_GROUP}}', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'), $query_str);
			$query_str = str_replace('{{LAST_THREE_MONTHS}}', date('Y-m-d', strtotime('-3 months', strtotime(date('Y-m-d')))), $query_str);
			
			if(isset($report_args['period_from'])) {
				$query_str = str_replace('{{PERIOD_FROM}}', date('Y-m-d', strtotime($report_args['period_from'])), $query_str);
			}
			else {
				$query_str = str_replace('{{PERIOD_FROM}}', date('Y-01-01'), $query_str);
			}
			
			if(isset($report_args['period_to'])) {
				$query_str = str_replace('{{PERIOD_TO}}', date('Y-m-d', strtotime($report_args['period_to'])), $query_str);
			}
			else {
				$query_str = str_replace('{{PERIOD_TO}}', date('Y-m-d'), $query_str);
			}
			
			$result = $this->db->query($query_str)->result_array();
		}
		else {
			$result = $this->model_db_crud->get_data($query_specs['from'], $query_specs);
		}
		
		if(isset($table_info['render'])) {
			for($i=0; $i<count($result); $i++) {
				foreach($table_info['render'] as $field_name => $render_info) {
					$result[$i]['plain_'.$field_name] = $result[$i][$field_name];
					switch($render_info['render_type']) {
						case 'link':
							$result[$i][$field_name] = '<a href="{U}db/view/'.$render_info['entity_name'].'/'.$result[$i][$render_info['link_id_field']].'">'.$result[$i][$field_name].'</a>';
							break;
						case 'option_label':
							$result[$i][$field_name] = format_text($result[$i][$field_name], 'option_label', array('options_type'=>$render_info['options_type']));
							break;
						case 'format_text':
							$result[$i][$field_name] = format_text($result[$i][$field_name], $render_info['format_type'], $render_info['format_args']);
							break;
						default:
					}
				}
			}
		}
		
		if(isset($table_info['create_list'])) {
			$tmp_result = $result;
			$result = array();
			
			foreach($tmp_result as $this_row) {
				$result[$this_row[$table_info['create_list']['field_name']]] = $this_row;
			}
			
			if($table_info['create_list']['list_type'] == 'date') {
				$field_name = $table_info['create_list']['field_name'];
				
				if(isset($report_args['period']) && $report_args['period'] <> 'all') {
					$period_dates = $this->get_period_dates($report_args['period']);
					$period_from = $period_dates['period_from'];
					$period_to = $period_dates['period_to'];
				}
				else {
					$period_from = strtotime(min(array_column($tmp_result, $field_name)));
					$period_to = strtotime(date('Y-m-d'));
				}
				
				if(isset($report_args['period_from'])) {
					$period_from = $report_args['period_from'];
				}
				if(isset($report_args['period_to'])) {
					$period_to = $report_args['period_to'];
				}
				
				if(isset($table_info['render'])) {
					foreach($table_info['render'] as $render_field => $render_info) {
						$table_info['create_list']['other_fields_default']['plain_'.$render_field] = 0;
					}
				}
				
				for($current_date = $period_from; $current_date <= $period_to; ) {
					if(!isset($result[date('Y-m-d', $current_date)])) {
						$result[date('Y-m-d', $current_date)] = array($field_name => date('Y-m-d', $current_date));
						foreach($table_info['create_list']['other_fields_default'] as $this_field_name => $this_field_value) {
							$result[date('Y-m-d', $current_date)][$this_field_name] = $this_field_value;
						}
					}

					$current_date = strtotime('+1 day', $current_date);
				}
				
				ksort($result);
				$result = array_values($result);
			}
		}
		
		return $result;
	}
	
	public function get_report_subtitle($report_args) {
		$report_subtitles = array();
		$date_format = 'd-m-Y';
		
		if(!empty($report_args['period'])) {
			$period_dates = $this->get_period_dates($report_args['period']);
			$period_from = $period_dates['period_from'];
			$period_to = $period_dates['period_to'];
			
			$report_subtitle = '';
			if(isset($this->model_report_config->options_template['period']['options'][$report_args['period']]['label'])) {
				$report_subtitle .= $this->model_report_config->options_template['period']['options'][$report_args['period']]['label'];
			}
			if(isset($period_from)) {
				$report_subtitle .= ' ('.date($date_format, $period_from);
				if($period_to <> $period_from) {
					$report_subtitle .= ' - '.date($date_format, $period_to);
				}
				$report_subtitle .= ')';
			}
			$report_subtitles[] = $report_subtitle;
		}
		else if(!empty($report_args['period_from']) || !empty($report_args['period_to'])) {
			$period_from = strtotime('2019-01-01');
			$period_to = strtotime(date('Y-m-d 23:59:59'));
			
			if(!empty($report_args['period_from'])) {
				$period_from = strtotime($report_args['period_from']);
			}
			if(!empty($report_args['period_to'])) {
				$period_to = strtotime($report_args['period_to'] . ' 23:59:59');
			}
			
			$report_subtitle = date($date_format, $period_from);
			if($period_to <> $period_from) {
				$report_subtitle .= ' - '.date($date_format, $period_to);
			}

			$report_subtitles[] = $report_subtitle;
		}
		
		if(!empty($report_args['comparison_period'])) {
			switch($report_args['comparison_period']) {
				case 'previous_period':
					break;
				case 'previous_year':
					if(isset($period_from)) {
						$period_from = strtotime('-1 year', $period_from);
						$period_to = strtotime('-1 year', $period_to);
					}
					break;
				default:
			}
			
			$report_subtitle = '';
			if(isset($this->model_report_config->options_template['comparison_period']['options'][$report_args['comparison_period']]['label'])) {
				$report_subtitle .= 'Compare To ' . $this->model_report_config->options_template['comparison_period']['options'][$report_args['comparison_period']]['label'];
			}
			if(isset($period_from)) {
				$report_subtitle .= ' ('.date($date_format, $period_from);
				if($period_to <> $period_from) {
					$report_subtitle .= ' - '.date($date_format, $period_to);
				}
				$report_subtitle .= ')';
			}
			$report_subtitles[] = $report_subtitle;
		}
		
		if(!empty($report_args['revenue_by'])) {
			if(isset($this->model_report_config->options_template['revenue_by']['options'][$report_args['revenue_by']]['label'])) {
				$report_subtitles[] = 'By ' . $this->model_report_config->options_template['revenue_by']['options'][$report_args['revenue_by']]['label'];
			}
		}
		
		return implode(', ', $report_subtitles);
	}
	
	public function get_filter_query_specs($parameter_info, $filter_value) {
		$result = array();
		
		if($parameter_info['filter_type'] == 'period') {
			$period_dates = $this->get_period_dates($filter_value);
			$period_from = $period_dates['period_from'];
			$period_to = $period_dates['period_to'];
			
			if(!empty($period_from)) {
				$result = array(
					$parameter_info['field'] . ' >=' => date('Y-m-d', $period_from),
					$parameter_info['field'] . ' <=' => date('Y-m-d 23:59:59', $period_to));
			}
		}
		else if($parameter_info['filter_type'] == 'period_from') {
			$result = array($parameter_info['field'] . ' >=' => date('Y-m-d', strtotime($filter_value)));
		}
		else if($parameter_info['filter_type'] == 'period_to') {
			$result = array($parameter_info['field'] . ' <=' => date('Y-m-d 23:59:59', strtotime($filter_value)));
		}
		
		return $result;
	}
	
	public function get_period_dates($period_name) {
		$period_from = null;
		$period_to = null;
		
		switch($period_name) {
			case 'today':
				$period_from = $period_to = strtotime(date('Y-m-d'));
				break;
			case 'yesterday':
				$period_from = $period_to = strtotime('-1 day', strtotime(date('Y-m-d')));
				break;
			case 'this_week':
				$period_from = strtotime('previous monday', strtotime(date('Y-m-d')));
				$period_to = strtotime(date('Y-m-d'));
				break;
			case 'last_week':
				$period_from = strtotime('previous monday - 7 days', strtotime(date('Y-m-d')));
				$period_to = strtotime('previous sunday', strtotime(date('Y-m-d')));
				break;
			case 'last_seven_days':
				$period_from = strtotime('- 7 days', strtotime(date('Y-m-d')));
				$period_to = strtotime(date('Y-m-d'));
				break;
			case 'this_month':
				$period_from = strtotime(date('Y-m-01'));
				$period_to = strtotime(date('Y-m-d'));
				break;
			case 'last_month':
				$period_from = strtotime('-1 month', strtotime(date('Y-m-01')));
				$period_to = strtotime('-1 day', strtotime(date('Y-m-01')));
				break;
			case 'last_thirty_days':
				$period_from = strtotime('-30 days', strtotime(date('Y-m-d')));
				$period_to = strtotime(date('Y-m-d'));
				break;
			case 'last_three_months':
				$period_from = strtotime('-3 months', strtotime(date('Y-m-d')));
				$period_to = strtotime(date('Y-m-d'));
				break;
			case 'this_year':
				$period_from = strtotime(date('Y-01-01'));
				$period_to = strtotime(date('Y-m-d'));
				break;
			case 'last_year':
				$period_from = strtotime('-1 year', strtotime(date('Y-01-01')));
				$period_to = strtotime('-1 day', strtotime(date('Y-01-01')));
				break;
			default:
		}
		
		return array(
			'period_from' => $period_from,
			'period_to' => $period_to
		);
	}
}