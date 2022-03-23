<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_batching_helper extends CI_Model {
	public function __construct() {
		$this->load->database();
	}
	
	public function get_monitoring_data($data) {
		$redstag_db = $this->load->database('redstag', TRUE);
		
		return $data;
	}
}