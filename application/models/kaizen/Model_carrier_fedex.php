<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_carrier_fedex extends CI_Model {
	private $fedex_api_credentials;
	
	public function __construct() {
		$this->load->database();
		
		$fedex_api_credentials_tmp = $this->db
			->select('setting_name, value')
			->from('settings')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', 1)
			->like('setting_name', 'fedex')
			->get()->result_array();
		
		if(!empty($fedex_api_credentials_tmp)) {
			$this->fedex_api_credentials = array_combine(
				array_column($fedex_api_credentials_tmp, 'setting_name'),
				array_column($fedex_api_credentials_tmp, 'value')
			);
		}
	}
	
	public function get_cached_fedex_api_access_token() {
		return !empty($this->fedex_api_credentials['fedex_api_access_token']) ? $this->fedex_api_credentials['fedex_api_access_token'] : null;
	}
	
	public function update_cached_fedex_api_access_token() {
		$result = array();
		
		$fedex_api_key = !empty($this->fedex_api_credentials['fedex_api_key']) ? $this->fedex_api_credentials['fedex_api_key'] : null;
		$fedex_secret_key = !empty($this->fedex_api_credentials['fedex_secret_key']) ? $this->fedex_api_credentials['fedex_secret_key'] : null;
		$fedex_oauth_api_url = !empty($this->fedex_api_credentials['fedex_oauth_api_url']) ? $this->fedex_api_credentials['fedex_oauth_api_url'] : 'https://apis-sandbox.fedex.com/oauth/token';
		
		$curl = curl_init();
		
		curl_setopt_array($curl, array(
			CURLOPT_URL => $fedex_oauth_api_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => 'grant_type=client_credentials&client_id='.$fedex_api_key.'&client_secret='.$fedex_secret_key, 
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/x-www-form-urlencoded'
			)
		));
		
		$response = json_decode(curl_exec($curl));
		
		$status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if($status_code == 200) { // Success
			$access_token = $response->access_token;
			
			$this->db
				->set('value', $access_token)
				->set('last_modified_time', date('Y-m-d H:i:s'))
				->where('setting_name', 'fedex_api_access_token')
				->update('settings');
			
			$result['success'] = true;
		}
		else {
			$result['success'] = false;
			$result['error_message'] = !empty($response->errors[0]->message) ? $response->errors[0]->message : 'Unknown error.';
		}
		
		return $result;
	}
	
	public function get_fedex_tracking_info($track_numbers, $other_args = array()) {
		$result = array();
		$original_input_track_numbers = $track_numbers;

		if(empty($track_numbers)) {
			$result['success'] = false;
			$result['error_message'] = 'Empty track numbers.';
			return $result;
		}
		
		if(!is_array($track_numbers)) {
			$track_numbers = array($track_numbers);
		}
		
		$access_token = $this->get_cached_fedex_api_access_token();
		
		if(empty($access_token)) {
			$result['success'] = false;
			$result['error_message'] = 'Invalid access token.';
			return $result;
		}
		
		$fedex_track_api_url = !empty($this->fedex_api_credentials['fedex_track_api_url']) ? $this->fedex_api_credentials['fedex_track_api_url'] : 'https://apis-sandbox.fedex.com/oauth/token';

		$post_fields = array(
			'trackingInfo' => array(),
			'includeDetailedScans' => true
		);
		foreach($track_numbers as $track_number) {
			$post_fields['trackingInfo'][] = array(
				'trackingNumberInfo' => array(
					'trackingNumber' => $track_number
				)
			);
		}
		$json_encoded_post_fields = json_encode($post_fields);
		
		$curl = curl_init();
		
		curl_setopt_array($curl, array(
			CURLOPT_URL => $fedex_track_api_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $json_encoded_post_fields,
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer '.$access_token,
				'X-Locale: en_US',
				'Content-Type: application/json'
			)
		));
		
		$response = json_decode(curl_exec($curl));
		
		$status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

		if($status_code == 200) { // Success
			$result['tracking_data'] = array();
			
			$complete_track_results = !empty($response->output->completeTrackResults) ? $response->output->completeTrackResults : null;
			
			if(empty($complete_track_results)) {
				$result['success'] = false;
				$result['error_message'] = 'Empty track results';
				return $result;
			}
			
			foreach($complete_track_results as $complete_track_result) {
				//debug_var($complete_track_result, 'complete!!!');
				$track_number = !empty($complete_track_result->trackingNumber) ? $complete_track_result->trackingNumber : null;
				if(empty($track_number)) continue;
				
				$track_result = $complete_track_result->trackResults[0];
				
				if(!empty($track_result->error)) {
					// Error occurred with the FedEx API
					continue;
				}
				
				$tracking_data = array();
				
				$tracking_data['track_number'] = $track_number;
				
				$tracking_data['carrier_service_name'] = !empty($track_result->serviceDetail->description) ? $track_result->serviceDetail->description : null;
				
				$tracking_data['destination_city'] = !empty($track_result->deliveryDetails->actualDeliveryAddress->city) ? $track_result->deliveryDetails->actualDeliveryAddress->city : '';
				$tracking_data['destination_city'] .= !empty($track_result->deliveryDetails->actualDeliveryAddress->stateOrProvinceCode) ? ', ' . $track_result->deliveryDetails->actualDeliveryAddress->stateOrProvinceCode : '';
				$tracking_data['destination_city'] .= !empty($track_result->deliveryDetails->actualDeliveryAddress->countryCode) ? ', ' . $track_result->deliveryDetails->actualDeliveryAddress->countryCode : '';
				
				$tracking_data['weight'] = !empty($track_result->packageDetails->weightAndDimensions->weight[0]->value) ? $track_result->packageDetails->weightAndDimensions->weight[0]->value : null;
				$tracking_data['weight_measurement'] = !empty($track_result->packageDetails->weightAndDimensions->weight[0]->unit) ? $track_result->packageDetails->weightAndDimensions->weight[0]->unit : null;
				
				$tracking_data['received_by'] = !empty($track_result->deliveryDetails->receivedByName) ? $track_result->deliveryDetails->receivedByName : null;
				
				$tracking_data['left_at'] = !empty($track_result->latestStatusDetail->ancillaryDetails[0]->reasonDescription) ? $track_result->latestStatusDetail->ancillaryDetails[0]->reasonDescription : null;
				
				$tracking_data['track_url'] = 'https://www.fedex.com/fedextrack/?trknbr=' . $track_number;
				
				if(!empty($track_result->latestStatusDetail->description)) {
					$tracking_data['status'] = $track_result->latestStatusDetail->description;
				}
				
				$scan_events = !empty($track_result->scanEvents) ? $track_result->scanEvents : array();
				if(!empty($scan_events)) {
					foreach($scan_events as $scan_event) {
						if($scan_event->eventType == 'DL') {
							$actual_delivery_time_raw = $scan_event->date;
							$tracking_data['actual_delivery_date'] = str_replace('T', ' ', substr($actual_delivery_time_raw, 0, 19));
							$tracking_data['actual_delivery_time_utc'] = date('Y-m-d H:i:s', strtotime((substr($actual_delivery_time_raw,19,1)=='-'?'+':'-').intval(substr($actual_delivery_time_raw,20,2)).' hour '.$tracking_data['actual_delivery_date']));
						}
						else if($scan_event->eventType == 'OC') {
							$carrier_first_scan_time_raw = $scan_event->date;
							$tracking_data['carrier_first_scan_at'] = str_replace('T', ' ', substr($carrier_first_scan_time_raw, 0, 19));
							$tracking_data['carrier_first_scan_at_utc'] = date('Y-m-d H:i:s', strtotime((substr($carrier_first_scan_time_raw,19,1)=='-'?'+':'-').intval(substr($carrier_first_scan_time_raw,20,2)).' hour '.$tracking_data['carrier_first_scan_at']));
						}
					}
				}
				
				$result['tracking_data'][$track_number] = $tracking_data;
			}
			
			$result['success'] = true;
			
			if(!is_array($original_input_track_numbers)) {
				$result['tracking_data'] = !empty($result['tracking_data']) ? array_values($result['tracking_data'])[0] : null;
			}
		}
		else if($status_code == 401 && !isset($other_args['is_repeat'])) { // Access token may have been expired, and it's not a repeated attempt
			$this->update_cached_fedex_api_access_token();
			
			// Re-attempt to get the tracking with the renewed access token
			return $this->get_fedex_tracking_info($original_input_track_numbers, array('is_repeat' => true));
		}
		else {
			$result['success'] = false;
			$result['error_message'] = !empty($response->errors[0]->message) ? $status_code . ': '. $response->errors[0]->message : 'Unknown error.';
			return $result;
		}
		
		return $result;
	}
}