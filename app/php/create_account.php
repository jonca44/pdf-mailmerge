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

		$keys = array_keys($_POST);
		$outData = array();
		
		foreach($keys as $key) {	
			$outData[$key] = $this->clean_input($_POST[$key]);		
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
	
	
	public function sendTechEmail($toAddress, $subject, $body) {

		$headers = "From: tech@rocketmailmerge.com";
		@mail($toAddress, $subject, $body, $headers);
	}

	
		
	public function createNewSubscribedUser($inData) {
		
		$pass_hasher	= new PasswordHash(8, FALSE);
		$time			= $this->getCurrentDateTime();
		$affectedRows	= 0;
		$userID		    = "";
		
		//
		//Begin account creation
		/////////////////////////////////////////
		if(strlen($inData['password']) < 5) {
			$output['success']	= 0;
			$output['text']		= "Sorry, your password must be at least 5 characters.";
			$output['rex']		= "Bark bark woof woof!";
			$output['return']	= 2;
		
			$this->outData = $output;
			
			$this->sendTechEmail("support@rocketmailmerge.com", "Subscriber: Pass too short", "A user tried to register and account but their password was too short -----\n".print_r($inData,1));
			header("location:https://".$_SERVER['SERVER_NAME']."/account/register.html?r=0"); //Pass to short.
			die();
			
			return($output);	
		}
		
		//
		//Begin account creation
		/////////////////////////////////////////
		
		$username = $inData['email'];
		$email = $inData['email'];
		$password = @$pass_hasher->HashPassword($inData['password']);		
		$new_user_guid	= $this->create_guid();
		$file_directory	= $this->create_guid();
		
		
		ChargeBee_Environment::configure("rocketmailmerge", "Chargebe api token");

		//Build up card data
		$cardNumber      = $inData['number'];
		$cardexpiryMonth = $inData['expiryMonth'];
		$cardexpiryYear  = $inData['expiryYear'];
		$cardcvv         = $inData['cvv'];
		$cardbillingZip  = $inData['billingZip'];
						  
		//Check that they supplied a valid email address
		if(filter_var($inData['email'], FILTER_VALIDATE_EMAIL)) {
		
			//Store the email in the session in case they're redirected back to thje register page.
			session_start();
			$_SESSION['email'] = $inData['email'];
			
			$customer = array(	"first_name" => $inData['email']  );
			$customer['email'] = $inData['email']; 
		} else {
			$this->sendTechEmail("support@rocketmailmerge.com", "Subscriber: Bogus Email", "A user tried to register and account but their email was bogus -----\n".print_r($inData,1));
			header("location:https://".$_SERVER['SERVER_NAME']."/account/register.html?r=2"); //Bogus email address
			die();
		}
		
		$card = array(	"number" => $cardNumber,
						"expiryMonth" => $cardexpiryMonth,
						"expiryYear" => $cardexpiryYear,
						"cvv" => $cardcvv,
						"billingZip" => $cardbillingZip);						  
		
		try {
			$chargebee_result = ChargeBee_Subscription::create(array(
																	  "planId" => "base_rate", 
																	  "id"	   => $new_user_guid,
																	  "customer" => $customer,
																	  "card"   => $card,
																	  "list" => 1));
		} catch(Exception $e) {
			$this->sendTechEmail("support@rocketmailmerge.com", "Subscriber: Bad Credit Card", "A user tried to register and account but their credit card was rejected by Chargebee -----\n".print_r($inData,1));
			header("location:https://".$_SERVER['SERVER_NAME']."/account/register.html?r=3"); //Invalid Credit Card
			die();
		}
		
		
		$query = "INSERT INTO users (created_at, updated_at, username, password_hash, file_directory, email)
				  VALUES (?, NOW(), ?, ?, ?, ?)";
				  
		$stmt = $this->sql->link->prepare($query);
		if (!$stmt) {
		  die('Invalid query: ' . $this->sql->link->error);
		} else {
						
			$stmt->bind_param('sssss', $time, $username, $password, $file_directory, $email);
			$resultFromExec = $stmt->execute();
			
			//Record the subscription management in the database for this user.
			if($resultFromExec) {
				$affectedRows	+= $stmt->affected_rows;
				$userID			=  $stmt->insert_id; 
				
				////////////////////////////////////////////////////////////////////////////////////////////////////////
				// Create the user's example files
				
				//Create the background file link - Invoice mailmerge high.
				$dir1 = (__DIR__."/../user_files/$file_directory/backgrounds/files/png/Invoice_backdrop-mailmerge-high/");
				mkdir($dir1, 0700, true);
				symlink((__DIR__."/../newuser_examples/backgrounds/files/png/Invoice_backdrop-mailmerge-high/Invoice_backdrop-mailmerge-high.png"), ($dir1."Invoice_backdrop-mailmerge-high.png"));
				$dir2 = (__DIR__."/../user_files/$file_directory/backgrounds/previews/png/Invoice_backdrop-mailmerge-high/");
				mkdir($dir2, 0700, true);
				symlink((__DIR__."/../newuser_examples/backgrounds/previews/png/Invoice_backdrop-mailmerge-high/Invoice_backdrop-mailmerge-high.png"), ($dir2."Invoice_backdrop-mailmerge-high.png"));
				$dir3 = (__DIR__."/../user_files/$file_directory/backgrounds/templates/png/Invoice_backdrop-mailmerge-high/");
				mkdir($dir3, 0700, true);
				symlink((__DIR__."/../newuser_examples/backgrounds/templates/png/Invoice_backdrop-mailmerge-high/Invoice_backdrop-mailmerge-high.png"), ($dir3."Invoice_backdrop-mailmerge-high.png"));
				$dir4 = (__DIR__."/../user_files/$file_directory/backgrounds/thumbnails/png/Invoice_backdrop-mailmerge-high/");
				mkdir($dir4, 0700, true);
				symlink((__DIR__."/../newuser_examples/backgrounds/thumbnails/png/Invoice_backdrop-mailmerge-high/Invoice_backdrop-mailmerge-high.png"), ($dir4."Invoice_backdrop-mailmerge-high.png"));
				
				//Insert it into the database.
				$query = "INSERT INTO backgrounds (created_at, updated_at, user_id, name, data_path) 
						  VALUES (?, NOW(), ?, ?, ?)";
						  
				$stmt     = $this->sql->link->prepare($query);
				$name     = "Invoice_backdrop-mailmerge-high.png";
				$dataPath = "/backgrounds/server/user_files/$file_directory/backgrounds/templates/png/Invoice_backdrop-mailmerge-high/";
				$stmt->bind_param('siss', $time, $userID, $name, $dataPath);
				$stmt->execute();				
				$backgroundID1 = $stmt->insert_id; 
				
				$query = "INSERT INTO backgrounds_pages (created_at, updated_at, background_id, background_pg_num, file_name) 
						  VALUES (?, NOW(), ?, '1', ?)";
						  
				$stmt     = $this->sql->link->prepare($query);
				$name     = "Invoice_backdrop-mailmerge-high.png";
				$stmt->bind_param('sis', $time, $backgroundID1, $name);
				$stmt->execute();
				$backgroundPGID1 = $stmt->insert_id; 
								
				//Create the background file link - Example envelope.
				$dir1 = (__DIR__."/../user_files/$file_directory/backgrounds/files/png/example-envelope/");
				mkdir($dir1, 0700, true);
				symlink((__DIR__."/../newuser_examples/backgrounds/files/png/example-envelope/example-envelope.png"), ($dir1."example-envelope.png"));
				$dir2 = (__DIR__."/../user_files/$file_directory/backgrounds/previews/png/example-envelope/");
				mkdir($dir2, 0700, true);
				symlink((__DIR__."/../newuser_examples/backgrounds/previews/png/example-envelope/example-envelope.png"), ($dir2."example-envelope.png"));
				$dir3 = (__DIR__."/../user_files/$file_directory/backgrounds/templates/png/example-envelope/");
				mkdir($dir3, 0700, true);
				symlink((__DIR__."/../newuser_examples/backgrounds/templates/png/example-envelope/example-envelope.png"), ($dir3."example-envelope.png"));
				$dir4 = (__DIR__."/../user_files/$file_directory/backgrounds/thumbnails/png/example-envelope/");
				mkdir($dir4, 0700, true);
				symlink((__DIR__."/../newuser_examples/backgrounds/thumbnails/png/example-envelope/example-envelope.png"), ($dir4."example-envelope.png"));
				
				//Insert it into the database.
				$query = "INSERT INTO backgrounds (created_at, updated_at, user_id, name, data_path) 
						  VALUES (?, NOW(), ?, ?, ?)";
						  
				$stmt     = $this->sql->link->prepare($query);
				$name     = "example-envelope.png";
				$dataPath = "/backgrounds/server/user_files/$file_directory/backgrounds/templates/png/example-envelope/";
				$stmt->bind_param('siss', $time, $userID, $name, $dataPath);
				$stmt->execute();				
				$backgroundID2 = $stmt->insert_id; 
				
				$query = "INSERT INTO backgrounds_pages (created_at, updated_at, background_id, background_pg_num, file_name) 
						  VALUES (?, NOW(), ?, '1', ?)";
						  
				$stmt     = $this->sql->link->prepare($query);
				$name     = "example-envelope.png";
				$stmt->bind_param('sis', $time, $backgroundID2, $name);
				$stmt->execute();
				$backgroundPGID2 = $stmt->insert_id; 
				
				//Create the datasource file copy - Example data.
				$dir1 = (__DIR__."/../user_files/$file_directory/datasources/Example - Customer Invoices 2013/");
				mkdir($dir1, 0700, true);
				copy((__DIR__."/../newuser_examples/datasources/Example - Customer Invoices 2013/Example - Customer Invoices 2013"), ($dir1."Example - Customer Invoices 2013"));
				
				$query = "INSERT INTO datasources (created_at, updated_at, user_id, name, data_path, file_name, headers, `lines`) 
						  VALUES (?, NOW(), ?, ?, ?, ?, ?, 2)";
						  
				$stmt      = $this->sql->link->prepare($query);
				$path      = "/datasources/server/user_files/$file_directory/datasources/Example - Customer Invoices 2013/";
				$name1     = "Example - Business Letter 2014";
				$name2     = "Example - Business Letter 2014";
				$headers   = '["invoiceNum","name","address","suburb","state","postcode","billingPeriod","invoiceIssuedDate","clientNum","billingDetails","thankyouTxt","invoiceAmt","overdueTxt","overdueAmt","GrandTotal"]';
				$stmt->bind_param('sissss', $time, $userID, $name1, $path, $name2, $headers );
				$stmt->execute();
				$datasourceID = $stmt->insert_id; 
				
				//Create a sample document for the new user.
				$query = "INSERT INTO documents (created_at, updated_at, user_id, name, datasource_id) 
						  VALUES (?, NOW(), ?, ?, ?)";
						  
				$stmt     = $this->sql->link->prepare($query);
				$name     = "Example - Business Letter";
				$stmt->bind_param('sisi', $time, $userID, $name, $datasourceID );
				$stmt->execute();
				$documentID = $stmt->insert_id; 
				
				$query = "INSERT INTO documents_pages (created_at, updated_at, document_id, document_pg_num, background_id, background_pg_id, preset, width, height, selected_measurement_unit, variables ) 
						  VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?), (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?)";
				$stmt     = $this->sql->link->prepare($query);
				
				$variables1 = 'a:1:{i:0;a:12:{s:4:"name";s:8:"variable";s:1:"x";s:5:"127.5";s:1:"y";s:3:"294";s:5:"width";s:3:"381";s:6:"height";s:2:"87";s:4:"text";s:44:"<name>
<address>
<suburb> <state> <postcode>";s:11:"font_family";s:5:"Arial";s:9:"font_size";s:2:"16";s:10:"font_style";s:0:"";s:12:"font_padding";s:2:"10";s:10:"font_color";s:12:"rgb(0, 0, 0)";s:10:"font_align";s:1:"L";}}';
				$variables2 = 'a:13:{i:0;a:12:{s:4:"name";s:8:"variable";s:1:"x";s:3:"700";s:1:"y";s:3:"700";s:5:"width";s:3:"200";s:6:"height";s:4:"39.2";s:4:"text";s:12:"<invoiceAmt>";s:11:"font_family";s:5:"Arial";s:9:"font_size";s:2:"16";s:10:"font_style";s:4:"bold";s:12:"font_padding";s:2:"10";s:10:"font_color";s:12:"rgb(0, 0, 0)";s:10:"font_align";s:1:"L";}i:1;a:12:{s:4:"name";s:8:"variable";s:1:"x";s:3:"536";s:1:"y";s:3:"750";s:5:"width";s:3:"161";s:6:"height";s:2:"36";s:4:"text";s:12:"<overdueTxt>";s:11:"font_family";s:5:"Arial";s:9:"font_size";s:2:"16";s:10:"font_style";s:4:"bold";s:12:"font_padding";s:2:"10";s:10:"font_color";s:12:"rgb(0, 0, 0)";s:10:"font_align";s:1:"R";}i:2;a:12:{s:4:"name";s:8:"variable";s:1:"x";s:3:"700";s:1:"y";s:3:"750";s:5:"width";s:3:"201";s:6:"height";s:2:"36";s:4:"text";s:12:"<overdueAmt>";s:11:"font_family";s:5:"Arial";s:9:"font_size";s:2:"16";s:10:"font_style";s:4:"bold";s:12:"font_padding";s:2:"10";s:10:"font_color";s:12:"rgb(0, 0, 0)";s:10:"font_align";s:1:"L";}i:3;a:12:{s:4:"name";s:8:"variable";s:1:"x";s:3:"201";s:1:"y";s:3:"427";s:5:"width";s:3:"229";s:6:"height";s:2:"32";s:4:"text";s:10:"30/11/2012";s:11:"font_family";s:5:"Arial";s:9:"font_size";s:2:"12";s:10:"font_style";s:4:"bold";s:12:"font_padding";s:2:"10";s:10:"font_color";s:12:"rgb(0, 0, 0)";s:10:"font_align";s:1:"L";}i:4;a:12:{s:4:"name";s:8:"variable";s:1:"x";s:3:"201";s:1:"y";s:3:"466";s:5:"width";s:3:"229";s:6:"height";s:2:"32";s:4:"text";s:15:"<billingPeriod>";s:11:"font_family";s:5:"Arial";s:9:"font_size";s:2:"12";s:10:"font_style";s:4:"bold";s:12:"font_padding";s:2:"10";s:10:"font_color";s:12:"rgb(0, 0, 0)";s:10:"font_align";s:1:"L";}i:5;a:12:{s:4:"name";s:8:"variable";s:1:"x";s:3:"201";s:1:"y";s:3:"506";s:5:"width";s:3:"230";s:6:"height";s:2:"32";s:4:"text";s:19:"<invoiceIssuedDate>";s:11:"font_family";s:5:"Arial";s:9:"font_size";s:2:"12";s:10:"font_style";s:4:"bold";s:12:"font_padding";s:2:"10";s:10:"font_color";s:12:"rgb(0, 0, 0)";s:10:"font_align";s:1:"L";}i:6;a:12:{s:4:"name";s:8:"variable";s:1:"x";s:3:"202";s:1:"y";s:3:"546";s:5:"width";s:3:"228";s:6:"height";s:2:"32";s:4:"text";s:11:"<clientNum>";s:11:"font_family";s:5:"Arial";s:9:"font_size";s:2:"12";s:10:"font_style";s:4:"bold";s:12:"font_padding";s:2:"10";s:10:"font_color";s:12:"rgb(0, 0, 0)";s:10:"font_align";s:1:"L";}i:7;a:12:{s:4:"name";s:8:"variable";s:1:"x";s:3:"201";s:1:"y";s:3:"586";s:5:"width";s:3:"228";s:6:"height";s:2:"32";s:4:"text";s:12:"<invoiceNum>";s:11:"font_family";s:5:"Arial";s:9:"font_size";s:2:"12";s:10:"font_style";s:4:"bold";s:12:"font_padding";s:2:"10";s:10:"font_color";s:12:"rgb(0, 0, 0)";s:10:"font_align";s:1:"L";}i:8;a:12:{s:4:"name";s:8:"variable";s:1:"x";s:3:"629";s:1:"y";s:3:"901";s:5:"width";s:3:"276";s:6:"height";s:2:"52";s:4:"text";s:12:"<GrandTotal>";s:11:"font_family";s:5:"Arial";s:9:"font_size";s:2:"32";s:10:"font_style";s:0:"";s:12:"font_padding";s:2:"10";s:10:"font_color";s:12:"rgb(0, 0, 0)";s:10:"font_align";s:1:"L";}i:9;a:12:{s:4:"name";s:8:"variable";s:1:"x";s:2:"46";s:1:"y";s:3:"181";s:5:"width";s:3:"438";s:6:"height";s:2:"49";s:4:"text";s:22:"INVOICE : <invoiceNum>";s:11:"font_family";s:5:"Arial";s:9:"font_size";s:2:"26";s:10:"font_style";s:0:"";s:12:"font_padding";s:2:"10";s:10:"font_color";s:14:"rgb(0, 3, 215)";s:10:"font_align";s:1:"L";}i:10;a:12:{s:4:"name";s:8:"variable";s:1:"x";s:3:"128";s:1:"y";s:3:"299";s:5:"width";s:3:"331";s:6:"height";s:18:"63.199999999999996";s:4:"text";s:50:"<name>
<address>
<suburb>    <state>    <postcode>";s:11:"font_family";s:5:"Arial";s:9:"font_size";s:2:"12";s:10:"font_style";s:0:"";s:12:"font_padding";s:2:"10";s:10:"font_color";s:12:"rgb(0, 0, 0)";s:10:"font_align";s:1:"L";}i:11;a:12:{s:4:"name";s:8:"variable";s:1:"x";s:3:"496";s:1:"y";s:3:"560";s:5:"width";s:3:"384";s:6:"height";s:4:"34.4";s:4:"text";s:13:"<thankyouTxt>";s:11:"font_family";s:5:"Arial";s:9:"font_size";s:2:"12";s:10:"font_style";s:0:"";s:12:"font_padding";s:2:"10";s:10:"font_color";s:12:"rgb(0, 0, 0)";s:10:"font_align";s:1:"R";}i:12;a:12:{s:4:"name";s:8:"variable";s:1:"x";s:3:"501";s:1:"y";s:3:"296";s:5:"width";s:3:"378";s:6:"height";s:18:"41.599999999999994";s:4:"text";s:16:"<billingDetails>";s:11:"font_family";s:5:"Arial";s:9:"font_size";s:2:"16";s:10:"font_style";s:0:"";s:12:"font_padding";s:2:"10";s:10:"font_color";s:12:"rgb(0, 0, 0)";s:10:"font_align";s:1:"R";}}';
				$pgNum  = 1;
				$pgNum2 = 2;
				$empty  = '';
				$pgSize = 'a4';
				$zero   = 0;
				$size1  = 220;
				$size4  = 110;
				$size2  = 210;
				$size3  = 297;
				$stmt->bind_param('siiiissssssiiiisssss',$time, $documentID, $pgNum, $backgroundID2, $backgroundPGID2, $empty, $size1, $size4, $zero, $variables1,   $time, $documentID, $pgNum2, $backgroundID1, $backgroundPGID1, $pgSize, $size2, $size3, $zero, $variables2);
				$stmt->execute();
													
				////////////////////////////////////////////////////////////////////////////////////////////////////////
				// Create the user's welcome messages
				
				$welcomeMessage[0] = "If you need any support simply email our friendly team at <a href=\"mailto:support@rocketmailmerge.com\">support@rocketmailmerge.com</a>";
				$welcomeMessage[1] = "Thanks for signing up to Rocket Mail Merge! This is a help notice, you can click on it to hide it.";
				
				
				$query = "INSERT INTO user_notices (created_at, updated_at, user_id, type, message) 
						  VALUES (?, NOW(), ?, 'msg_Info', ?), (?, NOW(), ?, 'msg_Info', ?)";
						  
				$stmt = $this->sql->link->prepare($query);
				$stmt->bind_param('sissis', $time, $userID, $welcomeMessage[0], $time, $userID, $welcomeMessage[1]);
				
				$resultFromExec = $stmt->execute();
				$affectedRows  += $stmt->affected_rows;	
				
				////////////////////////////////////////////////////////////////////////////////////////////////////////
				// Create their statistics data
				
				$query = "INSERT INTO user_statistics (created_at, updated_at, user_id) 
						  VALUES(?, NOW(), ?)";
						  
				$stmt = $this->sql->link->prepare($query);
				$stmt->bind_param('si', $time, $userID);
				
				$resultFromExec = $stmt->execute();
				$affectedRows  += $stmt->affected_rows;	
				
				////////////////////////////////////////////////////////////////////////////////////////////////////////
				// Create their subscription record.
				
				$query = "INSERT INTO user_subscriptions (created_at, updated_at, user_id, chargebee_id, subscription_plan_id, subscription_status  ) 
						  VALUES(?, NOW(), ?, ?, 'base_rate', 'active')";
						  
				$stmt = $this->sql->link->prepare($query);
				$stmt->bind_param('sis', $time, $userID, $new_user_guid);
				
				$resultFromExec = $stmt->execute();
				$affectedRows  += $stmt->affected_rows;	
				
				//We delegate our chargebee account creation as the curl process can take a few seconds to return the data
				//we don't want new user's to be delayed in account creation.
				if($resultFromExec) {				
					
				}
			}
						
			/* free result */
			$stmt->free_result();			  
			$stmt->close();	
		}		
		
		if($affectedRows > 0 && $userID != "") {	
			$output['success']	= 1; 
			$output['text']		= "Congratulations, your account has been created.";
			$output['return']	= 1;
			$output['data']['user_id'] = $userID;
			$output['parse_curl'] = 1;
			
			$session = new iSession();
			$session->mod_var("logged_in", 1);
			$session->mod_var("user_id", $userID);
			$session->update_session();
			
			//Great success - Redirect the user to their dashboard.
			header('Location: https://app.rocketmailmerge.com/dashboard/?tut=1');
			die();
		} else {
			$output['success']	= 0;
			$output['text']		= "Sorry, that email is already registered to an account. Please choose another.";
			$output['rex']		= "Grrr";
			$output['return']	= 1;
			
			$this->sendTechEmail("support@rocketmailmerge.com", "Subscriber: Account already registered", "A user tried to register and account but their email was already registered -----\n".print_r($inData,1));
			header("location:https://".$_SERVER['SERVER_NAME']."/account/register.html?r=1"); //Account already taken.
			die();
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
		//echo "<pre>";
		//print_r(($this->outData));	
	}

}

$server = new dataServer(true);

if(isset($_POST["rex"])) {
	
	$inData		= $server->getCleanPost();
	
	switch($inData["rex"]) {
		
		//1 - Fetch document information & subsequent page data.
		case "woof" : $server->createNewSubscribedUser($inData); break;		
		//case 2 : $server->parse_curl_requests(); break;
		//Default Action.
		default : break;
	}
} else {
	$server->outputResults();
}
?>
