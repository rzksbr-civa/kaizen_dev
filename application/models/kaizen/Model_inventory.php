<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_inventory extends CI_Model {
	public function __construct() {
		$this->load->database();
		$this->load->model('model_db_crud');
	}
	
	public function get_inventory_report_data($args) {
		set_time_limit ( 300 );
		
		$result = array();
		
		$redstag_db = $this->load->database('redstag', TRUE);
		
		$facility_data = !empty($args['facility']) ? $this->model_db_crud->get_specific_data('facility', $args['facility']) : null;
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5;
		$timezone += date('I'); // Daylight saving time
		
		$result['product_cubic_inches_data'] = $redstag_db
			->select('catalog_product_entity.entity_id AS product_id, catalog_product_entity.width * catalog_product_entity.height * catalog_product_entity.length AS cubic_inches', false)
			->from('catalog_product_entity')
			->where(
				"catalog_product_entity.entity_id IN (
					SELECT DISTINCT sales_flat_order_item.product_id
					FROM sales_flat_order_item
					JOIN sales_flat_order ON sales_flat_order.entity_id = sales_flat_order_item.order_id
					WHERE `sales_flat_order`.`created_at` >= '".date('Y-m-d H:i:s', strtotime(($timezone <= 0 ? '+' : '').($timezone*-1). ' hour ' . date('Y-m-d', strtotime('-60 day'))))."'
				)", null, false)
			->get()->result_array();

		$result['product_cubic_inches_data'] = array_combine(
			array_column($result['product_cubic_inches_data'], 'product_id'),
			array_column($result['product_cubic_inches_data'], 'cubic_inches')
		);
		
		$redstag_db
			->select('cataloginventory_stock_item.product_id, SUM(cataloginventory_stock_item.qty_on_hand) AS qty_on_hand', false)
			->from('cataloginventory_stock_item')
			->where(
				"cataloginventory_stock_item.product_id IN (
					SELECT DISTINCT sales_flat_order_item.product_id
					FROM sales_flat_order_item
					JOIN sales_flat_order ON sales_flat_order.entity_id = sales_flat_order_item.order_id
					WHERE `sales_flat_order`.`created_at` >= '".date('Y-m-d H:i:s', strtotime(($timezone <= 0 ? '+' : '').($timezone*-1). ' hour ' . date('Y-m-d', strtotime('-60 day'))))."'
				)", null, false)
			->group_by('cataloginventory_stock_item.product_id');
		
		if(!empty($args['facility'])) {
			$redstag_db
				->where('cataloginventory_stock_item.stock_id', $facility_data['stock_id']);
		}
		
		$result['product_qty_on_hand_data'] = $redstag_db->get()->result_array();

		$result['product_qty_on_hand_data'] = array_combine(
			array_column($result['product_qty_on_hand_data'], 'product_id'),
			array_column($result['product_qty_on_hand_data'], 'qty_on_hand')
		);
		
		$redstag_db
			->select('sales_flat_order_item.product_id, catalog_product_entity.sku, COUNT(*) / 60 AS sixty_day_avg', false)
			->from('sales_flat_order_item_stock')
			->join('sales_flat_order_stock', 'sales_flat_order_stock.order_stock_id = sales_flat_order_item_stock.order_stock_id')
			->join('sales_flat_order_item', 'sales_flat_order_item.item_id = sales_flat_order_item_stock.order_item_id')
			->join('sales_flat_order', 'sales_flat_order_item.order_id = sales_flat_order.entity_id')
			->join('catalog_product_entity', 'sales_flat_order_item.product_id = catalog_product_entity.entity_id')
			->where('sales_flat_order.created_at >=', date('Y-m-d H:i:s', strtotime(($timezone <= 0 ? '+' : '').($timezone*-1). ' hour ' . date('Y-m-d', strtotime('-60 day')))))
			->group_by('sales_flat_order_item.product_id')
			->order_by('sku');
			
		if(!empty($args['facility'])) {
			$redstag_db
				->where('sales_flat_order_stock.stock_id', $facility_data['stock_id']);
		}
		
		$result['products_data'] = $redstag_db->get()->result_array();
		
		$result['pallet_cubic_inches'] = 92160;
		
		return $result;
	}
	
	public function get_inventory_board_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		$prod_db = $this->load->database('prod', TRUE);
		
		if(!empty($data['facility'])) {
			$facility_data = $this->model_db_crud->get_specific_data('facility', $data['facility']);
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}
		
		$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : null;
		$data['stock_id'] = $stock_id;
		
		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		
		$date = !empty($data['date']) ? $data['date'] : date('Y-m-d');

		$redstag_db
			->select(
				"name,
				 HOUR(IF(stock_id IN (3,6),CONVERT_TZ(started_at,'UTC','US/Mountain'),CONVERT_TZ(started_at,'UTC','US/Eastern'))) AS local_started_hour,
				 COUNT(*) AS drops_count", false)
			->from('action_log')
			->join('admin_user', 'admin_user.user_id = action_log.user_id')
			->where("IF(stock_id IN (3,6),CONVERT_TZ(started_at,'UTC','US/Mountain'),CONVERT_TZ(started_at,'UTC','US/Eastern')) >= '".$data['date']."'", null, false)
			->where("IF(stock_id IN (3,6),CONVERT_TZ(started_at,'UTC','US/Mountain'),CONVERT_TZ(started_at,'UTC','US/Eastern')) < '".date('Y-m-d', strtotime('+1 day '.$data['date']))."'", null, false)
			->where('action', 'relocate')
			->where('entity_type', 'relocation')
			->like('name', '(INV)')
			->group_by("name, HOUR(IF(stock_id IN (3,6),CONVERT_TZ(started_at,'UTC','US/Mountain'),CONVERT_TZ(started_at,'UTC','US/Eastern')))", false)
			->order_by('name');
		
		if(!empty($data['facility'])) {
			$redstag_db->where('action_log.stock_id', $stock_id);
		}
		
		$total_drops_per_hour_raw_data = $redstag_db->get()->result_array();
		
		$total_drops_per_hour_data = array(
			'total' => array(),
			'user_breakdown' => array());
		
		$hourly_template = array();
		for($i=0; $i<24; $i++) {
			$hourly_template[$i] = 0;
		}
		
		$total_drops_per_hour_data['total'] = $hourly_template;
		
		if(!empty($total_drops_per_hour_raw_data)) {
			foreach($total_drops_per_hour_raw_data as $current_data) {
				if(!isset($total_drops_per_hour_data['user_breakdown'][$current_data['name']])) {
					$total_drops_per_hour_data['user_breakdown'][$current_data['name']] = $hourly_template;
				}
				
				$total_drops_per_hour_data['user_breakdown'][$current_data['name']][$current_data['local_started_hour']] = $current_data['drops_count'];
				$total_drops_per_hour_data['total'][$current_data['local_started_hour']] += $current_data['drops_count'];
			}
		}

		$data['total_drops_per_hour_data'] = $total_drops_per_hour_data;
		$data['hours'] = array();
		if(!empty($total_drops_per_hour_data['total'])) {
			foreach(array_keys($total_drops_per_hour_data['total']) as $current_hour) {
				$data['hours'][] = date('Y-m-d H:00:00', strtotime('+'.$current_hour.' hour '.$data['date']));
			}
		}

		$live_drops_data = $this->get_live_drops_data($data);
		
		$data['live_drops_data_count'] = count($live_drops_data);
		
		// Live drops per hour data
		$live_drops_per_hour_data = array();
		foreach($data['hours'] as $hour) {
			$live_drops_per_hour_data[$hour] = 0;
		}
		
		$prod_db
			->select('datetime, SUM(new_drops_count) AS total_live_drops_count', false)
			->from('live_drops_logs')
			->like('datetime', $data['date'])
			->group_by('datetime');
		
		if(!empty($data['facility'])) {
			$prod_db->where('facility', $data['facility']);
		}
		
		$live_drops_per_hour_data_raw = $prod_db->get()->result_array();
		
		foreach($live_drops_per_hour_data_raw as $current_data) {
			$live_drops_per_hour_data[$current_data['datetime']] = $current_data['total_live_drops_count'];
		}
		
		$data['page_generated_time'] = (new DateTime( ($data['facility']==2 ? 'America/Denver' : 'America/New_York') ))->format('Y-m-d H:i:s');
		
		$last_hour_live_drops_skus_data = $prod_db
			->select('sku')
			->from('live_drops')
			->where('datetime', date('Y-m-d H:i:s', strtotime('-1 hour '.date('Y-m-d H:00:00', strtotime($data['page_generated_time'])))))
			->get()->result_array();
		
		$last_hour_live_drops_skus = array();
		if(!empty($last_hour_live_drops_skus_data)) {
			$last_hour_live_drops_skus = array_column($last_hour_live_drops_skus_data, 'sku');
		}
			
		$new_drops_count = 0;
		foreach($live_drops_data as $key => $current_data) {
			if(!in_array($current_data['sku'], $last_hour_live_drops_skus)) {
				$new_drops_count++;
			}
		}
		
		$live_drops_per_hour_data[date('Y-m-d H:00:00', strtotime($data['page_generated_time']))] = $new_drops_count; // $data['live_drops_data_count'];
		
		$data['live_drops_per_hour_data'] = $live_drops_per_hour_data;
		
		$data['inventory_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_inventory_board_visualization', $data, true);
		
		$data['js_inventory_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/js_view_inventory_board_visualization', $data, true);
		
		return $data;
	}
	
	public function get_live_drops_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		$prod_db = $this->load->database('prod', TRUE);
		
		if(!empty($data['facility'])) {
			$facility_data_tmp = $this->db
				->select('*')
				->from('facilities')
				->where('data_status',DATA_ACTIVE)
				->where('id',$data['facility'])
				->get()->result_array();
			$facility_data = $facility_data_tmp[0];
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}
		
		$stock_id = isset($facility_data['stock_id']) ? $facility_data['stock_id'] : null;
		$data['stock_id'] = $stock_id;
		
		// Live drops
		$drop_sheet_data_query = "SELECT s.name AS warehouse,
			   w.name                AS merchant,
			   product_name,
			   sku,
			   qty_demand AS qty_allocated,
			   qty_pickable,
			   qty_needed AS qty_required,
			   label AS location,
			   origination_date,
			   days_until_expiration,
			   expiration_date,
			   stock_sub(stock_add(qty_unreserved, qty_reserved, lcm), qty_locked, lcm) AS qrt_relocatable
		FROM (
			   SELECT stock_id,
					  website_id,
					  product_id,
					  sku,
					  name AS product_name,
					  stock_item_id,
					  stock_sub(stock_add(qty_allocated, qty_reserved, lcm), qty_locked, lcm) AS qty_demand,
					  qty_pickable,
					  stock_sub(stock_sub(stock_add(qty_allocated, qty_reserved, lcm), qty_locked, lcm), qty_pickable, lcm) AS qty_needed,
					  lcm
			   FROM cataloginventory_stock_item AS si
			   JOIN (
				 SELECT l.stock_item_id,
						lcm,
						ROUND(SUM(qty_locked * lcm) / lcm, 4) AS qty_locked,
						ROUND(SUM(IF(is_pickable = -1, ROUND(qty_unreserved * lcm) + ROUND(qty_reserved * lcm) - ROUND(qty_locked * lcm), 0)) / lcm, 4) AS qty_pickable,
						ROUND(SUM(IF(is_pickable = 0, ROUND(qty_unreserved * lcm) + ROUND(qty_reserved * lcm) - ROUND(qty_locked * lcm), 0)) / lcm, 4) AS qty_unpickable
				 FROM cataloginventory_stock_location l
				 INNER JOIN (
					 SELECT item_id as stock_item_id, COALESCE(lcm, 10000) as lcm
					 FROM cataloginventory_stock_item si
					 LEFT OUTER JOIN catalog_product_bom_lcm_idx lcm ON lcm.product_id = si.product_id
				 ) lcm ON l.stock_item_id = lcm.stock_item_id
				 GROUP BY stock_item_id
			   ) AS t1 ON si.item_id = t1.stock_item_id
			   JOIN catalog_product_entity AS cp ON (si.product_id = cp.entity_id)
			   WHERE stock_sub(stock_sub(stock_add(qty_allocated, qty_reserved, lcm), qty_locked, lcm), qty_pickable, lcm) > 0
			   ORDER BY stock_id, website_id, qty_needed DESC, product_id
		) AS t2
		JOIN cataloginventory_stock_location AS sl ON t2.stock_item_id = sl.stock_item_id AND sl.is_temp = 0
		LEFT OUTER JOIN stock_lot AS slot ON sl.lot_id = slot.lot_id
		JOIN cataloginventory_stock AS s ON sl.stock_id = s.stock_id
		JOIN core_website AS w ON t2.website_id = w.website_id
		WHERE stock_sub(stock_add(qty_unreserved, qty_reserved, lcm), qty_locked, lcm) > 0
		  ".(!empty($data['stock_id']) ? "AND t2.stock_id = ".$data['stock_id'] : "")."
		  AND sl.is_pickable = 0
		ORDER BY t2.stock_id, t2.website_id, qty_needed DESC, t2.product_id, -days_until_expiration DESC, origination_date ASC, stock_sub(stock_add(qty_unreserved, qty_reserved, lcm), qty_locked, lcm) ASC, priority";
		
		$drop_sheet_data = $redstag_db->query($drop_sheet_data_query)->result_array();
		
		$new_and_in_processing_skus_data = $redstag_db->query("SELECT DISTINCT(catalog_product_entity.sku) AS sku
				FROM sales_flat_shipment_item
				JOIN sales_flat_shipment ON sales_flat_shipment.entity_id = sales_flat_shipment_item.parent_id
				JOIN sales_flat_order ON sales_flat_order.entity_id = sales_flat_shipment.order_id
				JOIN catalog_product_entity ON catalog_product_entity.entity_id = sales_flat_shipment_item.product_id
				WHERE sales_flat_shipment.target_ship_date = ".$redstag_db->escape($data['date'])."
				AND sales_flat_order.status IN ('new', 'processing')")->result_array();
			
		$new_and_in_processing_skus = array();
		if(!empty($new_and_in_processing_skus_data)) {
			$new_and_in_processing_skus = array_column($new_and_in_processing_skus_data, 'sku');
		}
		
		$this->load->model(PROJECT_CODE.'/model_replenishment');
		$replenishment_stock_data_tmp = $this->model_replenishment->get_replenishment_stock_data(
			array(
				'facility' => $data['facility'],
				'service_level_percentage' => 0.97
			)
		);
		
		$replenishment_stock_data = array();
		foreach($replenishment_stock_data_tmp as $current_data) {
			$replenishment_stock_data[$current_data['sku']] = $current_data;
		}
		
		$unique_warehouse_merchant_skus = array();
		$live_drops_data = array();
		
		foreach($drop_sheet_data as $current_data) {
			$warehouse_merchant_sku = $current_data['warehouse'] . ';' . $current_data['merchant'] . ';' . $current_data['sku'];
			
			if(isset($replenishment_stock_data[$current_data['sku']])) {
				$current_data['sku_tier'] = $replenishment_stock_data[$current_data['sku']]['sku_tier'];
				$current_data['current_nonpickable_stock'] = number_format($replenishment_stock_data[$current_data['sku']]['current_nonpickable_stock'],0,'.','');
				$current_data['total_stock_on_hand'] = number_format($replenishment_stock_data[$current_data['sku']]['total_stock_on_hand'],0,'.','');
				$current_data['stock_needed_for_service_level'] = number_format($replenishment_stock_data[$current_data['sku']]['stock_needed_for_service_level'],1,'.','');
				$current_data['need_restock'] = $replenishment_stock_data[$current_data['sku']]['need_restock'];
				
				$current_data['row_color'] = 'none';
				if($current_data['need_restock'] == 'Yes') {
					if($current_data['sku_tier'] == 'T1') {
						$current_data['row_color'] = 'red';
					}
					else if($current_data['sku_tier'] == 'T2' || $current_data['sku_tier'] == 'T3') {
						$current_data['row_color'] = 'orange';
					}
					else if($current_data['sku_tier'] == 'T4' || $current_data['sku_tier'] == 'T5') {
						$current_data['row_color'] = 'yellow';
					}
				}
			}
			else {
				$current_data['sku_tier'] = '';
				$current_data['current_nonpickable_stock'] = '';
				$current_data['total_stock_on_hand'] = '';
				$current_data['stock_needed_for_service_level'] = '';
				$current_data['need_restock'] = '';
				$current_data['row_color'] = 'none';
			}
			
			if(!in_array($warehouse_merchant_sku, $unique_warehouse_merchant_skus)) {
				$unique_warehouse_merchant_skus[] = $warehouse_merchant_sku;

				if(in_array($current_data['sku'], $new_and_in_processing_skus)) {
					$live_drops_data[] = $current_data;
				}
			}
		}
		
		return $live_drops_data;
	}
	
	public function get_live_drops_board_data($data) {
		if(!empty($data['facility'])) {
			$facility_data_tmp = $this->db
				->select('*')
				->from('facilities')
				->where('data_status',DATA_ACTIVE)
				->where('id',$data['facility'])
				->get()->result_array();
			$facility_data = $facility_data_tmp[0];
			$data['facility_name'] = strtoupper($facility_data['facility_name']);
		}

		$timezone = isset($facility_data['timezone']) ? $facility_data['timezone'] : -5; // Default is Island River facility timezone
		
		$data['live_drops_data'] = $this->get_live_drops_data($data);
		
		$data['page_generated_time'] = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.gmdate('Y-m-d H:i:s')));
		
		$data['live_drops_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_live_drops_board_visualization', $data, true);
		
		$data['js_live_drops_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/js_view_live_drops_board_visualization', $data, true);
		
		return $data;
	}
	
	public function update_live_drops_logs() {
		$result = array();
		$prod_db = $this->load->database('prod', TRUE);
		
		$prod_db->trans_start();
		
		$facility_data = $prod_db
			->select("id AS facility_id, facility_name, stock_id, IF(timezone=-7,'America/Denver','America/New_York') AS timezone_name")
			->from('facilities')
			->where('data_status', DATA_ACTIVE)
			->get()->result_array();

		$result['message'] = '';
		
		foreach($facility_data as $current_facility) {
			// Island River
			$facility_current_time = (new DateTime($current_facility['timezone_name']))->format('Y-m-d H:i:s');
			$facility_datetime = date('Y-m-d H:00:00', strtotime($facility_current_time));
			
			$last_hour_live_drops_skus_data = $prod_db
				->select('sku')
				->from('live_drops')
				->where('datetime', date('Y-m-d H:i:s', strtotime('-1 hour '.$facility_datetime)))
				->get()->result_array();
			
			$last_hour_live_drops_skus = array();
			if(!empty($last_hour_live_drops_skus_data)) {
				$last_hour_live_drops_skus = array_column($last_hour_live_drops_skus_data, 'sku');
			}
			
			$live_drops_data_raw = $this->get_live_drops_data(array('date'=>date('Y-m-d', strtotime($facility_current_time)), 'facility'=>$current_facility['facility_id'], 'stock_id'=>$current_facility['stock_id']));
			
			$new_drops_count = 0;
			
			$live_drops_data = array();
			foreach($live_drops_data_raw as $current_data) {
				if(!in_array($current_data['sku'], $last_hour_live_drops_skus)) {
					$new_drops_count++;
				}
				
				$live_drops_data[] = array(
					'facility' => $current_facility['facility_id'],
					'datetime' => $facility_datetime,
					'created_time' => $facility_current_time,
					'warehouse' => $current_data['warehouse'],
					'merchant' => $current_data['merchant'],
					'product_name' => $current_data['product_name'],
					'sku' => $current_data['sku'],
					'qty_allocated' => $current_data['qty_allocated'],
					'qty_pickable' => $current_data['qty_pickable'],
					'qty_required' => $current_data['qty_required'],
					'location' => $current_data['location']
				);
			}

			$prod_db
				->where('facility', $current_facility['facility_id'])
				->where('datetime', $facility_datetime)
				->delete('live_drops');
			
			$prod_db->insert_batch('live_drops', $live_drops_data);
			
			$live_drops_count = count($live_drops_data);
			
			$current_live_drops_data = $prod_db
				->select('facility, datetime')
				->from('live_drops_logs')
				->where('facility', $current_facility['facility_id'])
				->where('datetime', $facility_datetime)
				->get()->result_array();
			
			if(!empty($current_live_drops_data)) {
				$prod_db
					->set('live_drops_count', $live_drops_count)
					->set('new_drops_count', $new_drops_count)
					->set('last_modified_time', $facility_current_time)
					->where('facility', $current_facility['facility_id'])
					->where('datetime', $facility_datetime)
					->update('live_drops_logs');
			}
			else {
				$prod_db->insert('live_drops_logs', array(
					'facility' => $current_facility['facility_id'],
					'datetime' => $facility_datetime,
					'live_drops_count' => $live_drops_count,
					'new_drops_count' => $new_drops_count,
					'created_time' => $facility_current_time,
					'last_modified_time' => $facility_current_time
				));
			}
			
			$result['message'] .= $current_facility['facility_name'] . ' ' . date('Y-m-d H:00:00', strtotime($facility_current_time)) . ': ' . $live_drops_count . '<br>';	
		}
		
		$prod_db->trans_complete();
		
		return $result;
	}
	
	public function get_inventory_counts_board_data($data) {
		$prod_db = $this->load->database('prod', TRUE);
		$redstag_db = $this->load->database('redstag', TRUE);
		
		$the_month = date('Y-m', strtotime($data['month']));
		
		$inventory_data = $prod_db
			->select('
				DATE_FORMAT(date, "%Y-%m") AS month,
				facility_name,
				website_code AS client_name,
				inventory_levels.product_id,
				sku,
				null AS length,
				null AS width,
				null AS height,
				SUM(qty_on_hand/DAY(LAST_DAY(DATE))) AS average_daily_inventory_count', false)
			->from('inventory_levels')
			->join('products', 'products.product_id = inventory_levels.product_id')
			->join('facilities', 'facilities.stock_id = inventory_levels.stock_id')
			->where('date >=', $data['month'])
			->where('date <', date('Y-m-01', strtotime('+1 month '.$data['month'])))
			->where('products.is_packing_solution', 0)
			->group_by('facility_name, inventory_levels.product_id, month')
			->order_by('month, facility_name, inventory_levels.product_id')
			->get()->result_array();
			
			
		// Get month-end products dimension
		
		// a. Get current products dimension
		$products_dimension = array();
		$products_dimension_tmp = $redstag_db
			->select('entity_id AS product_id, length, width, height')
			->from('catalog_product_entity')
			->get()->result_array();
		
		$products_dimension = array_combine(
			array_column($products_dimension_tmp, 'product_id'),
			$products_dimension_tmp
		);
		
		// b. Get all product dimension change after the month-end
		$products_dimension_changes = $redstag_db
			->select('product_id, change_subject, REPLACE(catalog_product_history.before_value," in","") AS before_value', false)
			->from('catalog_product_history')
			->where_in('change_subject', array('length','width','height'))
			->where('updated_at >=', date('Y-m-01', strtotime('+1 month '.$data['month'])))
			->order_by('product_history_id', 'desc')
			->get()->result_array();
		
		if(!empty($products_dimension_changes)) {
			foreach($products_dimension_changes as $product_dimension_change) {
				$products_dimension[$product_dimension_change['product_id']][$product_dimension_change['change_subject']] = $product_dimension_change['before_value'];
			}
		}		
		
		if(!empty($inventory_data)) {
			foreach($inventory_data as $key => $current_data) {
				$product_id = $current_data['product_id'];
				$length = isset($products_dimension[$product_id]['length']) ? $products_dimension[$product_id]['length'] : null;
				$width = isset($products_dimension[$product_id]['width']) ? $products_dimension[$product_id]['width'] : null;
				$height = isset($products_dimension[$product_id]['height']) ? $products_dimension[$product_id]['height'] : null;
				
				$inventory_data[$key]['length'] = $length;
				$inventory_data[$key]['width'] = $width;
				$inventory_data[$key]['height'] = $height;
				
				$inventory_data[$key]['total_cubic_ft_of_inventory'] = null;
				
				if(isset($current_data['average_daily_inventory_count']) && isset($length) && isset($width) && isset($height)) {
					$inventory_data[$key]['total_cubic_ft_of_inventory'] = $current_data['average_daily_inventory_count'] * $length * $width * $height / 1728;
				}
			}
		}
		
		$data['inventory_data'] = $inventory_data;

		$data['inventory_counts_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_inventory_counts_board_visualization', $data, true);

		return $data;
	}
	
	public function get_last_month_inventory_counts($the_stock_id) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		ini_set('max_execution_time', 300);
		
		$last_month = date('Y-m-01', strtotime('-1 month'));
		$last_month_yyyy_mm = date('Y-m', strtotime('-1 month'));
		
		$facility_name_by_stock_id = array(
			'2' => 'TYS1A',
			'3' => 'SLC1',
			'4' => 'TYS1B',
			'6' => 'SLC2'
		);
		
		$this->benchmark->mark('code_start');
		
		//-- Find Historical stock movement
		$redstag_db
			->select('stock_id, stock_movement.product_id, DATE(stock_movement.created_at) AS the_date, COALESCE(SUM(qty_putaway),0) + COALESCE(SUM(qty_available),0) + COALESCE(SUM(qty_allocated),0) + COALESCE(SUM(qty_reserved),0) + COALESCE(SUM(qty_processed),0) + COALESCE(SUM(qty_picked),0) AS total_qty_adjusted')
			->from('stock_movement')
			->where('stock_movement.created_at >=', $last_month)
			->group_by('stock_id, stock_movement.product_id, DATE(stock_movement.created_at)')
			->order_by('the_date');
		
		$historical_on_hand_qty_adjustment_tmp = $redstag_db->get()->result_array();
		
		$this->benchmark->mark('after_get_stock_movement');
		
		foreach(array($the_stock_id) as $stock_id) {
			$products_tmp = $redstag_db
				->select('core_website.code AS client_name, cataloginventory_product.product_id, catalog_product_entity.sku, COALESCE(SUM(cataloginventory_stock_item.qty_on_hand),0) AS total_qty_on_hand, catalog_product_entity.length, catalog_product_entity.width, catalog_product_entity.height')
				->from('cataloginventory_stock_item')
				->join('cataloginventory_product', 'cataloginventory_product.product_id = cataloginventory_stock_item.product_id')
				->join('catalog_product_entity', 'catalog_product_entity.entity_id = cataloginventory_product.product_id')
				->join('core_website', 'core_website.website_id = catalog_product_entity.website_id')
				//->where('cataloginventory_product.product_id >=',50001)
				//->where('cataloginventory_product.product_id <=',60000)
				//->where('cataloginventory_product.product_id >=',$product_id_start)
				//->where('cataloginventory_product.product_id <=',$product_id_end)
				->where('cataloginventory_stock_item.stock_id', $stock_id)
				->group_by('core_website.code, cataloginventory_product.product_id, catalog_product_entity.sku, catalog_product_entity.length, catalog_product_entity.width, catalog_product_entity.height')
				->get()->result_array();
			
			$this->benchmark->mark('after_get_products');
			
			$product_ids = array();
			$products = array();
			if(!empty($products_tmp)) {
				$product_ids = array_column($products_tmp, 'product_id');
				
				foreach($products_tmp as $product) {
					$products[$product['product_id']] = $product;
				}
			}
			
			$historical_on_hand_qty_adjustment = array();
			if(!empty($historical_on_hand_qty_adjustment_tmp)) {
				foreach($historical_on_hand_qty_adjustment_tmp as $current_data) {
					if($current_data['stock_id'] == $stock_id) {
						$historical_on_hand_qty_adjustment[$current_data['product_id'].'-'.$current_data['the_date']] = $current_data['total_qty_adjusted'];
					}
				}
			}
			
			$inventory_data = array();
			foreach($product_ids as $product_id) {
				$daily_on_hand_qty_this_month = array();
				$daily_on_hand_qty_this_month[] = $products[$product_id]['total_qty_on_hand'];
				
				$the_date = date('Y-m-d', strtotime('yesterday'));
				$this_day_on_hand_qty = $products[$product_id]['total_qty_on_hand'];
				
				while(strtotime($the_date) >= strtotime($last_month)) {
					$the_following_date = date('Y-m-d', strtotime('+1 day '.$the_date));
					
					if(!empty($historical_on_hand_qty_adjustment[$product_id.'-'.$the_following_date])) {
						$this_day_on_hand_qty -= $historical_on_hand_qty_adjustment[$product_id.'-'.$the_following_date];
					}
					
					if(date('m', strtotime($the_date)) <> date('m', strtotime('+1 day '.$the_date))) {
						// End of the month
						$daily_on_hand_qty_this_month = array();
					}
					
					$daily_on_hand_qty_this_month[] = $this_day_on_hand_qty;
					
					if(date('j', strtotime($the_date)) == '1') {
						$month = date('Y-m', strtotime($the_date));
						
						if($month == $last_month_yyyy_mm) {
							$average_daily_inventory_count = array_sum($daily_on_hand_qty_this_month)/count($daily_on_hand_qty_this_month);
							
							$inventory_data[] = array(
								$facility_name_by_stock_id[$stock_id],
								$month,
								$products[$product_id]['client_name'],
								$product_id,
								$products[$product_id]['sku'],
								$products[$product_id]['length'],
								$products[$product_id]['width'],
								$products[$product_id]['height'],
								$average_daily_inventory_count,
								($products[$product_id]['length'] * $products[$product_id]['width'] * $products[$product_id]['height'] / (12*12*12)) * $average_daily_inventory_count
							);
						}
					}
					
					$the_date = date('Y-m-d', strtotime('-1 day '.$the_date));
				}
			}
			
			$this->benchmark->mark('after_finish '.$stock_id);
		}
		
		$marks = array('after_get_products', 'after_get_stock_movement', 'after_finish');
		foreach($marks as $mark) {
			$elapsed = $this->benchmark->elapsed_time('code_start', $mark);
			debug_var($elapsed, $mark);
		}
		
		return $inventory_data;
	}
	
	public function get_drops_count_board_data($data) {
		$prod_db = $this->load->database('prod', TRUE);
		
		$result = array();
		
		$result['total_new_drops_count'] = array();
		
		$date_format = 'DATE(datetime)';
		switch($data['periodicity']) {
			case 'weekly':
				$date_format = 'DATE_ADD(DATE(datetime), INTERVAL - WEEKDAY(DATE(datetime)) DAY)';
				break;
			case 'monthly':
				$date_format = 'DATE_FORMAT(datetime, "%Y-%m-01")';
				break;
			case 'yearly':
				$date_format = 'DATE_FORMAT(datetime, "%Y-01-01")';
				break;
			case 'hourly':
				$date_format = 'DATE_FORMAT(datetime, "%Y-%m-%d %k:00:00")';
				break;
			case 'daily':
			default:
				$date_format = 'DATE_FORMAT(datetime, "%Y-%m-%d")';
		}
		
		$prod_db
			->select($date_format.' AS the_date, SUM(new_drops_count) AS drops_count')
			->from('live_drops_logs')
			->where('datetime >=', $data['period_from'])
			->where('datetime <', date('Y-m-d', strtotime('+1 day '.$data['period_to'])))
			->group_by($date_format)
			->order_by($date_format);
		
		if(!empty($data['facility'])) {
			$prod_db->where('facility', $data['facility']);
		}
		
		$raw_drops_count_data = $prod_db->get()->result_array();
		
		$raw_drops_count_by_date = array_combine(
			array_column($raw_drops_count_data, 'the_date'),
			array_column($raw_drops_count_data, 'drops_count')
		);
		
		$drops_count_data = array();
		if(!empty($raw_drops_count_data)) {
			$date = date('Y-m-d H:i:s', strtotime($raw_drops_count_data[0]['the_date']));
			
			$period_label = array(
				'hourly' => 'hour',
				'daily' => 'day',
				'weekly' => 'week',
				'monthly' => 'month',
				'yearly' => 'year'
			);
			
			while(strtotime($date) < strtotime('+1 day '.$data['period_to'])) {
				$the_date = ($data['periodicity'] == 'hourly') ? $date : substr($date,0,10);
				
				$label = $the_date;
				switch($data['periodicity']) {
					case 'weekly':
						$label = 'Week '.$date;
						break;
					case 'monthly':
						$label = date('Y-m', strtotime($date));
						break;
					case 'yearly':
						$label = date('Y', strtotime($date));
						break;
					case 'hourly':
						$label = date('Y-m-d H:i:s', strtotime($date));
						break;
					case 'daily':
					default:
						$label = date('Y-m-d', strtotime($date));
				}
				
				$drops_count_data[$the_date] = array(
					'timestamp' => strtotime($date) * 1000,
					'label' => $label,
					'drops_count' => 0
				);
				
				if(!empty($raw_drops_count_by_date[$the_date])) {
					$drops_count_data[$the_date]['drops_count'] = $raw_drops_count_by_date[$the_date];
				}
				
				$date = date('Y-m-d H:i:s', strtotime('+1 '.$period_label[$data['periodicity']].' '.$date));
			}
		}
		
		$data['drops_count_data'] = $drops_count_data;
		
		$data['drops_count_data_for_chart'] = array();
		if(!empty($drops_count_data)) {
			foreach($drops_count_data as $the_date => $current_data) {
				$data['drops_count_data_for_chart'][] = array(
					'x' => $current_data['timestamp'],
					'y' => $current_data['drops_count']
				);
			}
		}
		
		$data['drops_count_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/view_drops_count_board_visualization', $data, true);
		$data['js_drops_count_board_visualization_html'] = $this->load->view(PROJECT_CODE.'/js_view_drops_count_board_visualization', $data, true);
		
		return $data;
	}
	
	public function update_inventory_levels_table($date) {
		$result = array();
		$redstag_db = $this->load->database('redstag', TRUE);
		$prod_db = $this->load->database('prod', TRUE);
		
		$base_inventory_levels = array();
		
		if(empty($date)) {
			$date = date('Y-m-d', strtotime('yesterday'));
		}
		
		$next_day_date = date('Y-m-d', strtotime('+1 day '.$date));
		$prev_day_date = date('Y-m-d', strtotime('-1 day '.$date));
		
		/*if(strtotime($date) < strtotime('2020-12-01')) {
			$result['success'] = false;
			$result['message'] = 'No accurate data prior Dec 2020.';
			return $result;
		}*/
		
		// If the date is today or in the future, do nothing
		if(strtotime($date) >= strtotime(date('Y-m-d'))) {
			$result['success'] = false;
			$result['message'] = 'Error. The date cannot be today or after.';
			return $result;
		}
		
		// If the date is yesterday, get today's data
		if(strtotime($date) == strtotime('yesterday')) {
			$next_day_inventory_tmp = $redstag_db
				->select('cataloginventory_stock_item.product_id, cataloginventory_stock_item.stock_id, COALESCE(SUM(cataloginventory_stock_item.qty_on_hand),0) AS qty_on_hand')
				->from('cataloginventory_stock_item')
				->where_not_in('stock_id', array(1,5))
				->group_by('cataloginventory_stock_item.product_id, cataloginventory_stock_item.stock_id')
				->get()->result_array();
		}
		else {
			// Check if the next day data is exist
			$next_day_products_tmp = $prod_db
				->select('*')
				->from('inventory_levels')
				->where('date', $next_day_date)
				->limit(1)
				->get()->result_array();
			
			if(empty($next_day_products_tmp)) {
				// If next day data is not exist, get next day data first...
				$this->update_inventory_levels_table($next_day_date);
				$this->update_inventory_levels_table($date);
			}
			
			$next_day_inventory_tmp = $prod_db
				->select('product_id, stock_id, qty_on_hand, daily_length, daily_width, daily_height')
				->from('inventory_levels')
				->where('date', $next_day_date)
				->get()->result_array();
		}
		
		// Current products
		$products_tmp = $redstag_db
			->select('entity_id AS product_id, length, width, height')
			->from('catalog_product_entity')
			->get()->result_array();
			
		$products = array_combine(
			array_column($products_tmp, 'product_id'),
			$products_tmp
		);
		
		$dimension_changes_tmp = $redstag_db
			->query(
				'SELECT catalog_product_history.product_id, catalog_product_history.change_subject, REPLACE(catalog_product_history.before_value," in","") AS the_value, catalog_product_history.updated_at
				FROM catalog_product_history
				JOIN (
				   SELECT MIN(updated_at) AS min_updated_at, product_id, change_subject
				   FROM catalog_product_history
				   WHERE change_subject IN ?
				   AND catalog_product_history.updated_at >= ?
				   GROUP BY product_id, change_subject
				) min_history on catalog_product_history.updated_at = min_history.min_updated_at 
				   AND catalog_product_history.product_id = min_history.product_id
				   AND catalog_product_history.change_subject = min_history.change_subject
				WHERE catalog_product_history.change_subject IN ?
				AND catalog_product_history.updated_at >= ?
				ORDER BY catalog_product_history.product_id, catalog_product_history.change_subject',
				array(
					array('length','width','height'),
					$next_day_date,
					array('length','width','height'),
					$next_day_date
				)
			)->result_array();
		
		$dimension_changes = array();
		if(!empty($dimension_changes_tmp)) {
			foreach($dimension_changes_tmp as $current_data) {
				$product_id = $current_data['product_id'];
				$metric = $current_data['change_subject'];
				
				$products[$product_id][$metric] = $current_data['the_value'];
			}
		}
		
		$next_day_inventory = array();
		foreach($next_day_inventory_tmp as $current_data) {
			$stock_id = $current_data['stock_id'];
			$product_id = $current_data['product_id'];
			$next_day_inventory[$stock_id.'-'.$product_id] = $current_data;
		}
		
		// Get the adjustment made
		$adjustments = $redstag_db
			->select('stock_id, stock_movement.product_id, COALESCE(SUM(qty_putaway),0) + COALESCE(SUM(qty_available),0) + COALESCE(SUM(qty_allocated),0) + COALESCE(SUM(qty_reserved),0) + COALESCE(SUM(qty_processed),0) + COALESCE(SUM(qty_picked),0) AS total_qty_adjusted')
			->from('stock_movement')
			->where('stock_movement.created_at >=', $next_day_date)
			->where('stock_movement.created_at <', date('Y-m-d', strtotime('+1 day '.$next_day_date)))
			->group_by('stock_id, stock_movement.product_id')
			->get()->result_array();

		
		if(!empty($adjustments)) {
			foreach($adjustments as $current_data) {
				if(empty($products[$product_id])) continue;
				
				$stock_id = $current_data['stock_id'];
				$product_id = $current_data['product_id'];
				
				if(empty($next_day_inventory[$stock_id.'-'.$product_id])) {
					$next_day_inventory[$stock_id.'-'.$product_id] = array(
						'stock_id' => $stock_id,
						'product_id' => $product_id,
						'qty_on_hand' => 0
					);
				}
				
				$next_day_inventory[$stock_id.'-'.$product_id]['qty_on_hand'] = $next_day_inventory[$stock_id.'-'.$product_id]['qty_on_hand'] - $current_data['total_qty_adjusted'];
				
				$next_day_inventory[$stock_id.'-'.$product_id]['date'] = $date;
			}
		}
		
		$inventory_data = array();
		foreach($next_day_inventory as $current_data) {
			if($current_data['qty_on_hand'] > 0) {
				$current_data['date'] = $date;
				$current_data['daily_length'] = $products[$current_data['product_id']]['length'];
				$current_data['daily_width'] = $products[$current_data['product_id']]['width'];
				$current_data['daily_height'] = $products[$current_data['product_id']]['height'];

				$inventory_data[] = $current_data;
			}
		}
		
		$prod_db->trans_start();
		
		$prod_db->where('date', $date)->delete('inventory_levels');
		$prod_db->insert_batch('inventory_levels', $inventory_data);
		
		/*$prod_db->query(
			'UPDATE inventory_levels
			 JOIN
			    (SELECT inventory_levels.product_id,
			            inventory_levels.daily_length,
			            inventory_levels.daily_width,
			            inventory_levels.daily_height
			       FROM inventory_levels
			       JOIN (
			          SELECT product_id, MAX(DATE) AS latest_date
			            FROM inventory_levels
			           WHERE date >= ?
			             AND date < ?
			        GROUP BY product_id
			       ) end_of_month_data ON end_of_month_data.product_id = inventory_levels.product_id
			                          AND end_of_month_data.latest_date = inventory_levels.date
				) latest_dimension ON latest_dimension.product_id = inventory_levels.product_id
			SET inventory_levels.monthly_length = latest_dimension.daily_length,
			    inventory_levels.monthly_width = latest_dimension.daily_width,
			    inventory_levels.monthly_height = latest_dimension.daily_height
			WHERE inventory_levels.date >= ?
			AND inventory_levels.date < ?',
			array(
				date('Y-m-01', strtotime($date)),
				date('Y-m-01', strtotime('+1 month '.$date)),
				date('Y-m-01', strtotime($date)),
				date('Y-m-01', strtotime('+1 month '.$date))
			)
		);*/
		
		$prod_db->trans_complete();
		
		$result['success'] = true;
		$result['message'] = 'Success';
		
		$prev_day_products_tmp = $prod_db
				->select('*')
				->from('inventory_levels')
				->where('date', $prev_day_date)
				->limit(1)
				->get()->result_array();
			
		if(empty($prev_day_products_tmp)) {
			$this->update_inventory_levels_table($prev_day_date);
		}
		
		return $result;
	}
	
	public function update_products_table() {
		$result = array();
		$redstag_db = $this->load->database('redstag', TRUE);
		$prod_db = $this->load->database('prod', TRUE);
		
		$products = $redstag_db
			->select('entity_id AS product_id, sku, catalog_product_entity.name, catalog_product_entity.updated_at, core_website.website_id, core_website.code AS website_code, length, width, height, 0 AS is_packing_solution')
			->from('catalog_product_entity')
			->join('core_website', 'core_website.website_id = catalog_product_entity.website_id')
			->get()->result_array();
			
		// Get the packing solution
		$packing_solution_products = $redstag_db
			->distinct()
			->select('product_id')
			->from('packing_solution_container')
			->get()->result_array();
		
		$packing_solution_product_ids = array_column($packing_solution_products, 'product_id');
		
		foreach($products as $key => $current_data) {
			if(in_array($current_data['product_id'], $packing_solution_product_ids)) {
				$products[$key]['is_packing_solution'] = 1;
			}
		}
		
		$prod_db->trans_start();
		
		$prod_db->where('product_id >=', 1)->delete('products');
		$prod_db->insert_batch('products', $products);
		
		$prod_db->trans_complete();
	}
	
	// $args : month [e.g. 2021-07-01], milestone (or days_backward) = 180
	public function get_long_term_inventory_report($args) {
		$result = $args;
		$redstag_db = $this->load->database('redstag', TRUE);
		$prod_db = $this->load->database('prod', TRUE);
		
		$days_backward = !empty($args['milestone']) ? $args['milestone'] : 180;
		
		if(!empty($args['report_type']) && !in_array($args['report_type'], array('detail', 'summary'))) {
			$args['report_type'] = 'summary';
		}
		$report_type = !empty($args['report_type']) ? $args['report_type'] : 'summary';
		
		$current_date = date('Y-m-01', strtotime($args['month']));
		$days_in_month = date('t', strtotime($args['month']));
		
		// Make sure the long term inventory levels data is complete
		$existing_dates = $prod_db
			->select('date')
			->from('long_term_inventory_levels')
			->where('days_backward', $days_backward)
			->where('date >=', $current_date)
			->where('date <', date('Y-m-d', strtotime('+1 month '.$current_date)))
			->group_by('date')
			->get()->result_array();
			
		if(count($existing_dates) <> $days_in_month) {
			$result['success'] = false;
			$result['error_message'] = 'Data in long_term_inventory_levels table is not complete';
			return $result;
		}
		
		$long_term_inventory = $prod_db
			->select('product_id, sku, website_code AS client_name')
			->from('products')
			->where('is_packing_solution', false)
			->get()->result_array();
		
		$long_term_inventory = array_combine( array_column($long_term_inventory, 'product_id'), $long_term_inventory );
		foreach($long_term_inventory as $key => $current_data) {
			$long_term_inventory[$key]['data'] = array();
			$long_term_inventory[$key]['qty'] = 0;
		}
		
		$inventory_data = $prod_db
			->select('products.product_id, long_term_inventory_qty')
			->from('long_term_inventory_levels')
			->join('products', 'products.product_id = long_term_inventory_levels.product_id')
			->where('products.is_packing_solution', false)
			->where('days_backward', $days_backward)
			->where('date >=', $current_date)
			->where('date <', date('Y-m-d', strtotime('+1 month '.$current_date)))
			->get()->result_array();
		
		foreach($inventory_data as $current_data) {
			$product_id = $current_data['product_id'];
			$long_term_inventory[$product_id]['qty'] += $current_data['long_term_inventory_qty'];
		}
		
		foreach($long_term_inventory as $key => $current_data) {
			$long_term_inventory[$key]['qty'] /= $days_in_month;
		}
		
		// Get month-end products dimension
		
		// a. Get current products dimension
		$products_dimension = $redstag_db
			->select('entity_id AS product_id, length, width, height')
			->from('catalog_product_entity')
			->get()->result_array();
		
		$products_dimension = array_combine(
			array_column($products_dimension, 'product_id'),
			$products_dimension
		);
		
		// b. Get all product dimension change after the month-end
		$products_dimension_changes = $redstag_db
			->select('product_id, change_subject, REPLACE(catalog_product_history.before_value," in","") AS before_value', false)
			->from('catalog_product_history')
			->where_in('change_subject', array('length','width','height'))
			->where('updated_at >=', date('Y-m-01', strtotime('+1 month '.$args['month'])))
			->order_by('product_history_id', 'desc')
			->get()->result_array();
		
		if(!empty($products_dimension_changes)) {
			foreach($products_dimension_changes as $product_dimension_change) {
				$products_dimension[$product_dimension_change['product_id']][$product_dimension_change['change_subject']] = $product_dimension_change['before_value'];
			}
		}
		
		foreach($long_term_inventory as $key => $current_data) {
			foreach(array('length','width','height') as $dimension_type) {
				$long_term_inventory[$key][$dimension_type] = $products_dimension[$current_data['product_id']][$dimension_type];
			}
		}
		
		$result['success'] = true;
		$result['long_term_inventory'] = $long_term_inventory;
		
		return $result;
	}
	
	public function update_long_term_inventory_levels_table($date, $days_backward = 180) {
		$result = array();
		$redstag_db = $this->load->database('redstag', TRUE);
		$prod_db = $this->load->database('prod', TRUE);
		
		$start_date = date('Y-m-d', strtotime('-'.($days_backward+1).' day '.$date));
		
		// Find out the starting inventory
		$long_term_inventory = $prod_db
			->select('product_id, SUM(qty_on_hand) AS starting_inventory_qty')
			->from('inventory_levels')
			->where('date', $start_date)
			->group_by('product_id')
			->get()->result_array();
		
		// Find out sold qty
		$sold = $redstag_db
			->select('sales_flat_shipment_package_item.product_id, SUM(sales_flat_shipment_package_item.qty) AS total')
			->from('sales_flat_shipment_package_item')
			->join('sales_flat_shipment_package', 'sales_flat_shipment_package.package_id = sales_flat_shipment_package_item.package_id')
			->where('sales_flat_shipment_package.created_at >=', $start_date)
			->where('sales_flat_shipment_package.created_at <', $date)
			->group_by('product_id')
			->get()->result_array();
		
		$sold = array_combine( array_column($sold, 'product_id'), $sold );
		
		// Find out long term inventory qty
		foreach($long_term_inventory as $key => $current_data) {
			$product_id = $current_data['product_id'];
			
			$sold_qty = !empty($sold[$product_id]) ? $sold[$product_id]['total'] : 0;
			
			$long_term_inventory_qty = $current_data['starting_inventory_qty'] - $sold_qty;
			if($long_term_inventory_qty < 0) $long_term_inventory_qty = 0;
			
			$long_term_inventory[$key]['days_backward'] = $days_backward;
			$long_term_inventory[$key]['date'] = $date;
			$long_term_inventory[$key]['start_date'] = $start_date;
			$long_term_inventory[$key]['sold_qty'] = $sold_qty;
			$long_term_inventory[$key]['long_term_inventory_qty'] = $long_term_inventory_qty;
		}

		// Update the table
		$prod_db->trans_start();
		
		$prod_db
			->where('days_backward', $days_backward)
			->where('date', $date)
			->delete('long_term_inventory_levels');
		
		$prod_db->where('date', $date)->where('days_backward', $days_backward)->delete('long_term_inventory_levels');
		
		$prod_db->insert_batch('long_term_inventory_levels', $long_term_inventory);
		$prod_db->trans_complete();
		
		$result['success'] = true;
		
		return $result;
	}
}