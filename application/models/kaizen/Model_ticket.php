<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_ticket extends CI_Model {
	public function __construct()	{
		$this->load->database(); 
		$this->load->model('model_db_crud');
	}
	
	public function import_ticket_data($date) {
		$result = array();
		
		$max_ticket_id = 0;
		
		$orgID = '1131188';
		$apiToken = 'eedaf4df-73c4-423c-807d-52db9605a120';
		$host = 'https://app.teamsupport.com/api/xml/Tickets?DateCreated[bt]='.date('Ymd000000',strtotime($date)).'&DateCreated[bt]='.date('Ymd000000',strtotime($date));
		$base64str=base64_encode("$orgID:$apiToken");
		
		$process = curl_init();
		curl_setopt( $process, CURLOPT_URL, $host );
		curl_setopt($process, CURLOPT_HTTPHEADER,array('Content-Type: application/xml', 'Authorization: Basic '.$base64str));
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
		
		$return = curl_exec($process);
		$response = curl_getinfo( $process );
		curl_close($process);
  
		$xml = simplexml_load_string($return, null, LIBXML_NOCDATA);
		$json = json_encode($xml);
		$array = json_decode($json,TRUE);
		
		$now = date('Y-m-d H:i:s');
		$user_group = $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group');
		$user_id = $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_id');
		
		$ticket_customers_data = $production_db->select('id')->from('ticket_customers')->where('data_status', DATA_ACTIVE)->get()->result_array();
		$ticket_contacts_data = $production_db->select('id')->from('ticket_contacts')->where('data_status', DATA_ACTIVE)->get()->result_array();
		
		$ticket_customer_ids = array_column($ticket_customers_data, 'id');
		$ticket_contact_ids = array_column($ticket_contacts_data, 'id');

		$tickets = array();
		$new_ticket_customer_ids = array();
		$new_ticket_contact_ids = array();
		$new_ticket_customers = array();
		$new_ticket_contacts = array();
		$new_tickets_customers_relationships = array();
		$new_tickets_contacts_relationships = array();
		
		if(empty($array['Ticket'])) {
			$result['count'] = 0;
			return $result;
		}
		
		if(isset($array['Ticket']['TicketID'])) {
			$array['Ticket'] = array( $array['Ticket'] );
		}
		
		foreach($array['Ticket'] as $item) {
			$tickets[] = array(
				'id' => $item['TicketID'],
				'product_name' => !is_array($item['ProductName']) ? $item['ProductName'] : null,
				'reported_version' => $item['ReportedVersion'],
				'solved_version' => $item['SolvedVersion'],
				'group_name' => $item['GroupName'],
				'ticket_type_name' => $item['TicketTypeName'],
				'user_name' => $item['UserName'],
				'status' => $item['Status'],
				'status_position' => $item['StatusPosition'],
				'severity_position' => $item['SeverityPosition'],
				'is_closed' => $item['IsClosed'] == 'True' ? true : false,
				'severity' => $item['Severity'],
				'ticket_number' => $item['TicketNumber'],
				'is_visible_on_portal' => $item['IsVisibleOnPortal'] == 'True' ? true : false,
				'is_knowledge_base' => $item['IsKnowledgeBase'] == 'True' ? true : false,
				'reported_version_id' => $item['ReportedVersionID'],
				'solved_version_id' => $item['SolvedVersionID'],
				'product_id' => $item['ProductID'],
				'group_id' => $item['GroupID'],
				'user_id' => $item['UserID'],
				'ticket_status_id' => $item['TicketStatusID'],
				'ticket_type_id' => $item['TicketTypeID'],
				'ticket_severity_id' => $item['TicketSeverityID'],
				'organization_id' => $item['OrganizationID'],
				'name' => $item['Name'],
				'parent_id' => $item['ParentID'],
				'modifier_id' => $item['ModifierID'],
				'creator_id' => $item['CreatorID'],
				'date_modified' =>empty($item['DateModified']) ? null : date_format(date_create_from_format('n/j/Y g:i A', $item['DateModified']), 'Y-m-d H:i'),
				'date_created' => empty($item['DateCreated']) ? null : date_format(date_create_from_format('n/j/Y g:i A', $item['DateCreated']), 'Y-m-d H:i'),
				'date_closed' => empty($item['DateClosed']) ? null : date_format(date_create_from_format('n/j/Y g:i A', $item['DateClosed']), 'Y-m-d H:i'),
				'closer_id' => $item['CloserID'],
				'days_closed' => $item['DaysClosed'],
				'days_opened' => $item['DaysOpened'],
				'closer_name' => $item['CloserName'],
				'creator_name' => is_string($item['CreatorName']) ? $item['CreatorName'] : null,
				'modifier_name' => is_string($item['ModifierName']) ? $item['ModifierName'] : null,
				'hours_spent' => $item['HoursSpent'],
				'sla_violation_time' => $item['SlaViolationTime'],
				'sla_warning_time' => $item['SlaWarningTime'],
				'sla_violation_hours' => $item['SlaViolationHours'],
				'sla_warning_hours' => $item['SlaWarningHours'],
				'knowledge_base_category_id' => $item['KnowledgeBaseCategoryID'],
				'knowledge_base_category_name' => $item['KnowledgeBaseCategoryName'],
				'due_date' => empty($item['DueDate']) ? null: date_format(date_create_from_format('n/j/Y', $item['DueDate']), 'Y-m-d'),
				'ticket_source' => $item['TicketSource'],
				'jira_key' => $item['JiraKey'],
				'tags' => (empty($item['Tags']['Tag']['Value']) ? null : $item['Tags']['Tag']['Value']),
				'data_status' => 'active',
				'data_group' => $user_group,
				'created_time' => $now,
				'created_user' => $user_id,
				'last_modified_time' => $now,
				'last_modified_user' => $user_id
			);
			
			if(isset($item['Customers']['Customer']['CustomerID'])) {
				if(!in_array($item['Customers']['Customer']['CustomerID'], $ticket_customer_ids) 
					&& !in_array($item['Customers']['Customer']['CustomerID'], $new_ticket_customer_ids)) {
					$new_ticket_customer_ids[] = $item['Customers']['Customer']['CustomerID'];
					$new_ticket_customers[] = array(
						'id' => $item['Customers']['Customer']['CustomerID'],
						'customer_name' => $item['Customers']['Customer']['CustomerName'],
						'data_status' => 'active',
						'data_group' => $user_group,
						'created_time' => $now,
						'created_user' => $user_id,
						'last_modified_time' => $now,
						'last_modified_user' => $user_id
					);
				}
				
				$new_tickets_customers_relationships[] = array(
					'ticket' => $item['TicketID'],
					'ticket_customer' => $item['Customers']['Customer']['CustomerID'],
					'data_status' => 'active',
					'data_group' => $user_group,
					'created_time' => $now,
					'created_user' => $user_id,
					'last_modified_time' => $now,
					'last_modified_user' => $user_id
				);
			}
			else if(isset($item['Customers']['Customer'][0])) {
				foreach($item['Customers']['Customer'] as $current_ticket_customer) {
					if(!in_array($current_ticket_customer['CustomerID'], $ticket_customer_ids)
						&& !in_array($current_ticket_customer['CustomerID'], $new_ticket_customer_ids)) {
						$new_ticket_customer_ids[] = $current_ticket_customer['CustomerID'];
						$new_ticket_customers[] = array(
							'id' => $current_ticket_customer['CustomerID'],
							'customer_name' => $current_ticket_customer['CustomerName'],
							'data_status' => 'active',
							'data_group' => $user_group,
							'created_time' => $now,
							'created_user' => $user_id,
							'last_modified_time' => $now,
							'last_modified_user' => $user_id
						);
					}
					
					$new_tickets_customers_relationships[] = array(
						'ticket' => $item['TicketID'],
						'ticket_customer' => $current_ticket_customer['CustomerID'],
						'data_status' => 'active',
						'data_group' => $user_group,
						'created_time' => $now,
						'created_user' => $user_id,
						'last_modified_time' => $now,
						'last_modified_user' => $user_id
					);
				}
			}
			
			if(isset($item['Contacts']['Contact']['ContactID'])) {
				if(!in_array($item['Contacts']['Contact']['ContactID'], $ticket_contact_ids)
					&& !in_array($item['Contacts']['Contact']['ContactID'], $new_ticket_contact_ids)) {
					$new_ticket_contact_ids[] = $item['Contacts']['Contact']['ContactID'];
					$new_ticket_contacts[] = array(
						'id' => $item['Contacts']['Contact']['ContactID'],
						'contact_name' => $item['Contacts']['Contact']['ContactName'],
						'data_status' => 'active',
						'data_group' => $user_group,
						'created_time' => $now,
						'created_user' => $user_id,
						'last_modified_time' => $now,
						'last_modified_user' => $user_id
					);
				}
				
				$new_tickets_contacts_relationships[] = array(
					'ticket' => $item['TicketID'],
					'ticket_contact' => $item['Contacts']['Contact']['ContactID'],
					'data_status' => 'active',
					'data_group' => $user_group,
					'created_time' => $now,
					'created_user' => $user_id,
					'last_modified_time' => $now,
					'last_modified_user' => $user_id
				);
			}
			else if(isset($item['Contacts']['Contact'][0])) {
				foreach($item['Contacts']['Contact'] as $current_ticket_contact) {
					if(!in_array($current_ticket_contact['ContactID'], $ticket_contact_ids)
						&& !in_array($current_ticket_contact['ContactID'], $new_ticket_contact_ids)) {
						$new_ticket_contact_ids[] = $current_ticket_contact['ContactID'];
						$new_ticket_contacts[] = array(
							'id' => $current_ticket_contact['ContactID'],
							'contact_name' => $current_ticket_contact['ContactName'],
							'data_status' => 'active',
							'data_group' => $user_group,
							'created_time' => $now,
							'created_user' => $user_id,
							'last_modified_time' => $now,
							'last_modified_user' => $user_id
						);
					}
					
					$new_tickets_contacts_relationships[] = array(
						'ticket' => $item['TicketID'],
						'ticket_contact' => $current_ticket_contact['ContactID'],
						'data_status' => 'active',
						'data_group' => $user_group,
						'created_time' => $now,
						'created_user' => $user_id,
						'last_modified_time' => $now,
						'last_modified_user' => $user_id
					);
				}
			}
		}
		
		for($i=0; $i<count($tickets); $i++) {
			foreach($tickets[$i] as $key => $value) {
				if($value == array()) {
					$tickets[$i][$key] = null;
				}
			}
		}
		
		$production_db = $this->load->database('prod', TRUE);
		
		$production_db->trans_start();
		if(!empty($tickets)) {
			//$production_db->insert_batch('tickets', $tickets);
			$production_db->update_batch('tickets', $tickets, 'id');
		}
		/*if(!empty($new_ticket_customers)) {
			$production_db->insert_batch('ticket_customers', $new_ticket_customers);
		}
		if(!empty($new_ticket_contacts)) {
			$production_db->insert_batch('ticket_contacts', $new_ticket_contacts);
		}
		if(!empty($new_tickets_customers_relationships)) {
			$production_db->insert_batch('tickets_customers_relationships', $new_tickets_customers_relationships);
		}
		if(!empty($new_tickets_contacts_relationships)) {
			$production_db->insert_batch('tickets_contacts_relationships', $new_tickets_contacts_relationships);
		}*/
		$production_db->trans_complete();
		
		$result['count'] = count($tickets);
		//$result['tickets'] = $array;
		
		return $result;
	}
	
	public function import_new_ticket_data() {
		$result = array();
		
		$user_group = !empty($this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group')) ? $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group') : 1;
		$user_id = !empty($this->session->userdata('chchdb_'.PROJECT_CODE.'_user_id')) ? $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_id') : 2;
		
		$production_db = $this->load->database('prod', TRUE);
		
		// Get Max Date Modified
		$query = $production_db
			->select('MAX(date_modified) AS max_date_modified')
			->from('tickets')
			->where('tickets.data_status', DATA_ACTIVE)
			->where('tickets.data_group', $user_group)
			->get()->result_array();
		
		$max_date_modified = !empty($query) ? $query[0]['max_date_modified'] : null;
		
		// Get Max Ticket ID
		$query = $production_db
			->select('MAX(id) AS max_ticket_id')
			->from('tickets')
			->where('tickets.data_status', DATA_ACTIVE)
			->where('tickets.data_group', $user_group)
			->get()->result_array();
		
		$max_ticket_id = !empty($query) ? $query[0]['max_ticket_id'] : 0;
		
		$ticket_customers_data = $production_db->select('id')->from('ticket_customers')->where('data_status', DATA_ACTIVE)->get()->result_array();
		$ticket_contacts_data = $production_db->select('id')->from('ticket_contacts')->where('data_status', DATA_ACTIVE)->get()->result_array();
		
		$ticket_customer_ids = array_column($ticket_customers_data, 'id');
		$ticket_contact_ids = array_column($ticket_contacts_data, 'id');
		
		$new_ticket_customer_ids = array();
		$new_ticket_contact_ids = array();
		$new_ticket_customers = array();
		$new_ticket_contacts = array();
		$new_tickets_customers_relationships = array();
		$new_tickets_contacts_relationships = array();
		
		$orgID = '1131188';
		$apiToken = 'eedaf4df-73c4-423c-807d-52db9605a120';
		
		$page = 1;
		$data_count_per_page = 200;
		
		while(true) {
			$host = 'https://app.teamsupport.com/api/xml/Tickets?pageNumber='.$page.'&pageSize='.$data_count_per_page.'&DateModified[gte]='.date('YmdHis', strtotime($max_date_modified));

			$base64str = base64_encode("$orgID:$apiToken");
			
			$process = curl_init();
			curl_setopt( $process, CURLOPT_URL, $host );
			curl_setopt($process, CURLOPT_HTTPHEADER,array('Content-Type: application/xml', 'Authorization: Basic '.$base64str));
			curl_setopt($process, CURLOPT_TIMEOUT, 30);
			curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
			
			$return = curl_exec($process);
			$response = curl_getinfo( $process );
			curl_close($process);
	  
			$xml = simplexml_load_string($return, null, LIBXML_NOCDATA);
			$json = json_encode($xml);
			$array = json_decode($json,TRUE);
			
			$now = date('Y-m-d H:i:s');
			
			$new_tickets = array();
			$updated_tickets = array();
			
			if(!empty($array['Ticket'])) {
				foreach($array['Ticket'] as $item) {
					if(empty($item['TicketID'])) continue;
					
					$customers = null;
					if(!empty($item['Customers']['Customer'])) {
						if(!empty($item['Customers']['Customer']['CustomerID'])) {
							$customers = $item['Customers']['Customer']['CustomerID'] . ';' . $item['Customers']['Customer']['CustomerName'];
						}
						else {
							$customers = '';
							for($i=0; $i<count($item['Customers']['Customer']); $i++) {
								if($i>0) $customers .= '|';
								$customers .= $item['Customers']['Customer'][$i]['CustomerID'] . ';' . $item['Customers']['Customer'][$i]['CustomerName'];
							}
						}
					}
					
					$contacts = null;
					if(!empty($item['Contacts']['Contact'])) {
						if(!empty($item['Contacts']['Contact']['ContactID'])) {
							$contacts = $item['Contacts']['Contact']['ContactID'] . ';' . $item['Contacts']['Contact']['ContactName'];
						}
						else {
							$contacts = '';
							for($i=0; $i<count($item['Contacts']['Contact']); $i++) {
								if($i>0) $contacts .= '|';
								$contacts .= $item['Contacts']['Contact'][$i]['ContactID'] . ';' . $item['Contacts']['Contact'][$i]['ContactName'];
							}
						}
					}
					
					$tags = null;
					if(!empty($item['Tags']['Tag'])) {
						if(!empty($item['Tags']['Tag']['Value'])) {
							$tags = $item['Tags']['Tag']['Value'] . ';' . $item['Tags']['Tag']['Value'];
						}
						else {
							$tags = '';
							for($i=0; $i<count($item['Tag']['Tag']); $i++) {
								if($i>0) $tags .= '|';
								$tags .= $item['Tags']['Tag'][$i]['Value'] . ';' . $item['Tags']['Tag'][$i]['Value'];
							}
						}
					}
					
					$ticket = array(
						'id' => $item['TicketID'],
						'product_name' => $item['ProductName'],
						'reported_version' => $item['ReportedVersion'],
						'solved_version' => $item['SolvedVersion'],
						'group_name' => $item['GroupName'],
						'ticket_type_name' => $item['TicketTypeName'],
						'user_name' => $item['UserName'],
						'status' => $item['Status'],
						'status_position' => $item['StatusPosition'],
						'severity_position' => $item['SeverityPosition'],
						'is_closed' => $item['IsClosed'] == 'True' ? true : false,
						'severity' => $item['Severity'],
						'ticket_number' => $item['TicketNumber'],
						'is_visible_on_portal' => $item['IsVisibleOnPortal'] == 'True' ? true : false,
						'is_knowledge_base' => $item['IsKnowledgeBase'] == 'True' ? true : false,
						'reported_version_id' => $item['ReportedVersionID'],
						'solved_version_id' => $item['SolvedVersionID'],
						'product_id' => $item['ProductID'],
						'group_id' => $item['GroupID'],
						'user_id' => $item['UserID'],
						'ticket_status_id' => $item['TicketStatusID'],
						'ticket_type_id' => $item['TicketTypeID'],
						'ticket_severity_id' => $item['TicketSeverityID'],
						'organization_id' => $item['OrganizationID'],
						'name' => $item['Name'],
						'parent_id' => $item['ParentID'],
						'modifier_id' => $item['ModifierID'],
						'creator_id' => $item['CreatorID'],
						'date_modified' =>empty($item['DateModified']) ? null : date_format(date_create_from_format('n/j/Y g:i A', $item['DateModified']), 'Y-m-d H:i'),
						'date_created' => empty($item['DateCreated']) ? null : date_format(date_create_from_format('n/j/Y g:i A', $item['DateCreated']), 'Y-m-d H:i'),
						'date_closed' => empty($item['DateClosed']) ? null : date_format(date_create_from_format('n/j/Y g:i A', $item['DateClosed']), 'Y-m-d H:i'),
						'closer_id' => $item['CloserID'],
						'days_closed' => $item['DaysClosed'],
						'days_opened' => $item['DaysOpened'],
						'closer_name' => $item['CloserName'],
						'creator_name' => is_string($item['CreatorName']) ? $item['CreatorName'] : null,
						'modifier_name' => is_string($item['ModifierName']) ? $item['ModifierName'] : null,
						'hours_spent' => $item['HoursSpent'],
						'sla_violation_time' => $item['SlaViolationTime'],
						'sla_warning_time' => $item['SlaWarningTime'],
						'sla_violation_hours' => $item['SlaViolationHours'],
						'sla_warning_hours' => $item['SlaWarningHours'],
						'knowledge_base_category_id' => $item['KnowledgeBaseCategoryID'],
						'knowledge_base_category_name' => $item['KnowledgeBaseCategoryName'],
						'due_date' => empty($item['DueDate']) ? null: date_format(date_create_from_format('n/j/Y', $item['DueDate']), 'Y-m-d'),
						'ticket_source' => $item['TicketSource'],
						'jira_key' => $item['JiraKey'],
						'tags' => $tags,
						'data_status' => 'active',
						'data_group' => $user_group,
						'last_modified_time' => $now,
						'last_modified_user' => $user_id
					);
					
					if(isset($item['Customers']['Customer']['CustomerID'])) {
						if(!in_array($item['Customers']['Customer']['CustomerID'], $ticket_customer_ids) 
							&& !in_array($item['Customers']['Customer']['CustomerID'], $new_ticket_customer_ids)) {
							$new_ticket_customer_ids[] = $item['Customers']['Customer']['CustomerID'];
							$new_ticket_customers[] = array(
								'id' => $item['Customers']['Customer']['CustomerID'],
								'customer_name' => $item['Customers']['Customer']['CustomerName'],
								'data_status' => 'active',
								'data_group' => $user_group,
								'created_time' => $now,
								'created_user' => $user_id,
								'last_modified_time' => $now,
								'last_modified_user' => $user_id
							);
						}
						
						$new_tickets_customers_relationships[] = array(
							'ticket' => $item['TicketID'],
							'ticket_customer' => $item['Customers']['Customer']['CustomerID'],
							'data_status' => 'active',
							'data_group' => $user_group,
							'created_time' => $now,
							'created_user' => $user_id,
							'last_modified_time' => $now,
							'last_modified_user' => $user_id
						);
					}
					else if(isset($item['Customers']['Customer'][0])) {
						foreach($item['Customers']['Customer'] as $current_ticket_customer) {
							if(!in_array($current_ticket_customer['CustomerID'], $ticket_customer_ids)
								&& !in_array($current_ticket_customer['CustomerID'], $new_ticket_customer_ids)) {
								$new_ticket_customer_ids[] = $current_ticket_customer['CustomerID'];
								$new_ticket_customers[] = array(
									'id' => $current_ticket_customer['CustomerID'],
									'customer_name' => $current_ticket_customer['CustomerName'],
									'data_status' => 'active',
									'data_group' => $user_group,
									'created_time' => $now,
									'created_user' => $user_id,
									'last_modified_time' => $now,
									'last_modified_user' => $user_id
								);
							}
							
							$new_tickets_customers_relationships[] = array(
								'ticket' => $item['TicketID'],
								'ticket_customer' => $current_ticket_customer['CustomerID'],
								'data_status' => 'active',
								'data_group' => $user_group,
								'created_time' => $now,
								'created_user' => $user_id,
								'last_modified_time' => $now,
								'last_modified_user' => $user_id
							);
						}
					}
					
					if(isset($item['Contacts']['Contact']['ContactID'])) {
						if(!in_array($item['Contacts']['Contact']['ContactID'], $ticket_contact_ids)
							&& !in_array($item['Contacts']['Contact']['ContactID'], $new_ticket_contact_ids)) {
							$new_ticket_contact_ids[] = $item['Contacts']['Contact']['ContactID'];
							$new_ticket_contacts[] = array(
								'id' => $item['Contacts']['Contact']['ContactID'],
								'contact_name' => $item['Contacts']['Contact']['ContactName'],
								'data_status' => 'active',
								'data_group' => $user_group,
								'created_time' => $now,
								'created_user' => $user_id,
								'last_modified_time' => $now,
								'last_modified_user' => $user_id
							);
						}
						
						$new_tickets_contacts_relationships[] = array(
							'ticket' => $item['TicketID'],
							'ticket_contact' => $item['Contacts']['Contact']['ContactID'],
							'data_status' => 'active',
							'data_group' => $user_group,
							'created_time' => $now,
							'created_user' => $user_id,
							'last_modified_time' => $now,
							'last_modified_user' => $user_id
						);
					}
					else if(isset($item['Contacts']['Contact'][0])) {
						foreach($item['Contacts']['Contact'] as $current_ticket_contact) {
							if(!in_array($current_ticket_contact['ContactID'], $ticket_contact_ids)
								&& !in_array($current_ticket_contact['ContactID'], $new_ticket_contact_ids)) {
								$new_ticket_contact_ids[] = $current_ticket_contact['ContactID'];
								$new_ticket_contacts[] = array(
									'id' => $current_ticket_contact['ContactID'],
									'contact_name' => $current_ticket_contact['ContactName'],
									'data_status' => 'active',
									'data_group' => $user_group,
									'created_time' => $now,
									'created_user' => $user_id,
									'last_modified_time' => $now,
									'last_modified_user' => $user_id
								);
							}
							
							$new_tickets_contacts_relationships[] = array(
								'ticket' => $item['TicketID'],
								'ticket_contact' => $current_ticket_contact['ContactID'],
								'data_status' => 'active',
								'data_group' => $user_group,
								'created_time' => $now,
								'created_user' => $user_id,
								'last_modified_time' => $now,
								'last_modified_user' => $user_id
							);
						}
					}
		
					if(intval($item['TicketID']) > $max_ticket_id) { // new data
						$ticket['created_time'] = $now;
						$ticket['created_user'] = $user_id;
						$new_tickets[] = $ticket;
					}
					else {
						$updated_tickets[] = $ticket;
					}
				}
			}
			
			for($i=0; $i<count($new_tickets); $i++) {
				foreach($new_tickets[$i] as $key => $value) {
					if($value == array()) {
						$new_tickets[$i][$key] = null;
					}
				}
			}
			
			for($i=0; $i<count($updated_tickets); $i++) {
				foreach($updated_tickets[$i] as $key => $value) {
					if($value == array()) {
						$updated_tickets[$i][$key] = null;
					}
				}
			}
			
			$production_db->trans_start();
			if(!empty($new_tickets)) {
				foreach($new_tickets as $new_ticket) {
					$production_db->replace('tickets', $new_ticket);
				}
			}
			if(!empty($updated_tickets)) {
				$production_db->update_batch('tickets', $updated_tickets, 'id');
			}
			if(!empty($new_ticket_customers)) {
				$production_db->insert_batch('ticket_customers', $new_ticket_customers);
			}
			if(!empty($new_ticket_contacs)) {
				$production_db->insert_batch('ticket_contacts', $new_ticket_contacts);
			}
			if(!empty($new_tickets_customers_relationships)) {
				$production_db->insert_batch('tickets_customers_relationships', $new_tickets_customers_relationships);
			}
			if(!empty($new_tickets_contacts_relationships)) {
				$production_db->insert_batch('tickets_contacts_relationships', $new_tickets_contacts_relationships);
			}
			$production_db->trans_complete();
			
			$total_data = intval($array['TotalRecords']);
			
			if($total_data <= $page * $data_count_per_page) {
				break;
			}
			else {
				$page++;
			}
		}
		
		$result['success'] = true;
		
		return $result;
	}
	
	public function get_ticket_table_data($args) {
		$result = array();

		$production_db
			->select('tickets.*')
			->from('tickets')
			->where('tickets.data_status', DATA_ACTIVE)
			->where('tickets.data_group', $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group'))
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'));
		
		if(!empty($args['period_from'])) {
			$production_db->where('tickets.date_created >=', $args['period_from']);
		}
		if(!empty($args['period_to'])) {
			$production_db->where('tickets.date_created <=', $args['period_to'] . ' 23:59:59');
		}
		if(!empty($args['status'])) {
			$production_db->where('tickets.status', $args['status']);
		}

		$result = $production_db->get()->result_array();

		foreach($result as $key => $value) {
			$result[$key]['e'] = '';
			$result[$key]['row_id'] = 'row-order-' . $result[$key]['id'];
			$result[$key]['ticket_number'] = '<a href="'.base_url('db/view/ticket/'.$result[$key]['id'].'/work_order').'">'.$result[$key]['ticket_number'].'</a>';
		}
		
		return $result;
	}
	
	public function get_client_support_board_data($args) {
		$result = $args;
		
		$production_db = $this->load->database('prod', TRUE);
		
		// $this->import_new_ticket_data();
		
		$user_group = $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group');
		
		$excluded_creator_modifier_ids = array(
			4704209, // Ecosa Support
			5616910,  // Ecosa Sleep
			5833602 // Valued Customer (Secretlab)
		);
		
		$timezone = -5;
		$timezone += date('I'); // Daylight saving time
		$page_generated_time = date('Y-m-d H:i:s', strtotime(($timezone > 0 ? '+' : '').$timezone.' hours '.gmdate('Y-m-d H:i:s')));
		
		$customer_filter = "tickets.id IN (SELECT ticket FROM tickets_customers_relationships WHERE tickets_customers_relationships.data_status='active' AND tickets_customers_relationships.ticket_customer=".$production_db->escape($args['customer']).")";
		$ticket_history_customer_filter = "ticket_history.ticket_id IN (SELECT ticket FROM tickets_customers_relationships WHERE tickets_customers_relationships.data_status='active' AND tickets_customers_relationships.ticket_customer=".$production_db->escape($args['customer']).")";
		
		$created_date_format = 'DATE(date_created)';
		$closed_date_format = 'DATE(date_closed)';
		switch($args['periodicity']) {
			case 'hourly':
				$created_date_format = 'DATE_FORMAT(date_created, "%Y-%m-%d %H:00:00")';
				$closed_date_format = 'DATE_FORMAT(date_closed, "%Y-%m-%d %H:00:00")';
				break;
			default:
				$created_date_format = 'DATE(date_created)';
				$closed_date_format = 'DATE(date_closed)';
				break;
		}
		
		// Get total open tickets per day
		
		// Get newly created tickets
		$production_db
			->select($created_date_format.' AS date_created, COUNT(*) AS new_tickets_count')
			->from('tickets')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
			->where('date_created >=', $args['period_from'])
			->where('date_created <', date('Y-m-d', strtotime('+1 day '.$args['period_to'])))
			->where_not_in('creator_id', $excluded_creator_modifier_ids)
			->where_not_in('modifier_id', $excluded_creator_modifier_ids)
			->group_by($created_date_format)
			->order_by($created_date_format);
		
		if(!empty($args['customer'])) {
			$production_db->where($customer_filter);
		}
		
		$new_tickets_data = $production_db->get()->result_array();
		
		$raw_new_tickets_count_by_date = array_combine(
			array_column($new_tickets_data, 'date_created'),
			array_column($new_tickets_data, 'new_tickets_count')
		);
		
		$new_tickets_count_by_date = array();
		for($date = $args['period_from']; ; ) {
			$mapped_date = $this->map_date($date, $args['periodicity']);
			
			if(!isset($new_tickets_count_by_date[$mapped_date])) {
				$new_tickets_count_by_date[$mapped_date] = array('total' => 0, 'count' => 0, 'average' => 0);
			}
			
			if(isset($raw_new_tickets_count_by_date[$date])) {
				$new_tickets_count_by_date[$mapped_date]['total'] += $raw_new_tickets_count_by_date[$date];
				$new_tickets_count_by_date[$mapped_date]['count']++;
				$new_tickets_count_by_date[$mapped_date]['average'] = $new_tickets_count_by_date[$mapped_date]['total'] / $new_tickets_count_by_date[$mapped_date]['count'];
			}
			
			switch($args['periodicity']) {
				case 'hourly':
					$date = date('Y-m-d H:i:s', strtotime('+1 hour '.$date));
					break;
				default:
					$date = date('Y-m-d', strtotime('+1 day '.$date));
			}
			
			if(strtotime($date) >= strtotime('+1 day ' . $args['period_to'])) break;
		}

		foreach($new_tickets_count_by_date as $mapped_date => $current_data) {
			$result['new_tickets_count_by_date'][$mapped_date] = round($current_data['total']);
		}
		
		// Get closed tickets
		$production_db
			->select($closed_date_format.' AS date_closed, COUNT(*) AS closed_tickets_count')
			->from('tickets')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
			->where('date_closed >=', $args['period_from'])
			->where('date_closed <', date('Y-m-d', strtotime('+1 day '.$args['period_to'])))
			->where_not_in('creator_id', $excluded_creator_modifier_ids)
			->where_not_in('modifier_id', $excluded_creator_modifier_ids)
			->group_by($closed_date_format)
			->order_by($closed_date_format);
		
		if(!empty($args['customer'])) {
			$production_db->where($customer_filter);
		}
		
		$closed_tickets_data = $production_db->get()->result_array();
		
		$raw_closed_tickets_count_by_date = array_combine(
			array_column($closed_tickets_data, 'date_closed'),
			array_column($closed_tickets_data, 'closed_tickets_count')
		);
		
		$closed_tickets_count_by_date = array();
		for($date = $args['period_from']; ; ) {
			$mapped_date = $this->map_date($date, $args['periodicity']);
			
			if(!isset($closed_tickets_count_by_date[$mapped_date])) {
				$closed_tickets_count_by_date[$mapped_date] = array('total' => 0, 'count' => 0, 'average' => 0);
			}
			
			if(isset($raw_closed_tickets_count_by_date[$date])) {
				$closed_tickets_count_by_date[$mapped_date]['total'] += $raw_closed_tickets_count_by_date[$date];
				$closed_tickets_count_by_date[$mapped_date]['count']++;
				$closed_tickets_count_by_date[$mapped_date]['average'] = $closed_tickets_count_by_date[$mapped_date]['total'] / $closed_tickets_count_by_date[$mapped_date]['count'];
			}
			
			switch($args['periodicity']) {
				case 'hourly':
					$date = date('Y-m-d H:i:s', strtotime('+1 hour '.$date));
					break;
				default:
					$date = date('Y-m-d', strtotime('+1 day '.$date));
			}
			
			if(strtotime($date) >= strtotime('+1 day ' . $args['period_to'])) break;
		}
		
		$result['ticket_efficiency'] = array();
		foreach($closed_tickets_count_by_date as $mapped_date => $current_data) {
			$result['closed_tickets_count_by_date'][$mapped_date] = round($current_data['total']);
			$result['ticket_efficiency'][$mapped_date] = number_format((!empty($result['new_tickets_count_by_date'][$mapped_date]) ? $result['closed_tickets_count_by_date'][$mapped_date] / $result['new_tickets_count_by_date'][$mapped_date] * 100 : 0),2);
		}
		
		// Get open tickets on {period_from}
		$open_tickets_count_by_date = array();
		
		switch($args['periodicity']) {
			case 'hourly':
				$production_db
					->select('COUNT(*) AS open_tickets_count')
					->from('tickets')
					->where('data_status', DATA_ACTIVE)
					->where('data_group', $user_group)
					->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
					->where('date_created <', $args['period_from'])
					->where_not_in('creator_id', $excluded_creator_modifier_ids)
					->where_not_in('modifier_id', $excluded_creator_modifier_ids)
					->group_start()
						->where('date_closed >=', $args['period_from'])
						->or_group_start()
							->where('date_closed IS NULL', null, false)
							->not_like('status', 'Closed')
						->group_end()
					->group_end();
				
				if(!empty($args['customer'])) {
					$production_db->where($customer_filter);
				}
					
				$initial_open_tickets_data = $production_db->get()->result_array();
				break;
			default:
				$production_db
					->select('COUNT(*) AS open_tickets_count')
					->from('tickets')
					->where('data_status', DATA_ACTIVE)
					->where('data_group', $user_group)
					->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
					->where('date_created <', date('Y-m-d', strtotime('+1 day '.$args['period_from'])))
					->where_not_in('creator_id', $excluded_creator_modifier_ids)
					->where_not_in('modifier_id', $excluded_creator_modifier_ids)
					->group_start()
						->where('date_closed >=', date('Y-m-d', strtotime('+1 day '.$args['period_from'])))
						->or_group_start()
							->where('date_closed IS NULL', null, false)
							->not_like('status', 'Closed')
						->group_end()
					->group_end();
				
				if(!empty($args['customer'])) {
					$production_db->where($customer_filter);
				}
				
				$initial_open_tickets_data = $production_db->get()->result_array();
				break;
		}

		$production_db
			->select($created_date_format.' AS date_created, COUNT(*) AS created_tickets_count')
			->from('tickets')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
			->where('date_created >=', $args['period_from'])
			->where('date_created <', date('Y-m-d', strtotime('+1 day '.$args['period_to'])))
			->where_not_in('creator_id', $excluded_creator_modifier_ids)
			->where_not_in('modifier_id', $excluded_creator_modifier_ids)
			->group_by($created_date_format);
		
		if(!empty($args['customer'])) {
			$production_db->where($customer_filter);
		}		
		
		$created_tickets_by_date_data = $production_db->get()->result_array();
		
		$production_db
			->select($closed_date_format.' AS date_closed, COUNT(*) AS closed_tickets_count')
			->from('tickets')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
			->where('date_closed >=', $args['period_from'])
			->where('date_closed <', date('Y-m-d', strtotime('+1 day '.$args['period_to'])))
			->where_not_in('creator_id', $excluded_creator_modifier_ids)
			->where_not_in('modifier_id', $excluded_creator_modifier_ids)
			->group_by($closed_date_format);
		
		if(!empty($args['customer'])) {
			$production_db->where($customer_filter);
		}	
		
		$closed_tickets_by_date_data = $production_db->get()->result_array();
		
		$created_and_closed_tickets_count_by_date = array();
		foreach($created_tickets_by_date_data as $current_data) {
			$created_and_closed_tickets_count_by_date[$current_data['date_created']] = array('created_tickets_count' => $current_data['created_tickets_count'], 'closed_tickets_count' => 0);
		}
		
		foreach($closed_tickets_by_date_data as $current_data) {
			if(!isset($created_and_closed_tickets_count_by_date[$current_data['date_closed']])) {
				$created_and_closed_tickets_count_by_date[$current_data['date_closed']] = array('created_tickets_count' => 0, 'closed_tickets_count' => 0);
			}
			
			$created_and_closed_tickets_count_by_date[$current_data['date_closed']]['closed_tickets_count'] = $current_data['closed_tickets_count'];
		}
		
		switch($args['periodicity']) {
			case 'hourly':
				$mapped_date = $this->map_date($args['period_from'] . ' 00:00:00', $args['periodicity']);			
				break;
			default:
				$mapped_date = $this->map_date($args['period_from'], $args['periodicity']);
				break;
		}
		
		$open_tickets_count_by_date[$mapped_date] = array(
			'total' => $initial_open_tickets_data[0]['open_tickets_count'],
			'count' => 1,
			'average' => $initial_open_tickets_data[0]['open_tickets_count']
		);
		$previous_day_open_tickets_count = $initial_open_tickets_data[0]['open_tickets_count'];
		
		for($date = $args['period_from']; ; ) {
			switch($args['periodicity']) {
				case 'hourly':
					$date = date('Y-m-d H:i:s', strtotime('+1 hour '.$date));
					break;
				default:
					$date = date('Y-m-d', strtotime('+1 day '.$date));
			}
			
			if(strtotime($date) >= strtotime('+1 day ' . $args['period_to'])) break;
			
			$mapped_date = $this->map_date($date, $args['periodicity']);
			
			if(!isset($open_tickets_count_by_date[$mapped_date])) {
				$open_tickets_count_by_date[$mapped_date] = array('total' => 0, 'count' => 0, 'average' => 0);
			}
			
			if($args['periodicity'] == 'hourly' && strtotime($date) > strtotime($page_generated_time)) {
				$open_tickets_count_by_date[$mapped_date]['average'] = null;
			}
			else {
				$this_day_open_tickets_count = $previous_day_open_tickets_count;
				
				if(isset($created_and_closed_tickets_count_by_date[$date])) {
					$this_day_open_tickets_count = $this_day_open_tickets_count +
						$created_and_closed_tickets_count_by_date[$date]['created_tickets_count'] -
						$created_and_closed_tickets_count_by_date[$date]['closed_tickets_count'];
				}
				
				$open_tickets_count_by_date[$mapped_date]['total'] += $this_day_open_tickets_count;
				$open_tickets_count_by_date[$mapped_date]['count']++;
				$open_tickets_count_by_date[$mapped_date]['average'] = $open_tickets_count_by_date[$mapped_date]['total'] / $open_tickets_count_by_date[$mapped_date]['count'];
				
				$previous_day_open_tickets_count = $this_day_open_tickets_count;
			}
		}
		
		$result['open_tickets_count_by_date'] = array();

		foreach($open_tickets_count_by_date as $mapped_date => $current_data) {
			$result['open_tickets_count_by_date'][$mapped_date] = round($current_data['average']);
		}	
		
		// Open tickets count by type and date
		switch($args['periodicity']) {
			case 'hourly':
				$production_db
					->select('ticket_type_name, COUNT(*) AS open_tickets_count')
					->from('tickets')
					->where('data_status', DATA_ACTIVE)
					->where('data_group', $user_group)
					->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
					->where('date_created <', $args['period_from'])
					->where_not_in('creator_id', $excluded_creator_modifier_ids)
					->where_not_in('modifier_id', $excluded_creator_modifier_ids)
					->group_start()
						->where('date_closed >=', $args['period_from'])
						->or_group_start()
							->where('date_closed IS NULL', null, false)
							->not_like('status', 'Closed')
						->group_end()
					->group_end()
					->group_by('ticket_type_name');
				
				if(!empty($args['customer'])) {
					$production_db->where($customer_filter);
				}
				
				$initial_open_tickets_data_by_type = $production_db->get()->result_array();
				break;
			default:
				$production_db
					->select('ticket_type_name, COUNT(*) AS open_tickets_count')
					->from('tickets')
					->where('data_status', DATA_ACTIVE)
					->where('data_group', $user_group)
					->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
					->where('date_created <', date('Y-m-d', strtotime('+1 day '.$args['period_from'])))
					->where_not_in('creator_id', $excluded_creator_modifier_ids)
					->where_not_in('modifier_id', $excluded_creator_modifier_ids)
					->group_start()
						->where('date_closed >=', date('Y-m-d', strtotime('+1 day '.$args['period_from'])))
						->or_group_start()
							->where('date_closed IS NULL', null, false)
							->not_like('status', 'Closed')
						->group_end()
					->group_end()
					->group_by('ticket_type_name');
				
				if(!empty($args['customer'])) {
					$production_db->where($customer_filter);
				}
				
				$initial_open_tickets_data_by_type = $production_db->get()->result_array();
				break;
		}
		
		$production_db
			->select($created_date_format.' AS date_created, ticket_type_name, COUNT(*) AS created_tickets_count')
			->from('tickets')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
			->where('date_created >=', $args['period_from'])
			->where('date_created <', date('Y-m-d', strtotime('+1 day '.$args['period_to'])))
			->where_not_in('creator_id', $excluded_creator_modifier_ids)
			->where_not_in('modifier_id', $excluded_creator_modifier_ids)
			->group_by($created_date_format.', ticket_type_name');
		
		if(!empty($args['customer'])) {
			$production_db->where($customer_filter);
		}
				
		$created_tickets_by_type_and_date_data = $production_db->get()->result_array();
		
		$production_db
			->select($closed_date_format.' AS date_closed, ticket_type_name, COUNT(*) AS closed_tickets_count')
			->from('tickets')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
			->where('date_closed >=', $args['period_from'])
			->where('date_closed <', date('Y-m-d', strtotime('+1 day '.$args['period_to'])))
			->where_not_in('creator_id', $excluded_creator_modifier_ids)
			->where_not_in('modifier_id', $excluded_creator_modifier_ids)
			->group_by($closed_date_format.', ticket_type_name');
		
		if(!empty($args['customer'])) {
			$production_db->where($customer_filter);
		}
		
		$closed_tickets_by_type_and_date_data = $production_db->get()->result_array();
		
		$previous_day_open_tickets_by_type_count = array_combine(
			array_column($initial_open_tickets_data_by_type, 'ticket_type_name'),
			array_column($initial_open_tickets_data_by_type, 'open_tickets_count')
		);
		
		$created_and_closed_tickets_count_by_type_and_date = array();
		foreach($created_tickets_by_type_and_date_data as $current_data) {
			if(empty($current_data['ticket_type_name'])) {
				$current_data['ticket_type_name'] = 'No Type';
			}
			$created_and_closed_tickets_count_by_type_and_date[$current_data['ticket_type_name'].$current_data['date_created']] = array('created_tickets_count' => $current_data['created_tickets_count'], 'closed_tickets_count' => 0, 'average_tickets_age' => 0);
			
			if(!isset($previous_day_open_tickets_by_type_count[$current_data['ticket_type_name']])) {
				$previous_day_open_tickets_by_type_count[$current_data['ticket_type_name']] = 0;
			}
		}
		
		foreach($closed_tickets_by_type_and_date_data as $current_data) {
			if(empty($current_data['ticket_type_name'])) {
				$current_data['ticket_type_name'] = 'No Type';
			}
			if(!isset($created_and_closed_tickets_count_by_type_and_date[$current_data['ticket_type_name'].$current_data['date_closed']])) {
				$created_and_closed_tickets_count_by_type_and_date[$current_data['ticket_type_name'].$current_data['date_closed']] = array('created_tickets_count' => 0, 'closed_tickets_count' => 0, 'average_tickets_age' => 0);
			}
			
			$created_and_closed_tickets_count_by_type_and_date[$current_data['ticket_type_name'].$current_data['date_closed']]['closed_tickets_count'] = $current_data['closed_tickets_count'];
			
			if(!isset($previous_day_open_tickets_by_type_count[$current_data['ticket_type_name']])) {
				$previous_day_open_tickets_by_type_count[$current_data['ticket_type_name']] = 0;
			}	
		}
		
		$open_tickets_count_by_type_and_date = array();
		
		foreach($previous_day_open_tickets_by_type_count as $ticket_type_name => $open_tickets_count) {
			if(empty($ticket_type_name)) {
				$ticket_type_name = 'No Type';
			}
			
			switch($args['periodicity']) {
				case 'hourly':
					$mapped_date = $this->map_date($args['period_from'] . ' 00:00:00', $args['periodicity']);
					break;
				case 'daily':
					$mapped_date = $this->map_date($args['period_from'], $args['periodicity']);
					break;
			}
			
			$open_tickets_count_by_type_and_date[$ticket_type_name] = array();
			$open_tickets_count_by_type_and_date[$ticket_type_name][$mapped_date] = array('total' => $open_tickets_count, 'count' => 1, 'average' => $open_tickets_count);
		}

		for($date = $args['period_from']; ; ) {
			switch($args['periodicity']) {
				case 'hourly':
					$date = date('Y-m-d H:i:s', strtotime('+1 hour '.$date));
					break;
				default:
					$date = date('Y-m-d', strtotime('+1 day '.$date));
			}
			
			if(strtotime($date) >= strtotime('+1 day ' . $args['period_to'])) break;
			
			$mapped_date = $this->map_date($date, $args['periodicity']);
			
			if($args['periodicity'] == 'hourly' && strtotime($date) > strtotime($page_generated_time)) {
				break;
				// $open_tickets_count_by_type_and_date[$ticket_type_name][$mapped_date]['average'] = null;
			}
			else {
				foreach($previous_day_open_tickets_by_type_count as $ticket_type_name => $open_tickets_count) {
					if(!isset($open_tickets_count_by_type_and_date[$ticket_type_name])) {
						$open_tickets_count_by_type_and_date[$ticket_type_name] = array();
					}
					if(!isset($open_tickets_count_by_type_and_date[$ticket_type_name][$mapped_date])) {
						$open_tickets_count_by_type_and_date[$ticket_type_name][$mapped_date] = array('total' => 0, 'count' => 0, 'average' => 0);
					}
					
					$this_day_open_tickets = $open_tickets_count;
					
					if(isset($created_and_closed_tickets_count_by_type_and_date[$ticket_type_name.$date])) {
						$this_day_open_tickets = $this_day_open_tickets +
							$created_and_closed_tickets_count_by_type_and_date[$ticket_type_name.$date]['created_tickets_count'] -
							$created_and_closed_tickets_count_by_type_and_date[$ticket_type_name.$date]['closed_tickets_count'];
					}
					
					$open_tickets_count_by_type_and_date[$ticket_type_name][$mapped_date]['total'] += $this_day_open_tickets;
					$open_tickets_count_by_type_and_date[$ticket_type_name][$mapped_date]['count']++;
					$open_tickets_count_by_type_and_date[$ticket_type_name][$mapped_date]['average'] = $open_tickets_count_by_type_and_date[$ticket_type_name][$mapped_date]['total'] / $open_tickets_count_by_type_and_date[$ticket_type_name][$mapped_date]['count'];
					
					$previous_day_open_tickets_by_type_count[$ticket_type_name] = $this_day_open_tickets;
				}
			}
		}
		
		$result['open_tickets_count_by_type_and_date'] = array();
		foreach($open_tickets_count_by_type_and_date as $ticket_type_name => $ticket_count_by_type_data) {
			$result['open_tickets_count_by_type_and_date'][$ticket_type_name] = array();
			ksort($ticket_count_by_type_data);
			foreach($ticket_count_by_type_data as $mapped_date => $current_data) {
				$result['open_tickets_count_by_type_and_date'][$ticket_type_name][$mapped_date] = round($current_data['average']);
			}
		}
		
		$result['ticket_types'] = array_keys($open_tickets_count_by_type_and_date);
		
		$the_date = $args['periodicity'] == 'hourly' ? 'date' : 'DATE(date) AS date';
		$open_tickets_by_status_and_date_data_raw = $production_db
			->select($the_date.', ticket_status, open_tickets_count')
			->from('open_tickets_status_count')
			->where('periodicity', ($args['periodicity'] == 'hourly' ? 'hourly' : 'daily'))
			->where('date >=', $args['period_from'])
			->where('date <', date('Y-m-d', strtotime('+1 day '.$args['period_to'])))
			->get()->result_array();
			
		$open_tickets_by_status_and_date_data = array();
		foreach($open_tickets_by_status_and_date_data_raw as $current_data) {
			if(!isset($open_tickets_by_status_and_date_data[$current_data['ticket_status']])) {
				$open_tickets_by_status_and_date_data[$current_data['ticket_status']] = array();
			}
			$open_tickets_by_status_and_date_data[$current_data['ticket_status']][$current_data['date']] = $current_data['open_tickets_count'];
		}

		$open_tickets_by_status_and_date = array();
		for($date = $args['period_from']; ; ) {
			$mapped_date = $this->map_date($date, $args['periodicity']);
			
			if($args['periodicity'] == 'hourly' && strtotime($date) > strtotime($page_generated_time)) {
				break;
			}
			else {
				foreach($open_tickets_by_status_and_date_data as $ticket_status => $current_data) {
					if(!isset($open_tickets_count_by_status_and_date[$ticket_status])) {
						$open_tickets_count_by_status_and_date[$ticket_status] = array();
					}
					
					if(!isset($open_tickets_count_by_status_and_date[$ticket_status][$mapped_date])) {
						$open_tickets_count_by_status_and_date[$ticket_status][$mapped_date] = array('total' => 0, 'count' => 0, 'average' => 0);
					}

					if(isset($current_data[$date])) {
						$open_tickets_count_by_status_and_date[$ticket_status][$mapped_date]['total'] += $current_data[$date];
						$open_tickets_count_by_status_and_date[$ticket_status][$mapped_date]['count']++;
						$open_tickets_count_by_status_and_date[$ticket_status][$mapped_date]['average'] = $open_tickets_count_by_status_and_date[$ticket_status][$mapped_date]['total'] / $open_tickets_count_by_status_and_date[$ticket_status][$mapped_date]['count'];
					}	
				}
			}
			
			switch($args['periodicity']) {
				case 'hourly':
					$date = date('Y-m-d H:i:s', strtotime('+1 hour '.$date));
					break;
				default:
					$date = date('Y-m-d', strtotime('+1 day '.$date));
			}
			
			if(strtotime($date) >= strtotime('+1 day ' . $args['period_to'])) break;
		}

		$result['open_tickets_count_by_status_and_date'] = array();
		if(!empty($open_tickets_count_by_status_and_date)) {
			foreach($open_tickets_count_by_status_and_date as $ticket_status => $ticket_count_by_status_data) {
				$result['open_tickets_count_by_status_and_date'][$ticket_status] = array();
				ksort($ticket_count_by_status_data);
				foreach($ticket_count_by_status_data as $mapped_date => $current_data) {
					$result['open_tickets_count_by_status_and_date'][$ticket_status][$mapped_date] = round($current_data['average']);
				}
			}
		}
		
		$result['ticket_statuses'] = !empty($open_tickets_count_by_status_and_date) ? array_keys($open_tickets_count_by_status_and_date) : array();

		
		// Average Closed Tickets Age
		
		switch($args['periodicity']) {
			case 'weekly':
				$select_period_query_date_closed = 'DATE_ADD(DATE(date_closed), INTERVAL - WEEKDAY(DATE(date_closed)) DAY)';
				$select_period_query_date_created = 'DATE_ADD(DATE(date_created), INTERVAL - WEEKDAY(DATE(date_created)) DAY)';
				break;
			case 'monthly':
				$select_period_query_date_closed = 'DATE_FORMAT(date_closed, "%Y-%m-01")';
				$select_period_query_date_created = 'DATE_FORMAT(date_created, "%Y-%m-01")';
				break;
			case 'yearly':
				$select_period_query_date_closed = 'DATE_FORMAT(date_closed, "%Y-01-01")';
				$select_period_query_date_created = 'DATE_FORMAT(date_created, "%Y-01-01")';
				break;
			case 'hourly':
				$select_period_query_date_closed = 'DATE_FORMAT(date_closed, "%Y-%m-%d %k:00:00")';
				$select_period_query_date_created = 'DATE_FORMAT(date_created, "%Y-%m-%d %k:00:00")';
				break;
			case 'daily':
			default:
				$select_period_query_date_closed = 'DATE(date_closed)';
				$select_period_query_date_created = 'DATE(date_created)';
		}
		
		// Overall
		$production_db
			->select($select_period_query_date_closed.' AS date_closed, AVG(days_opened) AS average_tickets_age')
			->from('tickets')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
			->where('date_closed >=', $args['period_from'])
			->where('date_closed <', date('Y-m-d', strtotime('+1 day '.$args['period_to'])))
			->where_not_in('creator_id', $excluded_creator_modifier_ids)
			->where_not_in('modifier_id', $excluded_creator_modifier_ids)
			->group_by($select_period_query_date_closed);
		
		if(!empty($args['customer'])) {
			$production_db->where($customer_filter);
		}
		
		$average_closed_tickets_age_data = $production_db->get()->result_array();
		
		$average_tickets_age_by_type_and_date = array('All' => array());
		
		foreach($average_closed_tickets_age_data as $current_data) {
			$average_tickets_age_by_type_and_date['All'][$current_data['date_closed']] = $current_data['average_tickets_age'];
		}
		
		// By Type
		$production_db
			->select($select_period_query_date_closed.' AS date_closed, ticket_type_name, AVG(days_opened) AS average_tickets_age')
			->from('tickets')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
			->where('date_closed >=', $args['period_from'])
			->where('date_closed <', date('Y-m-d', strtotime('+1 day '.$args['period_to'])))
			->where_not_in('creator_id', $excluded_creator_modifier_ids)
			->where_not_in('modifier_id', $excluded_creator_modifier_ids)
			->group_by($select_period_query_date_closed.', ticket_type_name');
			
		if(!empty($args['customer'])) {
			$production_db->where($customer_filter);
		}
		
		$average_closed_tickets_age_data = $production_db->get()->result_array();
		
		foreach($average_closed_tickets_age_data as $current_data) {
			if(!isset($average_tickets_age_by_type_and_date[$current_data['ticket_type_name']])) {
				$average_tickets_age_by_type_and_date[$current_data['ticket_type_name']] = array();
			}
			
			$average_tickets_age_by_type_and_date[$current_data['ticket_type_name']][$current_data['date_closed']] = $current_data['average_tickets_age'];
		}
		
		$result['average_tickets_age_by_type_and_date_for_chart'] = array();
		foreach($average_tickets_age_by_type_and_date as $ticket_type_name => $average_tickets_age) {
			$result['average_tickets_age_by_type_and_date_for_chart'][$ticket_type_name] = array();
			
			foreach($average_tickets_age as $the_date => $the_age) {
				$result['average_tickets_age_by_type_and_date_for_chart'][$ticket_type_name][] = array($the_date, $the_age);
			}
		}
		$result['average_tickets_age_by_type_and_date'] = $average_tickets_age_by_type_and_date;
		
		// By Group
		$production_db
			->select($select_period_query_date_closed." AS date_closed, IF(ISNULL(tickets.group_name),'No Group',IF(LEFT(tickets.group_name,2)='IR','IR',IF(LEFT(tickets.group_name,2)='SL','SLC',tickets.group_name))) AS group_name, AVG(days_opened) AS average_tickets_age")
			->from('tickets')
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->where('date_closed >=', $args['period_from'])
			->where('date_closed <', date('Y-m-d', strtotime('+1 day '.$args['period_to'])))
			->where_not_in('creator_id', $excluded_creator_modifier_ids)
			->where_not_in('modifier_id', $excluded_creator_modifier_ids)
			->group_by($select_period_query_date_closed.", IF(ISNULL(tickets.group_name),'No Group',IF(LEFT(tickets.group_name,2)='IR','IR',IF(LEFT(tickets.group_name,2)='SL','SLC',tickets.group_name)))");
		
		if(!empty($args['customer'])) {
			$production_db->where($customer_filter);
		}
		
		$average_closed_tickets_age_by_group_data = $production_db->get()->result_array();
		$average_tickets_age_by_group_and_date = array();
		
		foreach($average_closed_tickets_age_by_group_data as $current_data) {
			if(!isset($average_tickets_age_by_group_and_date[$current_data['group_name']])) {
				$average_tickets_age_by_group_and_date[$current_data['group_name']] = array();
			}
			
			$average_tickets_age_by_group_and_date[$current_data['group_name']][$current_data['date_closed']] = $current_data['average_tickets_age'];
		}
		
		$result['average_tickets_age_by_group_and_date_for_chart'] = array();
		if(!empty($average_tickets_age_by_group_and_date)) {
			foreach($average_tickets_age_by_group_and_date as $group_name => $average_tickets_age) {
				$result['average_tickets_age_by_group_and_date_for_chart'][$group_name] = array();
				
				foreach($average_tickets_age as $the_date => $the_age) {
					$result['average_tickets_age_by_group_and_date_for_chart'][$group_name][] = array($the_date, $the_age);
				}
			}
		}
		$result['average_tickets_age_by_group_and_date'] = $average_tickets_age_by_group_and_date;
		
		// Initial Response Time
		
		$production_db
			->select($select_period_query_date_created.' AS date_created, AVG(mins_to_initial_response) AS average_initial_response_time')
			->from('tickets')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
			->where('tickets.date_created >=', $args['period_from'])
			->where('tickets.date_created <', date('Y-m-d', strtotime('+1 day '.$args['period_to'])))
			->where('mins_to_initial_response <', 480)
			->where('mins_to_initial_response IS NOT NULL', null, false)
			->where_not_in('DAYOFWEEK(date_created)', array(1,7))
			->where('HOUR(date_created) >=', 8)
			->where('HOUR(date_created) <', 17)
			->where_not_in('creator_id', $excluded_creator_modifier_ids)
			->where_not_in('modifier_id', $excluded_creator_modifier_ids)
			->not_like('creator_name', 'Red Stag')
			->group_by($select_period_query_date_created);
		
		if(!empty($args['customer'])) {
			$production_db->where($customer_filter);
		}
		
		$initial_response_time_data = $production_db->get()->result_array();
		
		$average_initial_response_time_by_date = array();
		
		if(!empty($initial_response_time_data)) {
			$average_initial_response_time_by_date = array_combine(
				array_column($initial_response_time_data, 'date_created'),
				array_column($initial_response_time_data, 'average_initial_response_time')
			);
		}
		
		$result['average_initial_response_time_by_date_for_chart'] = array();
		foreach($average_initial_response_time_by_date as $the_date => $initial_response_time) {
			$result['average_initial_response_time_by_date_for_chart'][] = array($the_date, $initial_response_time);
		}
		$result['average_initial_response_time_by_date'] = $average_initial_response_time_by_date;
		
		// Days Opened & Total Tickets by Group
		
		$production_db
			->select("group_name, AVG(days_opened) AS average_days_opened, COUNT(*) AS total_open_tickets", false)
			->from('tickets')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
			->where('tickets.date_closed IS NULL', null, false)
			//->where('date_created >=', date('Y-m-d', strtotime('-90 day')))
			//->where('date_created <', date('Y-m-d', strtotime('-59 day')))
			->group_by("group_name")
			->order_by("group_name");
			
		if(!empty($args['customer'])) {
			$production_db->where($customer_filter);
		}
		
		$tickets_by_group = $production_db->get()->result_array();
		
		$result['days_opened_by_group_for_chart'] = array();
		$result['total_tickets_opened_by_group_for_chart'] = array();
		
		foreach($tickets_by_group as $current_data) {
			$group_name = !empty($current_data['group_name']) ? $current_data['group_name'] : 'No Group';
			$result['days_opened_by_group_for_chart'][$group_name] = round($current_data['average_days_opened'],2);
			$result['total_tickets_opened_by_group_for_chart'][$group_name] = $current_data['total_open_tickets'];
		}
		
		// Open tickets count by group and date
		switch($args['periodicity']) {
			case 'hourly':
				$production_db
					->select("group_name, COUNT(*) AS open_tickets_count")
					->from('tickets')
					->where('data_status', DATA_ACTIVE)
					->where('data_group', $user_group)
					->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
					->where('date_created <', $args['period_from'])
					->where_not_in('creator_id', $excluded_creator_modifier_ids)
					->where_not_in('modifier_id', $excluded_creator_modifier_ids)
					->group_start()
						->where('date_closed >=', $args['period_from'])
						->or_group_start()
							->where('date_closed IS NULL', null, false)
							->not_like('status', 'Closed')
						->group_end()
					->group_end()
					->group_by("group_name");
				
				if(!empty($args['customer'])) {
					$production_db->where($customer_filter);
				}
				
				$initial_open_tickets_data_by_group = $production_db->get()->result_array();
				
				$production_db
					->select("IF(ISNULL(tickets.group_name),'No Group',IF(LEFT(tickets.group_name,2)='IR','IR',IF(LEFT(tickets.group_name,2)='SL','SLC',tickets.group_name))) AS group_name, COUNT(*) AS open_tickets_count")
					->from('tickets')
					->where('data_status', DATA_ACTIVE)
					->where('data_group', $user_group)
					->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
					->where('date_created <', $args['period_from'])
					->where_not_in('creator_id', $excluded_creator_modifier_ids)
					->where_not_in('modifier_id', $excluded_creator_modifier_ids)
					->group_start()
						->where('date_closed >=', $args['period_from'])
						->or_group_start()
							->where('date_closed IS NULL', null, false)
							->not_like('status', 'Closed')
						->group_end()
					->group_end()
					->group_by("IF(ISNULL(tickets.group_name),'No Group',IF(LEFT(tickets.group_name,2)='IR','IR',IF(LEFT(tickets.group_name,2)='SL','SLC',tickets.group_name)))");
					
				if(!empty($args['customer'])) {
					$production_db->where($customer_filter);
				}
				
				$initial_open_tickets_data_by_group_facility = $production_db->get()->result_array();
				
				break;
			default:
				$production_db
					->select("IF(ISNULL(group_name),'No Group',group_name) AS group_name, COUNT(*) AS open_tickets_count", false)
					->from('tickets')
					->where('data_status', DATA_ACTIVE)
					->where('data_group', $user_group)
					->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
					->where('date_created <', date('Y-m-d', strtotime('+1 day '.$args['period_from'])))
					->where_not_in('creator_id', $excluded_creator_modifier_ids)
					->where_not_in('modifier_id', $excluded_creator_modifier_ids)
					->group_start()
						->where('date_closed >=', date('Y-m-d', strtotime('+1 day '.$args['period_from'])))
						->or_group_start()
							->where('date_closed IS NULL', null, false)
							->not_like('status', 'Closed')
						->group_end()
					->group_end()
					->group_by("IF(ISNULL(group_name),'No Group',group_name)");
				
				if(!empty($args['customer'])) {
					$production_db->where($customer_filter);
				}				
					
				$initial_open_tickets_data_by_group = $production_db->get()->result_array();
			
				$production_db
					->select("IF(ISNULL(tickets.group_name),'No Group',IF(LEFT(tickets.group_name,2)='IR','IR',IF(LEFT(tickets.group_name,2)='SL','SLC',tickets.group_name))) AS group_name, COUNT(*) AS open_tickets_count", false)
					->from('tickets')
					->where('data_status', DATA_ACTIVE)
					->where('data_group', $user_group)
					->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
					->where('date_created <', date('Y-m-d', strtotime('+1 day '.$args['period_from'])))
					->where_not_in('creator_id', $excluded_creator_modifier_ids)
					->where_not_in('modifier_id', $excluded_creator_modifier_ids)
					->group_start()
						->where('date_closed >=', date('Y-m-d', strtotime('+1 day '.$args['period_from'])))
						->or_group_start()
							->where('date_closed IS NULL', null, false)
							->not_like('status', 'Closed')
						->group_end()
					->group_end()
					->group_by("IF(ISNULL(tickets.group_name),'No Group',IF(LEFT(tickets.group_name,2)='IR','IR',IF(LEFT(tickets.group_name,2)='SL','SLC',tickets.group_name)))");
				
				if(!empty($args['customer'])) {
					$production_db->where($customer_filter);
				}
				
				$initial_open_tickets_data_by_group_facility = $production_db->get()->result_array();
				
				break;
		}
		
		$production_db
			->select($created_date_format." AS date_created, group_name, COUNT(*) AS created_tickets_count", false)
			->from('tickets')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
			->where('date_created >=', $args['period_from'])
			->where('date_created <', date('Y-m-d', strtotime('+1 day '.$args['period_to'])))
			->where_not_in('creator_id', $excluded_creator_modifier_ids)
			->where_not_in('modifier_id', $excluded_creator_modifier_ids)
			->group_by($created_date_format.", group_name");
		
		if(!empty($args['customer'])) {
			$production_db->where($customer_filter);
		}
				
		$created_tickets_by_group_and_date_data = $production_db->get()->result_array();
		
		$production_db
			->select($created_date_format." AS date_created, IF(ISNULL(group_name),'No Group',IF(LEFT(group_name,2)='IR','IR',IF(LEFT(group_name,2)='SL','SLC',group_name))) AS group_name, COUNT(*) AS created_tickets_count", false)
			->from('tickets')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
			->where('date_created >=', $args['period_from'])
			->where('date_created <', date('Y-m-d', strtotime('+1 day '.$args['period_to'])))
			->where_not_in('creator_id', $excluded_creator_modifier_ids)
			->where_not_in('modifier_id', $excluded_creator_modifier_ids)
			->group_by($created_date_format.", IF(ISNULL(tickets.group_name),'No Group',IF(LEFT(tickets.group_name,2)='IR','IR',IF(LEFT(tickets.group_name,2)='SL','SLC',tickets.group_name)))");
		
		if(!empty($args['customer'])) {
			$production_db->where($customer_filter);
		}
		
		$created_tickets_by_group_facility_and_date_data = $production_db->get()->result_array();
		
		$production_db
			->select($closed_date_format." AS date_closed, group_name, COUNT(*) AS closed_tickets_count", false)
			->from('tickets')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
			->where('date_closed >=', $args['period_from'])
			->where('date_closed <', date('Y-m-d', strtotime('+1 day '.$args['period_to'])))
			->where_not_in('creator_id', $excluded_creator_modifier_ids)
			->where_not_in('modifier_id', $excluded_creator_modifier_ids)
			->group_by($closed_date_format.", group_name");
		
		if(!empty($args['customer'])) {
			$production_db->where($customer_filter);
		}
		
		$closed_tickets_by_group_and_date_data = $production_db->get()->result_array();
		
		$production_db
			->select($closed_date_format." AS date_closed, IF(ISNULL(group_name),'No Group',IF(LEFT(group_name,2)='IR','IR',IF(LEFT(group_name,2)='SL','SLC',group_name))) AS group_name, COUNT(*) AS closed_tickets_count", false)
			->from('tickets')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
			->where('date_closed >=', $args['period_from'])
			->where('date_closed <', date('Y-m-d', strtotime('+1 day '.$args['period_to'])))
			->where_not_in('creator_id', $excluded_creator_modifier_ids)
			->where_not_in('modifier_id', $excluded_creator_modifier_ids)
			->group_by($closed_date_format.", IF(ISNULL(tickets.group_name),'No Group',IF(LEFT(tickets.group_name,2)='IR','IR',IF(LEFT(tickets.group_name,2)='SL','SLC',tickets.group_name)))");
		
		if(!empty($args['customer'])) {
			$production_db->where($customer_filter);
		}
		
		$closed_tickets_by_group_facility_and_date_data = $production_db->get()->result_array();
	
		// GROUP
		$previous_day_open_tickets_by_group_count = array_combine(
			array_column($initial_open_tickets_data_by_group, 'group_name'),
			array_column($initial_open_tickets_data_by_group, 'open_tickets_count')
		);
		
		$created_and_closed_tickets_count_by_group = array();
		foreach($created_tickets_by_group_and_date_data as $current_data) {
			if(empty(trim($current_data['group_name']))) {
				$current_data['group_name'] = 'No Group';
			}
			$created_and_closed_tickets_count_by_group_and_date[$current_data['group_name'].$current_data['date_created']] = array('created_tickets_count' => $current_data['created_tickets_count'], 'closed_tickets_count' => 0, 'average_tickets_age' => 0);
			
			if(!isset($previous_day_open_tickets_by_group_count[$current_data['group_name']])) {
				$previous_day_open_tickets_by_group_count[$current_data['group_name']] = 0;
			}
		}
		
		foreach($closed_tickets_by_group_and_date_data as $current_data) {
			if(empty($current_data['group_name'])) {
				$current_data['group_name'] = 'No Group';
			}
			if(!isset($created_and_closed_tickets_count_by_group_and_date[$current_data['group_name'].$current_data['date_closed']])) {
				$created_and_closed_tickets_count_by_group_and_date[$current_data['group_name'].$current_data['date_closed']] = array('created_tickets_count' => 0, 'closed_tickets_count' => 0, 'average_tickets_age' => 0);
			}
			
			$created_and_closed_tickets_count_by_group_and_date[$current_data['group_name'].$current_data['date_closed']]['closed_tickets_count'] = $current_data['closed_tickets_count'];
			
			if(!isset($previous_day_open_tickets_by_group_count[$current_data['group_name']])) {
				$previous_day_open_tickets_by_group_count[$current_data['group_name']] = 0;
			}	
		}
				
		$open_tickets_count_by_group_and_date = array();
		
		foreach($previous_day_open_tickets_by_group_count as $group_name => $open_tickets_count) {
			if(empty(trim($group_name))) {
				$group_name = 'No Group';
			}
			
			switch($args['periodicity']) {
				case 'hourly':
					$mapped_date = $this->map_date($args['period_from'] . ' 00:00:00', $args['periodicity']);
					break;
				case 'daily':
					$mapped_date = $this->map_date($args['period_from'], $args['periodicity']);
					break;
			}
			
			$open_tickets_count_by_group_and_date[$group_name] = array();
			$open_tickets_count_by_group_and_date[$group_name][$mapped_date] = array('total' => $open_tickets_count, 'count' => 1, 'average' => $open_tickets_count);
		}

		for($date = $args['period_from']; ; ) {
			switch($args['periodicity']) {
				case 'hourly':
					$date = date('Y-m-d H:i:s', strtotime('+1 hour '.$date));
					break;
				default:
					$date = date('Y-m-d', strtotime('+1 day '.$date));
			}
			
			if(strtotime($date) >= strtotime('+1 day ' . $args['period_to'])) break;
			
			$mapped_date = $this->map_date($date, $args['periodicity']);
			
			if($args['periodicity'] == 'hourly' && strtotime($date) > strtotime($page_generated_time)) {
				break;
			}
			else {
				foreach($previous_day_open_tickets_by_group_count as $group_name => $open_tickets_count) {
					if(!isset($open_tickets_count_by_group_and_date[$group_name])) {
						$open_tickets_count_by_group_and_date[$group_name] = array();
					}
					if(!isset($open_tickets_count_by_group_and_date[$group_name][$mapped_date])) {
						$open_tickets_count_by_group_and_date[$group_name][$mapped_date] = array('total' => 0, 'count' => 0, 'average' => 0);
					}
					
					$this_day_open_tickets = $open_tickets_count;
					
					if(isset($created_and_closed_tickets_count_by_group_and_date[$group_name.$date])) {
						$this_day_open_tickets = $this_day_open_tickets +
							$created_and_closed_tickets_count_by_group_and_date[$group_name.$date]['created_tickets_count'] -
							$created_and_closed_tickets_count_by_group_and_date[$group_name.$date]['closed_tickets_count'];
					}
					
					$open_tickets_count_by_group_and_date[$group_name][$mapped_date]['total'] += $this_day_open_tickets;
					$open_tickets_count_by_group_and_date[$group_name][$mapped_date]['count']++;
					$open_tickets_count_by_group_and_date[$group_name][$mapped_date]['average'] = $open_tickets_count_by_group_and_date[$group_name][$mapped_date]['total'] / $open_tickets_count_by_group_and_date[$group_name][$mapped_date]['count'];
					
					$previous_day_open_tickets_by_group_count[$group_name] = $this_day_open_tickets;
				}
			}
		}
		
		$result['open_tickets_count_by_group_and_date'] = array();
		foreach($open_tickets_count_by_group_and_date as $group_name => $ticket_count_by_group_data) {
			$result['open_tickets_count_by_group_and_date'][$group_name] = array();
			ksort($ticket_count_by_group_data);
			foreach($ticket_count_by_group_data as $mapped_date => $current_data) {
				$result['open_tickets_count_by_group_and_date'][$group_name][$mapped_date] = round($current_data['average']);
			}
		}
		
		// GROUP FACILITY
		$previous_day_open_tickets_by_group_facility_count = array_combine(
			array_column($initial_open_tickets_data_by_group_facility, 'group_name'),
			array_column($initial_open_tickets_data_by_group_facility, 'open_tickets_count')
		);
		
		$created_and_closed_tickets_count_by_group_facility = array();
		foreach($created_tickets_by_group_facility_and_date_data as $current_data) {
			if(empty($current_data['group_name'])) {
				$current_data['group_name'] = 'No Group';
			}
			$created_and_closed_tickets_count_by_group_facility_and_date[$current_data['group_name'].$current_data['date_created']] = array('created_tickets_count' => $current_data['created_tickets_count'], 'closed_tickets_count' => 0, 'average_tickets_age' => 0);
			
			if(!isset($previous_day_open_tickets_by_group_facility_count[$current_data['group_name']])) {
				$previous_day_open_tickets_by_group_count[$current_data['group_name']] = 0;
			}
		}
		
		foreach($closed_tickets_by_group_facility_and_date_data as $current_data) {
			if(empty($current_data['group_name'])) {
				$current_data['group_name'] = 'No Group';
			}
			if(!isset($created_and_closed_tickets_count_by_group_facility_and_date[$current_data['group_name'].$current_data['date_closed']])) {
				$created_and_closed_tickets_count_by_group_facility_and_date[$current_data['group_name'].$current_data['date_closed']] = array('created_tickets_count' => 0, 'closed_tickets_count' => 0, 'average_tickets_age' => 0);
			}
			
			$created_and_closed_tickets_count_by_group_facility_and_date[$current_data['group_name'].$current_data['date_closed']]['closed_tickets_count'] = $current_data['closed_tickets_count'];
			
			if(!isset($previous_day_open_tickets_by_group_facility_count[$current_data['group_name']])) {
				$previous_day_open_tickets_by_group_facility_count[$current_data['group_name']] = 0;
			}	
		}
				
		$open_tickets_count_by_group_facility_and_date = array();
		
		foreach($previous_day_open_tickets_by_group_facility_count as $group_name => $open_tickets_count) {
			if(empty($group_name)) {
				$group_name = 'No Group';
			}
			
			switch($args['periodicity']) {
				case 'hourly':
					$mapped_date = $this->map_date($args['period_from'] . ' 00:00:00', $args['periodicity']);
					break;
				case 'daily':
					$mapped_date = $this->map_date($args['period_from'], $args['periodicity']);
					break;
			}
			
			$open_tickets_count_by_group_facility_and_date[$group_name] = array();
			$open_tickets_count_by_group_facility_and_date[$group_name][$mapped_date] = array('total' => $open_tickets_count, 'count' => 1, 'average' => $open_tickets_count);
		}

		for($date = $args['period_from']; ; ) {
			switch($args['periodicity']) {
				case 'hourly':
					$date = date('Y-m-d H:i:s', strtotime('+1 hour '.$date));
					break;
				default:
					$date = date('Y-m-d', strtotime('+1 day '.$date));
			}
			
			if(strtotime($date) >= strtotime('+1 day ' . $args['period_to'])) break;
			
			$mapped_date = $this->map_date($date, $args['periodicity']);
			
			if($args['periodicity'] == 'hourly' && strtotime($date) > strtotime($page_generated_time)) {
				break;
			}
			else {
				foreach($previous_day_open_tickets_by_group_facility_count as $group_name => $open_tickets_count) {
					if(!isset($open_tickets_count_by_group_facility_and_date[$group_name])) {
						$open_tickets_count_by_group_facility_and_date[$group_name] = array();
					}
					if(!isset($open_tickets_count_by_group_facility_and_date[$group_name][$mapped_date])) {
						$open_tickets_count_by_group_facility_and_date[$group_name][$mapped_date] = array('total' => 0, 'count' => 0, 'average' => 0);
					}
					
					$this_day_open_tickets = $open_tickets_count;
					
					if(isset($created_and_closed_tickets_count_by_group_facility_and_date[$group_name.$date])) {
						$this_day_open_tickets = $this_day_open_tickets +
							$created_and_closed_tickets_count_by_group_facility_and_date[$group_name.$date]['created_tickets_count'] -
							$created_and_closed_tickets_count_by_group_facility_and_date[$group_name.$date]['closed_tickets_count'];
					}
					
					$open_tickets_count_by_group_facility_and_date[$group_name][$mapped_date]['total'] += $this_day_open_tickets;
					$open_tickets_count_by_group_facility_and_date[$group_name][$mapped_date]['count']++;
					$open_tickets_count_by_group_facility_and_date[$group_name][$mapped_date]['average'] = $open_tickets_count_by_group_facility_and_date[$group_name][$mapped_date]['count'] > 0 ?$open_tickets_count_by_group_facility_and_date[$group_name][$mapped_date]['total'] / $open_tickets_count_by_group_facility_and_date[$group_name][$mapped_date]['count'] : 0;
					
					$previous_day_open_tickets_by_group_facility_count[$group_name] = $this_day_open_tickets;
				}
			}
		}
				
		$result['open_tickets_count_by_group_facility_and_date'] = array();
		foreach($open_tickets_count_by_group_facility_and_date as $group_name => $ticket_count_by_group_facility_data) {
			$result['open_tickets_count_by_group_facility_and_date'][$group_name] = array();
			ksort($ticket_count_by_group_facility_data);
			foreach($ticket_count_by_group_facility_data as $mapped_date => $current_data) {
				$result['open_tickets_count_by_group_facility_and_date'][$group_name][$mapped_date] = round($current_data['average']);
			}
		}
		
		$result['ticket_groups'] = array_keys($open_tickets_count_by_group_and_date);
		
		// Top Users with Assigned Open Tickets
		$production_db
			->select('user_name, COUNT(*) AS total')
			->from('tickets')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
			->where('date_closed IS NULL', null, false)
			->where_not_in('creator_id', $excluded_creator_modifier_ids)
			->where_not_in('modifier_id', $excluded_creator_modifier_ids)
			->not_like('status', 'Closed')
			->where('user_name IS NOT NULL', null, false)
			->group_by('user_name')
			->order_by('total', 'desc');
		
		if(!empty($args['customer'])) {
			$production_db->where($customer_filter);
		}
		
		$user_with_assigned_open_tickets_tmp = $production_db->get()->result_array();
		
		$user_with_assigned_open_tickets = array();
		if(!empty($user_with_assigned_open_tickets_tmp)) {
			foreach($user_with_assigned_open_tickets_tmp as $current_data) {
				$current_data['yesterday_total_actions'] = 0;
				$user_with_assigned_open_tickets[$current_data['user_name']] = $current_data;
			}
		}
		
		$production_db
			->select('actor, COUNT(*) AS total')
			->from('ticket_history')
			->group_start()
				->like('description', 'Added public action')
				->or_like('description', 'Added private action')
			->group_end()
			->where('date_created >=', date('Y-m-d', strtotime('-1 day')))
			->where('date_created <', date('Y-m-d'))
			->group_by('actor')
			->order_by('total', 'desc');
		
		if(!empty($args['customer'])) {
			$production_db->where($ticket_history_customer_filter);
		}
		
		$yesterday_total_actions_tmp = $production_db->get()->result_array();

		if(!empty($yesterday_total_actions_tmp)) {
			foreach($yesterday_total_actions_tmp as $current_data) {
				if(isset($user_with_assigned_open_tickets[$current_data['actor']])) {
					$user_with_assigned_open_tickets[$current_data['actor']]['yesterday_total_actions'] = $current_data['total'];
				}
			}
		}
		
		$result['user_with_assigned_open_tickets'] = array_values($user_with_assigned_open_tickets);
		
		$chart_options = array();
		switch($args['periodicity']) {
			case 'hourly':
				$chart_options['datetime_formatter'] = 'yyyy-MM-dd HH:00';
				break;
			case 'daily':
			case 'weekly':
				$chart_options['datetime_formatter'] = 'yyyy-MM-dd';
				break;
			case 'monthly':
				$chart_options['datetime_formatter'] = "MMM 'yy";
				break;
			case 'yearly':
				$chart_options['datetime_formatter'] = 'yyyy';
				break;
			default:
				$chart_options['datetime_formatter'] = 'yyyy-MM-dd';
				break;
		}

		$result['chart_options'] = $chart_options;

		$result['page_generated_time'] = $page_generated_time;

		return $result;
	}
	
	public function map_date($date, $periodicity) {
		switch($periodicity) {
			case 'hourly':
				return date('Y-m-d H:i:s', strtotime($date));
			case 'daily':
				return $date;
			case 'weekly':
				return date('Y-m-d', strtotime('previous monday', strtotime('+1 day '.$date)));
			case 'monthly':
				return date('Y-m-01', strtotime($date));
			case 'yearly':
				return date('Y-01-01', strtotime($date));
		}
		
		return $date;
	}
	
	public function update_open_tickets_status_count() {
		$result = array();
		
		$excluded_creator_modifier_ids = array(
			4704209, // Ecosa Support
			5616910,  // Ecosa Sleep
			5833602 // Valued Customer (Secretlab)
		);
		
		$production_db = $this->load->database('prod', TRUE);
		
		$production_db->trans_start();
		
		$this->import_new_ticket_data();
		
		$max_ticket_id = 0;
		
		$orgID = '1131188';
		$apiToken = 'eedaf4df-73c4-423c-807d-52db9605a120';
		$host = 'https://app.teamsupport.com/api/json/Tickets?DateClosed=[null]';
		$base64str=base64_encode("$orgID:$apiToken");

		$process = curl_init();
		curl_setopt($process, CURLOPT_URL, $host);
		curl_setopt($process, CURLOPT_HTTPHEADER,array('Content-Type: application/xml', 'Authorization: Basic '.$base64str));
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
		
		$return = curl_exec($process);
		$response = curl_getinfo($process);
		curl_close($process);
		
		$array = json_decode($return,TRUE);
		
		$current_hour = date('Y-m-d H:00:00');
		$current_date = date('Y-m-d');
		$now = date('Y-m-d H:i:s');
		$user_group = !empty($this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group')) ? $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group') : 1;
		$user_id = !empty($this->session->userdata('chchdb_'.PROJECT_CODE.'_user_id')) ? $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_id') : 2;
		
		$tickets = array();
		
		$teamsupport_open_tickets_ids = array();
		
		if(empty($array['Tickets'])) {
			$result['message'] = 'No tickets found';
			return $result;
		}
		
		$open_tickets_by_status_count = array();
		
		foreach($array['Tickets'] as $item) {
			$ticket_status = !empty($item['Status']) ? trim(strtolower($item['Status'])) : null;
			
			$teamsupport_open_tickets_ids[] = $item['TicketID'];
			
			if(strpos($ticket_status, 'close') !== false) {
				continue;
			}
			
			if(empty($ticket_status)) {
				$ticket_status = 'no status';
			}
			
			if(!isset($open_tickets_by_status_count[$ticket_status])) {
				$open_tickets_by_status_count[$ticket_status] = 0;
			}
				
			$open_tickets_by_status_count[$ticket_status]++;
		}
		
		$data_to_insert = array();
		foreach($open_tickets_by_status_count as $ticket_status => $open_tickets_count) {
			// Hourly
			$data_to_insert[] = array(
				'periodicity' => 'hourly',
				'date' => $current_hour,
				'ticket_status' => $ticket_status,
				'open_tickets_count' => $open_tickets_count
			);
			
			// Daily
			$data_to_insert[] = array(
				'periodicity' => 'daily',
				'date' => $current_date,
				'ticket_status' => $ticket_status,
				'open_tickets_count' => $open_tickets_count
			);
		}
		
		// Get open tickets from db
		$db_open_tickets_data = $production_db
			->select('id')
			->from('tickets')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
			->where('date_closed IS NULL', null, false)
			->where_not_in('creator_id', $excluded_creator_modifier_ids)
			->where_not_in('modifier_id', $excluded_creator_modifier_ids)
			->not_like('status', 'Closed')
			->get()->result_array();
		
		$deleted_tickets = array();
		/*foreach($db_open_tickets_data as $current_data) {
			if(!in_array($current_data['id'], $teamsupport_open_tickets_ids)) {
				$deleted_tickets[] = array(
					'id' => $current_data['id'],
					'data_status' => 'deleted',
					'last_modified_time' => $now,
					'last_modified_user' => $user_id
				);
			}
		}*/
		
		if(!empty($data_to_insert)) {
			// Delete data
			$production_db
				->where('periodicity', 'hourly')
				->where('date', $current_hour)
				->delete('open_tickets_status_count');
			
			$production_db
				->where('periodicity', 'daily')
				->where('date', $current_date)
				->delete('open_tickets_status_count');
			
			$production_db->insert_batch('open_tickets_status_count', $data_to_insert);
		}
		
		if(!empty($deleted_tickets)) {
			$production_db->update_batch('tickets', $deleted_tickets, 'id');
		}
		$production_db->trans_complete();
		
		$result['count'] = count($data_to_insert) / 2;
		$result['message'] = $result['count'] . ' ticket status updated.';
		
		return $result;
	}
	
	public function update_mins_to_initial_response() {
		$result = array();
		
		$excluded_creator_modifier_ids = array(
			4704209, // Ecosa Support
			5616910,  // Ecosa Sleep
			5833602 // Valued Customer (Secretlab)
		);
		
		$production_db = $this->load->database('prod', TRUE);
		
		$user_group = !empty($this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group')) ? $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_group') : 1;
		$user_id = !empty($this->session->userdata('chchdb_'.PROJECT_CODE.'_user_id')) ? $this->session->userdata('chchdb_'.PROJECT_CODE.'_user_id') : 2;
		
		$tickets = $production_db
			->select('id, date_created')
			->from('tickets')
			->where('data_status', DATA_ACTIVE)
			->where('data_group', $user_group)
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
			->where('tickets.date_created <> tickets.date_modified', null, false)
			->where('mins_to_initial_response IS NULL', null, false)
			->where_not_in('DAYOFWEEK(date_created)', array(1,7))
			->not_like('creator_name', 'Red Stag')
			->where('HOUR(date_created) >=', 8)
			->where('HOUR(date_created) <', 17)
			->where_not_in('creator_id', $excluded_creator_modifier_ids)
			->where_not_in('modifier_id', $excluded_creator_modifier_ids)
			->where('date_created >', date('Y-m-d', strtotime('-6 days')))
			->order_by('ticket_history_last_checked_time')
			->get()->result_array();
		
		$ticket_history = $production_db
			->select('ticket_id, date_created')
			->from('ticket_history')
			->where_in('ticket_id', array_column($tickets, 'id'))
			->like('description', 'public action')
			->get()->result_array();
		
		$tickets = array_combine(
			array_column($tickets, 'id'),
			$tickets
		);
		
		$updated_tickets = array();
		$updated_tickets_count = 0;
		
		foreach($ticket_history as $current_data) {
			if(isset($updated_tickets[$current_data['ticket_id']])) {
				continue;
			}
			
			$ticket_created_date = $tickets[$current_data['ticket_id']]['date_created'];
			$ticket_initial_response_time = $current_data['date_created'];
			
			$mins_to_initial_response = floor((strtotime($ticket_initial_response_time) - strtotime($ticket_created_date)) / 60);
			
			$updated_tickets[$current_data['ticket_id']] = array(
				'id' => $current_data['ticket_id'],
				'mins_to_initial_response' => $mins_to_initial_response
			);
			
			$updated_tickets_count++;
		}
		
		if(!empty($updated_tickets)) {
			$updated_tickets = array_values($updated_tickets);
			$production_db->update_batch(
				'tickets',
				$updated_tickets,
				'id'
			);
		}
		
		$result['count'] = $updated_tickets_count;
		$result['message'] = $result['count'] . ' ticket responses updated.';
		
		return $result;
	}
	
	public function update_ticket_history() {
		$result = array();
		
		$excluded_creator_modifier_ids = array(
			4704209, // Ecosa Support
			5616910,  // Ecosa Sleep
			5833602 // Valued Customer (Secretlab)
		);
		
		$production_db = $this->load->database('prod', TRUE);
		
		// $existing_tickets = $production_db->select('ticket_id')->from('ticket_history')->group_by('ticket_id')->get()->result_array();
		
		$tickets = $production_db
			->select('id, date_closed')
			->from('tickets')
			->where('data_status', DATA_ACTIVE)
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
			->group_start()
				->where('tickets.date_closed IS NULL', null, false)
				->or_where('tickets.date_closed >=', '2021-03-10')
			->group_end()
			//->not_like('creator_name', 'Red Stag')
			//->where('HOUR(date_created) >=', 8)
			//->where('HOUR(date_created) <', 17)
			->where('date_created >=', date('Y-m-d', strtotime('-1 year')))
			->where_not_in('creator_id', $excluded_creator_modifier_ids)
			->where_not_in('modifier_id', $excluded_creator_modifier_ids)
			->where('ticket_history_check_completed', false)
			//->where_not_in('id', array_column($existing_tickets, 'ticket_id'))
			->order_by('ticket_history_last_checked_time')
			->order_by('tickets.date_created', 'desc')
			->limit(50)
			->get()->result_array();
		
		$ticket_ids = !empty($tickets) ? array_column($tickets,'id') : array();
		
		$existing_ticket_history_ids = array();
		if(!empty($tickets)) {
			$existing_ticket_history_ids_tmp = $production_db
				->select('id')
				->from('ticket_history')
				->where_in('ticket_id', $ticket_ids)
				->get()->result_array();
		}

		$existing_ticket_history_ids = !empty($existing_ticket_history_ids_tmp) ? array_column($existing_ticket_history_ids_tmp, 'id') : array();
		
		$orgID = '1131188';
		$apiToken = 'eedaf4df-73c4-423c-807d-52db9605a120';
		$base64str=base64_encode("$orgID:$apiToken");
		
		$total_new_ticket_history = 0;
		
		foreach($tickets as $ticket) {
			$host = 'https://app.teamsupport.com/api/json/Tickets/'.$ticket['id'].'/History';
		
			$process = curl_init();
			curl_setopt($process, CURLOPT_URL, $host);
			curl_setopt($process, CURLOPT_HTTPHEADER,array('Content-Type: application/xml', 'Authorization: Basic '.$base64str));
			curl_setopt($process, CURLOPT_TIMEOUT, 30);
			curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
			
			$return = curl_exec($process);
			$response = curl_getinfo($process);
			curl_close($process);
			
			$ticket_history_list = json_decode($return,TRUE);
			$new_ticket_history = array();
			
			if(!empty($ticket_history_list['History'])) {
				foreach($ticket_history_list['History'] as $ticket_history) {
					if(!in_array($ticket_history['ID'], $existing_ticket_history_ids)) {
						$is_public_action = ( (strpos($ticket_history['Description'], 'public') !== false) && (strpos($ticket_history['Description'], 'action') !== false) );
						
						$new_ticket_history[] = array(
							'id' => $ticket_history['ID'],
							'ticket_id' => $ticket['id'],
							'ref_type' => $ticket_history['RefType'],
							'ref_id' => $ticket_history['RefID'],
							'action_log_type' => $ticket_history['ActionLogType'],
							'description' => $ticket_history['Description'],
							'is_public_action' => $is_public_action,
							'date_created' => empty($ticket_history['DateCreated']) ? null: date_format(date_create_from_format('n/j/Y g:i A', $ticket_history['DateCreated']), 'Y-m-d H:i'),
							'date_modified' => empty($ticket_history['DateModified']) ? null: date_format(date_create_from_format('n/j/Y g:i A', $ticket_history['DateModified']), 'Y-m-d H:i'),
							'creator_id' => $ticket_history['CreatorID'],
							'modifier_id' => $ticket_history['ModifierID'],
							'actor' => $ticket_history['Actor']
						);
					}
				}
			}
			
			if(!empty($new_ticket_history)) {
				$production_db->trans_start();
				
				$production_db->insert_batch('ticket_history', $new_ticket_history);
				
				$ticket_history_check_completed = !empty($ticket['date_closed']);
				$production_db
					->set('ticket_history_check_completed', $ticket_history_check_completed)
					->set('ticket_history_last_checked_time', date('Y-m-d H:i:s'))
					->where('id', $ticket['id'])
					->update('tickets');
				
				$production_db->trans_complete();
				
				$total_new_ticket_history += count($new_ticket_history);
			}
		}
		
		$production_db
			->set('ticket_history_last_checked_time', date('Y-m-d H:i:s'))
			->where_in('id', $ticket_ids)
			->update('tickets');
		
		$result['message'] = $total_new_ticket_history . ' ticket history in ' . count($tickets) . ' tickets added.';
		
		return $result;
	}
	
	public function update_current_open_tickets() {
		$result = array();
		$production_db = $this->db;//$this->load->database('prod', TRUE);
		
		$open_tickets = $production_db
			->select('id')
			->from('tickets')
			->where_not_in('tickets.ticket_type_name', array('Carrier Inquiry/Request'))
			->where('date_closed IS NULL', null, false)
			->where('data_status', DATA_ACTIVE)
			//->where('last_modified_time <', '2021-05-24 13:00:00')
			//->order_by('last_modified_time', 'desc')
			->order_by('last_modified_time')
			->limit(100)
			->get()->result_array();

		$orgID = '1131188';
		$apiToken = 'eedaf4df-73c4-423c-807d-52db9605a120';
		$base64str=base64_encode("$orgID:$apiToken");
		
		$host = 'https://app.teamsupport.com/api/json/Tickets/?';
		for($i=0; $i<count($open_tickets); $i++) {
			if($i>0) {
				$host .= '&';
			}
			$host .= 'TicketID='.$open_tickets[$i]['id'];
		}
	
		$process = curl_init();
		curl_setopt($process, CURLOPT_URL, $host);
		curl_setopt($process, CURLOPT_HTTPHEADER,array('Content-Type: application/xml', 'Authorization: Basic '.$base64str));
		curl_setopt($process, CURLOPT_TIMEOUT, 30);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
		
		$return = curl_exec($process);
		$response = curl_getinfo($process);
		curl_close($process);
		
		$tickets_tmp = json_decode($return,TRUE);
		$tickets = !empty($tickets_tmp) ? $tickets_tmp['Tickets'] : array();
		
		$found_ticket_ids = !empty($tickets) ? array_column($tickets,'ID') : array();
		//echo $host.'<br>';
		echo 'FOUND:';print_r($found_ticket_ids);echo '<br><br>';
		$updated_tickets = array();
		
		$now = date('Y-m-d H:i:s');

		if(!empty($tickets)) {
			foreach($tickets as $item) {
				$updated_tickets[] = array(
					'id' => $item['ID'],
					'product_name' => $item['ProductName'],
					'reported_version' => $item['ReportedVersion'],
					'solved_version' => $item['SolvedVersion'],
					'group_name' => $item['GroupName'],
					'ticket_type_name' => $item['TicketTypeName'],
					'user_name' => $item['UserName'],
					'status' => $item['Status'],
					'status_position' => $item['StatusPosition'],
					'severity_position' => $item['SeverityPosition'],
					'is_closed' => $item['IsClosed'] == 'True' ? true : false,
					'severity' => $item['Severity'],
					'ticket_number' => $item['TicketNumber'],
					'is_visible_on_portal' => $item['IsVisibleOnPortal'] == 'True' ? true : false,
					'is_knowledge_base' => $item['IsKnowledgeBase'] == 'True' ? true : false,
					'reported_version_id' => $item['ReportedVersionID'],
					'solved_version_id' => $item['SolvedVersionID'],
					'product_id' => $item['ProductID'],
					'group_id' => $item['GroupID'],
					'user_id' => $item['UserID'],
					'ticket_status_id' => $item['TicketStatusID'],
					'ticket_type_id' => $item['TicketTypeID'],
					'ticket_severity_id' => $item['TicketSeverityID'],
					'organization_id' => $item['OrganizationID'],
					'name' => $item['Name'],
					'parent_id' => $item['ParentID'],
					'modifier_id' => $item['ModifierID'],
					'creator_id' => $item['CreatorID'],
					'date_modified' =>empty($item['DateModified']) ? null : date_format(date_create_from_format('n/j/Y g:i A', $item['DateModified']), 'Y-m-d H:i'),
					'date_created' => empty($item['DateCreated']) ? null : date_format(date_create_from_format('n/j/Y g:i A', $item['DateCreated']), 'Y-m-d H:i'),
					'date_closed' => empty($item['DateClosed']) ? null : date_format(date_create_from_format('n/j/Y g:i A', $item['DateClosed']), 'Y-m-d H:i'),
					'closer_id' => $item['CloserID'],
					'days_closed' => $item['DaysClosed'],
					'days_opened' => $item['DaysOpened'],
					'closer_name' => $item['CloserName'],
					'creator_name' => is_string($item['CreatorName']) ? $item['CreatorName'] : null,
					'modifier_name' => is_string($item['ModifierName']) ? $item['ModifierName'] : null,
					'hours_spent' => $item['HoursSpent'],
					'sla_violation_time' => $item['SlaViolationTime'],
					'sla_warning_time' => $item['SlaWarningTime'],
					'sla_violation_hours' => $item['SlaViolationHours'],
					'sla_warning_hours' => $item['SlaWarningHours'],
					'knowledge_base_category_id' => $item['KnowledgeBaseCategoryID'],
					'knowledge_base_category_name' => $item['KnowledgeBaseCategoryName'],
					'due_date' => empty($item['DueDate']) ? null: date_format(date_create_from_format('n/j/Y', $item['DueDate']), 'Y-m-d'),
					'ticket_source' => $item['TicketSource'],
					'jira_key' => $item['JiraKey'],
					'data_status' => 'active',
					'data_group' => 1,
					'last_modified_time' => $now,
					'last_modified_user' => 2
				);
			}
			
			for($i=0; $i<count($open_tickets); $i++) {
				if(!in_array($open_tickets[$i]['id'], $found_ticket_ids)) {
					$updated_tickets[] = array(
						'id' => $open_tickets[$i]['id'],
						'data_status' => 'deleted',
						'last_modified_time' => $now,
						'last_modified_user' => 2
					);
				}
			}
		}
		
		if(!empty($updated_tickets)) {
			$production_db->trans_start();
			
			$production_db->update_batch('tickets', $updated_tickets, 'id');

			$production_db->trans_complete();
		}
		
		// print_r($updated_tickets);
		
		return $result;
	}
	
	public function get_customer_list() {
		$prod_db = $this->load->database('prod', TRUE);
		
		$customer_list = $prod_db
			->select('id, customer_name')
			->from('ticket_customers')
			->where('data_status', DATA_ACTIVE)
			->order_by('customer_name')
			->get()->result_array();
		
		return $customer_list;
	}
}