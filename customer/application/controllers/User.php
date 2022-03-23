<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
    }
	
	public function index() {
		$this->_show_404_page();
	}
	
	public function settings() {
		$header_data = array();
		$body_data = array();
		$footer_data = array();
		
		// Set page title
		$header_data['page_title'] = generate_page_title(ucwords(lang('title__change_password')));
		
		$footer_data['js'] = $this->load->view('js_view_user_settings', array(), true);
		
		$this->load->view('view_header', $header_data);
		$this->load->view('view_user_settings', $body_data);
		$this->load->view('view_footer', $footer_data);
	}
}
