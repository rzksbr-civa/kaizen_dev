<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_user extends CI_Model {
	public function __construct()	{
		$this->load->database(); 
	}
	
	public function do_login() {
		$query = $this->db->get_where(USER_TABLE, 
			array(
				'user_name' => $this->input->post('username'),
				'data_status' => DATA_ACTIVE
			)
		);
		
		$result = $query->result_array();
		
		// If user with the given username is found...
		if(!empty($result) && password_verify($this->input->post('password'), $result[0]['password'])) {
			$user_data = $result[0];
			
			$users_capabilities_config = config_item('user_capabilities');
			if(isset($users_capabilities_config[$user_data['user_role']]['user_level'])) {
				$user_data['user_level'] = $users_capabilities_config[$user_data['user_role']]['user_level'];
			}
			else {
				$user_data['user_level'] = 0;
			}
	
			return $user_data;
		}
		else {
			return false;
		}
	}
	
	public function verify_user_password($user_id, $password) {
		$result = array();
		$result['success'] = true;
		$result['error_message'] = '';
		$result['result'] = false;
		
		$query = $this->db->get_where(USER_TABLE, 
			array(
				'id' => $user_id,
				'data_status' => DATA_ACTIVE
			)
		);
		
		$result = $query->result_array();
		
		// If user with the given username is found...
		if(!empty($result) && password_verify($password, $result[0]['password'])) {
			$result['result'] = true;
		}
		else {
			$result['result'] = false;
		}
		
		return $result;
	}
	
	public function change_user_password($user_id, $new_password) {
		$result = array();
		$result['success'] = false;
		$result['error_message'] = '';
		$result['result'] = '';
		
		$now = date('Y-m-d H:i:s');

		$this->db->set('password', password_hash($new_password, PASSWORD_DEFAULT));
		$this->db->set('last_modified_time', $now);
		$this->db->set('last_modified_user', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_id'));
		$this->db->where('id', $user_id);
		$this->db->update('users');
		
		$verify_new_password = $this->verify_user_password($user_id, $new_password);
		if($verify_new_password['result'] === true) {
			$result['success'] = true;
			$result['result'] = 'success';
		}
		else {
			$result['success'] = false;
			$result['result'] = 'failed';
		}
		
		return $result;
	}
	
	public function get_user_name_by_user_id($user_id) {
		$this->db->select('user_name');
		$this->db->from('users');
		$this->db->where('data_status', DATA_ACTIVE);
		$this->db->where('(user_group = ' . $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group') . ' OR user_group = 0)');
		
		$this->db->where('id', $user_id);
		
		$query = $this->db->get();
		$query_result = $query->result_array();
		
		if(count($query_result) === 1) {
			return $query_result[0]['user_name'];
		}
		else {
			return null;
		}
	}
	
	public function add_user($user_data) {
		$result = array();
		$result['success'] = false;
		$result['error_message'] = '';
		$result['result'] = '';
		
		$now = date('Y-m-d H:i:s');
		
		$user_data['password'] = password_hash($user_data['password'], PASSWORD_DEFAULT);
		
		$this->db->insert('users', $user_data);
		
		return $result;
	}
	
	
	function get_user_capability_item($item_name) {
		$user_capabilites = config_item('user_capabilities');
		if(array_key_exists($item_name, $user_capabilites[$this->session->userdata('chchdb_'.PROJECT_CODE.'_user_role')])) {
			return $user_capabilites[$this->session->userdata('chchdb_'.PROJECT_CODE.'_user_role')][$item_name];
		}
		else {
			return null;
		}
	}
	
	function get_user_group_name() {
		$user_group_info = $this->db
			->select('user_group_name')
			->from('user_groups')
			->where('user_groups.data_status', 'active')
			->where('id', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->get()->result_array();
		
		if(empty($user_group_info)) {
			return null;
		}
		
		return $user_group_info[0]['user_group_name'];
	}
}