<?php  
//error_reporting( E_ALL );
//if(!ob_start("ob_gzhandler")) ob_start();

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

require ("../php/iSQL.php");

//For the PDO, bind to an array.
function stmt_bind_assoc (&$stmt, &$out) {
    $data = mysqli_stmt_result_metadata($stmt);
    $fields = array();
    $out = array();

    $fields[0] = $stmt;
    $count = 1;

    while($field = mysqli_fetch_field($data)) {
        $fields[$count] = &$out[$field->name];
        $count++;
    }    
    call_user_func_array(mysqli_stmt_bind_result, $fields);
}

class dataServer {

	var $sql		= null;
	var $outData	= array();	
	
	// Initialise the DataServer Object. 
	public function dataServer($serverSQLConnect) {
		if($serverSQLConnect) {
			$this->sql 	= new iSQL();
		}
	}
	
	public function dataServerSQLConnect() {

		$this->sql 	= new iSQL();
		return true;
	}
	
	//Used to seed random numbers.
	private function make_seed()
	{
	  list($usec, $sec) = explode(' ', microtime());
	  return (float) $sec + ((float) $usec * 100000);
	}


	private function sec2hms ($sec, $padHours = false) 
	{
		// start with a blank string
		$hms = "";
		
		// do the hours first: there are 3600 seconds in an hour, so if we divide
		// the total number of seconds by 3600 and throw away the remainder, we're
		// left with the number of hours in those seconds
		$hours = intval(intval($sec) / 3600); 

		// add hours to $hms (with a leading 0 if asked for)
		$hms .= ($padHours) 
			  ? str_pad($hours, 2, "0", STR_PAD_LEFT). ":"
			  : $hours. ":";
		
		// dividing the total seconds by 60 will give us the number of minutes
		// in total, but we're interested in *minutes past the hour* and to get
		// this, we have to divide by 60 again and then use the remainder
		$minutes = intval(($sec / 60) % 60); 

		// add minutes to $hms (with a leading 0 if needed)
		$hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";

		// seconds past the minute are found by dividing the total number of seconds
		// by 60 and using the remainder
		$seconds = intval($sec % 60); 

		// add seconds to $hms (with a leading 0 if needed)
		$hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

		// done!
		return $hms;
		
	}

	private function getCurrentDateTime() {
		return gmdate('Y-m-d H:i:s');
	}

	private function create_guid($namespace = '') {     
		static $guid = '';
		$uid = uniqid("", true);
		$data = "";
		$data .= @$_SERVER['REQUEST_TIME'];
		$data .= @$_SERVER['HTTP_USER_AGENT'];
		$data .= @$_SERVER['LOCAL_ADDR'];
		$data .= @$_SERVER['LOCAL_PORT'];
		$data .= @$_SERVER['REMOTE_ADDR'];
		$data .= @$_SERVER['REMOTE_PORT'];
		$hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
		$guid = substr($hash,  0,  8) . 
				'-' .
				substr($hash,  8,  4) .
				'-' .
				substr($hash, 12,  4) .
				'-' .
				substr($hash, 16,  4) .
				'-' .
				substr($hash, 20, 12);
		return $guid;
	}	
	
	
	
	private function processChargeBeeData($inData) {
	
		//echo "Processing $inData";
		$inData = json_decode($inData, 1);
		$sqlData = array();
		$varData = array();
	
		if($inData === null) {
			echo "Invalid Data provided.\n";
			return "Invalid data!";
		}
		
		echo "Processing : ";
		print_r($inData);
		
		foreach($inData['content'] as $key => $value) {
		
		/*	"subscription": {
			"id": "HwqE2xeNnwOeYF44",
			"plan_id": "enterprise",
			"plan_quantity": 1,
			"status": "in_trial",
			"trial_start": 1352158863,
			"trial_end": 1353454863,
			"created_at": 1352158863,
			"due_invoices_count": 0,
			"object": "subscription"
		},
		"customer": {
			"id": "HwqE2xeNnwOeYF44",
			"first_name": "Leon",
			"last_name": "Test",
			"email": "leon@rocketmailmerge.com",
			"created_at": 1352158863,
			"object": "customer",
			"card_status": "valid"
		},
		"card": {
			"customer_id": "HwqE2xeNnwOeYF44",
			"status": "valid",
			"gateway": "chargebee",
			"first_name": "Leon",
			"last_name": "Testington",
			"iin": "411111",
			"last4": "1111",
			"card_type": "visa",
			"expiry_month": 2,
			"expiry_year": 2020,
			"billing_addr1": "123 Fake Street",
			"billing_city": "Fake suburb",
			"billing_state": "QLD",
			"billing_country": "AU",
			"billing_zip": "1234",
			"object": "card",
			"masked_number": "411111******1111"
		},
		*/
		
			switch($key) {
			
				case "customer"	: 						
						$allowed = array("customer_first_name", "customer_last_name", "customer_email", "customer_created_at");
						
						foreach($value as $customerKey => $customerData) {								
							if (in_array("customer_".$customerKey, $allowed)) {
								$sqlData[]  = "customer_".$customerKey." = ?";
								$varData[]  = $customerData;
							}
						}
																			
						//Flag the curl request as processed/deleted.
						$query = "UPDATE user_subscriptions 
								  SET ".implode(",",$sqlData)."	
								  WHERE chargebee_id = ?";
								  
						$stmt = $this->sql->link->prepare($query);
						
						for($c = 0; $c < sizeof($varData); $c++) {
							$stmt->mbind_param('s',$varData[$c]);
						}
						
						//Bind the document id, document page num and user id.
						$stmt->mbind_param('s', $value['id']);
						$stmt->execute();		
						
						break;
				case "card"		: 
						$allowed = array("card_status", "card_first_name", "card_last_name", "card_card_type", "card_expiry_month", "card_expiry_year", "card_billing_addr1", "card_billing_addr2", "card_billing_city", "card_billing_state", "card_billing_country", "card_billing_zip", "card_masked_number");
						
						foreach($value as $customerKey => $customerData) {								
							if (in_array("card_".$customerKey, $allowed)) {
								$sqlData[]  = "card_".$customerKey." = ?";
								$varData[]  = $customerData;
							}
						}
																			
						//Flag the curl request as processed/deleted.
						$query = "UPDATE user_subscriptions 
								  SET ".implode(",",$sqlData)."	
								  WHERE chargebee_id = ?";
								  
						$stmt = $this->sql->link->prepare($query);
						
						for($c = 0; $c < sizeof($varData); $c++) {
							$stmt->mbind_param('s',$varData[$c]);
						} 
						
						//Bind the document id, document page num and user id.
						$stmt->mbind_param('s', $value['customer_id']);
						$stmt->execute();		
						
						break;
				case "subscription" : 
						$allowed = array("subscription_plan_id", "subscription_plan_quantity", "subscription_status", "subscription_trial_start", "subscription_trial_end", "subscription_created_at", "subscription_due_invoices_count", "subscription_activated_at", "subscription_current_term_start", "subscription_current_term_end");
						
						foreach($value as $customerKey => $customerData) {								
							if (in_array("subscription_".$customerKey, $allowed)) {
								$sqlData[]  = "subscription_".$customerKey." = ?";
								$varData[]  = $customerData;
							}
						}
																			
						//Flag the curl request as processed/deleted.
						$query = "UPDATE user_subscriptions 
								  SET ".implode(",",$sqlData)."	
								  WHERE chargebee_id = ?";
								  
						$stmt = $this->sql->link->prepare($query);
						
						for($c = 0; $c < sizeof($varData); $c++) {
							$stmt->mbind_param('s',$varData[$c]);
						}
						
						//Bind the document id, document page num and user id.
						$stmt->mbind_param('s', $value['id']);
						$stmt->execute();		
						
						break;
			
				default : break;
			}	
		}
		
		require '../php/chargebee/ChargeBee.php';
		ChargeBee_Environment::configure("rocketmailmerge", "1opwNtcdAXu19XWee9cdCFe6E3FfYcCvUF");
				
		switch($inData['event_type']) {
		
			//Reactivate free trial accounts when the free trial runs out and is killed by chargebee
			case "subscription_canceled_no_card" :
			
				$returnResults = array();
								
				$subscriptionID = $inData['content']['subscription']['id'];
				$subscriptionPlanID = $inData['content']['subscription']['plan_id'];
				$cardStatus = $inData['content']['customer']['card_status'];
				$trialEnd = $inData['content']['subscription']['trial_end'];
				$currentTermStart = $inData['content']['subscription']['current_term_start'];
				
				//The free trial has expired. Reactivate their account.
				if($subscriptionPlanID == "free" && $cardStatus == "no_card" ) {				
					$result = ChargeBee_Subscription::reactivate($subscriptionID);
					
					//Locate the user's page usage.
					$query   = "SELECT user_subscriptions.chargebee_id, user_subscriptions.user_id, user_statistics.billing_cycle_pages, subscription_plans.included_pages
								FROM user_subscriptions
								LEFT JOIN user_statistics ON user_subscriptions.user_id = user_statistics.user_id
								LEFT JOIN subscription_plans ON user_subscriptions.subscription_plan_id = subscription_plans.plan_name
								WHERE user_subscriptions.chargebee_id =  ?";
							
					$stmt = $this->sql->link->prepare($query);
					if (!$stmt) {
					  die('Invalid query: ' . $this->sql->link->error);
					} else {
					
						$stmt->bind_param('s', $subscriptionID); 
						$resultFromExec = $stmt->execute();
						
						$stmt->store_result();
						stmt_bind_assoc($stmt, $returnResults);
				
						// loop through all result rows
						while ($stmt->fetch()) {
							foreach( $returnResults as $key=>$value ) {
									$row_tmb[ $key ] = $value;
							} 
							$data = $row_tmb;				
						}
					}
					
					$userID = $data['user_id'];
					
					//Reset the user statistics back to 0.
					$query = "UPDATE user_statistics SET billing_cycle_logins = 0, billing_cycle_documents = 0, billing_cycle_pages = 0
							  WHERE user_id = ?";	
							  
					$stmt = $this->sql->link->prepare($query);
					if (!$stmt) {
					  die('Invalid query: ' . $this->sql->link->error);
					} else {				
						$stmt->bind_param('i', $userID); 
						$resultFromExec = $stmt->execute();
					}
				}			
				
				break;
				
			case "invoice_created" : 
			
				echo "Invoice Created!";
				$returnResults = array();
				
				$invoiceID = $inData['content']['invoice']['id'];
				$subscriptionID = $inData['content']['invoice']['subscription_id'];
				$result = ChargeBee_Subscription::retrieve($subscriptionID);
				$subscription = $result->subscription();
				
				$addonName = $subscription->__get('planId')."_extra_pages"; //build out of the name of their plan + '_extra_pages'
								
				//Locate the user's page usage.
				$query   = "SELECT user_subscriptions.chargebee_id, user_subscriptions.user_id, user_statistics.billing_cycle_pages, subscription_plans.included_pages
							FROM user_subscriptions
							LEFT JOIN user_statistics ON user_subscriptions.user_id = user_statistics.user_id
							LEFT JOIN subscription_plans ON user_subscriptions.subscription_plan_id = subscription_plans.plan_name
							WHERE user_subscriptions.chargebee_id =  ?";
		
				$stmt = $this->sql->link->prepare($query);
				if (!$stmt) {
				  die('Invalid query: ' . $this->sql->link->error);
				} else {
				
					$stmt->bind_param('s', $subscriptionID); 
					$resultFromExec = $stmt->execute();
					
					$stmt->store_result();
					stmt_bind_assoc($stmt, $returnResults);
			
					// loop through all result rows
					while ($stmt->fetch()) {
						foreach( $returnResults as $key=>$value ) {
								$row_tmb[ $key ] = $value;
						} 
						$data = $row_tmb;				
					}
				}
				
				$userID = $data['user_id'];
				
				//Calculate how many pages over their included subscription pages they've used.
				$billingCyclePages = $data['billing_cycle_pages'];
				if(!is_numeric($data['billing_cycle_pages'])) {
					$billingCyclePages = 0;
				} 
				
				$billingCyclePages = $billingCyclePages - $data['included_pages'];
				
				//Add the extra pages used by the user to the invoice, if > 0
				if($billingCyclePages > 0) {
					$result = ChargeBee_Invoice::addAddonCharge($invoiceID, array(
					  "addon_id" => $addonName, 
					  "addon_quantity" => $billingCyclePages));
				}
				
				//Reset the user statistics back to 0.
				$query = "UPDATE user_statistics SET billing_cycle_logins = 0, billing_cycle_documents = 0, billing_cycle_pages = 0
						  WHERE user_id = ?";	
			  			  
				$stmt = $this->sql->link->prepare($query);
				if (!$stmt) {
				  die('Invalid query: ' . $this->sql->link->error);
				} else {				
					$stmt->bind_param('i', $userID); 
					$resultFromExec = $stmt->execute();
				}
				
				//Close the invoice and charge the customer.
				$result = ChargeBee_Invoice::collect($invoiceID);
				//$invoice = $result->invoice();
				
				break;
				
			case "subscription_renewed" :
				break;
				
			//Fired when a user reactivates their subscription. Clear the counters for paid accounts.
			case "subscription_reactivated" : 
							
				$returnResults = array();
								
				$subscriptionID = $inData['content']['subscription']['id'];
				$subscriptionPlanID = $inData['content']['subscription']['plan_id'];
				
								
				//Locate the user's page usage.
				$query   = "SELECT user_subscriptions.chargebee_id, user_subscriptions.user_id, user_statistics.billing_cycle_pages, subscription_plans.included_pages
							FROM user_subscriptions
							LEFT JOIN user_statistics ON user_subscriptions.user_id = user_statistics.user_id
							LEFT JOIN subscription_plans ON user_subscriptions.subscription_plan_id = subscription_plans.plan_name
							WHERE user_subscriptions.chargebee_id =  ?";
		
				$stmt = $this->sql->link->prepare($query);
				if (!$stmt) {
				  die('Invalid query: ' . $this->sql->link->error);
				} else {
				
					$stmt->bind_param('s', $subscriptionID); 
					$resultFromExec = $stmt->execute();
					
					$stmt->store_result();
					stmt_bind_assoc($stmt, $returnResults);
			
					// loop through all result rows
					while ($stmt->fetch()) {
						foreach( $returnResults as $key=>$value ) {
								$row_tmb[ $key ] = $value;
						} 
						$data = $row_tmb;				
					}
				}
				
				$userID = $data['user_id'];
						
				//Reset the usage for free accounts.
				if($subscriptionPlanID != "free") {
					//Reset the user statistics back to 0.
					$query = "UPDATE user_statistics SET billing_cycle_logins = 0, billing_cycle_documents = 0, billing_cycle_pages = 0
							  WHERE user_id = ?";	
							  
					$stmt = $this->sql->link->prepare($query);
					if (!$stmt) {
					  die('Invalid query: ' . $this->sql->link->error);
					} else {				
						$stmt->bind_param('i', $userID); 
						$resultFromExec = $stmt->execute();
					}
				}
								
				break;
			
			//Terminate free trial accounts when the free trial runs out.
			case "subscription_activated" :
			
				$returnResults = array();
								
				$subscriptionID = $inData['content']['subscription']['id'];
				$subscriptionPlanID = $inData['content']['subscription']['plan_id'];
				$cardStatus = $inData['content']['customer']['card_status'];
				$trialEnd = $inData['content']['subscription']['trial_end'];
				$currentTermStart = $inData['content']['subscription']['current_term_start'];
				
				//The free trial has expired. Cancel their account.
				//if($subscriptionPlanID == "free" && $cardStatus == "no_card" && ($trialEnd == $currentTermStart)) {				
				//	$result = ChargeBee_Subscription::cancel($subscriptionID);
				//}
				
				break;
							
			default : break;
		}
		
	}
		
		
	public function processChargeBeeWebHook($inData) {
		
		$time			= $this->getCurrentDateTime();
		$affectedRows	= 0;
						
		//Action id of 1 = create a new user in chargebee.
		$query = "INSERT INTO curl_requests (created_at, updated_at, deleted_at, action_id, data, actioned_at) 
			  VALUES(?, NOW(), NOW(), 2, ?, NOW())";		
				  
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
						
			$stmt->bind_param('ss', $time, $inData);
			$resultFromExec = $stmt->execute();
			
			//Record the subscription management in the database for this user.
			if($resultFromExec) {
				$affectedRows	+= $stmt->affected_rows;	
				$this->processChargeBeeData($inData);
			}
						
			/* free result */
			$stmt->free_result();			  
			$stmt->close();	
		}		
		
		if($affectedRows > 0) {	
			$output['success']	= 1;
			$output['text']		= "Stored & parsed the event.";
			$output['return']	= 1;
			$output['parse_curl'] = 1;
		} else {
			$output['success']	= 0;
			$output['text']		= "Unable to store the event data.";
			$output['rex']		= "Grrr";
			$output['return']	= 1;
		}
		
		$this->outData = $output;		
		return($output);	
	}
	
	public function outputResults() {
	
		global $start; //for total processing time.
		
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish = $time;
		$total_time = round(($finish - $start), 4);
		
		$this->outData['dog_years'] = $total_time;

		echo stripcslashes(json_encode($this->outData));	
	}
}

//Security. Require this key.
if($_GET['key'] !== "34u2fdasS3rja")
	die();

$server = new dataServer(true);
$inData = file_get_contents('php://input');

if(strlen($inData) > 0) {	
	$server->processChargeBeeWebHook($inData); 
}
?>
