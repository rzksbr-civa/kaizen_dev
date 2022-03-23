<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class CHCHDB_Controller extends CI_Controller {	
	public function __construct() {
		parent::__construct();
		if($this->session->userdata('chchdb_'.PROJECT_CODE.'_logged_in') !== TRUE) {
			redirect('login');
			exit;
		}
	}
	
	public function _show_404_page() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title(lang('title__page_not_found'));
		
		$this->load->view('view_header', $header_data);		
		$this->load->view('view_404');
		$this->load->view('view_footer');
	}
	
	public function _show_access_denied_page() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title(lang('title__access_denied'));
		
		$this->load->view('view_header', $header_data);		
		$this->load->view('view_access_denied_page');
		$this->load->view('view_footer');
	}
}