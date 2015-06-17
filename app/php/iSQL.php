<?php

/*************************************************************************
 *
 * CLASS: mysqli_enhanced - extends mysqli
 *
 * Description : Added multi bind functionality. 
 *               http://www.php.net/manual/en/mysqli-stmt.bind-param.php#110363
 *
 ************************************************************************/

class mysqli_enhanced extends mysqli {
    public function prepare($query) {
        return new mysqli_enhanced_stmt($this,$query);
    }
}

class mysqli_enhanced_stmt extends mysqli_stmt {
    public function __construct($link, $query) {
        $this->mbind_reset();
        parent::__construct($link, $query);
    }

    public function mbind_reset() {
        unset($this->mbind_params);
        unset($this->mbind_types);
        $this->mbind_params = array();
        $this->mbind_types = array();
    }
    
    //use this one to bind params by reference
    public function mbind_param($type, &$param) {
        @$this->mbind_types[0].= $type;
        $this->mbind_params[] = &$param;
    }
    
    //use this one to bin value directly, can be mixed with mbind_param()
    public function mbind_value($type, $param) {
        @$this->mbind_types[0].= $type;
        $this->mbind_params[] = $param;
    }
    
    
    public function mbind_param_do() {
        $params = array_merge($this->mbind_types, $this->mbind_params);
        return call_user_func_array(array($this, 'bind_param'), $this->makeValuesReferenced($params));
    }
    
    private function makeValuesReferenced($arr){
        $refs = array();
        foreach($arr as $key => $value)
        $refs[$key] = &$arr[$key];
        return $refs;

    }
    
    public function execute() {
        if(count($this->mbind_params))
            $this->mbind_param_do();
            
        return parent::execute();
    }
    
    private $mbind_types = array();
    private $mbind_params = array();
}

/*************************************************************************
 *
 * CLASS: iSQL
 *
 * Description: SQL container
 *
 *************************************************************************/

class iSQL
{

var $link = false;
var $host, $login, $database;
var $queries = 0;
var $qlog = array();

var $opt_log = false;	// log queries


/*********************************************
 *              iSQL Constructor
 *********************************************/

function iSQL ()
{	
	require ("dbinfo.php");
	
	$this->host		= $host;
	$this->login	= $username;
	$this->database	= $database;

	$this->link = new mysqli_enhanced($this->host, $this->login, $password, $this->database);

	if ($this->link->connect_error) {
		die('<B>SQL Engine Initialization Error:</B> (' . $this->link->connect_errno . ') '. $this->link->connect_error);
	}
}



/*********************************************
 *                   close
 *********************************************/

function close ()
{
	if ($this->link)
	{
		/* determine our thread id */
		$thread_id = $this->link->thread_id;

		/* Kill connection */
		$this->link->kill($thread_id);
		
		/* Close the SQL link */
		$this->link->close();
	}
}

/*********************************************
 *                  query
 *********************************************/

function query ($query, $nr=0, $count=0, $store=1)
{
	global $session, $error, $document, $config, $page;

	if ($config['debug']=='on')
	{
		$itime = new iChrono();
	}

	// start from $nr and return $count result
		
	if ($this->link->ping())
	{
		//echo "SQL = $query<br/>";
		
		//Record the query for debugging.
		//if($store)
		//	$this->query('insert into queries (user_id,query_string) values('.$session->sid.',"'.$query.'")', 0, 0, 0);
		
		$i = 0;
		$res = array();
		$ri = 0;
		
		// trim extra spaces		
		$query = trim($query);

		$this->queries++;

		// run the query
		$result = $this->link->query($query);

		// queries log selected, so log them		
		if ($this->opt_log)
		{
			$this->qlog[] = $query;
		}

		// query failed		
		if (!$result)
		{
			echo "<B>SQL Error</B>: ".$this->link->error.".";			
			return false;
			
		} else if($result) {

			// return the result in an array if possible
			
			if (preg_match ("/^((SELECT)|(SHOW)|(DESCRIBE)|(EXPLAIN)|(OPTIMIZE)|(CHECK)|(ANALYZE)|(REPAIR))/i", $query)) {
				while ($row = $result->fetch_assoc())
				{
					if ($i>=$nr)
					{
						if ($count==0)
						{
							$res[$ri++]=$row;
						}
						else
						{
							if ($i<($nr+$count))
							{
								$res[$ri++]=$row;
							}
						}
					}
					$i++;
				}
			}
			else
				$res = $result;
			
			// Free result set			
			//$result->free();
			if($this->link->more_results()) 
				$this->link->next_result();
			
			return $res;
		} 
	} else 
		return false;
		
}

/*********************************************
 *                  prepare
 *********************************************/
/*
function prepare ($query)
{
	global $session, $error, $document, $config, $page;

	if ($config['debug']=='on')
	{
		$itime = new iChrono();
	}

	// start from $nr and return $count result
		
	if ($this->link->ping())
	{
		//Record the query for debugging.
		//if($store)
		//	$this->query('insert into queries (user_id,query_string) values('.$session->sid.',"'.$query.'")', 0, 0, 0);
		
		$i = 0;
		$res = array();
		$ri = 0;
		
		// trim extra spaces		
		$query = trim($query);

		$this->queries++;

		// run the query
		$result = $this->link->prepare($query);

		// queries log selected, so log them		
		if ($this->opt_log)
		{
			$this->qlog[] = $query;
		}

		// query failed		
		if (!$result)
		{
			echo "<B>SQL Error</B>: ".$this->link->error.".";			
			return false;
			
			} else if($result) {

			$res = $result;
			
			return $res;
		} 
	} else 
		return false;
		
}
*/

/*********************************************
 *                connected
 *********************************************/

function connected ()
{
	return (@$this->link->ping()) ? true : false;
}

/*********************************************
 *               queries_used
 *********************************************/

function queries_used()
{
	return $this->queries;
}

/*********************************************
 *              connection_info
 *********************************************/

function connection_info ()
{
	return array (
		"connected"	=> ($this->link->ping()) ? true : false,
		"host"		=> $this->host,
		"login"		=> $this->login,
		"database"	=> $this->database,
		"queries"	=> $this->queries

		);
}

}
?>