<?php
require ("iSQL.php");
include ("iSession.php");

$sql = new iSQL();

session_start();
$session = null;

//If the session is invalid, quit.
function initiateSession($recurs = 0) {
	
	global $session;
	
	if(isset($_SESSION['session'])) {
			$session = unserialize($_SESSION['session']);
		
			//If the unserialize failed, $session will be false;
			if($session === false)
				return 1;
			
			$session->update_session();
									
			if( !$session->session_exists($session->sid) || $session->get_var("logged_in") != 1){
				return 1;
			} else { //Our session is valid, and logged in == 1
				return 0;				
			}
	}
	else {	
	
		if(!$recurs) {
			$session = new iSession();
			return initiateSession(1);
		} else 
			return 1; 
		
	}
	
	return 1;
}
 

//Start out session, or update it if it exists.
$noAccess = initiateSession(0);

if($noAccess || $session == null) {
		$session->session_end();
		header('Location: https://app.rocketmailmerge.com/account/login.html');
        die();
		/*echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
        echo '<html>';
        echo '<head>';
        echo '<title>Session expired :(</title>';
        echo '<meta http-equiv="REFRESH" content="3;url=https://app.rocketmailmerge.com/account/login.html"></HEAD>';
        echo '<BODY>';
        echo 'Sorry, you\'re not currently logged in. Please wait and you will be redirected.';
        echo '</BODY>';
        echo '</HTML>';
        die();*/
} 




/*echo "<img height=300 src='http://3.bp.blogspot.com/-lsvyrQoiJkA/T089lLbN6rI/AAAAAAAAClo/Pg-T5u7JdVM/s1600/a_winner_is_you_1024.jpeg'/>"; */
/*echo "<pre>";
print_r($session->read_session());


//<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
*/
?>