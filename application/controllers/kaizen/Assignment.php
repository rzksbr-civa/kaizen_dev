<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Assignment extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model(PROJECT_CODE.'/model_assignment');
    }
	
	public function index() {
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Assignment');

		$data = array();
		
		$data['facility_list'] = $this->model_db_crud->get_data(
			'facilities', 
			array(
				'select' => array('id', 'facility_name'),
				'order_by' => array('facility_name' => 'asc')
			)
		);
		
		$data['department_list'] = $this->model_db_crud->get_data(
			'departments', 
			array(
				'select' => array('id', 'department_name'),
				'order_by' => array('department_name' => 'asc')
			)
		);
		
		$data['assignment_type_list'] = $this->model_db_crud->get_data(
			'assignment_types', 
			array(
				'select' => array('id', 'assignment_type_name'),
				'order_by' => array('id' => 'asc')
			)
		);
		
		$data['employee_shift_type_list'] = $this->model_db_crud->get_data(
			'employee_shift_types', 
			array(
				'select' => array('id', 'employee_shift_type_name'),
				'order_by' => array('id' => 'asc')
			)
		);
		
		$data['generate'] = isset($_GET['generate']) ? $_GET['generate'] : false;
		$data['facility'] = isset($_GET['facility']) ? $_GET['facility'] : null;
		$data['department'] = isset($_GET['department']) ? $_GET['department'] : null;
		$data['employee_shift'] = isset($_GET['employee_shift']) ? $_GET['employee_shift'] : array();
		$data['date'] = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
		
		if($data['generate']) {
			$assignment_types = $this->model_db_crud->get_several_data('assignment_type');
			$data['assignment_types'] = array();
			foreach($assignment_types as $assignment_type) {
				$data['assignment_types'][$assignment_type['id']] = $assignment_type['assignment_type_name'];
			}
			
			$args = array(
				'date' => $data['date'],
				'facility' => $data['facility'],
				'department' => $data['department'],
				'employee_shift' => $data['employee_shift']
			);
			
			$data['employee_assignments'] = $this->model_assignment->get_employee_assignment_data($args);
		}
		
		$footer_data = array();
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/js_view_assignment', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/view_assignment', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
