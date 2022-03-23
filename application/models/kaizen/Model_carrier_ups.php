<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use Ups\Tracking;

class Model_carrier_ups extends CI_Model {
	private $ups_tracking;
	
	public function __construct() {
		$this->load->database();
		$this->ups_tracking = new Ups\Tracking(UPS_API_KEY, UPS_ACCOUNT_USER_ID, UPS_ACCOUNT_PASSWORD);
	}
	
	public function get_ups_tracking_info($track_number) {
		$result = array('success' => true);
		
		if(empty($track_number)) {
			$result['success'] = false;
			$result['error_message'] = 'Empty track number.';
			return $result;
		}
		
		try {
			$shipment = $this->ups_tracking->track($track_number);
		}
		catch(Exception $e) {
			$result['success'] = false;
			$result['error_message'] = $e->getMessage();
			return $result;
		}
		
		if(empty($shipment->Package->Activity)) {
			$result['success'] = false;
			$result['error_message'] = 'Package activity is not found in tracking info.';
			return $result;
		}
		
		$tracking_data = array();
		$tracking_data['track_number'] = $track_number;
		
		$latest_activity = is_array($shipment->Package->Activity) ? $shipment->Package->Activity[0] : $shipment->Package->Activity;
		
		if(is_array($shipment->Package->Activity)) {
			foreach($shipment->Package->Activity as $activity) {
				
				
				if(!empty($activity->Status->StatusCode->Code)) {
					switch($activity->Status->StatusCode->Code) {
						case 'OR': // Origin Scan
							$tracking_data['carrier_first_scan_at'] = date_format(date_create_from_format('YmdHis', $activity->Date . $activity->Time), 'Y-m-d H:i:s');
							$tracking_data['carrier_first_scan_at_utc'] = $activity->GMTDate . ' ' . $activity->GMTTime;
							break;
					}
				}
			}
		}
		
		$tracking_data['carrier_service_name'] = !empty($shipment->Service->Description) ? $shipment->Service->Description : null;
		$tracking_data['status'] = !empty($latest_activity->Status->StatusType->Description) ? $latest_activity->Status->StatusType->Description : null;
		
		$tracking_data['weight'] = !empty($shipment->ShipmentWeight->Weight) ? $shipment->ShipmentWeight->Weight : null;
		$tracking_data['weight_measurement'] = !empty($shipment->ShipmentWeight->UnitOfMeasurement->Code) ? $shipment->ShipmentWeight->UnitOfMeasurement->Code : null;
		
		/*if(isset($shipment->ShipTo->Address->PostalCode)) {
			$tracking_data['postcode'] = $shipment->ShipTo->Address->PostalCode;
		}*/
		
		if(isset($shipment->ShipTo->Address->StateProvinceCode)) {
			$tracking_data['state'] = $shipment->ShipTo->Address->StateProvinceCode;
		}
		
		$tracking_data['carrier_data_updated_at'] = date_format(date_create_from_format('YmdHis', $latest_activity->Date . $latest_activity->Time), 'Y-m-d H:i:s');
		$tracking_data['carrier_data_updated_at_utc'] = $latest_activity->GMTDate . ' ' . $latest_activity->GMTTime;

		if(!empty($latest_activity->Status->StatusType->Code) && $latest_activity->Status->StatusType->Code == 'D') {
			// If package has been delivered...
			
			// Assuming "DELIVERED" activity is always the latest activity
			$tracking_data['actual_delivery_date'] = $tracking_data['carrier_data_updated_at'];
			$tracking_data['actual_delivery_time_utc'] = $tracking_data['carrier_data_updated_at_utc'];
			
			$tracking_data['destination_city'] = !empty($latest_activity->ActivityLocation->Address->City) ? $latest_activity->ActivityLocation->Address->City : '';
			
			$tracking_data['destination_city'] .= !empty($latest_activity->ActivityLocation->Address->StateProvinceCode) ? ', ' . $latest_activity->ActivityLocation->Address->StateProvinceCode : '';
			
			$tracking_data['destination_city'] .= !empty($latest_activity->ActivityLocation->Address->CountryCode) ? ', ' . $latest_activity->ActivityLocation->Address->CountryCode : '';
			
			$tracking_data['received_by'] = !empty($latest_activity->ActivityLocation->SignedForByName) ? $latest_activity->ActivityLocation->SignedForByName : '';
			
			$tracking_data['left_at'] = !empty($latest_activity->ActivityLocation->Description) ? $latest_activity->ActivityLocation->Description : '';
		}
		
		$result['tracking_data'] = $tracking_data;
		
		return $result;
	}
}