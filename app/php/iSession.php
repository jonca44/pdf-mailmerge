<?php

/*************************************************************************
 *
 * CLASS: iSession
 *
 * Description: Session handling
 *
 *************************************************************************/

class iSession
{
// configuration variables (private)
var $time2live = 604800; //1 week in seconds.

// private class variables
var $sid, $session_info, $changed = false;

// class functions

/*********************************************
 *            iSession Constructor
 *********************************************/
function iSession()
{
	@session_start();
	$in_sid = 0;

	if (isset($_COOKIE['sid']) && $this->valid_sid($this->clean_input($_COOKIE['sid']))) {
		$in_sid = $this->clean_input($_COOKIE['sid']);
	}

	$this->sid = $this->sessionClass_start($in_sid);
	
	@setcookie("sid", $this->sid, time() + $this->time2live, '/');

	$this->session_info = $this->read_session();
	$this->user_info	= $this->read_user_info();

	if (!isset($_SESSION['session']))
	{
		session_regenerate_id();
		$_SESSION['session'] = serialize($this);
	}

	$_SESSION['session'] = serialize($this);
}

/*********************************************
 *              $this->clean_input
 *********************************************/
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
    //Escape the string for insertion into a MySQL query, and return it.
    return $sql->link->real_escape_string($input);
}

function generateGuid($include_braces = false) {
    if (function_exists('com_create_guid')) {
        if ($include_braces === true) {
            return com_create_guid();
        } else {
            return substr(com_create_guid(), 1, 36);
        }
    } else {
        mt_srand((double) microtime() * 10000);
        $charid = strtoupper(md5(uniqid(rand(), true)));
       
        $guid = substr($charid,  0, 8) . '-' .
                substr($charid,  8, 4) . '-' .
                substr($charid, 12, 4) . '-' .
                substr($charid, 16, 4) . '-' .
                substr($charid, 20, 12);
 
        if ($include_braces) {
            $guid = '{' . $guid . '}';
        }
   
        return $guid;
    }
}

/*********************************************
 *              valid_sid
 *********************************************/
function valid_sid ($sid)
{
	$sid = trim($sid);

	if (empty($sid)) {
		return false;
	}

	//We use windows type GUIDs
	//Style = 'A98C5A1E-A742-4808-96FA-6F409E799937'
	if (preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/', $sid)) {
		return true;
	} else {
		return false;
	}

	//should never reach this point.
	return false;
}


/*********************************************
 *              sessionClass_start
 *********************************************/
function sessionClass_start ($in_sid = 0)
{
	$user_ip = $this->clean_input($_SERVER['REMOTE_ADDR']);

	$user_agent = substr($this->clean_input($_SERVER['HTTP_USER_AGENT']),0,255);

	$this->cleanup_sessions();
	
	// Try to reuse existing session if we couldn't find their php session as it was cleaned up.
	// Lookup their session id if they had one set via cookies. Also check their user agent is a match.
	if($in_sid !== 0) {

		$sq = "SELECT ses_id, user_ip, user_agent
			   FROM `sessions`
			   WHERE ses_id='".$in_sid."'
			   LIMIT 0, 1";
			   
		$sesinfo = $this->query($sq);
		
		if (!empty($sesinfo[0]))
		{
			if ($sesinfo[0]['user_agent']==$user_agent)
			{
				// the session exists and we have the same agent so we accept the session id
				$this->sid = $in_sid;
				return $in_sid;
			}
		}		
	}

	// no match so we have to create a new session

	$this->sid = $this->create_session();
	return $this->sid;
}


/*********************************************
 *              cleanup_sessions
 *********************************************/
function cleanup_sessions ()
{

$time2live = time() - $this->time2live;

$sq = "DELETE FROM `sessions`
       WHERE last_time < ".$time2live;
  
 
$this->query($sq);
}


/*********************************************
 *                 query
 *********************************************/
function query ($sq)
{
global $sql;
return $sql->query($sq);
}


/*********************************************
 *              create_session
 *********************************************/
function create_session ()
{
$tm = time();
$user_ip = $this->clean_input($_SERVER['REMOTE_ADDR']);
$user_agent = substr($this->clean_input($_SERVER['HTTP_USER_AGENT']),0,255);

// generate a new session id

do
{
    $sid = $this->generateRandomGuid(false);
} while ($this->session_exists($sid));

$sq = "INSERT INTO `sessions`
    (ses_id, created_at, updated_at, start_time, last_time, user_ip, user_agent)
    VALUES
    ('".$sid."', NOW(), NOW(), '".$tm."', '".$tm."', '".$user_ip."','".$user_agent."')";
$this->query($sq);

return $sid;
}


/*********************************************
 *              session_exists
 *********************************************/
function session_exists ($sid)
{
//Clean up expired sessions.
$this->cleanup_sessions();

//Fetch user variables.
$user_ip    = $this->clean_input($_SERVER['REMOTE_ADDR']);
$user_agent = substr($this->clean_input($_SERVER['HTTP_USER_AGENT']),0,255);
$sid = $this->clean_input($sid);

//Locate the session in the Database to match to the user's details.
$sq = "SELECT ses_id, user_ip, user_agent 
    FROM `sessions`
    WHERE ses_id='".$sid."'
    LIMIT 0, 1";
$sesinfo = $this->query($sq);

if (!isset($sesinfo[0]['ses_id']))
{
    return false;
}

//Verify the session data matches the user trying to use this session.
if ($sesinfo[0]['user_agent']==$user_agent && $sesinfo[0]['ses_id']==$sid)
	return true;
else
	return false;
}


/*********************************************
 *              Generate a random GuID
 *********************************************/
function generateRandomGuid($include_braces = false) {
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


/*********************************************
 *              session_end
 *********************************************/
function session_end ()
{
	$sq = "DELETE FROM `sessions`
		   WHERE ses_id = '".$this->sid."'";
	$this->query($sq);
	
	$this->sid = $this->session_info = $this->changed = false;
	$params = session_get_cookie_params();
    @setcookie("sid", '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
	
	@session_unset(); 
	@session_destroy(); 
	
	return true;
}

/*********************************************
 *              update_session
 *********************************************/
function update_session()
{
	$param = "";
	
	//A cleanup is performed in session_exists.
	if(!$this->session_exists($this->sid))
		$this->session_end();
	else {
		
		if ($_COOKIE["sid"] == "") {
			@setcookie ("sid", $this->sid, time() + $this->time2live, '/');
		}
	
		if ($this->changed)	{
			$param = "";
			$this->changed = false;
		 
			reset ($this->session_info);
			while (list ($key, $val) = each ($this->session_info))
			{
				// skip the id field and last_time

				if ($key!="id" && $key!="last_time")
				{
					$param .= ", ".$key."='".$val."'";
				}
			}

			$_SESSION['session'] = serialize($this);
		}
		
		$sq = "UPDATE `sessions`
			   SET last_time='".time()."'".$param.",
			   updated_at = NOW()
			   WHERE ses_id='".$this->sid."'
			   LIMIT 1";
			   
		$this->query($sq);
		
		//We regenerate the session id after every 25 requests. This mitigates against session fixation attacks.
		if (++$_SESSION['lastRegeneration'] > 25) {
				$_SESSION['lastRegeneration'] = 0;
				session_regenerate_id();
				
				//Also generate a new SID for this session, it'll be transmitted to the client via the cookie.
				$newSID = $this->generateRandomGuid(false);				
				
				$sq = "UPDATE `sessions`
				   SET ses_id='".$newSID."' 
				   WHERE ses_id='".$this->sid."'
				   LIMIT 1";
				   
				$this->query($sq);
				
				$this->sid = $newSID;
		}
		
		@setcookie("sid", $this->sid, time() + $this->time2live, '/');

		$this->session_info = $this->read_session();
		$this->user_info	= $this->read_user_info();
		
		$_SESSION['session'] = serialize($this);

		if (!isset($_SESSION['session']))
		{
			session_regenerate_id();
			$_SESSION['session'] = serialize($this);;
		}		
		
	}
}


/********************************
*   Serialise the session for javascript storage.
*********************************/
function print_session()
{
	return serialize($this);
}


/*********************************************
 *              read_session
 *********************************************/
function read_session ()
{
$sq = "SELECT *
       FROM `sessions`
       WHERE ses_id='".$this->sid."'
       LIMIT 0, 1";
$sesinfo = $this->query($sq);

return $sesinfo[0];
}

/*********************************************
 *              read_user_info
 *********************************************/
function read_user_info ()
{
	
	$user_id = $this->clean_input($this->get_var('user_id'));
	
	if($user_id != "" && is_integer($user_id+0)) {
		$sq = "SELECT id, username, file_directory
			   FROM `users`
			   WHERE id='".$user_id."'
			   LIMIT 0, 1";
			   
		$userinfo = $this->query($sq);
		
		return @$userinfo[0];
	}
	
	return array();
	
	
}

/*********************************************
 *              get_var
 *********************************************/
function get_var ($var)
{
	return $this->session_info[$var];
}


/*********************************************
 *	get_user_var - return a variable from the logged in user.
 *********************************************/
function get_user_var ($var)
{
	return $this->user_info[$var];
}

/*********************************************
 *              mod_var
 *********************************************/
function mod_var ($var, $val, $immediate = false)
{
	$this->changed = true;
	$this->session_info[$var] = $val;
	
	//Each time data associated with the session is changed we should regenerate the session ID.
	session_regenerate_id();
	
	if ($immediate)
	{
		$this->update_session();
	}
}


}
?>