<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Export extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
		$this->load->model('model_db_crud');
    }
	
	public function index() {
		$this->_show_404_page();
	}
	
	public function excel($type) {
		if($type == 'team_helper') {
			$args = array(
				'generate' => true,
				'facility' => $this->input->get('facility'),
				'department' => $this->input->get('department'),
				'date' => $this->input->get('date')
			);
			
			$this->export_team_helper_data_to_excel($args);
		}
		else if($type == 'shipment_report') {
			$args = array(
				'generate' => true,
				'report_type' => $this->input->get('report_type'),
				'facility' => $this->input->get('facility'),
				'periodicity' => $this->input->get('periodicity'),
				'period_from' => $this->input->get('period_from'),
				'period_to' => $this->input->get('period_to')
			);
			
			$this->export_shipment_report_to_excel($args);
		}
		else if($type == 'inventory_report') {
			$args = array(
				'generate' => true,
				'facility' => $this->input->get('facility')
			);
			
			$this->export_inventory_report_to_excel($args);
		}
		else {
			$this->_show_404_page();
		}
	}
	
	// Feature to export team helper data to excel
	public function export_team_helper_data_to_excel($args) {
		$data = array();

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		
		$title = 'Team Helper Data';
		
		if(!empty($args['facility'])) {
			$title .= ' - ' . $this->model_db_crud->get_specific_data_field('facility', $args['facility'], 'facility_name');
		}
		
		if(!empty($args['department'])) {
			$title .= ' - ' . $this->model_db_crud->get_specific_data_field('department', $args['department'], 'department_name');
		}
		
		if(!empty($args['date'])) {
			$title .= ' - ' . $args['date'];
		}
		
		$spreadsheet->getProperties()
			->setTitle($title)
			->setSubject($title);
			
		$this->load->model(PROJECT_CODE.'/model_team_helper');
		$team_helper_data = $this->model_team_helper->get_team_helper_data($args);
        
		$fields = array(
			'employee_name' => 
				array(
					'col' => 'A',
					'col_width' => 20,
					'title' => 'Employee Name'
				),
			'packing_time' =>  
				array(
					'col' => 'B',
					'col_width' => 15,
					'title' => 'Packing Time',
					'number_format' => 'h:mm:ss'
				),
			'packing_qty' =>  
				array(
					'col' => 'C',
					'col_width' => 10,
					'title' => 'Packing Qty'
				),
			'packing_cost' =>  
				array(
					'col' => 'D',
					'col_width' => 10,
					'title' => 'Packing Cost',
					'number_format' => '"$"#,##0.00_-'
				),
			'picking_time' =>  
				array(
					'col' => 'E',
					'col_width' => 15,
					'title' => 'Picking Time',
					'number_format' => 'h:mm:ss'
				),
			'picking_qty' =>  
				array(
					'col' => 'F',
					'col_width' => 10,
					'title' => 'Picking Qty'
				),
			'picking_cost' =>  
				array(
					'col' => 'G',
					'col_width' => 10,
					'title' => 'Picking Cost',
					'number_format' => '"$"#,##0.00_-'
				),
			'loading_time' =>  
				array(
					'col' => 'H',
					'col_width' => 15,
					'title' => 'Loading Time',
					'number_format' => 'h:mm:ss'
				),
			'loading_qty' =>  
				array(
					'col' => 'I',
					'col_width' => 10,
					'title' => 'Loading Qty'
				),
			'loading_cost' =>  
				array(
					'col' => 'J',
					'col_width' => 10,
					'title' => 'Loading Cost',
					'number_format' => '"$"#,##0.00_-'
				),
			'support_time' =>  
				array(
					'col' => 'K',
					'col_width' => 15,
					'title' => 'Support Time',
					'number_format' => 'h:mm:ss'
				),
			'support_cost' =>  
				array(
					'col' => 'L',
					'col_width' => 10,
					'title' => 'Support Cost',
					'number_format' => '"$"#,##0.00_-'
				),
			'training_time' =>  
				array(
					'col' => 'M',
					'col_width' => 15,
					'title' => 'Training Time',
					'number_format' => 'h:mm:ss'
				),
			'training_cost' =>  
				array(
					'col' => 'N',
					'col_width' => 10,
					'title' => 'Training Cost',
					'number_format' => '"$"#,##0.00_-'
				),
			'planned_time' =>  
				array(
					'col' => 'O',
					'col_width' => 15,
					'title' => 'Planned Time',
					'number_format' => 'h:mm:ss'
				),
			'actual_time' =>  
				array(
					'col' => 'P',
					'col_width' => 15,
					'title' => 'Actual Time',
					'number_format' => 'h:mm:ss'
				),
			'total_value_added_time' =>  
				array(
					'col' => 'Q',
					'col_width' => 15,
					'title' => 'Total (VA) Time',
					'number_format' => 'h:mm:ss'
				),
			'total_non_value_added_time' =>  
				array(
					'col' => 'R',
					'col_width' => 15,
					'title' => 'Total (NVA) Time',
					'number_format' => 'h:mm:ss'
				),
			'total_qty' =>  
				array(
					'col' => 'S',
					'col_width' => 15,
					'title' => 'Total Time',
					'number_format' => 'h:mm:ss'
				),
			'total_value_added_cost' =>  
				array(
					'col' => 'T',
					'col_width' => 10,
					'title' => 'VA Cost',
					'number_format' => '"$"#,##0.00_-'
				),
			'total_non_value_added_cost' =>  
				array(
					'col' => 'U',
					'col_width' => 10,
					'title' => 'NVA Cost',
					'number_format' => '"$"#,##0.00_-'
				),
			'total_cost' =>  
				array(
					'col' => 'V',
					'col_width' => 10,
					'title' => 'Total Cost',
					'number_format' => '"$"#,##0.00_-'
				),
			'productivity_rate' =>  
				array(
					'col' => 'W',
					'col_width' => 10,
					'title' => 'Productivity Rate',
					'number_format' => '0%'
				)
		);
		
		// Set column width
		foreach($fields as $field_name => $field_data) {
			if(!empty($field_data['col_width'])) {
				$sheet->getColumnDimension($field_data['col'])->setWidth($field_data['col_width']);
			}
			
			if(!empty($field_data['number_format'])) {
				$sheet->getStyle($fields[$field_name]['col'])->getNumberFormat()->setFormatCode($field_data['number_format']);
			}
		}

		$current_row = 1;
		
		// Set header row
		foreach($fields as $field_name => $field_data) {
			if(!empty($field_data['title'])) {
				$sheet->setCellValue($field_data['col'].$current_row, $field_data['title']);
			}
		}
		
		foreach($team_helper_data['staff_time_log_summary'] as $employee_name => $summary) {
			$current_row++;
			
			if(isset($summary['productivity_rate'])) {
				if($summary['productivity_rate'] > 100) {
					$sheet->getStyle('A'.$current_row.':W'.$current_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffa500');
				}
				else if($summary['productivity_rate'] >= 85) {
					$sheet->getStyle('A'.$current_row.':W'.$current_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('00ff00');
				}
				else if($summary['productivity_rate'] >= 75) {
					$sheet->getStyle('A'.$current_row.':W'.$current_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ffff00');
				}
				else {
					$sheet->getStyle('A'.$current_row.':W'.$current_row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('ff0000');
					$sheet->getStyle('A'.$current_row.':W'.$current_row)->getFont()->getColor()->setARGB('ffffff');
				}
			}
			
			$sheet->setCellValue($fields['employee_name']['col'].$current_row, $employee_name);
			
			if(isset($summary['sum_of_time_by_status']['Packing'])) {
				$sheet->setCellValue($fields['packing_time']['col'].$current_row, $summary['sum_of_time_by_status']['Packing'] / 86400);
			}
			else {
				$sheet->setCellValue($fields['packing_time']['col'].$current_row, 0);
			}
			
			if(isset($summary['qty_by_status']['Packing'])) {
				$sheet->setCellValue($fields['packing_qty']['col'].$current_row, $summary['qty_by_status']['Packing']);
			}
			
			if(isset($summary['cost_by_status']['Packing'])) {
				$sheet->setCellValue($fields['packing_cost']['col'].$current_row, $summary['cost_by_status']['Packing']);
			}
			
			if(isset($summary['sum_of_time_by_status']['Picking'])) {
				$sheet->setCellValue($fields['picking_time']['col'].$current_row, $summary['sum_of_time_by_status']['Picking'] / 86400);
			}
			else {
				$sheet->setCellValue($fields['picking_time']['col'].$current_row, 0);
			}
			
			if(isset($summary['qty_by_status']['Picking'])) {
				$sheet->setCellValue($fields['picking_qty']['col'].$current_row, $summary['qty_by_status']['Picking']);
			}
			
			if(isset($summary['cost_by_status']['Picking'])) {
				$sheet->setCellValue($fields['picking_cost']['col'].$current_row, $summary['cost_by_status']['Picking']);
			}
			
			if(isset($summary['sum_of_time_by_status']['Loading'])) {
				$sheet->setCellValue($fields['loading_time']['col'].$current_row, $summary['sum_of_time_by_status']['Loading'] / 86400);
			}
			else {
				$sheet->setCellValue($fields['loading_time']['col'].$current_row, 0);
			}
			
			if(isset($summary['qty_by_status']['Loading'])) {
				$sheet->setCellValue($fields['loading_qty']['col'].$current_row, $summary['qty_by_status']['Loading']);
			}
			
			if(isset($summary['cost_by_status']['Loading'])) {
				$sheet->setCellValue($fields['loading_cost']['col'].$current_row, $summary['cost_by_status']['Loading']);
			}
			
			// Support
			if(isset($summary['sum_of_time_by_status']['Support'])) {
				$sheet->setCellValue($fields['support_time']['col'].$current_row, $summary['sum_of_time_by_status']['Support'] / 86400);
			}
			else {
				$sheet->setCellValue($fields['support_time']['col'].$current_row, 0);
			}
			
			if(isset($summary['cost_by_status']['Support'])) {
				$sheet->setCellValue($fields['support_cost']['col'].$current_row, $summary['cost_by_status']['Support']);
			}
			
			// Training
			if(isset($summary['sum_of_time_by_status']['Training'])) {
				$sheet->setCellValue($fields['training_time']['col'].$current_row, $summary['sum_of_time_by_status']['Training'] / 86400);
			}
			else {
				$sheet->setCellValue($fields['training_time']['col'].$current_row, 0);
			}
			
			if(isset($summary['cost_by_status']['Training'])) {
				$sheet->setCellValue($fields['training_cost']['col'].$current_row, $summary['cost_by_status']['Training']);
			}
			
			// Planned Time
			if(isset($summary['planned_time_in_seconds'])) {
				$sheet->setCellValue($fields['planned_time']['col'].$current_row, $summary['planned_time_in_seconds'] / 86400);
			}
			else {
				$sheet->setCellValue($fields['planned_time']['col'].$current_row, 0);
			}
			
			// Actual Time
			if(isset($summary['actual_time_in_seconds'])) {
				$sheet->setCellValue($fields['actual_time']['col'].$current_row, $summary['actual_time_in_seconds'] / 86400);
			}
			else {
				$sheet->setCellValue($fields['actual_time']['col'].$current_row, 0);
			}
			
			// Total (VA) Time
			if(isset($summary['sum_of_time'])) {
				$sheet->setCellValue($fields['total_value_added_time']['col'].$current_row, $summary['sum_of_time'] / 86400);
			}
			else {
				$sheet->setCellValue($fields['total_value_added_time']['col'].$current_row, 0);
			}
			
			// Total (NVA) Time
			if(isset($summary['non_value_added_time_in_seconds'])) {
				$sheet->setCellValue($fields['total_non_value_added_time']['col'].$current_row, $summary['non_value_added_time_in_seconds'] / 86400);
			}
			else {
				$sheet->setCellValue($fields['total_non_value_added_time']['col'].$current_row, 0);
			}
			
			// Total Qty
			if(isset($summary['total_qty'])) {
				$sheet->setCellValue($fields['total_qty']['col'].$current_row, $summary['total_qty']);
			}
			
			// VA Cost
			if(isset($summary['value_added_cost'])) {
				$sheet->setCellValue($fields['total_value_added_cost']['col'].$current_row, $summary['value_added_cost']);
			}
			
			// NVA Cost
			if(isset($summary['non_value_added_cost'])) {
				$sheet->setCellValue($fields['total_non_value_added_cost']['col'].$current_row, $summary['non_value_added_cost']);
			}
			
			// Total Cost
			if(isset($summary['total_cost'])) {
				$sheet->setCellValue($fields['total_cost']['col'].$current_row, $summary['total_cost']);
			}
			
			// Productivity Rate
			if(isset($summary['productivity_rate'])) {
				$sheet->setCellValue($fields['productivity_rate']['col'].$current_row, $summary['productivity_rate'] / 100);
			}
		}

		$writer = new Xlsx($spreadsheet);
		
		$file_name = $title;
		
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'. $file_name .'.xlsx"'); 
		header('Cache-Control: max-age=0');
		
		$writer->save('php://output');
	}
	
	public function export_shipment_report_to_excel($args) {
		$data = array();

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		
		$title = 'Shipment Report';
		
		if(!empty($args['facility'])) {
			$title .= ' - ' . $this->model_db_crud->get_specific_data_field('facility', $args['facility'], 'facility_name');
		}
		
		if(!empty($args['periodicity'])) {
			$title .= ' - ' . ucwords($args['periodicity']);
		}
		
		if(!empty($args['period_from'])) {
			$title .= ' - ' . $args['period_from'];
		}
		
		if(!empty($args['period_to'])) {
			$title .= ' - ' . $args['period_to'];
		}
		
		$spreadsheet->getProperties()
			->setTitle($title)
			->setSubject($title);
			
		$this->load->model(PROJECT_CODE.'/model_outbound');
		$report_data = $this->model_outbound->get_shipment_report_data($args);
		
		$current_row = 1;
		
		if($args['report_type'] == 'no_breakdown') {
			
		}
		else if($args['report_type'] == 'breakdown_by_product_family') {	
			$sheet->getStyle('A'.$current_row)->applyFromArray(array('font' => array('bold' => true, 'size' => 20)));
			$sheet->setCellValue('A'.$current_row, 'Actions');
			
			// ...
		}
		
		$writer = new Xlsx($spreadsheet);
		
		$file_name = $title;
		
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'. $file_name .'.xlsx"'); 
		header('Cache-Control: max-age=0');
		
		$writer->save('php://output');
	}
	
	public function export_inventory_report_to_excel($args) {
		$data = array();

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();
		
		$title = 'Inventory Report ' . date('Y-m-d');
		
		$spreadsheet->getProperties()
			->setTitle($title)
			->setSubject($title);
			
		$this->load->model(PROJECT_CODE.'/model_inventory');
		$report_data = $this->model_inventory->get_inventory_report_data($args);
		
		$current_row = 1;
		
		$sheet->setCellValue('A'.$current_row, 'Product');
		$sheet->setCellValue('B'.$current_row, 'On Hand Qty');
		$sheet->setCellValue('C'.$current_row, '(60 Day) Average Per Day');
		$sheet->setCellValue('D'.$current_row, 'Days On Hand Qty');
		$sheet->setCellValue('E'.$current_row, 'Excess Days On Hand Qty');
		$sheet->setCellValue('F'.$current_row, 'Cubic Inches');
		$sheet->setCellValue('G'.$current_row, 'On Hand Cubic Inches');
		$sheet->setCellValue('H'.$current_row, 'Pallet Cubic Inches');
		$sheet->setCellValue('I'.$current_row, 'On Hand Pallet Equivalents');
		$sheet->setCellValue('J'.$current_row, 'Cubic Amount to Keep');
		$sheet->setCellValue('K'.$current_row, 'Pallets to Keep');
		$sheet->setCellValue('L'.$current_row, 'Pallets to Send');
		
		$current_row++;
		
		foreach($report_data['products_data'] as $current_data) {
			$sheet->setCellValue('A'.$current_row, $current_data['sku']);
			
			if(isset($report_data['product_qty_on_hand_data'][$current_data['product_id']])) {
				$sheet->setCellValue('B'.$current_row, $report_data['product_qty_on_hand_data'][$current_data['product_id']]);
			}
			
			$sheet->setCellValue('C'.$current_row, $current_data['sixty_day_avg']);
			$sheet->setCellValue('D'.$current_row, '=B'.$current_row.'/C'.$current_row);
			$sheet->setCellValue('E'.$current_row, '=D'.$current_row.'-60');
			
			if(isset($report_data['product_cubic_inches_data'][$current_data['product_id']])) {
				$sheet->setCellValue('F'.$current_row, $report_data['product_cubic_inches_data'][$current_data['product_id']]);
			}
			
			$sheet->setCellValue('G'.$current_row, '=B'.$current_row.'*F'.$current_row);
			$sheet->setCellValue('H'.$current_row, $report_data['pallet_cubic_inches']);
			$sheet->setCellValue('I'.$current_row, '=G'.$current_row.'/H'.$current_row);
			$sheet->setCellValue('J'.$current_row, '=C'.$current_row.'*60*F'.$current_row);
			$sheet->setCellValue('K'.$current_row, '=J'.$current_row.'/H'.$current_row);
			$sheet->setCellValue('L'.$current_row, '=I'.$current_row.'-K'.$current_row);
			
			$current_row++;
		}
		
		// Grand Total
		$sheet->setCellValue('A'.$current_row, 'Grand Total');
		$sheet->setCellValue('B'.$current_row, '=SUM(B2:B'.($current_row-1).')');
		$sheet->setCellValue('C'.$current_row, '=SUM(C2:C'.($current_row-1).')');
		$sheet->setCellValue('D'.$current_row, '=B'.$current_row.'/C'.$current_row);
		$sheet->setCellValue('E'.$current_row, '=D'.$current_row.'-60');
		$sheet->setCellValue('F'.$current_row, '=SUM(F2:F'.($current_row-1).')');
		$sheet->setCellValue('G'.$current_row, '=B'.$current_row.'*F'.$current_row);
		$sheet->setCellValue('H'.$current_row, '=SUM(H2:H'.($current_row-1).')');
		$sheet->setCellValue('I'.$current_row, '=G'.$current_row.'/H'.$current_row);
		$sheet->setCellValue('J'.$current_row, '=C'.$current_row.'*60*F'.$current_row);
		$sheet->setCellValue('K'.$current_row, '=J'.$current_row.'/H'.$current_row);
		$sheet->setCellValue('L'.$current_row, '=I'.$current_row.'-K'.$current_row);
		
		foreach( array('A','B','C','D','E','F','G','H','I','J','K','L') as $column_id) {
			$sheet->getColumnDimension($column_id)->setAutoSize(true);
			$sheet->getStyle($column_id.'1')->applyFromArray(array('font' => array('bold' => true)));
		}
		
		foreach( array('B','C','D','E','F','G','H','I','J','K','L') as $column_id) {
			$sheet->getStyle($column_id.'2:'.$column_id.$current_row)->getNumberFormat()->setFormatCode('#,##0.0_-');
		}
		
		$sheet->getStyle('A1:A'.$current_row)->getAlignment()->setHorizontal('left');
		
		$writer = new Xlsx($spreadsheet);
		
		$file_name = $title;
		
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'. $file_name .'.xlsx"'); 
		header('Cache-Control: max-age=0');
		
		$writer->save('php://output');
	}
}