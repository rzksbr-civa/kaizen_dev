<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_utilization_metrics extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_utilization_metrics_report_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}
		
		$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : 2; // Default is Island River facility
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		
		$period_from = !empty($data['period_from']) ? $data['period_from'] : date('Y-m-d');
		$period_to = !empty($data['period_to']) ? $data['period_to'] : date('Y-m-d');
		
		if($data['report_type'] == 'utilization_report') {
			$local_date_field = "DATE(IF(sales_flat_shipment.stock_id IN (3,6),CONVERT_TZ(action_log.started_at,'UTC', 'US/Mountain'),CONVERT_TZ(action_log.started_at,'UTC','US/Eastern')))";

			switch($data['periodicity']) {
				case 'weekly':
					$select_period_query = 'DATE_ADD('.$local_date_field.', INTERVAL - WEEKDAY('.$local_date_field.') DAY)';
					$select_period_label_query = 'CONCAT("WEEK ", '.$select_period_query.')';
					break;
				case 'monthly':
					$select_period_query = 'DATE_FORMAT('.$local_date_field.', "%Y-%m-01")';
					$select_period_label_query = 'DATE_FORMAT('.$local_date_field.', "%Y-%m (%M %Y)")';
					break;
				case 'yearly':
					$select_period_query = 'DATE_FORMAT('.$local_date_field.', "%Y-01-01")';
					$select_period_label_query = 'DATE_FORMAT('.$local_date_field.', "%Y")';
					break;
				case 'daily':
				default:
					$select_period_query = $local_date_field;
					$select_period_label_query = $local_date_field;
			}
			
			$redstag_db
				->select(
					$select_period_query.' AS period, '.
					$select_period_label_query.' AS period_label,
					MID(sales_flat_shipment.label_print_target,3,3) AS family,
					COUNT(*) AS package_count,
					SUM(sales_flat_shipment_package.weight) AS total_weight,
					SUM(sales_flat_shipment_package.length*sales_flat_shipment_package.width*sales_flat_shipment_package.height)/ 1728 AS total_cubic_ft', false)
				->from('action_log')
				->join('sales_flat_shipment_package', 'sales_flat_shipment_package.package_id = action_log.entity_id')
				->join('sales_flat_shipment', 'sales_flat_shipment.entity_id = sales_flat_shipment_package.shipment_id')
				->where('action_log.action', 'load')
				->where('action_log.entity_type', 'package')
				->group_by($select_period_query . ','.$select_period_label_query.', MID(sales_flat_shipment.label_print_target,3,3)');

			if(!empty($data['facility'])) {
				$timezone_name = ($stock_id == 3 || $stock_id == 6) ? 'US/Mountain' : 'US/Eastern';
				
				$redstag_db
					->where('sales_flat_shipment.stock_id', $stock_id)
					->where("action_log.started_at >= CONVERT_TZ('".$period_from."','".$timezone_name."','UTC')", null, false)
					->where("action_log.started_at < CONVERT_TZ('".date('Y-m-d', strtotime('+1 day'.$period_to))."','".$timezone_name."','UTC')", null, false);
			}
			else {
				$redstag_db
					->where($select_period_query . ' >= \''.$period_from.'\'', null, false)
					->where($select_period_query . ' <', date('Y-m-d', strtotime('+1 day'.$period_to)));
			}
			
			if(!empty($data['customer'])) {
				$redstag_db->where('sales_flat_shipment.store_id', $data['customer']);
			}

			$utilization_metrics_report_data_raw = $redstag_db->get()->result_array();
			
			$template_data = array(
				'total_count' => 0,
				'total_weight' => 0,
				'average_weight' => 0,
				'total_cubic_ft' => 0,
				'average_cubic_ft' => 0
			);
			
			$assignment_types_data = $this->model_db_crud->get_several_data('assignment_type');
			$assignment_types = array();
			$assignment_type_id_by_label_printer_prefix = array();
			
			$assignment_types[0] = $template_data;
			$assignment_types[0]['assignment_type_name'] = '(No Assignment)';
			
			foreach($assignment_types_data as $current_assignment_type) {
				if(!empty($current_assignment_type['label_printer_prefix'])) {
					$assignment_types[$current_assignment_type['id']] = $template_data;
					$assignment_types[$current_assignment_type['id']]['assignment_type_name'] = $current_assignment_type['assignment_type_name'];
					$assignment_type_id_by_label_printer_prefix[$current_assignment_type['label_printer_prefix']] = $current_assignment_type['id'];
				}
			}
			
			$family_template = array();
			foreach($assignment_types as $assignment_type_id => $assignment_type) {
				$family_template[$assignment_type_id] = $template_data;
			}

			$utilization_metrics_report_data = array(
				'period' => array(),
				'total' => array()
			);
			
			foreach($utilization_metrics_report_data_raw as $current_data) {
				if(!isset($utilization_metrics_report_data['period'][$current_data['period_label']])) {
					$utilization_metrics_report_data['period'][$current_data['period_label']] = $family_template;
					$utilization_metrics_report_data['total'][$current_data['period_label']] = $template_data;
				}
				
				$assignment_type_id = 0;
				if(!empty($current_data['family']) && isset($assignment_type_id_by_label_printer_prefix[$current_data['family']])) {
					$assignment_type_id = $assignment_type_id_by_label_printer_prefix[$current_data['family']];
				}
				
				$utilization_metrics_report_data['period'][$current_data['period_label']][$assignment_type_id]['total_count'] += $current_data['package_count'];
				$utilization_metrics_report_data['period'][$current_data['period_label']][$assignment_type_id]['total_weight'] += $current_data['total_weight'];
				$utilization_metrics_report_data['period'][$current_data['period_label']][$assignment_type_id]['total_cubic_ft'] += $current_data['total_cubic_ft'];
				
				$utilization_metrics_report_data['total'][$current_data['period_label']]['total_count'] += $current_data['package_count'];
				$utilization_metrics_report_data['total'][$current_data['period_label']]['total_weight'] += $current_data['total_weight'];
				$utilization_metrics_report_data['total'][$current_data['period_label']]['total_cubic_ft'] += $current_data['total_cubic_ft'];
				
				$assignment_types[$assignment_type_id]['total_count'] += $current_data['package_count'];
				$assignment_types[$assignment_type_id]['total_weight'] += $current_data['total_weight'];
				$assignment_types[$assignment_type_id]['total_cubic_ft'] += $current_data['total_cubic_ft'];
			}
			
			switch($data['periodicity']) {
				case 'daily':
				case 'weekly':
					$data['datetime_formatter'] = 'yyyy-MM-dd';
					break;
				case 'monthly':
					$data['datetime_formatter'] = "MMM 'yy";
					break;
				case 'yearly':
					$data['datetime_formatter'] = 'yyyy';
					break;
				default:
					$data['datetime_formatter'] = 'yyyy-MM-dd';
					break;
			}
			
			$data['period_labels'] = array_keys($utilization_metrics_report_data['period']);
			
			$period_template = array();
			foreach($data['period_labels'] as $period_name) {
				$period_template[$period_name] = 0;
			}
			
			$utilization_metrics_report_graph_data = array();
			foreach($assignment_types as $assignment_type_id => $assignment_type) {
				$utilization_metrics_report_graph_data[$assignment_type_id] = array(
					'assignment_type_id' => $assignment_type_id,
					'assignment_type_name' => $assignment_type['assignment_type_name'],
					'average_weight_data' => $period_template,
					'average_cubic_ft_data' => $period_template
				);
			}
			$max_average_weight = 0;
			$max_average_cubic_ft = 0;
			foreach($utilization_metrics_report_data['period'] as $period_name => $family_data) {
				foreach($family_data as $assignment_type_id => $current_data) {
					$average_weight = $current_data['total_count'] > 0 ? $current_data['total_weight'] / $current_data['total_count'] : 0;
					$average_cubic_ft = $current_data['total_count'] > 0 ? $current_data['total_cubic_ft'] / $current_data['total_count'] : 0;
					
					if($average_weight > $max_average_weight) {
						$max_average_weight = $average_weight;
					}
					if($average_cubic_ft > $max_average_cubic_ft) {
						$max_average_cubic_ft = $average_cubic_ft;
					}
					
					$utilization_metrics_report_data['period'][$period_name][$assignment_type_id]['average_weight'] = $average_weight;
					$utilization_metrics_report_data['period'][$period_name][$assignment_type_id]['average_cubic_ft'] = $average_cubic_ft;
					
					$utilization_metrics_report_graph_data[$assignment_type_id]['average_weight_data'][$period_name] = $average_weight;
					$utilization_metrics_report_graph_data[$assignment_type_id]['average_cubic_ft_data'][$period_name] = $average_cubic_ft;
				}
			}
			
			$data['assignment_types'] = $assignment_types;
			$data['utilization_metrics_report_data'] = $utilization_metrics_report_data;
			$data['utilization_metrics_report_graph_data'] = $utilization_metrics_report_graph_data;
			$data['max_average_weight'] = $max_average_weight;
			$data['max_average_cubic_ft'] = $max_average_cubic_ft;

			$data['utilization_metrics_report_visualization'] = $this->load->view(PROJECT_CODE.'/report/view_utilization_metrics_report_visualization_utilization_report', $data, true);
			$data['js_utilization_metrics_report_visualization'] = $this->load->view(PROJECT_CODE.'/report/js_view_utilization_metrics_report_visualization_utilization_report', $data, true);
		}
		else if($data['report_type'] == 'utilization_trend') {
			$local_date_field = "DATE(IF(manifest.stock_id IN (3,6),CONVERT_TZ(manifest.created_at,'UTC','US/Mountain'),CONVERT_TZ(manifest.created_at,'UTC','US/Eastern')))";
			
			switch($data['periodicity']) {
				case 'weekly':
					$select_period_query = 'DATE_ADD('.$local_date_field.', INTERVAL - WEEKDAY('.$local_date_field.') DAY)';
					$select_period_label_query = 'CONCAT("WEEK ", '.$select_period_query.')';
					break;
				case 'monthly':
					$select_period_query = 'DATE_FORMAT('.$local_date_field.', "%Y-%m-01")';
					$select_period_label_query = 'DATE_FORMAT('.$local_date_field.', "%Y-%m (%M %Y)")';
					break;
				case 'yearly':
					$select_period_query = 'DATE_FORMAT('.$local_date_field.', "%Y-01-01")';
					$select_period_label_query = 'DATE_FORMAT('.$local_date_field.', "%Y")';
					break;
				case 'daily':
				default:
					$select_period_query = $local_date_field;
					$select_period_label_query = $local_date_field;
			}
			
			$redstag_db
				->select("COUNT(DISTINCT manifest.increment_id) AS manifest_count,
					".$select_period_query." AS period,
					".$select_period_label_query." AS period_label,
					manifest.carrier_code,
					SUM(sales_flat_shipment_package.weight) AS total_weight,
					SUM(sales_flat_shipment_package.weight)/(40000*COUNT(DISTINCT manifest.increment_id)) * 100 AS weight_percentage,
					SUM(length*width*height / 1728) / (8*8*53 * COUNT(DISTINCT manifest.increment_id)) * 100 AS cubic_ft_percentage", false)
				->from('manifest_item')
				->join('manifest', 'manifest.manifest_id = manifest_item.manifest_id')
				->join('sales_flat_shipment_package', 'sales_flat_shipment_package.package_id = manifest_item.package_id')
				->group_by($select_period_query.",".$select_period_label_query.", manifest.carrier_code")
				->order_by($select_period_query);
			
			if(!empty($data['facility'])) {
				$timezone_name = ($stock_id == 3 || $stock_id == 6) ? 'US/Mountain' : 'US/Eastern';
				
				$redstag_db
					->where('manifest.stock_id', $stock_id)
					->where("manifest.created_at >= CONVERT_TZ('".$period_from."','".$timezone_name."','UTC')", null, false)
					->where("manifest.created_at < CONVERT_TZ('".date('Y-m-d', strtotime('+1 day'.$period_to))."','".$timezone_name."','UTC')", null, false);
			}
			else {
				$redstag_db
					->where("IF(manifest.stock_id IN (3,6),CONVERT_TZ(manifest.created_at,'UTC','US/Mountain'),CONVERT_TZ(manifest.created_at,'UTC','US/Eastern')) >=", "'".$period_from."'", false)
					->where("IF(manifest.stock_id IN (3,6),CONVERT_TZ(manifest.created_at,'UTC','US/Mountain'),CONVERT_TZ(manifest.created_at,'UTC','US/Eastern')) <", "'".date('Y-m-d', strtotime('+1 day '.$period_to))."'", false);
			}
			
			if(!empty($data['customer'])) {
				$redstag_db->where('sales_flat_shipment.store_id', $data['customer']);
			}
			
			$data['utilization_metrics_trend_data'] = $redstag_db->get()->result_array();
			
			$data['period_labels'] = array_values(array_unique(array_column($data['utilization_metrics_trend_data'],'period_label')));
			$data['carriers'] = array_values(array_unique(array_column($data['utilization_metrics_trend_data'],'carrier_code')));
			
			$data['utilization_metrics_trend_graph_data'] = array();
			foreach($data['utilization_metrics_trend_data'] as $current_data) {
				if(!isset($data['utilization_metrics_trend_graph_data'][$current_data['carrier_code']])) {
					$data['utilization_metrics_trend_graph_data'][$current_data['carrier_code']] = array(
						'weight_percentage' => array(),
						'cubic_ft_percentage' => array()
					);
					
					foreach($data['period_labels'] as $period_label) {
						$data['utilization_metrics_trend_graph_data'][$current_data['carrier_code']]['weight_percentage'][$period_label] = 0;
						$data['utilization_metrics_trend_graph_data'][$current_data['carrier_code']]['cubic_ft_percentage'][$period_label] = 0;
					}
				}
				
				$data['utilization_metrics_trend_graph_data'][$current_data['carrier_code']]['weight_percentage'][$current_data['period_label']] = $current_data['weight_percentage'];
				
				$data['utilization_metrics_trend_graph_data'][$current_data['carrier_code']]['cubic_ft_percentage'][$current_data['period_label']] = $current_data['cubic_ft_percentage'];
			}
			
			$data['utilization_metrics_report_visualization'] = $this->load->view(PROJECT_CODE.'/report/view_utilization_metrics_report_visualization_utilization_trend', $data, true);
			$data['js_utilization_metrics_report_visualization'] = $this->load->view(PROJECT_CODE.'/report/js_view_utilization_metrics_report_visualization_utilization_trend', $data, true);
		}
		
		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.gmdate('Y-m-d H:i:s')));
		
		return $data;
	}
}