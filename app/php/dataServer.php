<?php  
//error_reporting( E_ALL );
//if(!ob_start("ob_gzhandler")) ob_start();

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$start = $time;

require ("chargebee/ChargeBee.php");
require ("PasswordHash.php");
require ("sessionStartAndCheck.php");
require ("parsecsv/parsecsv.lib.php");

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
    @call_user_func_array(mysqli_stmt_bind_result, $fields);
}

class dataServer {

	var $session 	= null;
	var $sql		= null;
	var $outData	= array();	
	
	// Initialise the DataServer Object. 
	public function dataServer($serverSQLConnect) {
		global $session;
		
		$this->session = $session;
		
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

	private function cleanEncoding( $text, $type='standard' ){
    // determine the encoding before we touch it
    $encoding = mb_detect_encoding($text, 'UTF-8, ISO-8859-1');
    // The characters to output
    if ( $type=='standard' ){
        $outp_chr = array('...',          "'",            "'",            '"',            '"',            '.',            '-',            '-'); // run of the mill standard characters
    } elseif ( $type=='reference' ) {
        $outp_chr = array('&#8230;',      '&#8216;',      '&#8217;',      '&#8220;',      '&#8221;',      '&#8226;',      '&#8211;',      '&#8212;'); // decimal numerical character references
    }
    // The characters to replace (purposely indented for comparison)
        $utf8_chr = array("\xe2\x80\xa6", "\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", '\xe2\x80\xa2', "\xe2\x80\x93", "\xe2\x80\x94"); // UTF-8 hex characters
        $winc_chr = array(chr(133),       chr(145),       chr(146),       chr(147),       chr(148),       chr(149),       chr(150),       chr(151)); // ASCII characters (found in Windows-1252)
    // First, replace UTF-8 characters.
    $text = str_replace( $utf8_chr, $outp_chr, $text);
    // Next, replace Windows-1252 characters.
    $text = str_replace( $winc_chr, $outp_chr, $text);
    // even if the string seems to be UTF-8, we can't trust it, so convert it to UTF-8 anyway
    $text = mb_convert_encoding($text, 'UTF-8', $encoding);

    $text = preg_replace('/[^\x0A\x20-\x7E]/', '', $text);
    return $text;
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
	
	private function clean_input($input, $entities = true, $escape = true)
	{			
		//Recursive array processing.
		if (is_array($input)) {
				foreach ($input as $index => $val) {
					$input[$index] = $this->clean_input($val, $entities, $escape);
				}
				return $input;
		} else {
		
			if($entities)
				$input = htmlentities($input);		
				
			if(get_magic_quotes_gpc())
			{
				//Remove slashes that were used to escape characters in post.
				$input = stripslashes($input);
			}
			//Remove ALL HTML tags to prevent XSS and abuse of the system.
			if($entities)
				$input = strip_tags($input);
				
			$input = trim($input);
			
			//Escape the string for insertion into a MySQL query, and return it.
			if($escape)
				return $this->cleanEncoding($this->sql->link->real_escape_string($input));
			else
				return $this->cleanEncoding($input);
		} 
	}


	private function getCurrentDateTime() {
		return gmdate('Y-m-d H:i:s');
	}


	public function getCleanPost($entities = true, $escape = true) {
	
		$keys = array_keys($_POST);
		$outData = array();
		
		foreach($keys as $key) {	
			$outData[$key] = $this->clean_input($_POST[$key], $entities, $escape);		
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
	
	private function fetchUserDetails($guid, $field = "guid") {
		
		$resultFromExec = 0;
		$returnResults = array();
		
		$query = "SELECT * from users where $field = ? limit 1";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$stmt->bind_param('s', $guid);
			$resultFromExec = $stmt->execute();
			
			stmt_bind_assoc($stmt, $returnResults);
			$stmt->fetch();
			
			// loop through all result rows
			//while ($stmt->fetch()) {
				//print_r($returnResults);
			//}
			  
			$stmt->close();	
		}
		
		return($returnResults);
	
	}
	
	public function fetchDashboardData() {
	
		$user_id	= $this->session->get_user_var('id');
		$data		= array();
		$row_tmb	= array();
		$returnResults = array();
			
		
		/**************
		* Locate user recently merged documents
		*****************************/
		$query = "SELECT generated_documents.id as generated_document_id, generated_documents.created_at, generated_documents.user_id, generated_documents.file_path, generated_documents.pages, documents.name as document_name, documents.id as documents_id, datasources.name as datasource_name, datasources.id as datasource_id
				  FROM generated_documents
				  JOIN documents on generated_documents.document_id = documents.id and documents.user_id = ?
				  LEFT JOIN datasources on generated_documents.generation_datasource_id = datasources.id and datasources.user_id = ?
				  WHERE generated_documents.user_id = ? 	
				  AND generated_documents.deleted_at is null
				  ORDER BY generated_documents.id desc limit 6";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$stmt->bind_param('iii', $user_id, $user_id, $user_id); 
			$resultFromExec = $stmt->execute();
			
			$stmt->store_result();
			stmt_bind_assoc($stmt, $returnResults);
	
			// loop through all result rows
			while ($stmt->fetch()) {
				foreach( $returnResults as $key=>$value ) {
						$row_tmb[ $key ] = $value;
				} 
				$data[] = $row_tmb;				
			}
		}
		
		$this->outData['recent_generated_documents'] = $data;	

		/**************
		* Locate user Notices
		*****************************/
		$row_tmb	= array();
		$data = array();
		$query = "SELECT id, message, type
				  FROM user_notices
				  WHERE user_notices.user_id = ? 
				  AND deleted_at is null
				  ORDER BY id desc";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$stmt->bind_param('i', $user_id); 
			$resultFromExec = $stmt->execute();
			
			$stmt->store_result();
			stmt_bind_assoc($stmt, $returnResults);
	
			// loop through all result rows
			while ($stmt->fetch()) {
				foreach( $returnResults as $key=>$value ) {
						$row_tmb[ $key ] = $value;
				} 
				$data[] = $row_tmb;				
			}
		}
		
		$this->outData['notices'] = $data;
		
		/**************
		* Locate user name and statistics
		*****************************/
		$row_tmb	= array();
		$data 		= array();
		$query = "SELECT user_subscriptions.chargebee_id, users.email, user_statistics.pages_made_total, user_statistics.billing_cycle_logins, user_statistics.billing_cycle_documents, user_statistics.billing_cycle_pages, subscription_plans.included_pages AS plan_included_pages, user_subscriptions.subscription_status, user_subscriptions.subscription_trial_start, user_subscriptions.subscription_trial_end, user_subscriptions.subscription_current_term_start, user_subscriptions.subscription_current_term_end, user_subscriptions.card_status, user_subscriptions.card_first_name, user_subscriptions.card_last_name, user_subscriptions.card_masked_number, user_subscriptions.card_expiry_month , user_subscriptions.card_expiry_year, user_subscriptions.subscription_plan_id                                                                                                                                                     
				  FROM users
				  LEFT JOIN user_statistics on users.id = user_statistics.user_id
				  LEFT JOIN user_subscriptions on users.id = user_subscriptions.user_id
				  LEFT JOIN subscription_plans on user_subscriptions.subscription_plan_id = subscription_plans.plan_name
				  WHERE users.id = ? 
				  AND users.deleted_at IS NULL";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$stmt->bind_param('i', $user_id); 
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
		
		$this->outData['statistics'] = $data;
		
		
		
		/**************
		* Locate user recently merged documents for their subscription period.
		*****************************/
		$row_tmb	= array();
		$data 		= array();
		$term_start = 0;
		$term_end   = 0;
				
		if(isset($this->outData['statistics']['subscription_current_term_start'])) {
			$term_start = $this->outData['statistics']['subscription_current_term_start'];
			$term_end   = $this->outData['statistics']['subscription_current_term_end'];
		} else if(isset($this->outData['statistics']['subscription_trial_start'])) {
			$term_start = $this->outData['statistics']['subscription_trial_start'];
			$term_end   = $this->outData['statistics']['subscription_trial_end'];
		}
		
		//As UNIX_TIMESTAMP expects the fully formed date to be in the localtime the sql server is set at, get any offset and reduce it to UTC. As our dates are all stored at UTC. Who the hell would store localtime?
		//Tried to put the offset into a mysql variable but the prepared statement broke.
		$query = "SELECT UNIX_TIMESTAMP(CONVERT_TZ(generated_documents.created_at,CONCAT((SELECT TIMESTAMPDIFF(HOUR, UTC_TIMESTAMP(), NOW())*-1),':00'),'+00:00')) as created_at, generated_documents.pages 
				  FROM generated_documents
				  WHERE generated_documents.user_id = ? 	
				  AND UNIX_TIMESTAMP(CONVERT_TZ(`created_at`,CONCAT((SELECT TIMESTAMPDIFF(HOUR, UTC_TIMESTAMP(), NOW())*-1),':00'),'+00:00')) >= $term_start
				  AND UNIX_TIMESTAMP(CONVERT_TZ(`created_at`,CONCAT((SELECT TIMESTAMPDIFF(HOUR, UTC_TIMESTAMP(), NOW())*-1),':00'),'+00:00')) <= $term_end
				  ORDER BY id ASC";
				  		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$stmt->bind_param('i', $user_id); 
			$resultFromExec = $stmt->execute();
			
			$stmt->store_result();
			stmt_bind_assoc($stmt, $returnResults);
	
			// loop through all result rows
			while ($stmt->fetch()) {
				foreach( $returnResults as $key=>$value ) {
						$row_tmb[ $key ] = $value;
				} 
				$data[] = $row_tmb;				
			}
		}
		
		$this->outData['billing_period_generated_documents'] = $data;				
		
		
		/**************
		* Locate login details
		*****************************/
		$row_tmb	= array();
		$data 		= array();
		$query = "SELECT ip, created_at
				  FROM login_attempts
				  WHERE successful_user_id = ?
				  AND successful_login = 1
				  ORDER BY id desc
				  LIMIT 1,1";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$stmt->bind_param('i', $user_id); 
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
				
		if(!isset($data['created_at'])) { //new user!
			$data['created_at'] = gmdate("Y-m-d H:i:s", $this->session->get_var('start_time'));
			$data['ip'] = $_SERVER['REMOTE_ADDR'];
		}
		
		$this->outData['last_login'] = $data;
		
		
		/**************
		* Locate documents, backgrounds, pages totals.
		*****************************/
		$row_tmb	= array();
		$data 		= array();
		$query = "SELECT count(id) as document_count
				  FROM documents
				  WHERE user_id = ?
				  AND deleted_at is null";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$stmt->bind_param('i', $user_id); 
			$resultFromExec = $stmt->execute();
			
			$stmt->store_result();
			stmt_bind_assoc($stmt, $returnResults);
	
			// loop through all result rows
			while ($stmt->fetch()) {
				foreach( $returnResults as $key=>$value ) {
						$row_tmb[ $key ] = $value;
				} 
				$this->outData['totals']['document_count'] = $row_tmb['document_count'];				
			}
		}
		
		/**************
		* Locate documents, backgrounds, pages totals.
		*****************************/
		$row_tmb	= array();
		$data 		= array();
		$query = "SELECT count(id) as datasource_count
				  FROM datasources
				  WHERE user_id = ?
				  AND deleted_at is null";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$stmt->bind_param('i', $user_id); 
			$resultFromExec = $stmt->execute();
			
			$stmt->store_result();
			stmt_bind_assoc($stmt, $returnResults);
	
			// loop through all result rows
			while ($stmt->fetch()) {
				foreach( $returnResults as $key=>$value ) {
						$row_tmb[ $key ] = $value;
				} 
				$this->outData['totals']['datasource_count'] = $row_tmb['datasource_count'];	
			}
		}
		
		/**************
		* Locate documents, backgrounds, pages totals.
		*****************************/
		$row_tmb	= array();
		$data 		= array();
		$query = "SELECT count(id) as background_count
				  FROM backgrounds
				  WHERE user_id = ?
				  AND deleted_at is null";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$stmt->bind_param('i', $user_id); 
			$resultFromExec = $stmt->execute();
			
			$stmt->store_result();
			stmt_bind_assoc($stmt, $returnResults);
	
			// loop through all result rows
			while ($stmt->fetch()) {
				foreach( $returnResults as $key=>$value ) {
						$row_tmb[ $key ] = $value;
				} 
				$this->outData['totals']['background_count'] = $row_tmb['background_count'];		
			}
		}
	}
	
	
	
	public function fetchMergedDocumentsData() {
	
		$user_id	= $this->session->get_user_var('id');
		$data		= array();
		$row_tmb	= array();
		$returnResults = array();
			
		
		/**************
		* Locate user recently merged documents
		*****************************/
		$query = "SELECT generated_documents.id as document_id, generated_documents.created_at, generated_documents.user_id, generated_documents.file_path, generated_documents.pages, documents.name as document_name, documents.id as documents_id, datasources.name as datasource_name, datasources.id as datasource_id
				  FROM generated_documents
				  JOIN documents on generated_documents.document_id = documents.id and documents.user_id = ?
				  LEFT JOIN datasources on generated_documents.generation_datasource_id = datasources.id and datasources.user_id = ?
				  WHERE generated_documents.user_id = ? 	
				  AND generated_documents.deleted_at is null
				  ORDER BY generated_documents.id desc";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$stmt->bind_param('iii', $user_id, $user_id, $user_id); 
			$resultFromExec = $stmt->execute();
			
			$stmt->store_result();
			stmt_bind_assoc($stmt, $returnResults);
	
			// loop through all result rows
			while ($stmt->fetch()) {
				foreach( $returnResults as $key=>$value ) {
						$row_tmb[ $key ] = $value;
				} 
				$data[] = $row_tmb;				
			}
		}
		
		$this->outData['recent_generated_documents'] = $data;	

	}
	
	
	public function fetchDocumentsTemplates() {
	
		$user_id	= $this->session->get_user_var('id');
		$data		= array();
		$row_tmb	= array();
		$returnResults = array();
			
		
		/**************
		* Locate user recently merged documents
		*****************************/
		$query = "SELECT documents.id as document_id, documents.created_at, documents.user_id, (IFNULL( datasources.lines, 0 )) AS datasource_lines, documents.name as document_name, documents.id as documents_id, datasources.name as datasource_name, datasources.id as datasource_id
				  FROM documents
				  LEFT JOIN datasources on documents.datasource_id = datasources.id and datasources.user_id = ?
				  WHERE documents.user_id = ? 	
				  AND documents.deleted_at is null
				  ORDER BY documents.id desc";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$stmt->bind_param('ii', $user_id, $user_id); 
			$resultFromExec = $stmt->execute();
			
			$stmt->store_result();
			stmt_bind_assoc($stmt, $returnResults);
	
			// loop through all result rows
			while ($stmt->fetch()) {
				foreach( $returnResults as $key=>$value ) {
						$row_tmb[ $key ] = $value;
				} 
				$data[] = $row_tmb;				
			}
		}
		
		$this->outData['document_templates'] = $data;	

	}
	
	
	public function deleteMergedDocument($inData) {
		
		$documentID = $inData['document_id'];
		$user_id = $this->session->get_user_var('id');
		
		/**************
		* Locate user Notices
		*****************************/
		$row_tmb	= array();
		$data = array();
		$query = "UPDATE generated_documents set deleted_at = NOW()
				  WHERE id = ? 
				  AND deleted_at is null
				  AND user_id = ?";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$stmt->bind_param('ii', $documentID, $user_id); 
			$resultFromExec = $stmt->execute();
			$affectedRows  += $stmt->affected_rows;
		}
		
		$this->outData['success'] = $affectedRows;
	}
	
	
	public function deleteDocumentTemplate($inData) {
		
		$documentID = $inData['document_id'];
		$user_id = $this->session->get_user_var('id');
		
		/**************
		* Locate user Notices
		*****************************/
		$row_tmb	= array();
		$data = array();
		$query = "UPDATE documents set deleted_at = NOW()
				  WHERE id = ? 
				  AND deleted_at is null
				  AND user_id = ?";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$stmt->bind_param('ii', $documentID, $user_id); 
			$resultFromExec = $stmt->execute();
			$affectedRows  += $stmt->affected_rows;
		}
		
		$this->outData['success'] = $affectedRows;
	}
	
	
	public function ackNotice($inData) {
		
		$noticeID = $inData['notice_id'];
		$user_id = $this->session->get_user_var('id');
		
		/**************
		* Locate user Notices
		*****************************/
		$row_tmb	= array();
		$data = array();
		$query = "UPDATE user_notices set deleted_at = NOW()
				  WHERE id = ? 
				  AND deleted_at is null
				  AND user_id = ?";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$stmt->bind_param('ii', $noticeID, $user_id); 
			$resultFromExec = $stmt->execute();
			$affectedRows  += $stmt->affected_rows;
		}
		
		$this->outData['success'] = $affectedRows;
	}
	
	
	public function readCSV($inData) {
		
		$csvData		= array('error' => "File Not Found");
		$noticeID 		= $inData['notice_id'];
		$user_file_dir	= $this->session->get_user_var('file_directory');
		
		$basePath		= "../user_files/".$user_file_dir."/datasources/";
		$realBasePath	= realpath($basePath);
		$userPath		= $basePath . $inData['subdir'] . $inData['file'];
		$realUserPath	= realpath($userPath);
				
		if ($realUserPath === false || strpos($realUserPath, $realBasePath) !== 0) {
			//Is directory Traversal, bad!
			$this->outData['data'] = $csvData;
		} else { //Not directory Traversal.
			$csv = new parseCSV();
			
			if(filesize($realUserPath) > 0) {
				$csv->parse($realUserPath, 0 , 10000); // At max 10000 lines.				
				$csvDataRows = $csv->unparse($csv->data, $csv->titles, null, null, null, true);			
			} else {
				$csvDataRows = array(array(""));
			}
			
			$csvData = array("csv_data" => $csvDataRows,
							 "header_count" => count($csv->titles),
							 "headers" => $csv->titles,
							 "row_count" => count($csvDataRows),
							 "csv_errors" => $csv->error_info);
									
			$this->outData['data'] = $csvData;
		}
		
		echo json_encode($csvData);
	}
	
	
	public function saveCSV($inData) {
		
		$csvData		= array('error' => "File Not Found");
		$user_file_dir	= $this->session->get_user_var('file_directory');
		$userID         = $this->session->get_user_var('id');
		
		$basePath		= "../user_files/".$user_file_dir."/datasources/";
		$realBasePath	= realpath($basePath);
		$userPath		= $basePath . $inData['subdir'] . $inData['file'];
		$realUserPath	= realpath($userPath);
		
		$dataOut = array();
		$returnResults = array();
				
		if ($realUserPath === false || strpos($realUserPath, $realBasePath) !== 0) {
			//Is directory Traversal, bad!
			$this->outData['data'] = $csvData;
		} else { //Not directory Traversal.
			$csv = new parseCSV();
			
			$data = stripslashes(html_entity_decode($inData['data']));
			$data = json_decode($data);
			
			$csv->save($realUserPath, $data, false, array());
			
			$sqlDir = "/datasources/server/user_files/".$user_file_dir."/datasources/".$inData['subdir'];
			//Flag the curl request as processed/deleted.
			$query = "SELECT id from datasources WHERE user_id = ? and data_path = ? and file_name = ?";						  
			$stmt = $this->sql->link->prepare($query);
			$stmt->bind_param('iss', $userID, $sqlDir, $inData['file']);
			$stmt->execute();
			
			$stmt->store_result();
			stmt_bind_assoc($stmt, $returnResults);
	
			// loop through all result rows
			while ($stmt->fetch()) {
				foreach( $returnResults as $key=>$value ) {
						$row_tmb[ $key ] = $value;
				} 
				$dataOut = $row_tmb;				
			}			
			
			$lineCount = count($data) - 1;
			if($lineCount < 0)
				$lineCount = 0;
			$headers = json_encode(array_filter($data[0]));
			$query = "UPDATE datasources SET `headers` = ?, `lines` = ? WHERE `id` = ?";						  
			$stmt = $this->sql->link->prepare($query);
			$stmt->bind_param('sii', $headers, $lineCount, $dataOut['id']);
			$stmt->execute();	
			
			$this->outData['success'] = 1;
		}
		
		echo json_encode($this->outData);
	}
	
	
	
	public function createBlankCSV($inData) {
			
		$userID       = $this->session->get_user_var('id');
		$time         = $this->getCurrentDateTime();
		$fileName	  = str_replace("..",".",$inData['file']);
		$fileName	  = trim($fileName, ".");
		$affectedRows = 0;
		
		$csvData		= array('error' => "File Not Found");
		$user_id 		= $this->session->get_user_var('id');
		$user_file_dir	= $this->session->get_user_var('file_directory');		
		$subDir 		= implode('/', array_reverse(explode('.', $fileName))).'/';
		
		$basePath		= "../user_files/".$user_file_dir."/datasources/";
		$realBasePath	= realpath($basePath);
				
		//Create the user's blank file
		if (!is_dir($realBasePath ."/". $subDir)) {
				if (!mkdir($realBasePath ."/". $subDir, 0700, true)) {
					die('Failed to create folders...');
				}
			}
			
		$userPath		= $basePath . $subDir;
		$realUserPath	= realpath($userPath);
		
		if ($realUserPath === false || strpos($realUserPath, $realBasePath) !== 0) {
			//Is directory Traversal, bad!
			$this->outData['data'] = $csvData;
		} else { //Not directory Traversal.				
			$ourFileHandle = @fopen($realUserPath ."/". $fileName, 'x') or $fileExists = 1;
			
			if(!$fileExists)
				fclose($ourFileHandle);
		}		
	
		if(!$fileExists) {
			$query = "INSERT INTO datasources (created_at, updated_at, user_id, name, data_path, file_name)
					  VALUES (?, NOW(), ?, ?, ?, ?)";
					  
			$stmt = $this->sql->link->prepare($query);
			if (!$stmt) {
			  die('Invalid query: ' . $this->sql->link->error);
			} else {
							
				$dataPath = "/datasources/server/user_files/".$user_file_dir."/datasources/".$subDir;
				$stmt->bind_param('sisss', $time, $userID, $fileName, $dataPath, $fileName );
				$resultFromExec = $stmt->execute();
				
				if($resultFromExec) {
					$affectedRows += $stmt->affected_rows;
					$documentID   = $stmt->insert_id; 
													
				}
							
				/* free result */
				$stmt->free_result();			  
				$stmt->close();	
			}			 
		}
		
		if($affectedRows > 0) {	
			$output['success']	= 1;
			$output['text']		= "Successfully created the blank datasource.";
			$output['data']		= array("filename" => $fileName,
										"rawFilename" => rawurlencode($fileName),
										"subdir" => $subDir,
										"rawSubdir" => rawurlencode($subDir));
			$output['return']	= 1;
		} else {
			$output['success']	= 1;
			$output['text']		= "Sorry, a datasource with that filename already exists.";
			$output['return']	= 2;
		}
		
		$this->outData = $output;		
		return($output);
	}
	
	public function fetchDocumentData($inData, $datasource_data = 0) {
	
		$user_id = $this->session->get_user_var('id');
		$data = array();
		$returnResults = array();
			
		
		//recorded_at is the time on the device.
		$query = "SELECT documents.id as document_id, documents.name as document_name, documents_pages.document_pg_num, documents_pages.variables, documents_pages.preset, documents_pages.width, documents_pages.height, documents_pages.selected_measurement_unit, documents.datasource_id, datasources.headers as datasource_headers, ".($datasource_data ? 'datasources.data_path as datasource_data_path, datasources.file_name as datasource_file_name, datasources.lines as datasource_lines, ' : '')." backgrounds.data_path, backgrounds_pages.file_name, backgrounds_pages.id as background_pg_id, backgrounds.id as background_id 
				  FROM documents
				  LEFT JOIN documents_pages on documents.id = documents_pages.document_id AND documents_pages.deleted_at is null
				  LEFT JOIN backgrounds on backgrounds.id   = documents_pages.background_id AND backgrounds.user_id = ? AND backgrounds.deleted_at is null
				  LEFT JOIN backgrounds_pages on backgrounds_pages.id = documents_pages.background_pg_id AND backgrounds_pages.background_id = backgrounds.id
				  LEFT JOIN datasources on documents.datasource_id = datasources.id AND datasources.user_id = ? AND datasources.deleted_at is null
				  WHERE documents.user_id = ? 			  
				  AND documents.id = ?
				  ORDER BY documents_pages.document_pg_num ASC";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$stmt->bind_param('iiii', $user_id, $user_id, $user_id, $inData['document_id']);
			$resultFromExec = $stmt->execute();
			
			$stmt->store_result();
			stmt_bind_assoc($stmt, $returnResults);
	
			// loop through all result rows
			while ($stmt->fetch()) {
				foreach( $returnResults as $key=>$value ) {
					if($key == "variables")
						$row_tmb[ $key ] = unserialize($value); //As json isn't allowed to have escaped 's, get rid of them.
					else
						$row_tmb[ $key ] = $value;
				} 
				$data[$returnResults['document_pg_num']] = $row_tmb;				
			}
		}
		
		$this->outData['data'] = $data;		 

		return $data;
	}
	
	
	public function fetchBackgrounds() {
	
		$user_id = $this->session->get_user_var('id');
		$data = array();
		$returnResults = array();
			
		
		//recorded_at is the time on the device.
		$query = "SELECT backgrounds.id as background_id, backgrounds.name as background_name, backgrounds.data_path as background_data_path, backgrounds_pages.id as background_page_id, backgrounds_pages.background_pg_num as background_pg_num, backgrounds_pages.file_name as background_file_name
				  FROM backgrounds
				  JOIN backgrounds_pages on backgrounds_pages.background_id = backgrounds.id
				  WHERE backgrounds.user_id = ? 
				  AND backgrounds.deleted_at is null
				  order by background_id desc";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$stmt->bind_param('i', $user_id);
			$resultFromExec = $stmt->execute();
			
			$stmt->store_result();
			stmt_bind_assoc($stmt, $returnResults);
	
			// loop through all result rows
			while ($stmt->fetch()) {
				foreach( $returnResults as $key=>$value ) {
					$row_tmb[ $key ] = $value;
				}
				
				$data[$row_tmb['background_id']]['background_id'] = $row_tmb['background_id'];
				$data[$row_tmb['background_id']]['background_name'] = $row_tmb['background_name'];
				$data[$row_tmb['background_id']]['background_thumb_path'] = str_replace("/backgrounds/templates/", "/backgrounds/thumbnails/", $row_tmb['background_data_path']);
				$data[$row_tmb['background_id']]['background_data_path'] = $row_tmb['background_data_path'];
				
				$data[$row_tmb['background_id']]['pages'][$row_tmb['background_page_id']]['background_page_id'] = $row_tmb['background_page_id'];
				$data[$row_tmb['background_id']]['pages'][$row_tmb['background_page_id']]['background_pg_num'] = $row_tmb['background_pg_num'];
				$data[$row_tmb['background_id']]['pages'][$row_tmb['background_page_id']]['background_file_name'] = $row_tmb['background_file_name'];
				
			}
		}
		
		$this->outData['data'] = $data;
		
		return $data;		 	
	}
	
	
	public function fetchDatasources() {
	
		$user_id = $this->session->get_user_var('id');
		$data = array();
		$returnResults = array();
			
		
		//recorded_at is the time on the device.
		$query = "SELECT datasources.id as datasources_id, datasources.name as datasources_name, datasources.headers as datasources_headers, datasources.lines as datasources_lines
				  FROM datasources
				  WHERE datasources.user_id = ? 
				  AND datasources.deleted_at is null
				  order by datasources_id desc";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$stmt->bind_param('i', $user_id);
			$resultFromExec = $stmt->execute();
			
			$stmt->store_result();
			stmt_bind_assoc($stmt, $returnResults);
	
			// loop through all result rows
			while ($stmt->fetch()) {
				foreach( $returnResults as $key=>$value ) {
					$row_tmb[ $key ] = $value;
				}
				$data[] = $row_tmb;
			}
		}
		
		$this->outData['data'] = $data;
		
		return $data;		 	
	}
	
	
	public function fetchDocumentsList($inData) {
	
		$user_id = $this->session->get_user_var('id');
		$data = array();
		$returnResults = array();
			
		
		//recorded_at is the time on the device.
		$query = "SELECT documents.created_at, documents.updated_at, documents.name, datasources.name as datasource_name, datasources.id as datasource_id
				  FROM documents
				  LEFT JOIN datasources on datasources.id = documents.datasource_id AND datasources.user_id = ?
				  WHERE documents.user_id = ?				  
				  AND documents.deleted_at IS NULL
				  order by documents.id";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$stmt->bind_param('ii', $user_id, $user_id);
			$resultFromExec = $stmt->execute();
			
			$stmt->store_result();
			stmt_bind_assoc($stmt, $returnResults);
	
			// loop through all result rows
			while ($stmt->fetch()) {
				foreach( $returnResults as $key=>$value ) {
					$row_tmb[ $key ] = $value;
				}
				$data[] = $row_tmb;	
			}
		}
		
		$this->outData['data'] = $data;
		 	
	}
	
	
	public function updateDocumentData($inData) {
			
		$documentID	  = $inData['document_id'];
		$userID       = $this->session->get_user_var('id');
		$time         = $this->getCurrentDateTime();
		
		$affectedRows = 0;	
		
		$row_tmb	= array();
		$data = array();
				  
		$query = "SELECT documents_pages.id, documents_pages.document_pg_num
				  FROM documents
				  INNER JOIN documents_pages on documents.id = documents_pages.document_id
				  WHERE documents.user_id = ? and documents.id = ?";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
					
			$stmt->bind_param('ii', $userID, $documentID); 
			$resultFromExec = $stmt->execute();
			
			$stmt->store_result();
			stmt_bind_assoc($stmt, $returnResults);
	
			// loop through all result rows
			while ($stmt->fetch()) {
				foreach( $returnResults as $key=>$value ) {
						$row_tmb[ $key ] = $value;
				} 
				$data[$returnResults['document_pg_num']] = $row_tmb;				
			}
		}
		
		foreach($inData['page_data'] as $pageKey => $pageData) {
		
			$sqlData = array();
			$varData = array();	
			
			//print_r($inData['page_data']);
			//If we've posted data for a page that doesn't exist in our database, we must first create an entry for it in the documents_pages table.
			if(!isset($data[$pageData['pg_num']])) {

				$query = "INSERT INTO documents_pages (created_at, updated_at, document_id, document_pg_num) 
						  VALUES(?, NOW(), ?, ?)";
						  
				$pgNum = $pageData['pg_num'];
										  
				$stmt = $this->sql->link->prepare($query);
				$stmt->bind_param('sii', $time, $documentID, $pgNum);
				
				$resultFromExec = $stmt->execute();
				$affectedRows  += $stmt->affected_rows;				
				
			}
			
			//Make the changes to our page.
		
			//SQL that should only be triggered once. 
			//Like updates to the master document variables.
			if($pageKey == count($inData['page_data'])-1){				
				if(isset($inData['doc_name'])) {
					$sqlData[]  = "documents.name = ?";
					$varData[]  = $inData['doc_name'];
				}
				
				if(isset($inData['doc_datasource'])) {
					$sqlData[]  = "documents.datasource_id = ?";
					$varData[]  = $inData['doc_datasource'];
				}
			}
		
			//Background for the pages.
			if(isset($pageData['background_id']) && isset($pageData['background_pg_id'])) {
				$sqlData[]  = "documents_pages.background_id = ?";
				$varData[]  = $pageData['background_id'] == "" ? null : $pageData['background_id'];
				
				$sqlData[]  = "documents_pages.background_pg_id = ?";
				$varData[]  = $pageData['background_pg_id'] == "" ? null : $pageData['background_pg_id'];
			}
			
			//Declare a preset page size for the page - only cosmetic.
			if(isset($pageData['preset'])) {
				$sqlData[]  = "documents_pages.preset = ?";
				$varData[]  = count(($pageData['preset'])) > 0 ? ($pageData['preset']) : null;
			}
			
			//Width and Height of the page.
			if(isset($pageData['width']) && isset($pageData['height'])) {			
				$sqlData[]  = "documents_pages.width = ?";
				$varData[]  = ($pageData['width']);
				
				$sqlData[]  = "documents_pages.height = ?";
				$varData[]  = ($pageData['height']);
			}
			
			//The select page measurement unit - inches or mm. 0=mm, 1=inch
			if(isset($pageData['selected_measurement_unit'])) {
				$sqlData[]  = "documents_pages.selected_measurement_unit = ?";
				$varData[]  = ($pageData['selected_measurement_unit']);
			}
			
			
			//Variables for the pages.
			if(isset($pageData['variables'])) {						
				$sqlData[]    = "documents_pages.variables = ?";
				$varData[]    = serialize($pageData['variables']);								
			}
			
			//Set this page to not be deleted, if it was flagged as such.
			//On second thought, if a page is marked as deleted just leave it deleted.
			//$sqlData[]  = "documents_pages.deleted_at = ?";
			//$varData[]  = null;
			if(isset($pageData['deleted_at'])) {
				$sqlData[]  = "documents_pages.deleted_at = NOW()";
				//$varData[]  = $time;
			} else {
				$sqlData[]  = "documents_pages.deleted_at = null"; 
			}
				
			$query = "UPDATE documents 
					  INNER JOIN documents_pages on documents.id = documents_pages.document_id
					  SET ".implode(",",$sqlData)."
					  WHERE documents.id = ? and documents_pages.document_pg_num = ? and documents.user_id = ?";
			
			$stmt = $this->sql->link->prepare($query);
			if (!$stmt) {
			  die('Invalid query: ' . $this->sql->link->error);
			} else {
							
				//Bind the variable SQL to its actual variables.
				//Do not use foreach as we need to bind via reference.
				//foreach($varData as $key => $data) {
				for($c = 0; $c < sizeof($varData); $c++) {
					$stmt->mbind_param('s',$varData[$c]);
				}
				
				//Bind the document id, document page num and user id.
				$stmt->mbind_param('s',$documentID);
				$stmt->mbind_param('s',$pageData['pg_num']); //pagekey is our page counter.
				$stmt->mbind_param('s',$this->session->get_user_var('id'));
				
				$resultFromExec = $stmt->execute();
				
				if($resultFromExec) {
					$affectedRows += $stmt->affected_rows;
				}
							
				/* free result */
				$stmt->free_result();			  
				$stmt->close();	
			}
		
		}
		
		
		if($affectedRows > 0) {	
			$output['success']	= 1;
			$output['text']		= "Successfully saved the pages provided.";
			$output['return']	= 1;
		} else {
			$output['success']	= 1;
			$output['text']		= "Successfully saved, yet no data to update.";
			$output['return']	= 2;
		}
		
		$this->outData = $output;
		
		$this->outputResults();
		
		return($output);
	}
	
	
	public function createBlankDocument($inData) {
			
		$documentName = $inData['document_name'];
		$userID       = $this->session->get_user_var('id');
		$time         = $this->getCurrentDateTime();
		$affectedRows = 0;
		
		$query = "INSERT INTO documents (created_at, updated_at, user_id, name)
				  VALUES (?, NOW(), ?, ?)";
				  
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
						
			$stmt->bind_param('sis', $time, $userID, $documentName);
			$resultFromExec = $stmt->execute();
			
			if($resultFromExec) {
				$affectedRows += $stmt->affected_rows;
				$documentID   = $stmt->insert_id; 
								
				$query = "INSERT INTO documents_pages (created_at, updated_at, document_id, document_pg_num) 
						  VALUES(?, NOW(), ?, 1)";
						  
				$stmt = $this->sql->link->prepare($query);
				$stmt->bind_param('si', $time, $documentID);
				
				$resultFromExec = $stmt->execute();
				$affectedRows  += $stmt->affected_rows;
				
			}
						
			/* free result */
			$stmt->free_result();			  
			$stmt->close();	
		}		
		
		//Used to offset our times, is set by a cookie in main.js
		$gmtOffset = 0;
		if(isset($_COOKIE['gmtoffset'])) 
			@$gmtOffset = (is_int((int)$_COOKIE['gmtoffset']) && (int)$_COOKIE['gmtoffset'] < 780 && (int)$_COOKIE['gmtoffset'] > -780 ? ($_COOKIE['gmtoffset'] * 60) : 0);

		
		if($affectedRows > 0) {	
			$output['success']	= 1;
			$output['data']		= array("document_name" => $documentName, "document_id" => $documentID, "date" => gmdate("H:i, dS M Y",strtotime($time) + $gmtOffset));
			$output['text']		= "Successfully created the blank document.";
			$output['return']	= 1;
		} else {
			$output['success']	= 1;
			$output['text']		= "Unable to create the blank document.";
			$output['return']	= 2;
		}
		
		$this->outData = $output;		
		return($output);
	}	
	
	
	
	public function createNewSubscribedUser($inData) {
		
		$pass_hasher = new PasswordHash(8, FALSE);
		$time			= $this->getCurrentDateTime();
		$affectedRows	= 0;
		$chargebee_result = "";
		
		$username = $inData['email'];
		$password = @$pass_hasher->HashPassword($inData['password']);		
		$new_user_guid	= $this->create_guid();
		$file_directory	= $this->create_guid();
		
		$query = "INSERT INTO users (created_at, updated_at, username, password_hash, file_directory, email)
				  VALUES (?, NOW(), ?, ?, ?, ?)";
				  
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
						
			$stmt->bind_param('sssss', $time, $username, $password, $file_directory, $username);
			$resultFromExec = $stmt->execute();
			
			if($resultFromExec) {
				$affectedRows	+= $stmt->affected_rows;
				$userID			=  $stmt->insert_id; 
								
				$query = "INSERT INTO user_subscriptions (created_at, updated_at, user_id, chargebee_id) 
						  VALUES(?, NOW(), ?, ?)";
						  
				$stmt = $this->sql->link->prepare($query);
				$stmt->bind_param('sis', $time, $userID, $new_user_guid);
				
				$resultFromExec = $stmt->execute();
				$affectedRows  += $stmt->affected_rows;	
				
				//We delegate our chargebee account creation as the curl process can take a few seconds to return the data
				//we don't want new user's to be delayed in account creation.
				if($resultFromExec) {				
					
					$data = array("chargebee_id" => $new_user_guid,
								  "email"        => $username);
				
					//Action id of 1 = create a new user in chargebee.
					$query = "INSERT INTO curl_requests (created_at, updated_at, user_id, action_id, data) 
						  VALUES(?, NOW(), ?, 1, ?)";
						  
					$stmt = $this->sql->link->prepare($query);
					$stmt->bind_param('sis', $time, $userID, json_encode($data));
					
					$resultFromExec = $stmt->execute();
					$affectedRows  += $stmt->affected_rows;
				}
			}
						
			/* free result */
			$stmt->free_result();			  
			$stmt->close();	
		}		
		
		if($affectedRows > 0) {	
			$output['success']	= 1;
			$output['text']		= "Congratulations, your account has been created.";
			$output['return']	= 1;
			$output['parse_curl'] = 1;
		} else {
			$output['success']	= 1;
			$output['text']		= "Sorry, That email is already registered to an account.";
			$output['return']	= 2;
		}
		
		$this->outData = $output;		
		return($output);
	
	}
	
	
	public function parse_curl_requests() {
		ChargeBee_Environment::configure("rocketmailmerge", "Chargebee api token");
		$data = array();	
		$returnResults = array();
		
		$query = "SELECT id, user_id, action_id, data 
				  FROM curl_requests
				  WHERE deleted_at IS NULL";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$resultFromExec = $stmt->execute();
			
			$stmt->store_result();
			stmt_bind_assoc($stmt, $returnResults);
	
			// loop through all result rows
			while ($stmt->fetch()) {
				foreach( $returnResults as $key=>$value ) {
					$row_tmb[ $key ] = $value;
				} 
				$data[] = $row_tmb;				
			}
		}
		
		//Loop through the pending curl requests.
		foreach($data as $key => $value) {
			
			switch($value['action_id']) {
				case "1" : 
													  
					$CBData = json_decode(stripslashes($value['data']), 1);
					$chargebee_result = ChargeBee_Subscription::create(array(
						  "planId" => "basic", 
						  "id"	   => $CBData['chargebee_id'],
						  "customer" => array(
							"email" => $CBData['email']
						  ),
						  "list" => 1));
					
					$subscription = $chargebee_result->subscription();
					$customer = $chargebee_result->customer();
					$card = $chargebee_result->card();
					
					/*
					print_r($subscription->all);
					print_r($customer->all);
					print_r($card->all);
					*/
					
					break;
				
				default : break;		
			}
			
			//Flag the curl request as processed/deleted.
			$query = "UPDATE curl_requests SET deleted_at = NOW(), actioned_at = NOW() WHERE id = ?";						  
			$stmt = $this->sql->link->prepare($query);
			$stmt->bind_param('i', $value['id']);
			$stmt->execute();				
		}
	}

	
	//Check a user's password.
	//IN  :
	//		inData['guid']  = GUID of the user to lookup
	//		inData['pass']	= password hash to check.
	//OUT :
	//		true  =	Password match 
	//		false =	Password doesn't match.
	public function checkPassword($inData) {
	
		$userData = $this->fetchUserDetails($inData['guid']);
		
		$pass_hasher = new PasswordHash(8, FALSE);
					
		$result = $pass_hasher->CheckPassword($inData['pass'], $userData['pass_hash']);
				
		if($result) {	
			$output['success']	= 1;
			$output['text']		= "Password correct.";
			$output['return']	= 1;
		} else {
			$output['success']	= 0;
			$output['text']		= "Username or password incorrect.";
			$output['return']	= 1;
		}
		
		$this->outData = $output;
		return($output);	
	}
	
	//Return a user's data (Will not return hashed password).
	//IN  :
	//		inData['ph_number'] = The user's phone number.
	//		inData['pass']		= password hash to check.
	//OUT :
	//		true  =	User data attached. 
	//		false = Could not find this user, or password incorrect.
	public function fetchUserData($inData) {
			
		$userData = $this->fetchUserDetails($this->session->get_user_var('id'), "id");
		
		$pass_hasher = new PasswordHash(8, FALSE);
					
		$result = $pass_hasher->CheckPassword($inData['pass'], $userData['password_hash']);
				
		if($result) {	
			unset($userData['password_hash']);
			
			$output['success']	= 1;
			$output['text']		= "User found.";
			$output['return']	= 1;
			$output['data']		= $userData;
		} else {
			$output['success']	= 0;
			$output['text']		= "The username or password is incorrect.";
			$output['return']	= 1;
		}
		
		$this->outData = $output;
		return($output);	
	}



	//Request a chargebee hosted page to change your subscription plan.
	public function requestPlanChange($inData) {
		
		$userID = $this->session->get_user_var('id');
		$plans  = array('free', 'contractor', 'professional', 'small_business', 'office', 'enterprise', 'cancel', 'reactivate', 'card_update');
		
		if( in_array($inData['plan'] , $plans) ) {
		
			/**************
			* Locate user'S SUBSCRIPTION ID
			*****************************/
			$row_tmb	= array();
			$data 		= array();
			$query = "SELECT chargebee_id
					  FROM user_subscriptions
					  WHERE user_id = ?";
			
			$stmt = $this->sql->link->prepare($query);
			if (!$stmt) {
			  die('Invalid query: ' . $this->sql->link->error);
			} else {
			
				$stmt->bind_param('i', $userID); 
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
		
			try {
				
				ChargeBee_Environment::configure("rocketmailmerge", "1opwNtcdAXu19XWee9cdCFe6E3FfYcCvUF");
			
				switch($inData['plan']) {
					case "card_update" :
						$result = ChargeBee_HostedPage::updateCard(array( "customer" => array( "id" => $data['chargebee_id'] )));
						
						$hostedPage = $result->hostedPage();
					
						$output['success']	= 1;
						$output['text']		= "Successfully fetched the card update page.";
						$output['url']		= $hostedPage->__get('url');
						$output['return']	= 1;
  
						break;
					case "cancel" :
						$result = ChargeBee_Subscription::cancel($data['chargebee_id'], array( "end_of_term" => 'true'));						
						$subscription_status = $result->subscription()->__get('status');						
						$query = "UPDATE user_subscriptions set subscription_status = ?
								  WHERE user_id = ?";
						
						$stmt = $this->sql->link->prepare($query);
						if (!$stmt) {
						  die('Invalid query: ' . $this->sql->link->error);
						} else {
						
							$stmt->bind_param('si', $subscription_status, $userID); 
							$resultFromExec = $stmt->execute();
						}
						
						$output['success']	= 1;
						$output['text']		= "Successfully cancelled your subscription.";
						$output['return']	= 4;
						break;
						
					case "reactivate" :
						$result = ChargeBee_Subscription::reactivate($data['chargebee_id']);
						$subscription_status = $result->subscription()->__get('status');						
						$query = "UPDATE user_subscriptions set subscription_status = ?
								  WHERE user_id = ?";
						
						$stmt = $this->sql->link->prepare($query);
						if (!$stmt) {
						  die('Invalid query: ' . $this->sql->link->error);
						} else {
						
							$stmt->bind_param('si', $subscription_status, $userID); 
							$resultFromExec = $stmt->execute();
						}
						
						$output['success']	= 1;
						$output['text']		= "Successfully reactivated your subscription.";
						$output['return']	= 5;
						break;						
						
					case "free" :
						$this->fetchDashboardData();
						
						if($this->outData['totals']['document_count'] <= 3 && $this->outData['totals']['document_count'] <= 3 && $this->outData['totals']['document_count'] <= 3) {
							$result = ChargeBee_Subscription::update($data['chargebee_id'], array( "planId" => "free"));  
							
							$output['success']	= 1;
							$output['text']		= "Successfully updated your account to free.";
							$output['return']	= 2;
						} else {
							$output['success']	= 1;
							$output['text']		= "Your account does not meet the criteria for the free subscription.";
							$output['return']	= 6;
						}
					
						break;
						
					default :
						$result = ChargeBee_HostedPage::checkoutExisting(array(
						  "subscription" => array(
							"id" => $data['chargebee_id'], 
							"planId" => $inData['plan']
						  )));
						  
						$hostedPage = $result->hostedPage();
					
						$output['success']	= 1;
						$output['text']		= "Successfully fetched the plan change page.";
						$output['url']		= $hostedPage->__get('url');
						$output['return']	= 1;
					
						break;
				
				}
								
				$this->outData = $output;
				return($output);	
			
			} catch(Exception $e) {
				$output['success']	= 0;
				$output['text']		= "Unable to fetch the plan change page.";
				$output['return']	= 3;
				
				$this->outData = $output;
				return($output);	
			}			
		}		
	}		
	
	
	public function checkAvailableQuota($inData) {
		$user_id	= $this->session->get_user_var('id');
		$row_tmb	= array();
		$data 		= array();
		
		$query = "SELECT user_statistics.billing_cycle_pages, subscription_plans.included_pages AS plan_included_pages, user_subscriptions.subscription_status, user_subscriptions.card_status, user_subscriptions.subscription_plan_id                                                                                                                                                     
				  FROM users
				  LEFT JOIN user_statistics on users.id = user_statistics.user_id
				  LEFT JOIN user_subscriptions on users.id = user_subscriptions.user_id
				  LEFT JOIN subscription_plans on user_subscriptions.subscription_plan_id = subscription_plans.plan_name
				  WHERE users.id = ? 
				  AND users.deleted_at IS NULL";
		
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
		
			$stmt->bind_param('i', $user_id); 
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
		
		$planData = $data;
		$documentData = $this->fetchDocumentData($inData, 1);
		$this->outData = array();
		
		$lastInArray = end($documentData);
		
		$mergeTotalPages = $lastInArray['datasource_lines'] * count($documentData);
		
		$this->outData['alert']			  = ($planData['plan_included_pages'] - $planData['billing_cycle_pages'] - $mergeTotalPages) < 0 ? 1 : 0; //Alert the user if their quota will fall below 0 and incur additional fees.
		$this->outData['freeTrial']		  = $planData['subscription_plan_id'] == "free" ? true : false;
		$this->outData['cardValid']		  = $planData['card_status'] == "valid" ? true : false;
		$this->outData['subscriptionStatus'] = $planData['subscription_status'];		
		$this->outData['totalMergePages'] = $mergeTotalPages;
		$this->outData['totalMergePagesDiff'] = $mergeTotalPages - (($planData['plan_included_pages'] - $planData['billing_cycle_pages'] > 0) ? ($planData['plan_included_pages'] - $planData['billing_cycle_pages']) : 0);
		$this->outData['totalMergePagesDiff'] = (($this->outData['totalMergePagesDiff'] < 0) ? 0 : $this->outData['totalMergePagesDiff']);
		$this->outData['quotaCurrentlyExceeded'] = (($planData['plan_included_pages'] - $planData['billing_cycle_pages'] < 0) ? 1 : 0);
		
	}
	
	
	//Logoff from the RMM system.
	public function logout() {
			
		$this->session->session_end();
		$output['success']	= 1;
		$output['text']		= "Logout successful.";
		$output['return']	= 1;
				
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
		//echo "<pre>";
		//print_r(($this->outData));	
	}

}


// Make sure we have a logged in user.
if(!is_numeric($session->get_user_var('id'))) {
	die ('Data Server - Error 1');
}

$server = new dataServer(true);


if(isset($_POST["rex"])) {
	
	$inData		= $server->getCleanPost();
	
	switch($inData["rex"]) {		
		//1 - Fetch document information & subsequent page data.
		case 1 : $server->fetchDocumentData($inData); break;
		//2 - Change a user's password.
		case 2 : $server->checkPassword($inData); break;
		//3 - Update a document's data variables
		case 3 : 
			$inData	= $server->getCleanPost(false, false);
			$server->updateDocumentData($inData); break;
		//4 - Fetch document information including datasource information.
		case 4 : $server->fetchDocumentData($inData, 1); break;
		//5 - Fetch the backgrounds for a user.
		case 5 : $server->fetchBackgrounds(); break;
		//6 - Create a blank document for the user.
		case 6 : $server->createBlankDocument($inData); break;
		//7 - Fetch a user's data, provide their phone number and password.
		case 7 : $server->fetchUserData($inData); break;
		//8 - Fetch a list of this user's documents.
		case 8 : $server->fetchDocumentsList($inData); break;
		//9 - Create a new user.
		//Commented out because we create user's through the create_account.php file.
		//case 9 : $server->createNewSubscribedUser($inData); break;
		//10 - Parse Curl Requests
		//case 10 : $server->parse_curl_requests(); break;
		//11 - Logout of the system
		case 11 : $server->logout(); break;
		//12 - Acknowledge a notice
		case 12 : $server->ackNotice($inData); break;
		//13 - Parse a CSV file and return it as JSON
		case 13 : $server->readCSV($inData); break;
		//14 - Create a blank CSV file.
		case 14 : $server->createBlankCSV($inData); break;
		//15 - Save a CSV.
		case 15 : $server->saveCSV($inData); break;
		//16 - Fetch the list of merged documents a user has 
		case 16 : $server->fetchMergedDocumentsData(); break;
		//17 - Delete a generated document
		case 17 : $server->deleteMergedDocument($inData); break;
		//18 - Delete a document template
		case 18 : $server->deleteDocumentTemplate($inData); break;
		//19 - Delete a document template
		case 19 : $server->requestPlanChange($inData); break;
		//20 - Check available quota
		case 20 : $server->checkAvailableQuota($inData); break;	
		//Default Action.
		default : break;
	}
}

if(isset($_POST["out"])) {
	$server->outputResults();
}

?>
