<?php  
//error_reporting( E_ALL );
//if(!ob_start("ob_gzhandler")) ob_start();

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

require ("chargebee/ChargeBee.php");
require ("PasswordHash.php");
require ("iSQL.php");
require ("iSession.php");

$sql = new iSQL();

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

	var $session 	= null;
	var $sql		= null;
	var $outData	= array();	
	
	// Initialise the DataServer Object. 
	public function dataServer($serverSQLConnect) {
		global $session, $sql;
		
		$this->session = $session;
		
		if($serverSQLConnect) {
			$this->sql 	= new iSQL();
			$sql = $this->sql; //For global access by iSession.
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
	
	private function clean_input($input)
	{	
		//Recursive array processing.
		if (is_array($input)) {
				foreach ($input as $index => $val) {
					$input[$index] = $this->clean_input($val);
				}
				return $input;
		} else {
			$input = htmlentities($input);		
				
			if(get_magic_quotes_gpc())
			{
				//Remove slashes that were used to escape characters in post.
				$input = stripslashes($input);
			}
			//Remove ALL HTML tags to prevent XSS and abuse of the system.
			$input = strip_tags($input);
			//Escape the string for insertion into a MySQL query, and return it.
			return $this->sql->link->real_escape_string($input);
		} 
	}


	private function getCurrentDateTime() {
		return gmdate('Y-m-d H:i:s');
	}


	public function getCleanPost() {

		$keys = array_keys($_REQUEST);
		$outData = array();
		
		foreach($keys as $key) {	
			$outData[$key] = $this->clean_input($_REQUEST[$key]);		
		}
		
		return $outData;
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
	
	
	public function reactivateAccount($inData) {
		ChargeBee_Environment::configure("rocketmailmerge", "Chargebee api token");
		$data = array();			
		
		$card = array(	"number" => $inData['number'],
						"expiryMonth" => $inData['expiryMonth'],
						"expiryYear" => $inData['expiryYear'],
						"cvv" => $inData['cvv'], 
						"billingZip" => $inData['billingZip']);						  
		
		try {
				$chargebee_result = ChargeBee_Subscription::update($inData['token'], array(
				  "planId" => "base_rate",
				  "trialEnd" => 0,
				  "card"   => $card));

		} catch(Exception $e) {
			header('Location: https://app.rocketmailmerge.com/account/reactivate.html?id='.$inData['token']."&crd=0");
			die();
		}	
		
		//Reactivate their subscription
		$query = "UPDATE user_subscriptions set subscription_status = 'active'
				  WHERE chargebee_id = ?";
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$stmt->bind_param('s', $inData['token']); 
			$resultFromExec = $stmt->execute();
		}
		
		//Log them in
		$this->lookupUser(array("id" => $inData['token']));		
		$session = new iSession();
		$session->mod_var("logged_in", 1);
		$session->mod_var("user_id", $this->outData['data']['user_id']);
		$session->update_session();
		header('Location: https://app.rocketmailmerge.com/dashboard/?tut=1');
	}
	
	
	public function lookupUser($inData) {
	
		if(!isset($inData['id'])) {
			return;
		}
		
		$query = "SELECT *
				  FROM user_subscriptions
				  left join users on users.id = user_subscriptions.user_id
				  WHERE user_subscriptions.chargebee_id = ?";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
			die('Invalid query: ' . $this->sql->link->error);
		} else {
			
			$stmt->bind_param('s', $inData['id']);
			$resultFromExec = $stmt->execute();
			
			$stmt->store_result();
			stmt_bind_assoc($stmt, $returnResults);
	
			// loop through all result rows
			while ($stmt->fetch()) {
				foreach( $returnResults as $key=>$value ) {
					$row_tmb[ $key ] = addslashes($value);
				} 
				$data = $row_tmb;				
			}
		}
		
		$this->outData['data'] = $data;
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
		//echo "<pre>";
		//print_r(($this->outData));	
	}

}

$server = new dataServer(true);
$inData	= $server->getCleanPost();

if(isset($_POST["rex"])) {
		
	switch($inData["rex"]) {
		
		//1 - Fetch document information & subsequent page data.
		case "woof" : $server->reactivateAccount($inData); break;	
		default : break;
	}
} else {

	//Lookup the user ID
	$server->lookupUser($inData);
}
?>
