<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Widget_table_footer_total_info {
	protected $CI;
	
	protected $widget_data = array();
	protected $widget_js_data = array();
	
	public function __construct(){
		$this->CI =& get_instance();
		$this->CI->load->model('model_db_crud');
    }
	
	public function set_widget_specs($widget_specs) {
		$widget_data = array();
		
		$this->CI->load->model(PROJECT_CODE.'/widgets/model_widget_table_footer_total_info');
		$widget_data = $this->CI->model_widget_table_footer_total_info->get_data($widget_specs);
		
		$this->widget_data = $widget_data;
	}
	
	public function get_rendered_widget() {
		return $this->CI->load->view('chchdb/widgets/view_widget_table_footer_total_info', $this->widget_data, true);
	}
	
	public function get_rendered_widget_js() {
		return null;
	}
	
	public function get_sales_order_total_info($sales_order_id) {
		$data = array();
		
		$sales_order = $this->CI->model_db_crud->get_specific_data('sales_order', $sales_order_id);
		
		$sales_order['subtotal_price'] = !empty($sales_order['subtotal_price']) ? $sales_order['subtotal_price'] : 0;
		$sales_order['delivery_cost'] = !empty($sales_order['delivery_cost']) ? $sales_order['delivery_cost'] : 0;
		$sales_order['other_cost'] = !empty($sales_order['other_cost']) ? $sales_order['other_cost'] : 0;
		$sales_order['total_price'] = !empty($sales_order['total_price']) ? $sales_order['total_price'] : 0;
		
		$data['subtotal'] = array(
			'label' => 'Subtotal',
			'value' => format_text($sales_order['subtotal_price'], 'currency', array('currency'=>'IDR')),
			'extras' => array('total_qty_info')
		);
		
		$data['other_costs'] = array(
			array(
				'name' => 'delivery_cost',
				'label' => 'Delivery Cost',
				'value' => format_text($sales_order['delivery_cost'], 'currency', array('currency'=>'IDR'))
			),
			array(
				'name' => 'other_cost',
				'label' => 'Other Cost',
				'value' => format_text($sales_order['other_cost'], 'currency', array('currency'=>'IDR'))
			)
		);

		$data['total'] = array(
			'label' => 'Total',
			'value' => format_text($sales_order['total_price'], 'currency', array('currency'=>'IDR'))
		);
		
		$data['extras'] = array();
		
		$query_total_qty = $this->CI->model_db_crud->get_data(
			'sales_order_details',
			array(
				'select' => 'SUM(qty) as total_qty',
				'where' => array('sales_order' => $sales_order_id)
			)
		);
		$data['extras']['total_qty_info'] = '';
		if($query_total_qty[0]['total_qty'] > 0) {
			$data['total_qty_info'] = '(' . $query_total_qty[0]['total_qty'] . ' ' . ($query_total_qty[0]['total_qty'] == 1 ? 'item' : 'items') . ')';
		};
		
		return $data;
	}
	
	public function get_purchase_order_total_info($purchase_order_id) {
		$data = array();
		
		$purchase_order = $this->CI->model_db_crud->get_specific_data('purchase_order', $purchase_order_id);
		
		$currency = $purchase_order['currency'];
		
		$purchase_order['subtotal_price'] = !empty($purchase_order['product_cost']) ? $purchase_order['product_cost'] : 0;
		$purchase_order['total_price'] = !empty($purchase_order['total_price']) ? $purchase_order['total_price'] : 0;
		
		$data['subtotal'] = format_text($sales_order['subtotal_price'], 'currency', array('currency'=>'IDR'));
		
		$data['other_costs'] = array(
			array(
				'name' => 'delivery_cost',
				'label' => 'Delivery Cost',
				'value' => format_text($sales_order['delivery_cost'], 'currency', array('currency'=>'IDR'))
			),
			array(
				'name' => 'other_cost',
				'label' => 'Other Cost',
				'value' => format_text($sales_order['other_cost'], 'currency', array('currency'=>'IDR'))
			)
		);

		$data['total'] = format_text($sales_order['total_price'], 'currency', array('currency'=>'IDR'));
		
		$query_total_qty = $this->CI->model_db_crud->get_data(
			'sales_order_details',
			array(
				'select' => 'SUM(qty) as total_qty',
				'where' => array('sales_order' => $sales_order_id)
			)
		);
		$data['total_qty_info'] = '';
		if($query_total_qty[0]['total_qty'] > 0) {
			$data['total_qty_info'] = '(' . $query_total_qty[0]['total_qty'] . ' ' . ($query_total_qty[0]['total_qty'] == 1 ? 'item' : 'items') . ')';
		};
		
		return $data;
	}
}
