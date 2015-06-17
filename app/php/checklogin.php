<?php
	//ob_start(); //oUTPUT BUFFERING.
	
	require ("iSQL.php");
	require ("PasswordHash.php");
	include ("iSession.php");
	
	
	$sql = new iSQL();
	$pwdHasher = new PasswordHash(8, FALSE);

	function clean_input($input)
	{
		global $sql;
		
		if(get_magic_quotes_gpc())
		{
			//Remove slashes that were used to escape characters in post.
			$input = stripslashes($input);
		}
		//Remove ALL HTML tags to prevent XSS and abuse of the system.
		$input = strip_tags($input);
		//Escape the string for INSERTion into a MySQL query, and return it.
		return $sql->link->real_escape_string($input);
	}
	
	///////////////////////////////////
	// Logic Code
	///////////////////////////////////
	
	$user_ip = clean_input($_SERVER['REMOTE_ADDR']);
	$user_agent = substr(clean_input($_SERVER['HTTP_USER_AGENT']),0,255);

	// username and password sent from form 
	$myusername=isset($_POST['username']) ? clean_input($_POST['username']) : ""; 
	$mypassword=isset($_POST['password']) ? clean_input($_POST['password']) : "";

	//Leon 5-9-12
        //Check if passwords are <= 72 chars. Any longer could result in DOS attacks on our hasher.
        if(strlen($mypassword) > 72) {
		header("location:https://".$_SERVER['SERVER_NAME']."/app/?pw=0"); //incorrect pass
        }


	$query="SELECT * FROM users WHERE username='$myusername' limit 0,1";
	$result = $sql->link->query($query);
	
	if (!$result) {  
	  //die('Invalid query: ' . $sql->link->error);
		die("Invalid login attempt");
	} 

	// Mysql_num_row is counting table row
	$count= $result->num_rows;
	// If result matched $myusername and $mypassword, table row must be 1 row

	$row = $result->fetch_assoc();
	$permissions = unserialize(trim($row['permissions']));

	if($result->num_rows == 0 ) {//No account found.
		header("location:https://".$_SERVER['SERVER_NAME']."/account/login.html?pw=0"); //incorrect pass
		return 1;
	}

	if($row['account_enabled'] == 0) {	
		header("location:https://".$_SERVER['SERVER_NAME']."/account/login.html?pw=1"); //account locked.
		return 1;
	}

	// Base-2 logarithm of the iteration count used for password stretching
	$hash_cost_log2 = 8;
	// Do we require the hashes to be portable to older systems (less secure)?
	$hash_portable = FALSE; 

	$hasher = new PasswordHash($hash_cost_log2, $hash_portable);

	if($pwdHasher->CheckPassword($mypassword, $row['password_hash'])){
		// Register $myusername, $mypassword and redirect to file "login_success.php"
		$session = new iSession();
		$session->mod_var("logged_in", 1);
		$session->mod_var("user_id", $row['id']);
		$session->update_session();
		$result->close();
		
		$query="INSERT into login_attempts(created_at, ip,user_agent,username,successful_login, successful_user_id) values (UTC_TIMESTAMP(), '$user_ip', '$user_agent', '$myusername', true, '".$row['id']."')";
		$result = $sql->link->query($query);
		
		if (!$result) {  
		  die('Invalid query: ' . $sql->link->error);
		} 

		$query="UPDATE user_statistics set billing_cycle_logins = billing_cycle_logins+1 where user_id = '".$row['id']."'";
		$result = $sql->link->query($query);
		
		if (!$result) {  
		  die('Invalid query: ' . $sql->link->error);
		} 
		
		header("location:https://".$_SERVER['SERVER_NAME']."/dashboard"); //Correct pass
	}
	else {
		$result->close();		
		
		$query="INSERT into login_attempts(created_at, ip,user_agent,username,successful_login,successful_user_id) values (UTC_TIMESTAMP(), '$user_ip', '$user_agent', '$myusername', false, null)";
		$result = $sql->link->query($query);
		if (!$result) {  
		  die('Invalid query: ' . $sql->link->error);
		} 
		
		header("location:https://".$_SERVER['SERVER_NAME']."/account/login.html?pw=0"); //Incorrect pass
		return 1;
	}
	
	//$sql->close();
	//ob_end_flush(); //Flush output buffer.
?>
