<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_carrier extends CI_Model {
	public function __construct() {
		
	}
	
	// Packages: package_id, carrier_code, track_number
	public function get_tracking_info_in_bulk($packages) {
		$result = array();
		
		if(empty($packages)) {
			$result[] = array(
				'package_id' => null,
				'success' => false,
				'error_message' => 'Empty packages in argument.'
			);
			return $result;
		}
		
		ob_start();
		$process = curl_init();
		
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
		
		foreach($packages as $package) {
			$package_id = !empty($package['package_id']) ? $package['package_id'] : null;
			$carrier_code = !empty($package['carrier_code']) ? $package['carrier_code'] : null;
			$track_number = !empty($package['track_number']) ? $package['track_number'] : null;
			
			if(empty($track_number)) {
				$result[] = array(
					'package_id' => $package_id,
					'success' => false,
					'error_message' => 'Empty track number.'
				);
				continue;
			}
			
			if(!in_array($carrier_code, array('fedex','lasership','ontrac','usps'))) {
				$result[] = array(
					'package_id' => $package_id,
					'success' => false,
					'error_message' => 'Carrier not supported.'
				);
				continue;
			}
			
			if($carrier_code == 'lasership') {
				$api_url = 'https://t.lasership.com/Track/' . $track_number . '/json';
			}
			else {
				$api_url = 'https://shipit-api.herokuapp.com/api/carriers/'. $carrier_code . '/' . $track_number;
			}
			
			curl_setopt($process, CURLOPT_URL, $api_url);
			
			try {
				$return = json_decode(curl_exec($process), true);
			}
			catch(Exception $e) {
				$result[] = array(
					'package_id' => $package_id,
					'success' => false,
					'error_message' => $e->getMessage()
				);
				continue;
			}
			
			$tracking_data = array('package_id' => $package_id);
			
			if($carrier_code == 'lasership') {
				// LaserShip
				if(empty($return['Pieces'][0]['TrackingNumber'])) {
					$result[] = array(
						'package_id' => $package_id,
						'success' => false,
						'error_message' => 'Tracking not found.'
					);
					continue;
				}
				
				$tracking_data['track_number'] = $return['Pieces'][0]['TrackingNumber'];
				
				if($tracking_data['track_number'] <> $track_number) {
					$result[] = array(
						'package_id' => $package_id,
						'success' => false,
						'error_message' => 'Inconsistent track number.'
					);
					continue;
				}
				
				$tracking_data['track_number'] = $track_number;
				$tracking_data['track_url'] = 'https://t.lasership.com/Track/' . $track_number;
	
				$tracking_data['carrier_service_name'] = 'LaserShip';
				
				$tracking_data['carrier_eta'] = !empty($return['EstimatedDeliveryDate']) ? $return['EstimatedDeliveryDate'] . ' 23:59:59' : null;
				
				$tracking_data['weight'] = !empty($return['Pieces'][0]['Weight']) ? $return['Pieces'][0]['Weight'] : null;
				$tracking_data['weight_measurement'] = !empty($return['Pieces'][0]['WeightUnit']) ? $return['Pieces'][0]['WeightUnit'] : null;
				
				$latest_event = null;
				$pod_photo_event = null;
				$delivered_event = null;
				$carrier_first_scan_event = null;
				
				$latest_datetime = '2001-01-01';
				
				foreach($return['Events'] as $event) {
					$datetime = str_replace('T',' ',$event['DateTime']);
					if(strtotime($datetime) > strtotime($latest_datetime)) {
						$latest_datetime = $datetime;
						$latest_event = $event;
					}
					
					switch($event['EventModifier']) {
						case 'DLVD':
							$delivered_event = $event;
							break;
						case 'FOTO':
							$pod_photo_event = $event;
							break;
						case 'ORIG':
							$carrier_first_scan_event = $event;
							break;
					}
				}
				
				if(!empty($delivered_event)) {
					// Package has been delivered
					$tracking_data['destination_city'] = !empty($delivered_event['City']) ? $delivered_event['City'] : '';
					$tracking_data['destination_city'] .= !empty($delivered_event['State']) ? ', ' . $delivered_event['State'] : '';
					$tracking_data['destination_city'] .= !empty($delivered_event['Country']) ? ', ' . $delivered_event['Country'] : '';
					
					$tracking_data['actual_delivery_date'] = !empty($delivered_event['DateTime']) ? str_replace('T',' ',$delivered_event['DateTime']) : null;
					$tracking_data['actual_delivery_time_utc'] = !empty($delivered_event['UTCDateTime']) ? str_replace('T',' ',$delivered_event['UTCDateTime']) : null;
					
					$tracking_data['received_by'] = !empty($delivered_event['Signature']) ? $delivered_event['Signature'] : null;
					$tracking_data['left_at'] = !empty($delivered_event['Location']) ? $delivered_event['Location'] : null;
					
					$tracking_data['pod_photo_url'] = !empty($pod_photo_event['PhotoPath']) ? $pod_photo_event['PhotoPath'] : null;
					$tracking_data['status'] = $delivered_event['EventShortText'];
				}
				else if(!empty($latest_event)) {
					// Package hasn't been delivered
					$tracking_data['destination_city'] = null;
					$tracking_data['received_by'] = null;
					$tracking_data['left_at'] = null;
					$tracking_data['pod_photo_url'] = null;
					
					$tracking_data['status'] = $latest_event['EventShortText'];
				}
				
				if(!empty($carrier_first_scan_event)) {
					$tracking_data['carrier_first_scan_at'] = str_replace('T',' ',$carrier_first_scan_event['DateTime']);
					$tracking_data['carrier_first_scan_at_utc'] = str_replace('T',' ',$carrier_first_scan_event['UTCDateTime']);
				}
				
				$tracking_data['carrier_data_updated_at'] = $latest_datetime;
			}
			else {
				// OnTrac, USPS
				if(isset($return['service'])) {
					$tracking_data['carrier_service_name'] = $return['service'];
				}
				
				if(isset($return['eta'])) {
					$tracking_data['carrier_eta'] = str_replace(array('T','.000Z'), array(' ',''), $return['eta']);
				}
				
				if(isset($return['weight'])) {
					$weight_parts = explode(' ', $return['weight']);
					
					if(is_numeric($weight_parts[0])) {
						$tracking_data['weight'] = $weight_parts[0];
					}
					
					if(count($weight_parts) > 1) {
						array_shift($weight_parts);
						$tracking_data['weight_measurement'] = implode(' ', $weight_parts);
					}
				}

				if(isset($return['activities']) && is_array($return['activities'])) {
					$activities = $return['activities'];
					
					$delivered_activity = null;
					$latest_activity = null;
					$latest_activity_datetime = '2001-01-01';
					
					foreach($activities as $activity) {
						$activity_datetime = str_replace('T', ' ', $activity['datetime']);					

						if(strpos(strtolower($activity['details']), 'delivered') !== false) {
							$delivered_activity = $activity;
						}
						
						if(strtotime($activity_datetime) > strtotime($latest_activity_datetime)) {
							$latest_activity = $activity;
							$latest_activity_datetime = $activity_datetime;
						}
					}
					
					$tracking_data['carrier_data_updated_at'] = ($latest_activity_datetime <> '2001-01-01') ? $latest_activity_datetime : null;
					
					if(!empty($delivered_activity)) {
						// Package has been delivered
						if(isset($return['destination'])) {
							$tracking_data['destination_city'] = $return['destination'];
						}
						
						$tracking_data['actual_delivery_time_utc'] = !empty($delivered_activity['timestamp']) ? str_replace(array('T','.000Z'), array(' ',''),$delivered_activity['timestamp']) : null;
						$tracking_data['actual_delivery_date'] = !empty($delivered_activity['datetime']) ? str_replace('T',' ',$delivered_activity['datetime']) : convert_timezone($tracking_data['actual_delivery_time_utc'],'UTC','US/Eastern');
						
						$tracking_data['status'] = $delivered_activity['details'];
					}
					else {
						// Package hasn't been delivered
						$tracking_data['destination_city'] = null;
						
						$tracking_data['status'] = $latest_activity['details'];
					}
				}
			}
			
			$result[] = array(
				'success' => true,
				'tracking_data' => $tracking_data
			);
		} // end foreach
		
		curl_close($process);
		ob_end_clean();
		
		return $result;
	}
	
	public function get_tracking_info($carrier_code, $track_number) {
		$result = $this->get_tracking_info_in_bulk( array( array('carrier_code' => $carrier_code, 'track_number' => $track_number) ) );
		
		if($result['success']) {
			
		}
		else {
			
		}
	}
}