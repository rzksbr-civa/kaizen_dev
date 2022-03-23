<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Client_complexity_and_profitability extends CHCHDB_Controller {
	public function __construct(){
        parent::__construct();
    }
	
	public function index() {
		if($this->session->userdata('chchdb_'.PROJECT_CODE.'_user_role') <> USER_ROLE_ADMIN_WITH_FINANCIAL) {
			$this->_show_access_denied_page();
			return;
		}
		
		$header_data = array();
		$header_data['page_title'] = generate_page_title('Client Complexity and Profitability');

		$data = array();
		
		$data['series_colors'] = array();
			
		$raw_data = array(
			array('client_name'=>'Secretlab US', 'complexity'=>38, 'profitability'=>1),
			array('client_name'=>'LGDC', 'complexity'=>0, 'profitability'=>2),
			array('client_name'=>'Inventables', 'complexity'=>26, 'profitability'=>2.76190476190476),
			array('client_name'=>'Zogics', 'complexity'=>15, 'profitability'=>3.61904761904762),
			array('client_name'=>'Omlet', 'complexity'=>30, 'profitability'=>6.14285714285714),
			array('client_name'=>'Wood Robot', 'complexity'=>36, 'profitability'=>9.47368421052632),
			array('client_name'=>'Jaquish Industrial Research', 'complexity'=>19, 'profitability'=>9.52380952380952),
			array('client_name'=>'CB Consumer Products', 'complexity'=>12, 'profitability'=>10.5),
			array('client_name'=>'FeetUp', 'complexity'=>24, 'profitability'=>12.1428571428571),
			array('client_name'=>'International Surf Ventures', 'complexity'=>26, 'profitability'=>13.8095238095238),
			array('client_name'=>'Kellyco Detectors', 'complexity'=>19, 'profitability'=>13.8947368421053),
			array('client_name'=>'Stone Road Family Farms', 'complexity'=>31, 'profitability'=>14.9230769230769),
			array('client_name'=>'White Duck Outdoors', 'complexity'=>40, 'profitability'=>16),
			array('client_name'=>'Leitmotif Services', 'complexity'=>9, 'profitability'=>16.125),
			array('client_name'=>'DoggoRamps', 'complexity'=>7, 'profitability'=>16.4),
			array('client_name'=>'PROJECTAD', 'complexity'=>24, 'profitability'=>17.6666666666667),
			array('client_name'=>'Guardian Bike Company', 'complexity'=>28, 'profitability'=>17.952380952381),
			array('client_name'=>'Cat Tree King', 'complexity'=>26, 'profitability'=>18),
			array('client_name'=>'NSG Hill', 'complexity'=>24, 'profitability'=>18.1428571428571),
			array('client_name'=>'Pop-A-Shot', 'complexity'=>16, 'profitability'=>20.5238095238095),
			array('client_name'=>'Chubby Pet Products', 'complexity'=>27, 'profitability'=>20.9047619047619),
			array('client_name'=>'Dream Vessels', 'complexity'=>24, 'profitability'=>22.0909090909091),
			array('client_name'=>'Tinkergarten', 'complexity'=>13, 'profitability'=>22.3809523809524),
			array('client_name'=>'Profile Studio', 'complexity'=>20, 'profitability'=>22.8095238095238),
			array('client_name'=>'Nootie', 'complexity'=>25, 'profitability'=>22.9047619047619),
			array('client_name'=>'Bonvera', 'complexity'=>18, 'profitability'=>23.047619047619),
			array('client_name'=>'Lori Wall Beds (HFL)', 'complexity'=>21, 'profitability'=>23.4285714285714),
			array('client_name'=>'Garadry', 'complexity'=>14, 'profitability'=>25.05),
			array('client_name'=>'HigherDOSE', 'complexity'=>17, 'profitability'=>25.3571428571429),
			array('client_name'=>'Ashland Bay Trading', 'complexity'=>27, 'profitability'=>26.1904761904762),
			array('client_name'=>'Labelcity', 'complexity'=>22, 'profitability'=>26.6),
			array('client_name'=>'Green Foods Corporation', 'complexity'=>37, 'profitability'=>27.3076923076923),
			array('client_name'=>'Hyll - Akron Street', 'complexity'=>12, 'profitability'=>29.3809523809524),
			array('client_name'=>'Color Copper', 'complexity'=>21, 'profitability'=>30.6666666666667),
			array('client_name'=>'Wintergreen Corporation', 'complexity'=>20, 'profitability'=>31.375),
			array('client_name'=>'Ecosa', 'complexity'=>12, 'profitability'=>31.5238095238095),
			array('client_name'=>'Retrofit', 'complexity'=>12, 'profitability'=>33),
			array('client_name'=>'Zen Habitats', 'complexity'=>6, 'profitability'=>34.5),
			array('client_name'=>'Kaftan', 'complexity'=>16, 'profitability'=>35.1428571428571),
			array('client_name'=>'Great Lakes Bio Systems', 'complexity'=>11, 'profitability'=>35.2),
			array('client_name'=>'Roar Ambition', 'complexity'=>25, 'profitability'=>35.7142857142857),
			array('client_name'=>'Wine Plum', 'complexity'=>21, 'profitability'=>36.1428571428571),
			array('client_name'=>'Miaustore SL', 'complexity'=>10, 'profitability'=>37.8947368421053),
			array('client_name'=>'MPH Trading Company', 'complexity'=>22, 'profitability'=>39.5238095238095),
			array('client_name'=>'Maap Co Pty Ltd', 'complexity'=>20, 'profitability'=>39.5714285714286),
			array('client_name'=>'Spawn Cycles', 'complexity'=>9, 'profitability'=>39.7333333333333),
			array('client_name'=>'Craycort', 'complexity'=>24, 'profitability'=>41.15),
			array('client_name'=>'Pentwater Capital - Hawken', 'complexity'=>15, 'profitability'=>41.6190476190476),
			array('client_name'=>'Vegepod', 'complexity'=>25, 'profitability'=>41.8095238095238),
			array('client_name'=>'FireResQ', 'complexity'=>21, 'profitability'=>42.75),
			array('client_name'=>'Modern Commerce Holdings', 'complexity'=>24, 'profitability'=>44.3333333333333),
			array('client_name'=>'Calroy Health Sciences', 'complexity'=>19, 'profitability'=>44.7142857142857),
			array('client_name'=>'Strikebold', 'complexity'=>23, 'profitability'=>45),
			array('client_name'=>'Pro Tool Warehouse', 'complexity'=>13, 'profitability'=>47.2857142857143),
			array('client_name'=>'2254062 Ontario', 'complexity'=>10, 'profitability'=>47.7619047619048),
			array('client_name'=>'PTAC USA', 'complexity'=>11, 'profitability'=>47.7619047619048),
			array('client_name'=>'Biogents', 'complexity'=>18, 'profitability'=>48.9047619047619),
			array('client_name'=>'Powerful Foods', 'complexity'=>19, 'profitability'=>49.6363636363636),
			array('client_name'=>'MuTu Systems', 'complexity'=>9, 'profitability'=>50.4285714285714),
			array('client_name'=>'Atmosphere Aerosol', 'complexity'=>20, 'profitability'=>51.1428571428571),
			array('client_name'=>'Cucunu Corp', 'complexity'=>9, 'profitability'=>51.8),
			array('client_name'=>'Superior Polymers', 'complexity'=>16, 'profitability'=>51.9047619047619),
			array('client_name'=>'CommQuest', 'complexity'=>20, 'profitability'=>53.0952380952381),
			array('client_name'=>'Sting USA', 'complexity'=>25, 'profitability'=>54.5625),
			array('client_name'=>'Bright Kids NYC', 'complexity'=>5, 'profitability'=>55.6190476190476),
			array('client_name'=>'The Better Packaging Co.', 'complexity'=>7, 'profitability'=>56.5714285714286),
			array('client_name'=>'Bootstrap Farmer', 'complexity'=>19, 'profitability'=>57),
			array('client_name'=>'Lucidity Lights', 'complexity'=>14, 'profitability'=>57.125),
			array('client_name'=>'Oatly', 'complexity'=>11, 'profitability'=>57.25),
			array('client_name'=>'Destination 49', 'complexity'=>5, 'profitability'=>57.7142857142857),
			array('client_name'=>'Powerdecal Holdings', 'complexity'=>7, 'profitability'=>60.1428571428571),
			array('client_name'=>'Ore Products', 'complexity'=>11, 'profitability'=>60.6666666666667),
			array('client_name'=>'Opti-Nutra', 'complexity'=>17, 'profitability'=>60.7619047619048),
			array('client_name'=>'Worldwide Prime', 'complexity'=>7, 'profitability'=>61),
			array('client_name'=>'BarcodeSource', 'complexity'=>14, 'profitability'=>61.9),
			array('client_name'=>'Fractioni Limited', 'complexity'=>5, 'profitability'=>62.9523809523809),
			array('client_name'=>'25kmh Pte Ltd', 'complexity'=>14, 'profitability'=>63.3333333333333),
			array('client_name'=>'Advatek Lighting', 'complexity'=>5, 'profitability'=>63.6666666666667),
			array('client_name'=>'Mumbelli', 'complexity'=>10, 'profitability'=>64.2),
			array('client_name'=>'Little Fish Audio', 'complexity'=>7, 'profitability'=>64.6190476190476),
			array('client_name'=>'Petroleum Service Company', 'complexity'=>12, 'profitability'=>66.2857142857143),
			array('client_name'=>'Green Water Sports', 'complexity'=>9, 'profitability'=>69.3333333333333),
			array('client_name'=>'Flipper Aquarium Products', 'complexity'=>5, 'profitability'=>69.6),
			array('client_name'=>'Glad Tidings Products', 'complexity'=>11, 'profitability'=>70.75),
			array('client_name'=>'Coovy Sports', 'complexity'=>8, 'profitability'=>70.8095238095238),
			array('client_name'=>'Xdesign', 'complexity'=>5, 'profitability'=>71.375),
			array('client_name'=>'Decorlives', 'complexity'=>3, 'profitability'=>73),
			array('client_name'=>'Up Right Designs', 'complexity'=>8, 'profitability'=>74.6190476190476),
			array('client_name'=>'Waters Co Australia', 'complexity'=>5, 'profitability'=>75.7),
			array('client_name'=>'Guang Dong Inlight', 'complexity'=>9, 'profitability'=>77.1904761904762),
			array('client_name'=>'ProPura', 'complexity'=>13, 'profitability'=>77.3),
			array('client_name'=>'Nuts About Nets', 'complexity'=>5, 'profitability'=>78.2857142857143),
			array('client_name'=>'Justin Hsu dba Avanzen', 'complexity'=>0, 'profitability'=>78.6666666666667),
			array('client_name'=>'W Durston Ltd.', 'complexity'=>10, 'profitability'=>78.75),
			array('client_name'=>'Lil\' Monkey USA', 'complexity'=>14, 'profitability'=>78.9285714285714),
			array('client_name'=>'Global Shade Corporation', 'complexity'=>2, 'profitability'=>79),
			array('client_name'=>'RA1601', 'complexity'=>5, 'profitability'=>79.5238095238095),
			array('client_name'=>'Coral Robots', 'complexity'=>6, 'profitability'=>79.85),
			array('client_name'=>'In the Garage', 'complexity'=>1, 'profitability'=>80.1666666666667),
			array('client_name'=>'USA Safety Solutions', 'complexity'=>4, 'profitability'=>80.6666666666667),
			array('client_name'=>'Big Rainforest', 'complexity'=>22, 'profitability'=>81.1666666666667),
			array('client_name'=>'LVT Design Floors', 'complexity'=>5, 'profitability'=>81.5),
			array('client_name'=>'Chef Kitch', 'complexity'=>0, 'profitability'=>82.5),
			array('client_name'=>'Easthills', 'complexity'=>14, 'profitability'=>86.8461538461538),
			array('client_name'=>'Easthills', 'complexity'=>14, 'profitability'=>86.8461538461538),
			array('client_name'=>'Towable Component Enterprises', 'complexity'=>5, 'profitability'=>87),
			array('client_name'=>'Bedroom Wholesalers', 'complexity'=>11, 'profitability'=>87.6),
			array('client_name'=>'Roadwarrior America', 'complexity'=>2, 'profitability'=>88.3333333333333),
			array('client_name'=>'Vertical Air Solutions', 'complexity'=>3, 'profitability'=>89.5),
			array('client_name'=>'East Africa Imports', 'complexity'=>4, 'profitability'=>92.5),
			array('client_name'=>'Wunderfood', 'complexity'=>11, 'profitability'=>94.3333333333333),
			array('client_name'=>'Pinto Canyon Management', 'complexity'=>0, 'profitability'=>95.5),
			array('client_name'=>'GOKI AMERICA', 'complexity'=>4, 'profitability'=>95.8),
			array('client_name'=>'Far and Away', 'complexity'=>0, 'profitability'=>99),
			array('client_name'=>'Fuelcare Ltd', 'complexity'=>8, 'profitability'=>99.75),
			array('client_name'=>'Stripgrate USA', 'complexity'=>7, 'profitability'=>100),
		);
		
		foreach($raw_data as $key => $current_data) {
			$raw_data[$key]['profitability'] = round($raw_data[$key]['profitability'],2) * -1;
		}
		
		$complexity_data = array_column($raw_data, 'complexity');
		sort($complexity_data);
		$data['max_complexity'] = max($complexity_data);
		$data['complexity_median'] = $data['max_complexity'] / 2; // $complexity_data[floor(count($complexity_data)/2)];
		
		$profitability_data = array_column($raw_data, 'profitability');
		sort($profitability_data);
		$data['max_profitability'] = min($profitability_data);
		$data['profitability_median'] = $data['max_profitability'] / 2; // $profitability_data[floor(count($profitability_data)/2)];
		
		$data['quadrant_total'] = array(
			'low_complexity_low_profitability' => 0,
			'low_complexity_high_profitability' => 0,
			'high_complexity_low_profitability' => 0,
			'high_complexity_high_profitability' => 0
		);
		
		$data['data'] = array();
		foreach($raw_data as $current_data) {
			$data['data'][] = array('name' => $current_data['client_name'], 'data' => array( array($current_data['complexity'], $current_data['profitability']) ));
		
			if($current_data['complexity'] >= $data['complexity_median'] && $current_data['profitability'] >= $data['profitability_median']) {
				$data['series_colors'][] = '#027BD8'; // Blue
				$data['quadrant_total']['high_complexity_high_profitability']++;
			}
			else if($current_data['complexity'] >= $data['complexity_median'] && $current_data['profitability'] < $data['profitability_median']) {
				$data['series_colors'][] = '#DA3D52'; // Red
				$data['quadrant_total']['high_complexity_low_profitability']++;
			}
			else if($current_data['complexity'] < $data['complexity_median'] && $current_data['profitability'] >= $data['profitability_median']) {
				$data['series_colors'][] = '#03C281'; // Green
				$data['quadrant_total']['low_complexity_high_profitability']++;
			}
			else if($current_data['complexity'] < $data['complexity_median'] && $current_data['profitability'] < $data['profitability_median']) {
				$data['series_colors'][] = '#D89715'; // Yellow
				$data['quadrant_total']['low_complexity_low_profitability']++;
			}
		}
		
		$data['total_data'] = count($raw_data);
		
		$data['quadrant_percentages'] = array(
			'low_complexity_low_profitability' => $data['quadrant_total']['low_complexity_low_profitability'] / $data['total_data'] * 100,
			'low_complexity_high_profitability' => $data['quadrant_total']['low_complexity_high_profitability'] / $data['total_data'] * 100,
			'high_complexity_low_profitability' => $data['quadrant_total']['high_complexity_low_profitability'] / $data['total_data'] * 100,
			'high_complexity_high_profitability' => $data['quadrant_total']['high_complexity_high_profitability'] / $data['total_data'] * 100
		);
		
		$footer_data['js'] = $this->load->view(PROJECT_CODE.'/report/js_view_client_complexity_and_profitability_report', $data, true);

		$this->load->view('view_header', $header_data);
		$this->load->view(PROJECT_CODE.'/report/view_client_complexity_and_profitability_report', $data);
		$this->load->view('view_footer', $footer_data);
	}
}
