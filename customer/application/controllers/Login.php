<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {
	public function __construct(){
        parent::__construct();
        $this->load->model('model_user');
    }

	public function index() {
		$body_data = array();
		$body_data['page_title'] = generate_page_title(ucwords(lang('word__login')));
		
		$this->load->view('view_login', $body_data);
	}
	
	public function process_login() {
		$result = array();
		$result['login_success'] = false;
		$result['error_message'] = '';
		
		$logged_in_user_data = $this->model_user->do_login();
		
		if(!empty($logged_in_user_data)) {
			$result['login_success'] = true;
			
			$login_session_data = array(
				'chchdb_'.PROJECT_CODE.'_user_id'  	=> $logged_in_user_data['id'],
				'chchdb_'.PROJECT_CODE.'_user_name'	=> $logged_in_user_data['user_name'],
				'chchdb_'.PROJECT_CODE.'_user_role'	=> $logged_in_user_data['user_role'],
				'chchdb_'.PROJECT_CODE.'_user_group' => $logged_in_user_data['user_group'],
				'chchdb_'.PROJECT_CODE.'_user_level' => $logged_in_user_data['user_level'],
				'chchdb_'.PROJECT_CODE.'_logged_in'=> TRUE
			);

			$this->session->set_userdata($login_session_data);
		}
		else {
			$result['error_message'] = lang('error_message__invalid_login');
		}
		
		echo json_encode($result);
	}
}
