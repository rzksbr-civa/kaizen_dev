<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Import_magento_data extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Import Magento Data');

		$data = array();
		
		$data['grid_type_list'] = array(
			'action_log' => 'Action Log',
			'staff_time_log' => 'Staff Time Log',
			'users' => 'User',
			'shipment' => 'Shipment'
		);

		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['grid_type'] = isset($_GET['grid_type']) ? $_GET['grid_type'] : null;
		$data['period_from'] = !empty($_GET['period_from']) ? $_GET['period_from'] : null;
		$data['period_to'] = !empty($_GET['period_to']) ? $_GET['period_to'] : $data['period_from'];
		
		$data['message'] = '';
		
		if($data['generate']) {
			$row = 1;
			
			$this->db->trans_start();
			
			$grid_filter = 'started_at[from]='.date('m', strtotime($data['period_from'])).'/'.date('j', strtotime($data['period_from'])).'/'.date('Y', strtotime($data['period_from'])).'&started_at[to]='.date('m', strtotime($data['period_to'])).'/'.date('j', strtotime($data['period_to'])).'/'.date('Y', strtotime($data['period_to'])).'&started_at[locale]=en_US&finished_at[locale]=en_US';
			
			$grid_filter = base64_encode(str_replace(array('%3D', '%26'), array('=','&'), urlencode($grid_filter)));
			
			// Import Action Log
			
			$action_log_api_url = 'https://redstagfulfillment.com/backend/automationv1.php?action=grid&auth_key=4CJeQNbMeiWuH7D782xmctOgbZBwPT4e&grid_type=action_log&grid_format=csv&grid_filter='.$grid_filter;
			
			$now = date('Y-m-d H:i:s');
			$user_group = $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group');
			$user_id = $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_id');
			
			$data_to_import = array();
			if (($handle = fopen($action_log_api_url, "r")) !== FALSE) {
				while (($csv_data = fgetcsv($handle, 1000, ",")) !== FALSE) {
					if($row > 1) {
						$data_to_import[] = array(
							'warehouse' => $csv_data[0],
							'type' => $csv_data[1],
							'action' => $csv_data[2],
							'entity_id' => $csv_data[3],
							'user' => $csv_data[4],
							'start' => $csv_data[5],
							'end' => $csv_data[6],
							'duration' => (strtotime($csv_data[6]) - strtotime($csv_data[5])),
							'order_id' => $csv_data[8],
							'data_status' => 'active',
							'data_group' => $user_group,
							'created_time' => $now,
							'created_user' => $user_id,
							'last_modified_time' => $now,
							'last_modified_user' => $user_id
						);
					}

					$row++;
				}
				fclose($handle);
			}

			if(count($data_to_import) > 0) {
				$this->db->insert_batch('action_logs', $data_to_import);
			}
			
			$row = 1;
			
			$staff_time_log_api_url = 'https://redstagfulfillment.com/backend/automationv1.php?action=grid&auth_key=4CJeQNbMeiWuH7D782xmctOgbZBwPT4e&grid_type=staff_time_log&grid_format=csv&grid_filter='.$grid_filter;
			
			$data_to_import = array();
			if (($handle = fopen($staff_time_log_api_url, "r")) !== FALSE) {
				while (($csv_data = fgetcsv($handle, 1000, ",")) !== FALSE) {
					if($row > 1) {
						$data_to_import[] = array(
							'warehouse' => $csv_data[0],
							'status' => $csv_data[1],
							'user' => $csv_data[2],
							'edited_by' => $csv_data[3],
							'start' => $csv_data[4],
							'end' => $csv_data[5],
							'duration' => (strtotime($csv_data[5]) - strtotime($csv_data[4])),
							'data_status' => 'active',
							'data_group' => $user_group,
							'created_time' => $now,
							'created_user' => $user_id,
							'last_modified_time' => $now,
							'last_modified_user' => $user_id
						);
					}

					$row++;
				}
				fclose($handle);
			}

			if(count($data_to_import) > 0) {
				$this->db->insert_batch('staff_time_logs', $data_to_import);
			}
			
			$this->db->trans_complete();
			
			$data['message'] = count($data_to_import) . ' data successfully imported';
		}
		
		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_import_magento_data', $data);
		$this->load->view('view_footer');
	}
}
